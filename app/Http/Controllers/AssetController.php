<?php
// app/Http/Controllers/AssetController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class AssetController extends Controller
{
    public function show(Request $request, string $symbol)
    {
        // 1) Normalize
        $symbol = strtoupper($symbol);
        $market = $request->segment(1);

        // 2) Load config
        $assets = config('assets');
        if (! isset($assets[$symbol])) {
            abort(404, "Unknown asset “{$symbol}”");
        }
        $cfg      = $assets[$symbol];
        $decimals = $cfg['decimals'] ?? 2;
        $name     = $cfg['name'] ?? $symbol;
        $type     = $cfg['type'] ?? 'unknown';

        // 3) Build safeKey
        $safeKey = str_replace('-', '_', $cfg['data_symbol']);

        // 4) Load last_price
        $lastPath    = "private/last_prices/{$safeKey}.json";
        $initialLast = null;
        if (Storage::disk('local')->exists($lastPath)) {
            $json        = json_decode(Storage::disk('local')->get($lastPath), true);
            $initialLast = isset($json['price']) ? (float)$json['price'] : null;
        }

        // 5) Load open_price for today (in asset timezone)
        $tz       = $cfg['timezone'] ?? 'UTC';
        $today    = Carbon::now($tz)->toDateString();
        $openPath = "private/open_prices/{$safeKey}_{$today}.json";
        $initialOpen = null;
        if (Storage::disk('local')->exists($openPath)) {
            $json        = json_decode(Storage::disk('local')->get($openPath), true);
            $initialOpen = isset($json['open']) ? (float)$json['open'] : null;
        }

        // ↪️ FALLBACK: if no open yet (e.g. forex), show last so change = 0%
        if ($initialOpen === null && $initialLast !== null) {
            $initialOpen = $initialLast;
        }

        // 6) user & access
        $user   = $request->user();
        $access = [
            'public'   => true,
            'loggedIn' => (bool)$user,
            'premium'  => $user?->hasPaidSubscription() ?? false,
        ];

        // 7) Render Inertia
        return Inertia::render('Asset/Show', [
            'market'      => ucfirst($market),
            'name'        => $name,
            'type'        => $type,
            'symbol'      => $symbol,
            'decimals'    => $decimals,
            'initialLast' => $initialLast,
            'initialOpen' => $initialOpen,
            'access'      => $access,
            'user'        => $user
                ? ['id'=>$user->id, 'name'=>$user->name]
                : null,
        ]);
    }
}
