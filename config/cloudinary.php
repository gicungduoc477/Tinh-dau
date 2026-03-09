<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | An official Laravel wrapper for the Cloudinary PHP SDK.
    |
    | More information can be found on the GitHub repository:
    | https://github.com/cloudinary-labs/cloudinary-laravel
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    /*
     * You can also configure the cloud using separate configuration keys,
     * which will override any configuration present in the CLOUDINARY_URL.
     */
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
    'api_key' => env('CLOUDINARY_API_KEY', ''),
    'api_secret' => env('CLOUDINARY_API_SECRET', ''),

    /*
     * Upload-specific configuration
     */
    'uploads' => [
        'use_filename' => true,
        'unique_filename' => false,
        'overwrite' => true,
    ],

    /*
     * Fetch-specific configuration
     */
    'fetch' => [
        'format' => 'auto',
        'quality' => 'auto',
    ],
];
