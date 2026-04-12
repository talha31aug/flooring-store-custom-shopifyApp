<?php

return [
    'paths' => [
        'api/*',              // General API routes, if applicable
        'custom-api',         // Your specific route for the Shopify API
        'custom-api/*',
        'csrf-token',         // Your specific route for the Shopify API
        'csrf-token/*',
        'custom-api-create-product',         // Your specific route for the Shopify API
        'custom-api-create-product/*',
        'custom-api-delete-product',         // Your specific route for the Shopify API
        'custom-api-delete-product/*',     // If you have other related routes under this endpoint
        // Add any other routes that require CORS
    ],
    'allowed_methods' => ['*'],  // Allows all HTTP methods
    'allowed_origins' => ['*'],  // Allows all origins (consider restricting this in production)
    'allowed_headers' => ['*'],   // Allows all headers
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
