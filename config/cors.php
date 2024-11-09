<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'room/*'],

    'allowed_methods' => ['*'],

    // Replace '*' with your frontend domain for better security, e.g., 'http://your-frontend-domain.com'
    'allowed_origins' => ['*'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // Set to true if you need credentials like cookies
];

