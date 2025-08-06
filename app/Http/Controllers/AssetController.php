<?php
// app/Http/Controllers/AssetController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function show(Request $request, string $symbol)
    {
        $market = $request->segment(1); 
        // Determine access level
        $user = $request->user();
        $level0 = true;                             // public
        $level1 = $user !== null;                   // logged-in
        $level2 = $user?->hasPaidSubscription() ?? false; // premium
        // (implement hasPaidSubscription() on your User model)

        return Inertia::render('Asset/Show', [
            'market'   => $market,
            'symbol'   => strtoupper($symbol),
            'access'   => [
                'public'   => $level0,
                'loggedIn' => $level1,
                'premium'  => $level2,
            ],
            'user' => $user ? [
                'id'    => $user->id,
                'name'  => $user->name,
                // …any other safe user info
            ] : null,
            // maybe preload free‐level data here
        ]);
    }
}