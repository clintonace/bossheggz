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
    | Company
    |--------------------------------------------------------------------------
    |
    | The brand shown in the logo, footer and contact page. The WhatsApp
    | number is written in international format without "+" or spaces.
    |
    */

    'company' => [
        'name' => env('COMPANY_NAME', 'BOSSHEGZ Realty'),
        'legal_name' => env('COMPANY_LEGAL_NAME', 'BOSSHEGZ Realty Ltd'),
        'tagline' => env('COMPANY_TAGLINE', 'Strategic Property. Lasting Wealth.'),
        'phone' => env('COMPANY_PHONE', '+2348111708068'),
        'whatsapp' => env('COMPANY_WHATSAPP', '2348111708068'),
        'email' => env('COMPANY_EMAIL', 'bosshegz@yahoo.com'),
        'instagram' => env('COMPANY_INSTAGRAM', 'bosshegzrealty'),
        'address' => env('COMPANY_ADDRESS'),
    ],

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
