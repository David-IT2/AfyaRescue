<?php

return [
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'eta_enabled' => env('GOOGLE_MAPS_ETA_ENABLED', false),
    ],
    'sms' => [
        'enabled' => env('AFYARESCUE_SMS_ENABLED', false),
        'driver' => env('AFYARESCUE_SMS_DRIVER', 'log'),
    ],
    'critical_alert_email' => env('AFYARESCUE_CRITICAL_ALERT_EMAIL'),
    'queue_notifications' => env('AFYARESCUE_QUEUE_NOTIFICATIONS', true),
];
