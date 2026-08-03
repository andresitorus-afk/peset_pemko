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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
    ],

    'chatbot' => [
        'fallback_reply' => 'Mohon maaf, pertanyaan Anda akan diteruskan ke petugas. Mohon tunggu balasan, terima kasih.',
        'greeting' => 'Halo! Selamat datang di Live Chat PESET. Kami siap membantu Anda terkait pemanfaatan aset daerah. Silakan ketik pertanyaan Anda.',
    ],

    'chat' => [
        // ponytail: auto-close dijalankan saat sesi dibaca (bukan cron); 10 menit tanpa pesan visitor → sesi tertutup.
        'auto_close_minutes' => env('CHAT_AUTO_CLOSE_MINUTES', 10),
    ],

];
