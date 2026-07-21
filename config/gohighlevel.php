<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | GoHighLevel (LeadConnector) credentials
    |--------------------------------------------------------------------------
    | A Private Integration Token ("pit-...") and the location it belongs to.
    */
    'token'       => env('GHL_PRIVATE_INTEGRATION_TOKEN'),
    'location_id' => env('GHL_LOCATION_ID'),
    'api_base'    => env('GHL_API_BASE', 'https://services.leadconnectorhq.com'),

    /*
    |--------------------------------------------------------------------------
    | Behaviour
    |--------------------------------------------------------------------------
    */
    // Timezone used when requesting calendar availability.
    'timezone' => env('GHL_TIMEZONE', 'America/New_York'),

    // Tag applied on every submission so a GHL Workflow re-fires (e.g. notify email).
    'quote_tag' => env('GHL_QUOTE_TAG', 'Website Quote Form'),

    // How many days of availability to offer (capped at 60).
    'availability_days' => (int) env('GHL_AVAILABILITY_DAYS', 14),

    // Reference only — surfaced in the UI. The GHL Workflow sends the email.
    'notification_email' => env('NOTIFICATION_EMAIL', 'support@domain.com'),

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    | When register_routes is true the package registers GET/POST routes under
    | the given prefix and middleware group. Set to false to wire your own.
    */
    'register_routes' => (bool) env('GHL_REGISTER_ROUTES', true),
    'route_prefix'    => env('GHL_ROUTE_PREFIX', 'ghl'),
    'route_middleware' => ['web'],
];
