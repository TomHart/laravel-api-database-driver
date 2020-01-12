<?php
return [
    'query_string_keys' => [
        'limit' => 'limit'
    ],

    'user_agent' => 'TomHart_API_Database_Driver',

    'headers' => [
        'User-Agent' => config('api-database.user_agent', 'TomHart_API_Database_Driver')
    ]
];
