<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Parking System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the parking system.
    |
    */

    'location_name' => env('PARKING_LOCATION_NAME', 'Parking Location'),

    'default_currency' => env('PARKING_DEFAULT_CURRENCY', '₱'),

    'session_number_prefix' => env('PARKING_SESSION_PREFIX', 'SES'),

    'ticket_number_prefix' => env('PARKING_TICKET_PREFIX', 'TKT'),

    'auto_print_sessions' => env('PARKING_AUTO_PRINT_SESSIONS', true),

    'auto_print_tickets' => env('PARKING_AUTO_PRINT_TICKETS', true),

    'printer_width' => env('PARKING_PRINTER_WIDTH', 32), // 58mm thermal printer

    'receipt_template' => [
        'header' => [
            'show_logo' => true,
            'show_location' => true,
            'show_title' => true,
        ],
        'footer' => [
            'show_instructions' => true,
            'show_thank_you' => true,
        ],
    ],
];
