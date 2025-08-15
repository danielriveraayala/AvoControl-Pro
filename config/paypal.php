<?php

return [
    'mode' => env('PAYPAL_ENVIRONMENT', 'sandbox'),

    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
        'webhook_id' => env('PAYPAL_SANDBOX_WEBHOOK_ID'),
    ],

    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
        'webhook_id' => env('PAYPAL_LIVE_WEBHOOK_ID'),
    ],

    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'locale' => env('PAYPAL_LOCALE', 'en_US'),
    'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true),

    'sandbox_api_url' => 'https://api-m.sandbox.paypal.com',
    'live_api_url' => 'https://api-m.paypal.com',

    'plans' => [
        'trial' => [
            'name' => 'Trial Gratuito',
            'description' => 'Prueba gratuita por 7 días',
            'amount' => 0,
            'currency' => 'USD',
            'interval_unit' => 'day',
            'interval_count' => 7,
            'trial_days' => 7,
            'paypal_plan_id' => env('PAYPAL_TRIAL_PLAN_ID'),
        ],
        'basic' => [
            'name' => 'Plan Básico',
            'description' => 'Perfecto para centros de acopio pequeños',
            'amount' => 29,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 7,
            'paypal_plan_id' => env('PAYPAL_BASIC_PLAN_ID'),
        ],
        'premium' => [
            'name' => 'Plan Premium',
            'description' => 'Para empresas medianas',
            'amount' => 79,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 7,
            'paypal_plan_id' => env('PAYPAL_PREMIUM_PLAN_ID'),
        ],
        'enterprise' => [
            'name' => 'Plan Enterprise',
            'description' => 'Para empresas grandes',
            'amount' => 199,
            'currency' => 'USD',
            'interval_unit' => 'month',
            'interval_count' => 1,
            'trial_days' => 14,
            'paypal_plan_id' => env('PAYPAL_ENTERPRISE_PLAN_ID'),
        ],
    ],

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

    'log_requests' => env('PAYPAL_LOG_REQUESTS', true),
    'log_responses' => env('PAYPAL_LOG_RESPONSES', true),
];