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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'momo' => [
        'partnerCode' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'), // THAY ĐỔI giá trị mặc định
        'accessKey' => env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'),   // THAY ĐỔI giá trị mặc định
        'secretKey' => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'), // THAY ĐỔI giá trị mặc định
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'), // Đảm bảo đúng
        'redirectUrl' => env('MOMO_REDIRECT_URL', 'http://127.0.0.1:8000/'),
        'ipnUrl' => env('MOMO_IPN_URL', 'http://127.0.0.1:8000/'),
        'autoCapture' => env('MOMO_AUTO_CAPTURE', true),
        'lang' => env('MOMO_LANG', 'vi'),
    ],
    'vnpay' => [
        'tmn_code' => env('VNP_TMN_CODE', 'EX88K7KG'), # THAY ĐỔI giá trị mặc định
        'hash_secret' => env('VNP_HASH_SECRET', '0KIAO1JWZK54YKNIVM4RENENN2TDP6KM'), # THAY ĐỔI giá trị mặc định
        'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNP_RETURN_URL', 'http://127.0.0.1:8000'),
        'ipn_url' => env('VNP_IPN_URL', 'http://127.0.0.1:8000'),
    ],
];
