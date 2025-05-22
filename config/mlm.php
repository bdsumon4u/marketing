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

    // Maximum number of direct referrals allowed per user
    'max_referrals_per_user' => env('MLM_MAX_REFERRALS', 4),

    // Registration fee in taka
    'registration_fee' => env('MLM_REGISTRATION_FEE', 200),
];
