<?php

namespace App\Console\Commands;

use App\Models\CFTCReport;
use App\Models\Instrument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class ImportCftcData extends Command
{
    protected $signature = 'cftc:import-all';
    protected $description = 'Unzip all CFTC archives and load Disaggregated & Financial data into cftc_reports';

    public function handle()
    {
        $baseStorage = storage_path('app' . DIRECTORY_SEPARATOR . 'cftc_historical');
        $tempBase    = storage_path('app' . DIRECTORY_SEPARATOR . 'tmp_cftc');

        // 1) Gather all ZIP archives
        $allZips = glob($baseStorage . DIRECTORY_SEPARATOR . '*.zip');
        if (empty($allZips)) {
            $this->info("No .zip files found in {$baseStorage}.");
            return 0;
        }

        // 2) Split into Disaggregated vs Financial by filename (including the 2006_2016 fin_fut_txt case)
        $disaggZips = array_filter($allZips, fn($path) =>
            str_contains(basename($path), 'disagg')
        );
        $finZips = array_filter($allZips, function($path) {
            $name = basename($path);
            return str_contains($name, 'fut_fin_txt')
                || str_contains($name, 'fin_fut_txt');
        });

        // 3) Ensure tmp folder exists
        if (! file_exists($tempBase)) {
            mkdir($tempBase, 0755, true);
        }

        // 4) Process Disaggregated first, then Financial
        foreach (array_merge($disaggZips, $finZips) as $zipPath) {
            $zipFilename = basename($zipPath);
            $this->info("Processing archive: {$zipFilename}");

            $tmpDir = $tempBase . DIRECTORY_SEPARATOR . pathinfo($zipFilename, PATHINFO_FILENAME);
            if (! file_exists($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($tmpDir);
                $zip->close();
            } else {
                $this->error("  ✗ Failed to open {$zipFilename} – skipping.");
                continue;
            }

            $txtFiles = glob($tmpDir . DIRECTORY_SEPARATOR . '*.txt');
            if (empty($txtFiles)) {
                $this->warn("  ! No .txt files found in {$zipFilename}; skipping.");
                $this->rrmdir($tmpDir);
                continue;
            }

            foreach ($txtFiles as $txtPath) {
                $this->info("  → Parsing " . basename($txtPath));
                $this->parseCsvFile($txtPath);
            }

            $this->rrmdir($tmpDir);
            $this->info("  ✓ Finished processing {$zipFilename}");
        }

        $this->info('All archives processed.');
        return 0;
    }

    protected function parseCsvFile(string $filepath): void
    {
        // Detect delimiter from first non-empty line
        $fp = fopen($filepath, 'r');
        if (! $fp) {
            $this->error("    ✗ Cannot open {$filepath}");
            return;
        }
        $firstLine = '';
        while (($line = fgets($fp)) !== false) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $firstLine = $trimmed;
                break;
            }
        }
        fclose($fp);

        $delimiter = strpos($firstLine, "\t") !== false ? "\t" : ",";

        $handle = fopen($filepath, 'r');
        if (! $handle) {
            $this->error("    ✗ Cannot re-open {$filepath}");
            return;
        }

        // Build columns map
        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (! $headerRow) {
            fclose($handle);
            $this->error("    ✗ Empty or unreadable header in {$filepath}");
            return;
        }
        $columns = [];
        foreach ($headerRow as $idx => $rawName) {
            $columns[$idx] = trim($rawName, " \t\n\r\0\x0B\"'");
        }

        // Required indexes
        $idxMarketName   = $this->findExactColumn($columns, 'Market_and_Exchange_Names');
        $idxOpenInterest = $this->findExactColumn($columns, 'Open_Interest_All')
                         ?? $this->findExactColumn($columns, 'Open Interest');

        $idxDateYYYY   = $this->findExactColumn($columns, 'As_of_Date_In_Form_YYYY-MM-DD');
        $idxDateYYMMDD = $this->findExactColumn($columns, 'As_of_Date_In_Form_YYMMDD');
        $idxReportDate = $this->findExactColumn($columns, 'Report_Date_as_YYYY-MM-DD');

        if ($idxMarketName === null
            || $idxOpenInterest === null
            || ($idxDateYYYY === null && $idxDateYYMMDD === null && $idxReportDate === null)
        ) {
            $this->warn("    ! Missing Market, Date or Open Interest in {$filepath}; skipping.");
            fclose($handle);
            return;
        }

        // Disaggregated headers mapping
        $idxProducerLong  = $this->findSubstringColumn($columns, ['Prod_Merc','Long']);
        $idxProducerShort = $this->findSubstringColumn($columns, ['Prod_Merc','Short']);
        $idxSwapLong      = $this->findSubstringColumn($columns, ['swap','long']);
        $idxSwapShort     = $this->findSubstringColumn($columns, ['swap','short']);
        $idxManagedLong   = $this->findSubstringColumn($columns, ['M_Money','Long']);
        $idxManagedShort  = $this->findSubstringColumn($columns, ['M_Money','Short']);
        $idxOtherLong     = $this->findSubstringColumn($columns, ['Other_Rept','Long']);
        $idxOtherShort    = $this->findSubstringColumn($columns, ['Other_Rept','Short']);
        $idxNonrepLong    = $this->findSubstringColumn($columns, ['NonRept','Long']);
        $idxNonrepShort   = $this->findSubstringColumn($columns, ['NonRept','Short']);

        $isDisaggregated = ($idxProducerLong !== null && $idxProducerShort !== null);
        $isFinancial     = $this->findExactColumn($columns, 'Dealer_Positions_Long_All') !== null;

        $rowsInserted = 0;
        $rowsSkipped  = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Market name
            $marketNameRaw = trim($data[$idxMarketName], "\"' ");

            // Parse report_date
            $reportDate = null;
            if ($idxDateYYYY !== null) {
                try {
                    $reportDate = Carbon::createFromFormat('Y-m-d', trim($data[$idxDateYYYY]))->toDateString();
                } catch (\Exception $e) {}
            }
            if (! $reportDate && $idxDateYYMMDD !== null) {
                try {
                    $reportDate = Carbon::createFromFormat('ymd', trim($data[$idxDateYYMMDD]))->toDateString();
                } catch (\Exception $e) {}
            }
            if (! $reportDate && $idxReportDate !== null) {
                try {
                    $reportDate = Carbon::createFromFormat('Y-m-d', trim($data[$idxReportDate]))->toDateString();
                } catch (\Exception $e) {}
            }
            if (! $reportDate) {
                $rowsSkipped++;
                continue;
            }

            // Find instrument
            $instrument = Instrument::where('cftc_name_scrapping', $marketNameRaw)->first();
            if (! $instrument) {
                $rowsSkipped++;
                continue;
            }

            // Skip duplicates
            if (CFTCReport::where('instrument_id', $instrument->instrument_id)
                          ->where('report_date', $reportDate)
                          ->exists()
            ) {
                $rowsSkipped++;
                continue;
            }

            // Base row
            $row = [
                'instrument_id'       => $instrument->instrument_id,
                'report_date'         => $reportDate,
                'producer_long'       => null,
                'producer_short'      => null,
                'swap_long'           => null,
                'swap_short'          => null,
                'managed_long'        => null,
                'managed_short'       => null,
                'otherreport_long'    => null,
                'otherreport_short'   => null,
                'nonreportable_long'  => null,
                'nonreportable_short' => null,
                'open_interest'       => intval(str_replace(',', '', $data[$idxOpenInterest] ?? '0')),
            ];

            // Fill Disaggregated
            if ($isDisaggregated) {
                if ($idxProducerLong  !== null) $row['producer_long']      = intval(str_replace(',', '', $data[$idxProducerLong]  ?? '0'));
                if ($idxProducerShort !== null) $row['producer_short']     = intval(str_replace(',', '', $data[$idxProducerShort] ?? '0'));
                if ($idxSwapLong      !== null) $row['swap_long']          = intval(str_replace(',', '', $data[$idxSwapLong]      ?? '0'));
                if ($idxSwapShort     !== null) $row['swap_short']         = intval(str_replace(',', '', $data[$idxSwapShort]     ?? '0'));
                if ($idxManagedLong   !== null) $row['managed_long']       = intval(str_replace(',', '', $data[$idxManagedLong]   ?? '0'));
                if ($idxManagedShort  !== null) $row['managed_short']      = intval(str_replace(',', '', $data[$idxManagedShort]  ?? '0'));
                if ($idxOtherLong     !== null) $row['otherreport_long']   = intval(str_replace(',', '', $data[$idxOtherLong]     ?? '0'));
                if ($idxOtherShort    !== null) $row['otherreport_short']  = intval(str_replace(',', '', $data[$idxOtherShort]    ?? '0'));
                if ($idxNonrepLong    !== null) $row['nonreportable_long'] = intval(str_replace(',', '', $data[$idxNonrepLong]    ?? '0'));
                if ($idxNonrepShort   !== null) $row['nonreportable_short']= intval(str_replace(',', '', $data[$idxNonrepShort]   ?? '0'));
            }

            // Fill Financial (if no Disagg)
            if ($isFinancial && ! $isDisaggregated) {
                // Dealer → swap
                $idl = $this->findExactColumn($columns, 'Dealer_Positions_Long_All');
                $ids = $this->findExactColumn($columns, 'Dealer_Positions_Short_All');
                if ($idl !== null) $row['swap_long']  = intval(str_replace(',', '', $data[$idl] ?? '0'));
                if ($ids !== null) $row['swap_short'] = intval(str_replace(',', '', $data[$ids] ?? '0'));

                // Managed = Asset_Mgr + Lev_Money
                $amL = intval(str_replace(',', '', $data[$this->findExactColumn($columns, 'Asset_Mgr_Positions_Long_All')]  ?? '0'));
                $amS = intval(str_replace(',', '', $data[$this->findExactColumn($columns, 'Asset_Mgr_Positions_Short_All')] ?? '0'));
                $lvL = intval(str_replace(',', '', $data[$this->findExactColumn($columns, 'Lev_Money_Positions_Long_All')]   ?? '0'));
                $lvS = intval(str_replace(',', '', $data[$this->findExactColumn($columns, 'Lev_Money_Positions_Short_All')]  ?? '0'));
                $row['managed_long']  = $amL + $lvL;
                $row['managed_short'] = $amS + $lvS;

                // Other reportables
                $oL = $this->findExactColumn($columns, 'Other_Rept_Positions_Long_All');
                $oS = $this->findExactColumn($columns, 'Other_Rept_Positions_Short_All');
                if ($oL !== null) $row['otherreport_long']  = intval(str_replace(',', '', $data[$oL] ?? '0'));
                if ($oS !== null) $row['otherreport_short'] = intval(str_replace(',', '', $data[$oS] ?? '0'));

                // Nonreportable
                $nL = $this->findExactColumn($columns, 'NonRept_Positions_Long_All');
                $nS = $this->findExactColumn($columns, 'NonRept_Positions_Short_All');
                if ($nL !== null) $row['nonreportable_long']  = intval(str_replace(',', '', $data[$nL] ?? '0'));
                if ($nS !== null) $row['nonreportable_short'] = intval(str_replace(',', '', $data[$nS] ?? '0'));
            }

            try {
                CFTCReport::create($row);
                $rowsInserted++;
            } catch (\Exception $e) {
                $this->error("    ✗ Error inserting {$marketNameRaw} @ {$reportDate}: " . $e->getMessage());
            }
        }

        fclose($handle);
        $this->info("    ✔ Inserted: {$rowsInserted}, Skipped: {$rowsSkipped}");
    }

    protected function findExactColumn(array $columns, string $name): ?int
    {
        foreach ($columns as $idx => $col) {
            if ($col === $name) {
                return $idx;
            }
        }
        return null;
    }

    protected function findSubstringColumn(array $columns, array $subs): ?int
    {
        foreach ($columns as $idx => $col) {
            $lower = mb_strtolower($col);
            $all   = true;
            foreach ($subs as $s) {
                if (mb_stripos($lower, mb_strtolower($s)) === false) {
                    $all = false;
                    break;
                }
            }
            if ($all) {
                return $idx;
            }
        }
        return null;
    }

    /**
     * Recursively remove a directory and all its contents.
     *
     * @param string $dir The directory path to remove.
     */
    protected function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $obj) {
            if ($obj === '.' || $obj === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $obj;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
