<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],




    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => null,
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_KEY_ID'),
        'private_key' => env('APPLE_PRIVATE_KEY'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

    'firebase' => [
        'credentials_file' => (function () {
            $custom = env('FIREBASE_CREDENTIALS');
            if (!empty($custom)) {
                if (file_exists($custom)) {
                    return $custom;
                }
                if (file_exists(base_path($custom))) {
                    return base_path($custom);
                }
            }
            $candidates = [
                storage_path('app/firebase-credentials.json'),
                storage_path('app/private/firebase-credentials.json'),
                storage_path('firebase-credentials.json'),
            ];
            foreach ($candidates as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            $google = env('GOOGLE_APPLICATION_CREDENTIALS');
            if (!empty($google)) {
                if (file_exists($google)) {
                    return $google;
                }
                if (file_exists(base_path($google))) {
                    return base_path($google);
                }
            }
            return null;
        })(),
    ],
];
