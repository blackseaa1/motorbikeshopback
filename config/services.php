<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS S3 and more. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional file to locate your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'momo' => [
        'partnerCode' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'),
        'accessKey' => env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'),
        'secretKey' => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'redirectUrl' => env('MOMO_REDIRECT_URL', 'http://127.0.0.1:8000/payment/momo/callback'),
        'ipnUrl' => env('MOMO_IPN_URL', 'http://127.0.0.1:8000/payment/momo/ipn'),
        'autoCapture' => env('MOMO_AUTO_CAPTURE', true),
        'lang' => env('MOMO_LANG', 'vi'),
    ],

    'vnpay' => [
        'tmn_code' => env('VNP_TMN_CODE', 'EX88K7KG'),
        'hash_secret' => env('VNP_HASH_SECRET', '0KIAO1JWZK54YKNIVM4RENENN2TDP6KM'),
        'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNP_RETURN_URL', 'http://127.0.0.1:8000/payment/vnpay/callback'), // Cần route callback chính xác
        'ipn_url' => env('VNP_IPN_URL', 'http://127.0.0.1:8000/payment/vnpay/ipn'), // Cần route IPN chính xác và HTTPS
    ],

];
