<?php

namespace App\Console\Commands;

use App\Models\CFTCReport;
use App\Models\Instrument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ImportCftcLive extends Command
{
    protected $signature   = 'cftc:import-live {--debug : Show name-matching diagnostics}';
    protected $description = 'Fetch live CFTC <pre> pages, compare all fields, and debug name mapping.';

    /**
     * Map normalized scraped name → normalized DB name.
     * Keys must exactly match normalizeMarketName($scrapedRawName).
     */
    protected $marketNameMap = [
        // no special mappings needed now that we capture '#' and '$'
    ];

    /**
     * Any instruments here won’t be treated as errors if they truly
     * never appear on the live pages.
     */
    protected $ignoreInstruments = [
        // none
    ];

    public function handle()
    {
        $debug = $this->option('debug');
        $this->info('Starting live import…');

        // 1) Gather live rows
        [ $rowsByName, $errors ] = $this->gatherLiveRows($debug);

        if ($debug) {
            $this->dumpNameDiagnostics($rowsByName);
            return 0;
        }

        // —— PRODUCTION: save any brand-new weekly report —— //

        // 2) Build list of all scraped dates, pick the latest
        $allDates = collect($rowsByName)
            ->flatten(1)
            ->pluck('report_date')
            ->unique()
            ->sortDesc();
        $latestScrapedDate = $allDates->first();

        // 3) Check what’s already in the DB
        $latestDbDate = CFTCReport::query()->max('report_date');

        if ($latestScrapedDate > $latestDbDate) {
            $this->info("New live report detected for {$latestScrapedDate}, inserting into database…");

            $inserted = 0;
            $skipped  = 0;

            foreach ($rowsByName as $marketName => $rows) {
                // find the row for the new date
                $row = collect($rows)
                    ->first(fn($r) => $r['report_date'] === $latestScrapedDate);
                if (! $row) {
                    continue;
                }

                // find matching instrument
                $instrument = Instrument::where('cftc_name_scrapping', $row['market_name'])->first();
                if (! $instrument) {
                    $this->warn("  • No Instrument for “{$row['market_name']}” — skipping.");
                    $skipped++;
                    continue;
                }

                // skip if already exists
                $exists = CFTCReport::where('instrument_id', $instrument->instrument_id)
                                    ->where('report_date',   $latestScrapedDate)
                                    ->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                // insert new report row
                CFTCReport::create([
                    'instrument_id'       => $instrument->instrument_id,
                    'report_date'         => $row['report_date'],
                    'producer_long'       => $row['producer_long'],
                    'producer_short'      => $row['producer_short'],
                    'swap_long'           => $row['swap_long'],
                    'swap_short'          => $row['swap_short'],
                    'managed_long'        => $row['managed_long'],
                    'managed_short'       => $row['managed_short'],
                    'otherreport_long'    => $row['otherreport_long'],
                    'otherreport_short'   => $row['otherreport_short'],
                    'nonreportable_long'  => $row['nonreportable_long'],
                    'nonreportable_short' => $row['nonreportable_short'],
                    'open_interest'       => $row['open_interest'],
                ]);

                $inserted++;
            }

            $this->info("Import complete. {$inserted} inserted, {$skipped} skipped.");
        } else {
            $this->info("No new report: latest in DB is {$latestDbDate}, latest scraped is {$latestScrapedDate}.");
        }

        $this->info('Live import finished.');
        return 0;

        // —— DEBUG/COMPARISON BLOCK (commented out for production) —— //
        /*
        // 4) VERIFY INTEGRITY AGAINST DB (was used for initial testing):
        $asOf = CFTCReport::query()->max('report_date');
        $this->info("Verifying scraped data against DB for {$asOf}…");
        $ok = $this->compareToDb($rowsByName, $asOf);
        return $ok ? 0 : 1;
        */
    }

    protected function gatherLiveRows(bool $debug): array
    {
        $urls = [
            'financial' => 'https://www.cftc.gov/dea/futures/financial_lf.htm',
            'ag'        => 'https://www.cftc.gov/dea/futures/ag_lf.htm',
            'petroleum' => 'https://www.cftc.gov/dea/futures/petroleum_lf.htm',
            'natgas'    => 'https://www.cftc.gov/dea/futures/nat_gas_lf.htm',
            'other'     => 'https://www.cftc.gov/dea/futures/other_lf.htm',
        ];

        $allRows = [];
        $errors  = [];

        foreach ($urls as $type => $url) {
            $this->info("→ GET {$url}");
            $resp = Http::timeout(15)->get($url);

            if (! $resp->ok()) {
                $this->error("  ✗ HTTP {$resp->status()}");
                $errors[] = $url;
                continue;
            }

            $pre   = $this->extractPreContent($resp->body());
            $parse = $type === 'financial'
                   ? $this->parseLiveFinancial($pre, $debug)
                   : $this->parseLiveDisaggregated($pre, $debug);

            foreach ($parse as $r) {
                $nm = $this->normalizeMarketName($r['market_name']);
                if (isset($this->marketNameMap[$nm])) {
                    $nm = $this->marketNameMap[$nm];
                }
                $allRows[$nm][] = $r;
            }
        }

        return [ $allRows, $errors ];
    }

    protected function parseLiveFinancial(string $text, bool $debug = false): array
    {
        $rows  = [];
        $lines = preg_split('/\r?\n/', $text);
        $date  = null;

        foreach ($lines as $l) {
            if (preg_match('/Positions as of\s+([A-Za-z]+\s+\d{1,2},\s*\d{4})/i', $l, $m)) {
                $date = Carbon::parse($m[1])->toDateString();
                break;
            }
        }
        if (! $date) {
            $this->warn("! No report_date in financial text");
            return [];
        }

        $c = count($lines);
        for ($i = 0; $i < $c; $i++) {
            if (
                preg_match('/^\s*([A-Z0-9 \-\(\),\.\/#%&\$]+?)\s*\(.*\)\s*$/i', $lines[$i], $m)
                && isset($lines[$i+1]) && stripos($lines[$i+1], 'CFTC Code') !== false
            ) {
                $marketRaw = trim($m[1]);
                if ($debug) {
                    $this->line("[F] Found header: “{$marketRaw}”");
                }

                // open interest
                $oi = null;
                for ($j = $i+1; $j < $c; $j++) {
                    if (preg_match('/Open Interest is\s*([\d,]+)/i', $lines[$j], $mO)) {
                        $oi = (int) str_replace(',', '', $mO[1]);
                        break;
                    }
                }
                if (! is_int($oi)) {
                    continue;
                }

                // positions row
                $k = $j + 1;
                while ($k < $c && stripos($lines[$k], 'Positions') === false) {
                    $k++;
                }
                if (! isset($lines[$k+1])) {
                    continue;
                }
                $nums = preg_split('/\s+/', trim($lines[$k+1]));
                if (count($nums) < 14) {
                    continue;
                }

                list($dL, $dS, , $aL, $aS, , $vL, $vS, , $oL, $oS, , $nrL, $nrS) = $nums;

                $rows[] = [
                    'market_name'         => $marketRaw,
                    'report_date'         => $date,
                    'producer_long'       => null,
                    'producer_short'      => null,
                    'swap_long'           => (int)str_replace(',','',$dL),
                    'swap_short'          => (int)str_replace(',','',$dS),
                    'managed_long'        => (int)str_replace(',','',$aL) + (int)str_replace(',','',$vL),
                    'managed_short'       => (int)str_replace(',','',$aS) + (int)str_replace(',','',$vS),
                    'otherreport_long'    => (int)str_replace(',','',$oL),
                    'otherreport_short'   => (int)str_replace(',','',$oS),
                    'nonreportable_long'  => (int)str_replace(',','',$nrL),
                    'nonreportable_short' => (int)str_replace(',','',$nrS),
                    'open_interest'       => $oi,
                ];
            }
        }

        return $rows;
    }

    protected function parseLiveDisaggregated(string $text, bool $debug = false): array
    {
        $rows  = [];
        $lines = preg_split('/\r?\n/', $text);
        $date  = null;

        foreach ($lines as $l) {
            if (preg_match(
                '/(?:Futures Only,|Options and Futures Combined Positions as of)\s*([A-Za-z]+\s+\d{1,2},\s*\d{4})/i',
                $l, $m
            )) {
                $date = Carbon::parse($m[1])->toDateString();
                break;
            }
        }
        if (! $date) {
            $this->warn("! No report_date in disagg text");
            return [];
        }

        $c = count($lines);
        for ($i = 0; $i < $c; $i++) {
            if (
                preg_match(
                    '/^([A-Z0-9\s\-\(\),\.\/#&%\$]+?)\s*-\s*([A-Z0-9\s\(\),\.]+?)\s+Code-\d+/i',
                    $lines[$i], $m
                )
            ) {
                $marketRaw = trim($m[1] . ' - ' . $m[2]);
                if ($debug) {
                    $this->line("[D] Found header: “{$marketRaw}”");
                }

                for ($j = $i+1; $j < $c; $j++) {
                    if (preg_match(
                        '/^All\s*:\s*([\d,]+):\s*((?:[\d,]+\s+){10,}[\d,]+):\s*([\d,]+\s+[\d,]+)/i',
                        $lines[$j], $m3
                    )) {
                        $oi  = (int)str_replace(',', '', $m3[1]);
                        $grp = preg_split('/\s+/', trim($m3[2]));
                        $nr  = preg_split('/\s+/', trim($m3[3]));
                        if (count($grp) < 11 || count($nr) < 2) {
                            continue;
                        }
                        $rows[] = [
                            'market_name'          => $marketRaw,
                            'report_date'          => $date,
                            'producer_long'        => (int)str_replace(',','',$grp[0]),
                            'producer_short'       => (int)str_replace(',','',$grp[1]),
                            'swap_long'            => (int)str_replace(',','',$grp[2]),
                            'swap_short'           => (int)str_replace(',','',$grp[3]),
                            'managed_long'         => (int)str_replace(',','',$grp[5]),
                            'managed_short'        => (int)str_replace(',','',$grp[6]),
                            'otherreport_long'     => (int)str_replace(',','',$grp[8]),
                            'otherreport_short'    => (int)str_replace(',','',$grp[9]),
                            'nonreportable_long'   => (int)str_replace(',','',$nr[0]),
                            'nonreportable_short'  => (int)str_replace(',','',$nr[1]),
                            'open_interest'        => $oi,
                        ];
                        break;
                    }
                }
            }
        }

        return $rows;
    }

    protected function compareToDb(array $rowsByName, string $asOfDate): bool
    {
        $fields = [
            'producer_long','producer_short',
            'swap_long','swap_short',
            'managed_long','managed_short',
            'otherreport_long','otherreport_short',
            'nonreportable_long','nonreportable_short',
            'open_interest',
        ];

        $mismatches = false;

        // preload the DB records for that exact date
        $dbRows = CFTCReport::where('report_date', $asOfDate)
                            ->get()
                            ->keyBy('instrument_id');

        foreach (Instrument::all() as $instr) {
            $name = $this->normalizeMarketName($instr->cftc_name_scrapping);

            if (in_array($name, $this->ignoreInstruments, true)) {
                continue;
            }

            // find the single live row for this date
            $liveRows = array_filter(
                $rowsByName[$name] ?? [],
                fn($r) => $r['report_date'] === $asOfDate
            );
            if (empty($liveRows)) {
                $this->error("✗ Missing live scrape for [{$instr->instrument_id}] {$name} @ {$asOfDate}");
                $mismatches = true;
                continue;
            }
            $live = array_values($liveRows)[0];

            if (! isset($dbRows[$instr->instrument_id])) {
                $this->error("✗ Missing DB record for [{$instr->instrument_id}] {$name} @ {$asOfDate}");
                $mismatches = true;
                continue;
            }
            $db = $dbRows[$instr->instrument_id];

            // compare each field
            foreach ($fields as $f) {
                $old = (int)$db->$f;
                $new = (int)$live[$f];
                if ($old !== $new) {
                    $this->warn("✗ {$name} @ {$asOfDate}: {$f} DB={$old} vs LIVE={$new}");
                    $mismatches = true;
                }
            }
        }

        if (! $mismatches) {
            $this->info("🎉 100% field match for all instruments at {$asOfDate}.");
        } else {
            $this->error("✗ Some mismatches detected—see above for details.");
        }

        return ! $mismatches;
    }

    protected function dumpNameDiagnostics(array $rowsByName): void
    {
        // unchanged
        $scraped = array_keys($rowsByName);
        sort($scraped);

        $table = Instrument::pluck('cftc_name_scrapping')
                          ->map(fn($n) => $this->normalizeMarketName($n))
                          ->all();
        sort($table);

        $this->info("\n=== Scraped Market Names (".count($scraped).") ===");
        foreach ($scraped as $n) {
            $this->line("  • $n");
        }

        $this->info("\n=== Instrument Table Names (".count($table).") ===");
        foreach ($table as $n) {
            $this->line("  • $n");
        }

        $missedInScrape = array_diff($table, $scraped, $this->ignoreInstruments);
        $unknownScrape  = array_diff($scraped, $table);

        $this->info("\n--- In table but NOT scraped (".count($missedInScrape).") ---");
        foreach ($missedInScrape as $want) {
            $closest = $this->findClosest($want, $scraped);
            $this->line("  ✗ $want  →  closest scraped: $closest");
        }

        $this->info("\n--- Scraped but NOT in table (".count($unknownScrape).") ---");
        foreach ($unknownScrape as $have) {
            $closest = $this->findClosest($have, $table);
            $this->line("  ? $have  →  closest in table: $closest");
        }

        $this->info("\nRun without `--debug` to perform field comparisons.");
    }

    protected function findClosest(string $needle, array $haystack): string
    {
        $best = null; $bestScore = PHP_INT_MAX;
        foreach ($haystack as $h) {
            $d = levenshtein($needle, $h);
            if ($d < $bestScore) {
                $best = $h; $bestScore = $d;
            }
        }
        return $best." ($bestScore)";
    }

    protected function extractPreContent(string $html): string
    {
        if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $html, $m)) {
            return html_entity_decode(strip_tags($m[1]));
        }
        return '';
    }

    protected function normalizeMarketName(string $raw): string
    {
        $n = preg_replace('/\s+/', ' ', trim($raw));
        return preg_replace('/\s*([.,\/\-%&])\s*/', '$1', $n);
    }
}
