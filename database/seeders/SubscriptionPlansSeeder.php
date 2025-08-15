<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing plans
        DB::table('subscription_plans')->delete();
        
        $plans = [
            [
                'key' => 'trial',
                'name' => 'Trial Gratuito',
                'description' => 'Prueba gratuita de 7 días para explorar todas las funcionalidades básicas del sistema.',
                'price' => 0.00,
                'annual_price' => null, // Trial no tiene precio anual
                'annual_discount_percentage' => null,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 7,
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => false,
                'show_on_landing' => true,
                'button_text' => 'Comenzar Prueba Gratis',
                'popular_badge' => null,
                'sort_order' => 10,
                'max_users' => 1,
                'max_lots_per_month' => 50,
                'max_storage_gb' => 1,
                'max_locations' => 1,
                'features' => [
                    'basic_reports',
                    'email_notifications',
                    'manual_backups',
                    'email_support'
                ],
                'color' => '#10B981',
                'icon' => 'fas fa-gift',
            ],
            [
                'key' => 'basic',
                'name' => 'Básico',
                'description' => 'Plan ideal para centros de acopio pequeños con funcionalidades esenciales.',
                'price' => 29.00,
                'annual_price' => 295.80, // $29 * 12 * 0.85 = $295.80 (15% descuento)
                'annual_discount_percentage' => 15,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 7,
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => false,
                'show_on_landing' => true,
                'button_text' => 'Contratar Ahora',
                'popular_badge' => null,
                'sort_order' => 20,
                'max_users' => 5,
                'max_lots_per_month' => 500,
                'max_storage_gb' => 2,
                'max_locations' => 1,
                'features' => [
                    'basic_reports',
                    'advanced_reports',
                    'export_excel',
                    'export_pdf',
                    'email_notifications',
                    'api_access',
                    'api_rate_limit_basic',
                    'automatic_backups',
                    'email_support'
                ],
                'color' => '#3B82F6',
                'icon' => 'fas fa-seedling',
                'paypal_plan_id' => env('PAYPAL_BASIC_PLAN_ID'),
                'paypal_annual_plan_id' => env('PAYPAL_BASIC_ANNUAL_PLAN_ID'),
            ],
            [
                'key' => 'premium',
                'name' => 'Premium',
                'description' => 'Plan avanzado para empresas medianas con funcionalidades completas y soporte prioritario.',
                'price' => 79.00,
                'annual_price' => 806.40, // $79 * 12 * 0.85 = $806.40 (15% descuento)
                'annual_discount_percentage' => 15,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => true,
                'is_custom' => false,
                'show_on_landing' => true,
                'button_text' => 'Más Popular',
                'popular_badge' => 'MÁS POPULAR',
                'sort_order' => 30,
                'max_users' => 25,
                'max_lots_per_month' => 2000,
                'max_storage_gb' => 10,
                'max_locations' => 3,
                'features' => [
                    'basic_reports',
                    'advanced_reports',
                    'custom_reports',
                    'export_excel',
                    'export_pdf',
                    'email_notifications',
                    'push_notifications',
                    'sms_notifications',
                    'api_access',
                    'api_rate_limit_premium',
                    'automatic_backups',
                    'cloud_storage',
                    'multi_location',
                    'advanced_permissions',
                    'priority_support'
                ],
                'color' => '#8B5CF6',
                'icon' => 'fas fa-star',
                'paypal_plan_id' => env('PAYPAL_PREMIUM_PLAN_ID'),
                'paypal_annual_plan_id' => env('PAYPAL_PREMIUM_ANNUAL_PLAN_ID'),
            ],
            [
                'key' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'Plan empresarial para operaciones grandes con todas las funcionalidades y soporte dedicado.',
                'price' => 199.00,
                'annual_price' => 2029.80, // $199 * 12 * 0.85 = $2029.80 (15% descuento)
                'annual_discount_percentage' => 15,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => false,
                'show_on_landing' => true,
                'button_text' => 'Contactar Ventas',
                'popular_badge' => null,
                'sort_order' => 40,
                'max_users' => 100,
                'max_lots_per_month' => -1, // Unlimited
                'max_storage_gb' => 50,
                'max_locations' => 10,
                'features' => [
                    'basic_reports',
                    'advanced_reports',
                    'custom_reports',
                    'export_excel',
                    'export_pdf',
                    'email_notifications',
                    'push_notifications',
                    'sms_notifications',
                    'custom_alerts',
                    'api_access',
                    'api_rate_limit_unlimited',
                    'automatic_backups',
                    'cloud_storage',
                    'custom_branding',
                    'custom_domain',
                    'multi_location',
                    'advanced_permissions',
                    'audit_logs',
                    'priority_support',
                    'phone_support',
                    'dedicated_manager'
                ],
                'color' => '#F59E0B',
                'icon' => 'fas fa-crown',
                'paypal_plan_id' => env('PAYPAL_ENTERPRISE_PLAN_ID'),
                'paypal_annual_plan_id' => env('PAYPAL_ENTERPRISE_ANNUAL_PLAN_ID'),
            ],
            [
                'key' => 'corporate',
                'name' => 'Corporativo',
                'description' => 'Solución personalizada para grandes corporativos con múltiples empresas y necesidades especiales.',
                'price' => 499.00,
                'annual_price' => 5089.80, // $499 * 12 * 0.85 = $5089.80 (15% descuento)
                'annual_discount_percentage' => 15,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => true,
                'show_on_landing' => true,
                'button_text' => 'Solicitar Demo',
                'popular_badge' => null,
                'sort_order' => 50,
                'max_users' => -1, // Unlimited
                'max_lots_per_month' => -1, // Unlimited
                'max_storage_gb' => -1, // Unlimited
                'max_locations' => -1, // Unlimited
                'features' => [
                    'basic_reports',
                    'advanced_reports',
                    'custom_reports',
                    'export_excel',
                    'export_pdf',
                    'email_notifications',
                    'push_notifications',
                    'sms_notifications',
                    'custom_alerts',
                    'api_access',
                    'api_rate_limit_unlimited',
                    'webhook_integrations',
                    'automatic_backups',
                    'cloud_storage',
                    'custom_branding',
                    'white_label',
                    'custom_domain',
                    'multi_location',
                    'multi_tenant',
                    'advanced_permissions',
                    'audit_logs',
                    'sla_guarantee',
                    'priority_support',
                    'phone_support',
                    'dedicated_manager',
                    'on_premise_option',
                    'custom_integrations'
                ],
                'color' => '#DC2626',
                'icon' => 'fas fa-building',
                'paypal_plan_id' => env('PAYPAL_CORPORATE_PLAN_ID'),
                'paypal_annual_plan_id' => env('PAYPAL_CORPORATE_ANNUAL_PLAN_ID'),
            ]
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::create($planData);
        }

        $this->command->info('Subscription plans seeded successfully!');
        $this->command->info('Total plans created: ' . count($plans));
        
        $plansWithAnnual = collect($plans)->filter(function($plan) {
            return !is_null($plan['annual_price']);
        })->count();
        
        $this->command->info('Plans with annual pricing: ' . $plansWithAnnual);
        $this->command->info('Each plan with annual pricing includes 15% discount');
    }
}