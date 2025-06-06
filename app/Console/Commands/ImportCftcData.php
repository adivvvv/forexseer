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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cftc:import-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unzip all CFTC archives and load both Disaggregated & Financial Futures data into cftc_reports';

    public function handle()
    {
        $baseStorage = storage_path('app' . DIRECTORY_SEPARATOR . 'cftc_historical');
        $tempBase    = storage_path('app' . DIRECTORY_SEPARATOR . 'tmp_cftc');

        $zipFiles = glob($baseStorage . DIRECTORY_SEPARATOR . '*.zip');
        if (empty($zipFiles)) {
            $this->info("No .zip files found in {$baseStorage}.");
            return 0;
        }

        // Ensure tmp base folder exists
        if (! file_exists($tempBase)) {
            mkdir($tempBase, 0755, true);
        }

        foreach ($zipFiles as $zipPath) {
            $zipFilename = basename($zipPath);
            $this->info("Processing archive: {$zipFilename}");

            // Create a unique temp folder for this ZIP
            $tmpDir = $tempBase . DIRECTORY_SEPARATOR . pathinfo($zipFilename, PATHINFO_FILENAME);
            if (! file_exists($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            // Unzip into $tmpDir
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($tmpDir);
                $zip->close();
            } else {
                $this->error("  ✗ Failed to open {$zipFilename} – skipping.");
                continue;
            }

            // Find all .txt files in $tmpDir (non‐recursive)
            $txtFiles = glob($tmpDir . DIRECTORY_SEPARATOR . '*.txt');
            if (empty($txtFiles)) {
                $this->warn("  ! No .txt files found in {$zipFilename}; skipping.");
                $this->rrmdir($tmpDir);
                continue;
            }

            // If this archive contains exactly one "FinFutYY.txt", treat it as the single financial file
            if (count($txtFiles) === 1 && stripos($txtFiles[0], 'FinFut') !== false) {
                $this->info("  → Detected single financial‐futures file: " . basename($txtFiles[0]));
                $this->parseFinancialFile($txtFiles[0]);
            } else {
                // Otherwise, parse each file as a Disaggregated (tab‐delimited or comma‐delimited) file
                foreach ($txtFiles as $txtPath) {
                    $this->info("  → Parsing " . basename($txtPath));
                    $this->parseDisaggregatedOrFinancialTxt($txtPath);
                }
            }

            // Cleanup this tmpDir
            $this->rrmdir($tmpDir);
            $this->info("  ✓ Finished processing {$zipFilename}");
        }

        $this->info('All archives processed.');
        return 0;
    }

    /**
     * Parses a Disaggregated‐style or Financial‐style CSV/TXT (when it is not the big FinFutYY.txt).
     */
    protected function parseDisaggregatedOrFinancialTxt(string $filepath): void
    {
        // Determine delimiter: if first line contains a tab, treat as Disaggregated; else comma
        $fh = fopen($filepath, 'r');
        if (! $fh) {
            $this->error("    ✗ Cannot open {$filepath}");
            return;
        }
        $firstLine = fgets($fh);
        fclose($fh);

        $delimiter = (strpos($firstLine, "\t") !== false) ? "\t" : ",";

        $handle = fopen($filepath, 'r');
        if (! $handle) {
            $this->error("    ✗ Cannot re-open {$filepath}");
            return;
        }

        // Read header row and build map: column name → index
        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (! $headerRow) {
            fclose($handle);
            $this->error("    ✗ Empty or unreadable header in {$filepath}");
            return;
        }
        $colIndex = [];
        foreach ($headerRow as $idx => $colName) {
            $clean = trim($colName, " \t\n\r\0\x0B\"'");
            $colIndex[$clean] = $idx;
        }

        // Determine date column & format
        $dateCol    = null;
        $dateFormat = null;
        if (isset($colIndex['As_of_Date_In_Form_YYYY-MM-DD'])) {
            $dateCol    = 'As_of_Date_In_Form_YYYY-MM-DD';
            $dateFormat = 'Y-m-d';
        } elseif (isset($colIndex['As_of_Date_In_Form_YYMMDD'])) {
            $dateCol    = 'As_of_Date_In_Form_YYMMDD';
            $dateFormat = 'ymd';
        } elseif (isset($colIndex['Report_Date_as_YYYY-MM-DD'])) {
            $dateCol    = 'Report_Date_as_YYYY-MM-DD';
            $dateFormat = 'Y-m-d';
        } else {
            $this->warn("    ! No recognized date column in {$filepath}; skipping.");
            fclose($handle);
            return;
        }

        // Must have Market_and_Exchange_Names and Open_Interest_All
        if (! isset($colIndex['Market_and_Exchange_Names'])) {
            $this->warn("    ! No Market_and_Exchange_Names in {$filepath}; skipping.");
            fclose($handle);
            return;
        }
        if (! isset($colIndex['Open_Interest_All'])) {
            $this->warn("    ! No Open_Interest_All in {$filepath}; skipping.");
            fclose($handle);
            return;
        }

        $openInterestCol = 'Open_Interest_All';

        // Disaggregated‐specific mappings
        $disaggCols = [
            'Producer_Merchant_Processor_User_Long'   => 'producer_long',
            'Producer_Merchant_Processor_User_Short'  => 'producer_short',
            'Swap_Dealer_Long'                        => 'swap_long',
            'Swap_Dealer_Short'                       => 'swap_short',
            'Managed_Money_Long'                      => 'managed_long',
            'Managed_Money_Short'                     => 'managed_short',
            'Other_Reporter_Long'                     => 'otherreport_long',
            'Other_Reporter_Short'                    => 'otherreport_short',
            'Nonreportable_Positions_Long'            => 'nonreportable_long',
            'Nonreportable_Positions_Short'           => 'nonreportable_short',
        ];

        // Financial‐style mappings (in case the file is financial CSV with categories)
        $finCols = [
            'Dealer_Positions_Long_All'        => 'swap_long',
            'Dealer_Positions_Short_All'       => 'swap_short',
            'Lev_Money_Positions_Long_All'     => 'managed_long',
            'Lev_Money_Positions_Short_All'    => 'managed_short',
            'Other_Rept_Positions_Long_All'    => 'otherreport_long',
            'Other_Rept_Positions_Short_All'   => 'otherreport_short',
            'NonRept_Positions_Long_All'       => 'nonreportable_long',
            'NonRept_Positions_Short_All'      => 'nonreportable_short',
        ];

        $inserted = 0;
        $skipped  = 0;

        // Loop through each data row
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $marketNameRaw = trim($data[$colIndex['Market_and_Exchange_Names']], "\"' ");
            $dateRaw       = trim($data[$colIndex[$dateCol]]);

            try {
                $dt = Carbon::createFromFormat($dateFormat, $dateRaw);
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }
            $reportDate = $dt->toDateString();

            // Find instrument
            $instrument = Instrument::where('cftc_name_scrapping', $marketNameRaw)->first();
            if (! $instrument) {
                $skipped++;
                continue;
            }

            // Duplicate check
            $exists = CFTCReport::where('instrument_id', $instrument->instrument_id)
                                ->where('report_date', $reportDate)
                                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            // Base insert data
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
                'open_interest'       => intval($data[$colIndex[$openInterestCol]] ?? 0),
            ];

            // Fill Disaggregated columns if present
            foreach ($disaggCols as $colName => $attr) {
                if (isset($colIndex[$colName])) {
                    $row[$attr] = intval($data[$colIndex[$colName]] ?? 0);
                }
            }
            // Fill Financial-style columns if present (overwrites Disaggregated if both exist)
            foreach ($finCols as $colName => $attr) {
                if (isset($colIndex[$colName])) {
                    $row[$attr] = intval($data[$colIndex[$colName]] ?? 0);
                }
            }

            try {
                CFTCReport::create($row);
                $inserted++;
            } catch (\Exception $e) {
                $this->error("    ✗ Failed inserting {$marketNameRaw} @ {$reportDate}: " . $e->getMessage());
            }
        }

        fclose($handle);
        $this->info("    ✔ Inserted: {$inserted}, Skipped: {$skipped}");
    }

    /**
     * Parses the single aggregated Financial-Futures file (FinFutYY.txt),
     * which lists multiple instruments in one text block.
     */
    protected function parseFinancialFile(string $filepath): void
    {
        $content = file_get_contents($filepath);
        $lines   = preg_split("/\r\n|\n|\r/", $content);

        $inserted = 0;
        $skipped  = 0;

        // We look for blocks starting with "Traders in Financial Futures - Futures Only Positions as of <Date>"
        // Then each instrument block begins when we see a line matching "<INSTRUMENT_CFTC_NAME>   (<CONTRACTS...>)"
        // Next line has "CFTC Code #xxxxx    Open Interest is   <number>"
        // Next line is "Positions"
        // Then a single data line like:
        //   47,230    421,491      6,884    435,558    125,222     27,020     83,874     76,941     27,930     30,934     26,319      5,747     94,072     41,695

        $i = 0;
        while ($i < count($lines)) {
            $line = trim($lines[$i]);

            // Find the start of a block: instrument name + " - " + exchange
            if (preg_match('/^([A-Z0-9\-\&\,\s]+ - [A-Z\-\s]+)\s+\(CONTRACTS/', $line, $m)) {
                $cftcNameRaw = trim($m[1]); // e.g. "EURO FX - CHICAGO MERCANTILE EXCHANGE"

                // Next line should contain "Open Interest is"
                $i++;
                if ($i >= count($lines)) break;
                $oiLine = trim($lines[$i]);
                // Extract open interest
                if (preg_match('/Open Interest is\s+([\d,]+)/i', $oiLine, $m2)) {
                    $openInterest = intval(str_replace(',', '', $m2[1]));
                } else {
                    $i++;
                    continue; // malformed block
                }

                // Next, skip until we find the line "Positions"
                while ($i < count($lines) && trim($lines[$i]) !== 'Positions') {
                    $i++;
                }
                if ($i >= count($lines)) break;

                // The very next line after "Positions" is the data line
                $i++;
                if ($i >= count($lines)) break;
                $dataLine = trim($lines[$i]);

                // Split on one-or-more spaces
                $nums = preg_split('/\s+/', $dataLine);

                // We expect exactly 14 numeric columns:
                //  1-3: Dealer (swap):   Long, Short, Spreading
                //  4-6: Asset Manager (managed): Long, Short, Spreading
                //  7-9: Leveraged Funds (also managed noncommercial): Long, Short, Spreading
                // 10-12: Other Reportables: Long, Short, Spreading
                // 13-14: Nonreportable: Long, Short
                if (count($nums) < 14) {
                    $i++;
                    continue; // malformed row
                }

                // Map into our columns
                $swapLong           = intval(str_replace(',', '', $nums[0]));
                $swapShort          = intval(str_replace(',', '', $nums[1]));
                // $swapSpread       = intval(str_replace(',', '', $nums[2])); // ignored for now

                $amLong             = intval(str_replace(',', '', $nums[3]));
                $amShort            = intval(str_replace(',', '', $nums[4]));
                // $amSpread         = intval(str_replace(',', '', $nums[5])); // ignored

                $levLong            = intval(str_replace(',', '', $nums[6]));
                $levShort           = intval(str_replace(',', '', $nums[7]));
                // $levSpread        = intval(str_replace(',', '', $nums[8])); // ignored

                $otherLong          = intval(str_replace(',', '', $nums[9]));
                $otherShort         = intval(str_replace(',', '', $nums[10]));
                // $otherSpread      = intval(str_replace(',', '', $nums[11])); // ignored

                $nonreportLong      = intval(str_replace(',', '', $nums[12]));
                $nonreportShort     = intval(str_replace(',', '', $nums[13]));

                // Find the instrument in our DB
                $instrument = Instrument::where('cftc_name_scrapping', $cftcNameRaw)->first();
                if (! $instrument) {
                    $skipped++;
                    $i++;
                    continue;
                }

                // Determine the report date from the block header at top of file:
                // Actually, the very first line in this file is "Traders in Financial Futures - Futures Only Positions as of <Month> <DD>, <YYYY>"
                // We can parse that one time, then reuse for all blocks in the same file. But to be safe, let’s find it again:
                // (Simplest: assume the report date is the same for the entire FinFutYY.txt, so parse it when i==0)

                static $financialReportDate = null;
                if ($financialReportDate === null) {
                    // Search from top for “Positions as of ...”
                    foreach ($lines as $l) {
                        if (preg_match('/Positions as of\s+([A-Za-z]+\s+\d{1,2},\s+\d{4})/i', $l, $md)) {
                            try {
                                $dt = Carbon::createFromFormat('F j, Y', $md[1]);
                                $financialReportDate = $dt->toDateString();
                            } catch (\Exception $e) {
                                $financialReportDate = null;
                            }
                            break;
                        }
                    }
                }
                if (! $financialReportDate) {
                    $skipped++;
                    $i++;
                    continue;
                }

                // Duplicate check
                $exists = CFTCReport::where('instrument_id', $instrument->instrument_id)
                                    ->where('report_date', $financialReportDate)
                                    ->exists();
                if ($exists) {
                    $skipped++;
                    $i++;
                    continue;
                }

                // Build insert data
                $row = [
                    'instrument_id'       => $instrument->instrument_id,
                    'report_date'         => $financialReportDate,
                    'producer_long'       => null,
                    'producer_short'      => null,
                    'swap_long'           => $swapLong,
                    'swap_short'          => $swapShort,
                    // Combine Asset Manager + Leveraged into managed:
                    'managed_long'        => $amLong + $levLong,
                    'managed_short'       => $amShort + $levShort,
                    'otherreport_long'    => $otherLong,
                    'otherreport_short'   => $otherShort,
                    'nonreportable_long'  => $nonreportLong,
                    'nonreportable_short' => $nonreportShort,
                    'open_interest'       => $openInterest,
                ];

                try {
                    CFTCReport::create($row);
                    $inserted++;
                } catch (\Exception $e) {
                    $this->error("    ✗ Error inserting {$cftcNameRaw} @ {$financialReportDate}: " . $e->getMessage());
                }

                $i++;
                continue;
            }

            $i++;
        }

        $this->info("    ✔ Inserted: {$inserted}, Skipped: {$skipped} for FinFut file");
    }

    /**
     * Recursively remove a directory (used for cleaning up temp folders).
     */
    protected function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            if (is_dir($path)) {
                $this->rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
