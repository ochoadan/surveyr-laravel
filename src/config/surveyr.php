<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App ID
    |--------------------------------------------------------------------------
    |
    | The ID of this app in Surveyr.
    |
    */

    'app_id' => env('SURVEYR_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Enter your Surveyr API token here to allow your app to authorize with
    | the Surveyr API.
    |
    */

    'api_token' => env('SURVEYR_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | The Surveyr URL. You probably don't need to change this.
    |
    */

    'url' => env('SURVEYR_URL', 'https://surveyr.io'),

];
