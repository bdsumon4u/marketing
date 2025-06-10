<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MLM Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the MLM system.
    |
    */

    'registration_fee' => [
        'with_product' => env('REGISTRATION_FEE_WITH_PRODUCT', 1000),
        'without_product' => env('REGISTRATION_FEE_WITHOUT_PRODUCT', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rank Threshold
    |--------------------------------------------------------------------------
    |
    | This is the minimum number of active referrals required to achieve a new rank.
    |
    */
    'rank_threshold' => env('RANK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | bKash Number
    |--------------------------------------------------------------------------
    |
    | This is the bKash number for the MLM system.
    |
    */
    'bkash_number' => env('BKASH_NUMBER', '01717171717'),
];
