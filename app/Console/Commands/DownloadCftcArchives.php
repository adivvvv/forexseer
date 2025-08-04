<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DownloadCftcArchives extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cftc:download-archives';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download all Disaggregated and Financial Futures ZIP archives into storage/app/cftc_historical';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define the folder under storage/app
        $relativeFolder = 'cftc_historical';
        $absoluteFolder = storage_path('app' . DIRECTORY_SEPARATOR . $relativeFolder);

        // Make sure the directory exists on disk
        if (! File::exists($absoluteFolder)) {
            File::makeDirectory($absoluteFolder, 0755, true);
            $this->info("Created directory: {$absoluteFolder}");
        }

        // Remove all old archives so fresh copies will always be downloaded
        File::cleanDirectory($absoluteFolder);
        $this->info("Removed old archives from: {$absoluteFolder}");

        $urls = [
            // ── Disaggregated Futures-Only ──
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2025.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2024.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2023.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2022.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2021.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2020.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2019.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2018.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2017.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2016.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2015.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2014.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2013.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2012.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2011.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_2010.zip',
            'https://www.cftc.gov/files/dea/history/fut_disagg_txt_hist_2006_2016.zip',

            // ── Traders in Financial Futures (Futures Only) ──
            'https://www.cftc.gov/files/dea/history/fin_fut_txt_2006_2016.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2017.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2018.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2019.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2020.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2021.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2022.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2023.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2024.zip',
            'https://www.cftc.gov/files/dea/history/fut_fin_txt_2025.zip',
        ];

        foreach ($urls as $url) {
            $filename     = basename($url);
            $absolutePath = $absoluteFolder . DIRECTORY_SEPARATOR . $filename;

            $this->info("  ⇒ Downloading {$filename} …");

            try {
                $response = Http::timeout(120)->get($url);

                if ($response->ok()) {
                    file_put_contents($absolutePath, $response->body());
                    $this->info("    ✓ Saved {$filename} to: {$absolutePath}");
                } else {
                    $this->error("    ✗ Failed to download {$filename} (HTTP status {$response->status()}).");
                }
            } catch (\Exception $e) {
                $this->error("    ✗ Error fetching {$filename}: {$e->getMessage()}");
            }
        }

        $this->info('All Disaggregated and Financial ZIPs processed.');
        return 0;
    }
}