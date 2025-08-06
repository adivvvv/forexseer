<?php
// config/assets.php

return [
    /*
     |--------------------------------------------------------------------------
     | Tracked Assets
     |--------------------------------------------------------------------------
     |
     | key: symbol (e.g. 'BTCUSD')
     | value: array of asset properties
     |   - data_symbol: symbol used in EODHD API (e.g. 'BTC-USD')
     |   - name: full name of the asset (e.g. 'Bitcoin | BTCUSD')
     |   - menu_display: how to display in the UI (e.g. 'Bitcoin (BTCUSD)')
     |   - type: asset type (e.g. 'crypto', 'forex', 'stock')
     |   - timezone: timezone for the asset (e.g. 'UTC') 
     |   - open_time: opening time for the asset (e.g. '00:00:00')
     |   - decimals: number of decimal places for price display
     */

    'BTCUSD' => [
        'data_symbol'   => 'BTC-USD',
        'name'      => 'Bitcoin | BTCUSD',
        'menu_display' => 'Bitcoin (BTCUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 2,
    ],

    'ETHUSD' => [
        'data_symbol'   => 'ETH-USD',
        'name'      => 'Ethereum | ETHUSD',
        'menu_display' => 'Ethereum (ETHUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 2,
    ],

    'SOLUSD' => [
        'data_symbol'   => 'SOL-USD',
        'name'      => 'Solana | SOLUSD',
        'menu_display' => 'Solana (SOLUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 3,
    ],

    'XRPUSD' => [
        'data_symbol'   => 'XRP-USD',
        'name'      => 'Ripple | XRPUSD',
        'menu_display' => 'Ripple (XRPUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 4,
    ],

    'BNBUSD' => [
        'data_symbol'   => 'BNB-USD',
        'name'      => 'Binance Coin | BNBUSD',
        'menu_display' => 'Binance Coin (BNBUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 3,
    ],

    'TRXUSD' => [
        'data_symbol'   => 'TRX-USD',
        'name'      => 'Tron | TRXUSD',
        'menu_display' => 'Tron (TRXUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 5,
    ],

    'DOGEUSD' => [
        'data_symbol'   => 'DOGE-USD',
        'name'      => 'Dogecoin | DOGEUSD',
        'menu_display' => 'Dogecoin (DOGEUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 5,
    ],

    'ADAUSD' => [
        'data_symbol'   => 'ADA-USD',
        'name'      => 'Cardano | ADAUSD',
        'menu_display' => 'Cardano (ADAUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 5,
    ],

    'AVAXUSD' => [
        'data_symbol'   => 'AVAX-USD',
        'name'      => 'Avalanche | AVAXUSD',
        'menu_display' => 'Avalanche (AVAXUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 4,
    ],

    'XMRUSD' => [
        'data_symbol'   => 'XMR-USD',
        'name'      => 'Monero | XMRUSD',
        'menu_display' => 'Monero (XMRUSD)',
        'type'      => 'crypto',
        'timezone'  => 'UTC',
        'open_time' => '00:00:00',
        'decimals' => 3,
    ],

    /*
    Major Forex Pairs
    */

    'EURUSD' => [
        'data_symbol'   => 'EURUSD',
        'name'      => 'EURUSD | 6E',
        'menu_display' => 'EUR/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'GBPUSD' => [
        'data_symbol'   => 'GBPUSD',
        'name'      => 'GBPUSD | 6B',
        'menu_display' => 'GBP/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'USDJPY' => [
        'data_symbol'   => 'USDJPY',
        'name'      => 'USDJPY | 6J',
        'menu_display' => 'USD/JPY',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 3,
    ],

    'USDCHF' => [
        'data_symbol'   => 'USDCHF',
        'name'      => 'USDCHF | 6S',
        'menu_display' => 'USD/CHF',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'USDCAD' => [
        'data_symbol'   => 'USDCAD',
        'name'      => 'USDCAD | 6C',
        'menu_display' => 'USD/CAD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'AUDUSD' => [
        'data_symbol'   => 'AUDUSD',
        'name'      => 'AUDUSD | 6A',
        'menu_display' => 'AUD/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'NZDUSD' => [
        'data_symbol'   => 'NZDUSD',
        'name'      => 'NZDUSD | 6N',
        'menu_display' => 'NZD/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'MXNUSD' => [
        'data_symbol'   => 'MXNUSD',
        'name'      => 'MXNUSD | 6M',
        'menu_display' => 'MXN/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'BRLUSD' => [
        'data_symbol'   => 'BRLUSD',
        'name'      => 'BRLUSD | 6B',
        'menu_display' => 'BRL/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'ZARUSD' => [
        'data_symbol'   => 'ZARUSD',
        'name'      => 'ZARUSD | 6Z',
        'menu_display' => 'ZAR/USD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    /* Cross Currency Pairs */

    'EURGBP' => [
        'data_symbol'   => 'EURGBP',
        'name'      => 'EURGBP',
        'menu_display' => 'EUR/GBP',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'EURJPY' => [
        'data_symbol'   => 'EURJPY',
        'name'      => 'EURJPY',
        'menu_display' => 'EUR/JPY',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 3,
    ],

    'GBPJPY' => [
        'data_symbol'   => 'GBPJPY',
        'name'      => 'GBPJPY',
        'menu_display' => 'GBP/JPY',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 3,
    ],

    'EURAUD' => [
        'data_symbol'   => 'EURAUD',
        'name'      => 'EURAUD',
        'menu_display' => 'EUR/AUD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'AUDJPY' => [
        'data_symbol'   => 'AUDJPY',
        'name'      => 'AUDJPY',
        'menu_display' => 'AUD/JPY',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 3,
    ],

    'EURCAD' => [
        'data_symbol'   => 'EURCAD',
        'name'      => 'EURCAD',
        'menu_display' => 'EUR/CAD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'CADJPY' => [
        'data_symbol'   => 'CADJPY',
        'name'      => 'CADJPY',
        'menu_display' => 'CAD/JPY',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 3,
    ],

    'GBPAUD' => [
        'data_symbol'   => 'GBPAUD',
        'name'      => 'GBPAUD',
        'menu_display' => 'GBP/AUD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'EURNZD' => [
        'data_symbol'   => 'EURNZD',
        'name'      => 'EURNZD',
        'menu_display' => 'EUR/NZD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    'AUDCAD' => [
        'data_symbol'   => 'AUDCAD',
        'name'      => 'AUDCAD',
        'menu_display' => 'AUD/CAD',
        'type'      => 'forex',
        'timezone'  => 'America/New_York',
        'open_time' => '17:00:00',
        'decimals' => 5,
    ],

    /* US Stocks */

    'NVDA' => [
        'data_symbol'   => 'NVDA',
        'name'      => 'NVIDIA Corporation | NVDA',
        'menu_display' => 'NVIDIA (NVDA)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'MSFT' => [
        'data_symbol'   => 'MSFT',
        'name'      => 'Microsoft Corporation | MSFT',
        'menu_display' => 'Microsoft (MSFT)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'AAPL' => [
        'data_symbol'   => 'AAPL',
        'name'      => 'Apple Inc. | AAPL',
        'menu_display' => 'Apple (AAPL)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'AMZN' => [
        'data_symbol'   => 'AMZN',
        'name'      => 'Amazon.com Inc. | AMZN',
        'menu_display' => 'Amazon (AMZN)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'META' => [
        'data_symbol'   => 'META',
        'name'      => 'Meta Platforms Inc. | META',
        'menu_display' => 'Meta (META)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'AVGO' => [
        'data_symbol'   => 'AVGO',
        'name'      => 'Broadcom Inc. | AVGO',
        'menu_display' => 'Broadcom (AVGO)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'GOOGL' => [
        'data_symbol'   => 'GOOGL',
        'name'      => 'Alphabet Inc. | GOOGL',
        'menu_display' => 'Alphabet (GOOGL)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'BRK.B' => [
        'data_symbol'   => 'BRK.B',
        'name'      => 'Berkshire Hathaway Inc. | BRK.B',
        'menu_display' => 'Berkshire (BRK.B)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'TSLA' => [
        'data_symbol'   => 'TSLA',
        'name'      => 'Tesla Inc. | TSLA',
        'menu_display' => 'Tesla (TSLA)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'JPM' => [
        'data_symbol'   => 'JPM',
        'name'      => 'JPMorgan Chase & Co. | JPM',
        'menu_display' => 'JPMorgan (JPM)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'V' => [
        'data_symbol'   => 'V',
        'name'      => 'Visa Inc. | V',
        'menu_display' => 'Visa (V)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'LLY' => [
        'data_symbol'   => 'LLY',
        'name'      => 'Eli Lilly and Company | LLY',
        'menu_display' => 'Eli Lilly (LLY)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'NFLX' => [
        'data_symbol'   => 'NFLX',
        'name'      => 'Netflix Inc. | NFLX',
        'menu_display' => 'Netflix (NFLX)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'XOM' => [
        'data_symbol'   => 'XOM',
        'name'      => 'Exxon Mobil Corporation | XOM',
        'menu_display' => 'Exxon Mobil (XOM)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'MA' => [
        'data_symbol'   => 'MA',
        'name'      => 'Mastercard Incorporated | MA',
        'menu_display' => 'Mastercard (MA)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'WMT' => [
        'data_symbol'   => 'WMT',
        'name'      => 'Walmart Inc. | WMT',
        'menu_display' => 'Walmart (WMT)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'COST' => [
        'data_symbol'   => 'COST',
        'name'      => 'Costco Wholesale Corporation | COST',
        'menu_display' => 'Costco (COST)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'ORCL' => [
        'data_symbol'   => 'ORCL',
        'name'      => 'Oracle Corporation | ORCL',
        'menu_display' => 'Oracle (ORCL)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'JNJ' => [
        'data_symbol'   => 'JNJ',
        'name'      => 'Johnson & Johnson | JNJ',
        'menu_display' => 'Johnson & Johnson (JNJ)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],

    'HD' => [
        'data_symbol'   => 'HD',
        'name'      => 'The Home Depot Inc. | HD',
        'menu_display' => 'Home Depot (HD)',
        'type'      => 'us',
        'timezone'  => 'America/New_York',
        'open_time' => '09:30:00',
        'decimals' => 2,
    ],
    

];