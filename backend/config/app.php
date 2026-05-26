<?php

return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'travelplan_pro',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ],
    'jwt' => [
        'secret' => 'ChangeThisSecretKeyToAStrongOne',
        'issuer' => 'TravelPlanProAPI',
        'audience' => 'TravelPlanProClient',
        'expiration' => 7200
    ],
    'openweather' => [
        'api_key' => 'YOUR_OPENWEATHER_API_KEY'
    ]
];
