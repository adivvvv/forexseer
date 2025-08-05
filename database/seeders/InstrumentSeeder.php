<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instrument;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $list = [
            //
            // ──────── CURRENCY FUTURES ────────
            //
            [
                'cftc_name'            => 'USD Index',
                'cftc_name_scrapping'  => 'USD INDEX - ICE FUTURES U.S.',
                'our_symbol'           => 'DX',
                'alt_symbol'           => 'DXY',
                'active_from'          => '2009-12-04',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Euro FX',
                'cftc_name_scrapping'  => 'EURO FX - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6E',
                'alt_symbol'           => 'EURUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'British Pound',
                'cftc_name_scrapping'  => 'BRITISH POUND - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6B',
                'alt_symbol'           => 'GBPUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Japanese Yen',
                'cftc_name_scrapping'  => 'JAPANESE YEN - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6J',
                'alt_symbol'           => 'USDJPY',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Swiss Franc',
                'cftc_name_scrapping'  => 'SWISS FRANC - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6S',
                'alt_symbol'           => 'USDCHF',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Canadian Dollar',
                'cftc_name_scrapping'  => 'CANADIAN DOLLAR - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6C',
                'alt_symbol'           => 'USDCAD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Australian Dollar',
                'cftc_name_scrapping'  => 'AUSTRALIAN DOLLAR - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6A',
                'alt_symbol'           => 'AUDUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'New Zealand Dollar',
                'cftc_name_scrapping'  => 'NZ DOLLAR - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6N',
                'alt_symbol'           => 'NZDUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Mexican Peso',
                'cftc_name_scrapping'  => 'MEXICAN PESO - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6M',
                'alt_symbol'           => 'MXNUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Brazilian Real',
                'cftc_name_scrapping'  => 'BRAZILIAN REAL - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6L',
                'alt_symbol'           => 'BRLUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'South African Rand',
                'cftc_name_scrapping'  => 'SO AFRICAN RAND - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => '6R',
                'alt_symbol'           => 'ZARUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],

            //
            // ──────── METAL FUTURES ────────
            //
            [
                'cftc_name'            => 'Gold',
                'cftc_name_scrapping'  => 'GOLD - COMMODITY EXCHANGE INC.',
                'our_symbol'           => 'GC',
                'alt_symbol'           => 'XAUUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Silver',
                'cftc_name_scrapping'  => 'SILVER - COMMODITY EXCHANGE INC.',
                'our_symbol'           => 'SI',
                'alt_symbol'           => 'XAGUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Copper',
                'cftc_name_scrapping'  => 'COPPER- #1 - COMMODITY EXCHANGE INC.',
                'our_symbol'           => 'HG',
                'alt_symbol'           => 'XCUUSD',
                'active_from'          => '2005-03-28', // Disaggregated data began 3/28/06; adjust if needed
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Platinum',
                'cftc_name_scrapping'  => 'PLATINUM - NEW YORK MERCANTILE EXCHANGE',
                'our_symbol'           => 'PL',
                'alt_symbol'           => 'XPTUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Palladium',
                'cftc_name_scrapping'  => 'PALLADIUM - NEW YORK MERCANTILE EXCHANGE',
                'our_symbol'           => 'PA',
                'alt_symbol'           => 'XPDUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            //
            // ──────── INTEREST-RATE & BOND FUTURES ────────
            //
            [
                'cftc_name'            => 'Fed Funds',
                'cftc_name_scrapping'  => 'FED FUNDS - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZQ',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'UST 2Y Note',
                'cftc_name_scrapping'  => 'UST 2Y NOTE - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZT',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'UST 5Y Note',
                'cftc_name_scrapping'  => 'UST 5Y NOTE - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZF',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'UST 10Y Note',
                'cftc_name_scrapping'  => 'UST 10Y NOTE - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZN',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'UST 30Y Bond',
                'cftc_name_scrapping'  => 'UST BOND - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZB',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Ultra UST 10Y',
                'cftc_name_scrapping'  => 'ULTRA UST 10Y - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'TN',
                'alt_symbol'           => null,
                // Launched September 26, 2011
                'active_from'          => '2011-09-26',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Ultra UST Bond',
                'cftc_name_scrapping'  => 'ULTRA UST BOND - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'UB',
                'alt_symbol'           => null,
                // Launched January 11, 2010
                'active_from'          => '2010-01-11',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'SOFR 3M',
                'cftc_name_scrapping'  => 'SOFR-3M - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'SR3M',
                'alt_symbol'           => 'SOFR3M',
                // Launched January 11, 2010
                'active_from'          => '2010-01-11',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'SOFR 1M',
                'cftc_name_scrapping'  => 'SOFR-1M - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'SR1M',
                'alt_symbol'           => 'SOFR1M',
                // Launched January 11, 2010
                'active_from'          => '2010-01-11',
                'active_to'            => null,
            ],

            //
            // ──────── ENERGY FUTURES ────────
            //
            [
                'cftc_name'            => 'Light Sweet Crude Oil',
                'cftc_name_scrapping'  => 'CRUDE OIL, LIGHT SWEET-WTI - ICE FUTURES EUROPE',  // :contentReference[oaicite:0]{index=0}
                'our_symbol'           => 'CL',
                'alt_symbol'           => 'WTIUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Brent Crude',
                'cftc_name_scrapping'  => 'BRENT LAST DAY - NEW YORK MERCANTILE EXCHANGE',              // :contentReference[oaicite:1]{index=1}
                'our_symbol'           => 'BZ',
                'alt_symbol'           => 'BRENTUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Natural Gas',
                'cftc_name_scrapping'  => 'NAT GAS NYME - NEW YORK MERCANTILE EXCHANGE',          // :contentReference[oaicite:2]{index=2}
                'our_symbol'           => 'NG',
                'alt_symbol'           => 'NGUSD',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'NY Harbor ULSD',
                'cftc_name_scrapping'  => 'NY HARBOR ULSD - NEW YORK MERCANTILE EXCHANGE',      // :contentReference[oaicite:5]{index=5}
                'our_symbol'           => 'HOE',     // (ultra-low-sulfur diesel)
                'alt_symbol'           => 'ULSDUSD',
                'active_from'          => '2006-08-16',
                'active_to'            => null,
            ],
            //
            // ──────── AGRICULTURAL FUTURES ────────
            //
            [
                'cftc_name'            => 'Corn',
                'cftc_name_scrapping'  => 'CORN - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZC',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Soybeans',
                'cftc_name_scrapping'  => 'SOYBEANS - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZS',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Soybean Oil',
                'cftc_name_scrapping'  => 'SOYBEAN OIL - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZL',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Soybean Meal',
                'cftc_name_scrapping'  => 'SOYBEAN MEAL - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZM',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Wheat',
                'cftc_name_scrapping'  => 'WHEAT-SRW - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZW',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Rough Rice',
                'cftc_name_scrapping'  => 'ROUGH RICE - CHICAGO BOARD OF TRADE',
                'our_symbol'           => 'ZR',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Cotton',
                'cftc_name_scrapping'  => 'COTTON NO. 2 - ICE FUTURES U.S.',
                'our_symbol'           => 'CT',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Sugar',
                'cftc_name_scrapping'  => 'SUGAR NO. 11 - ICE FUTURES U.S.',
                'our_symbol'           => 'SB',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Coffee',
                'cftc_name_scrapping'  => 'COFFEE C - ICE FUTURES U.S.',
                'our_symbol'           => 'KC',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Cocoa',
                'cftc_name_scrapping'  => 'COCOA - ICE FUTURES U.S.',
                'our_symbol'           => 'CC',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Lumber',
                'cftc_name_scrapping'  => 'LUMBER - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'LB',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Orange Juice',
                'cftc_name_scrapping'  => 'FRZN CONCENTRATED ORANGE JUICE - ICE FUTURES U.S.',
                'our_symbol'           => 'OJ',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],

            //
            // ──────── LIVESTOCK & DAIRY FUTURES ────────
            //
            [
                'cftc_name'            => 'Live Cattle',
                'cftc_name_scrapping'  => 'LIVE CATTLE - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'LE',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Feeder Cattle',
                'cftc_name_scrapping'  => 'FEEDER CATTLE - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'GF',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Lean Hogs',
                'cftc_name_scrapping'  => 'LEAN HOGS - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'HE',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Milk',
                'cftc_name_scrapping'  => 'MILK, Class III - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'           => 'DA',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            //
            // ──────── EQUITY INDEX FUTURES ────────
            //
            [
                'cftc_name'            => 'S&P 500',
                'cftc_name_scrapping'  => 'S&P 500 Consolidated - CHICAGO MERCANTILE EXCHANGE',            // :contentReference[oaicite:0]{index=0}
                'our_symbol'           => 'SP',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'E-Mini S&P 500',
                'cftc_name_scrapping'  => 'E-MINI S&P 500 - CHICAGO MERCANTILE EXCHANGE',      // :contentReference[oaicite:1]{index=1}
                'our_symbol'           => 'ES',
                'alt_symbol'           => 'US500',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'           => 'NASDAQ-100',
                'cftc_name_scrapping' => 'NASDAQ-100 Consolidated - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'ND',           // full‐size NQ futures
                'alt_symbol'          => 'US100',
                'active_from'         => '2005-01-01',
                'active_to'           => null,
            ],
            [
                'cftc_name'           => 'E-Mini NASDAQ-100',
                'cftc_name_scrapping' => 'NASDAQ MINI - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'NQ',           // E-mini
                'alt_symbol'          => 'US100',
                'active_from'         => '2005-01-01',
                'active_to'           => null,
            ],
            [
                'cftc_name'           => 'Russell 2000 E-mini',
                'cftc_name_scrapping' => 'RUSSELL E-MINI - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'RTY',
                'alt_symbol'          => null,
                'active_from'         => '2008-06-09',
                'active_to'           => null,
            ],
            [
                'cftc_name'            => 'Dow Jones Industrial Average',
                'cftc_name_scrapping'  => 'DJIA Consolidated - CHICAGO BOARD OF TRADE', // :contentReference[oaicite:5]{index=5}
                'our_symbol'           => 'DJ',
                'alt_symbol'           => null,
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Dow Jones Industrial Mini',
                'cftc_name_scrapping'  => 'DJIA x $5 - CHICAGO BOARD OF TRADE',   // :contentReference[oaicite:6]{index=6}
                'our_symbol'           => 'YM',
                'alt_symbol'           => 'US30',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            [
                'cftc_name'            => 'Nikkei (Yen Denominated)',
                'cftc_name_scrapping'  => 'NIKKEI STOCK AVERAGE YEN DENOM - CHICAGO MERCANTILE EXCHANGE',     // :contentReference[oaicite:9]{index=9}
                'our_symbol'           => 'NKD',
                'alt_symbol'           => 'NIK225',
                'active_from'          => '2005-01-01',
                'active_to'            => null,
            ],
            //
            // ──────── CRYPTO FUTURES ────────
            //
            [
                'cftc_name'           => 'Bitcoin',
                'cftc_name_scrapping' => 'BITCOIN - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'BTC',
                'alt_symbol'          => 'BTCUSD',
                // CME Bitcoin futures launched December 17, 2017
                'active_from'         => '2017-12-17',
                'active_to'           => null,
            ],
            [
                'cftc_name'           => 'Micro Bitcoin',
                'cftc_name_scrapping' => 'MICRO BITCOIN - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'MBT',
                'alt_symbol'          => 'BTCUSD',
                // Micro Bitcoin futures launched May 3, 2021
                'active_from'         => '2021-05-03',
                'active_to'           => null,
            ],
            [
                'cftc_name'           => 'Ether',
                'cftc_name_scrapping' => 'ETHER CASH SETTLED - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'ETH',
                'alt_symbol'          => 'ETHUSD',
                // CME Ether futures launched February 8, 2021
                'active_from'         => '2021-02-08',
                'active_to'           => null,
            ],
            [
                'cftc_name'           => 'Micro Ether',
                'cftc_name_scrapping' => 'MICRO ETHER  - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'MET',
                'alt_symbol'          => 'ETHUSD',
                // Micro Ether futures launched August 2, 2021
                'active_from'         => '2021-08-02',
                'active_to'           => null,
            ],
            [
                'cftc_name'           => 'Solana',
                'cftc_name_scrapping' => 'SOL - CHICAGO MERCANTILE EXCHANGE',
                'our_symbol'          => 'SOL',
                'alt_symbol'          => 'SOLUSD',
                // Micro Ether futures launched August 2, 2021
                'active_from'         => '2021-08-02',
                'active_to'           => null,
            ],



        ];

        foreach ($list as $item) {
            Instrument::updateOrCreate(
                ['cftc_name' => $item['cftc_name']],
                [
                    'cftc_name_scrapping' => $item['cftc_name_scrapping'],
                    'our_symbol'          => $item['our_symbol'],
                    'alt_symbol'          => $item['alt_symbol'],
                    'active_from'         => $item['active_from'],
                    'active_to'           => $item['active_to'],
                ]
            );
        }
    }
}