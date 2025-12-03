<?php
/**
 * Application Configuration
 * Heritage Family Tree Application
 */

return [
    'name' => 'Heritage',
    'version' => '1.0.0',
    'base_url' => 'http://localhost/heritage/public',
    'timezone' => 'UTC',
    'session' => [
        'lifetime' => 7200, // 2 hours
        'name' => 'HERITAGE_SESSION'
    ],
    'security' => [
        'password_min_length' => 8,
        'bcrypt_cost' => 10
    ]
];