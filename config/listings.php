<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The symbol shown in front of every property price on the site.
    |
    */

    'currency' => env('LISTING_CURRENCY', '₦'),

    /*
    |--------------------------------------------------------------------------
    | Contact details
    |--------------------------------------------------------------------------
    |
    | Shown in the site footer and on property pages.
    |
    */

    'contact_email' => env('LISTING_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),

    'contact_phone' => env('LISTING_CONTACT_PHONE'),

    /*
    |--------------------------------------------------------------------------
    | Test account switcher
    |--------------------------------------------------------------------------
    |
    | A floating "Test accounts" button that signs you in as any admin or
    | client in one click. It is a login backdoor, so it is only enabled in
    | the local environment by default and must never be enabled in production.
    |
    */

    'dev_account_switcher' => (bool) env('DEV_ACCOUNT_SWITCHER', env('APP_ENV') === 'local'),

];
