<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PayPal Subscription API integration
    |
    */

    'mode' => env('PAYPAL_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'live'

    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
        'app_id' => env('PAYPAL_SANDBOX_APP_ID'),
        'webhook_id' => env('PAYPAL_SANDBOX_WEBHOOK_ID'),
    ],

    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
        'app_id' => env('PAYPAL_LIVE_APP_ID'),
        'webhook_id' => env('PAYPAL_LIVE_WEBHOOK_ID'),
    ],

    'payment_action' => 'Sale', // Can only be 'Sale', 'Authorization' or 'Order'

    'currency' => env('PAYPAL_CURRENCY', 'USD'),

    'locale' => env('PAYPAL_LOCALE', 'en_US'),

    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | PayPal URLs
    |--------------------------------------------------------------------------
    */
    'sandbox_api_url' => 'https://api-m.sandbox.paypal.com',
    'live_api_url' => 'https://api-m.paypal.com',

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'trial' => [
            'name' => 'Trial Gratuito',
            'description' => 'Prueba gratuita por 7 días - Sin tarjeta de crédito requerida',
            'amount' => 0,
            'currency' => 'USD',
            'interval_unit' => 'day',
            'interval_count' => 7,
            'trial_days' => 7,
            'paypal_plan_id' => env('PAYPAL_TRIAL_PLAN_ID'),
            'features' => [
                'users' => 1,
                'lots_per_month' => 50,
                'storage_gb' => 0.5,
                'support' => false,
                'reports' => 'basic'
            ]
        ],
        'basic' => [
            'name' => 'Plan Básico',
            'description' => 'Perfecto para centros de acopio pequeños',
            'amount' => 39,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 7,
            'paypal_plan_id' => env('PAYPAL_BASIC_PLAN_ID'),
            'features' => [
                'users' => 5,
                'lots_per_month' => 500,
                'storage_gb' => 2,
                'support' => 'email',
                'reports' => 'all',
                'notifications' => 'email'
            ]
        ],
        'premium' => [
            'name' => 'Plan Premium',
            'description' => 'Para empresas medianas con más funcionalidades',
            'amount' => 89,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 7,
            'paypal_plan_id' => env('PAYPAL_PREMIUM_PLAN_ID'),
            'features' => [
                'users' => 25,
                'lots_per_month' => 2000,
                'storage_gb' => 10,
                'support' => 'priority',
                'reports' => 'advanced',
                'notifications' => 'email+push+sms',
                'api_access' => true,
                'backup' => 'automatic'
            ]
        ],
        'enterprise' => [
            'name' => 'Plan Enterprise',
            'description' => 'Para empresas grandes con necesidades avanzadas',
            'amount' => 249,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 14,
            'paypal_plan_id' => env('PAYPAL_ENTERPRISE_PLAN_ID'),
            'features' => [
                'users' => 100,
                'lots_per_month' => 'unlimited',
                'storage_gb' => 50,
                'support' => '24/7',
                'reports' => 'custom',
                'notifications' => 'all',
                'api_access' => 'full',
                'backup' => 'automatic',
                'multi_location' => true,
                'custom_branding' => true
            ]
        ],
        'corporate' => [
            'name' => 'Plan Corporativo',
            'description' => 'Solución personalizada para corporativos',
            'amount' => 499,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 30,
            'paypal_plan_id' => env('PAYPAL_CORPORATE_PLAN_ID'),
            'features' => [
                'users' => 'unlimited',
                'tenants' => 'unlimited',
                'storage_gb' => 'unlimited',
                'support' => 'dedicated',
                'reports' => 'unlimited',
                'dedicated_server' => true,
                'sla_guarantee' => true,
                'custom_development' => true
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'retry_attempts' => 3,
        'grace_period_days' => 3,
        'suspension_after_days' => 7,
        'auto_cancel_after_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Events to Handle
    |--------------------------------------------------------------------------
    */
    'webhook_events' => [
        'BILLING.SUBSCRIPTION.CREATED',
        'BILLING.SUBSCRIPTION.ACTIVATED',
        'BILLING.SUBSCRIPTION.UPDATED',
        'BILLING.SUBSCRIPTION.EXPIRED',
        'BILLING.SUBSCRIPTION.CANCELLED',
        'BILLING.SUBSCRIPTION.SUSPENDED',
        'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED',
        'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
        'PAYMENT.SALE.COMPLETED',
        'PAYMENT.SALE.DENIED',
        'PAYMENT.SALE.REFUNDED',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'log_requests' => env('PAYPAL_LOG_REQUESTS', true),
    'log_responses' => env('PAYPAL_LOG_RESPONSES', true),
    'log_webhooks' => env('PAYPAL_LOG_WEBHOOKS', true),
    'log_level' => env('PAYPAL_LOG_LEVEL', 'info'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'paypal_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Configuration for PayPal
    |--------------------------------------------------------------------------
    */
    'product' => [
        'name' => 'AvoControl Pro Subscription',
        'description' => 'Subscription to AvoControl Pro - Complete avocado collection center management system',
        'type' => 'SERVICE',
        'category' => 'SOFTWARE',
        'image_url' => env('APP_URL') . '/images/logo.png',
        'home_url' => env('APP_URL'),
    ],
];