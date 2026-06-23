<?php

namespace App\Constants;

class BillingModule
{
    public const RIDE    = 'ride';
    public const SERVICE = 'service';
    public const BUYSELL = 'buysell';
    public const RENTAL  = 'rental';

    public const ALL = [self::RIDE, self::SERVICE, self::BUYSELL, self::RENTAL];

    // Free-tier accounting per module:
    //   intro_credit  → lifetime free posts (ride, rental)
    //   active_cap    → max concurrent active items (buy/sell)
    //   category_cap  → max free categories (service provider)
    public const FREE_MODE = [
        self::RIDE    => 'intro_credit',
        self::RENTAL  => 'intro_credit',
        self::BUYSELL => 'active_cap',
        self::SERVICE => 'category_cap',
    ];
}
