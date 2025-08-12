<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'starter',
                'display_name' => 'Plan Inicial',
                'description' => 'Perfecto para pequeños acopiadores que inician su negocio. Incluye funcionalidades básicas para gestión de inventario y ventas.',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'max_users' => 3,
                'max_suppliers' => 25,
                'max_customers' => 50,
                'max_lots_per_month' => 200,
                'max_sales_per_month' => 300,
                'storage_limit_gb' => 2,
                'features' => [
                    'basic_inventory',
                    'sales_management',
                    'payment_tracking',
                    'basic_reports',
                    'email_support'
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1
            ],
            [
                'name' => 'professional',
                'display_name' => 'Plan Profesional',
                'description' => 'Ideal para centros de acopio en crecimiento. Incluye reportes avanzados, múltiples usuarios y mayor capacidad.',
                'price' => 59.99,
                'billing_cycle' => 'monthly',
                'max_users' => 10,
                'max_suppliers' => 100,
                'max_customers' => 200,
                'max_lots_per_month' => 1000,
                'max_sales_per_month' => 1500,
                'storage_limit_gb' => 10,
                'features' => [
                    'basic_inventory',
                    'sales_management',
                    'payment_tracking',
                    'advanced_reports',
                    'profit_analysis',
                    'supplier_analysis',
                    'customer_analysis',
                    'notification_system',
                    'email_support',
                    'priority_support',
                    'data_export'
                ],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'enterprise',
                'display_name' => 'Plan Empresarial',
                'description' => 'Para grandes operaciones. Sin límites en usuarios y con todas las funcionalidades premium incluidas.',
                'price' => 149.99,
                'billing_cycle' => 'monthly',
                'max_users' => -1, // Ilimitado
                'max_suppliers' => 500,
                'max_customers' => 1000,
                'max_lots_per_month' => 5000,
                'max_sales_per_month' => 10000,
                'storage_limit_gb' => 50,
                'features' => [
                    'basic_inventory',
                    'sales_management',
                    'payment_tracking',
                    'advanced_reports',
                    'profit_analysis',
                    'supplier_analysis',
                    'customer_analysis',
                    'notification_system',
                    'api_access',
                    'custom_integrations',
                    'multi_location',
                    'advanced_permissions',
                    'audit_logs',
                    'priority_support',
                    'phone_support',
                    'dedicated_support',
                    'data_export',
                    'custom_reports',
                    'white_labeling'
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3
            ],
            [
                'name' => 'trial',
                'display_name' => 'Período de Prueba',
                'description' => 'Plan gratuito por 14 días para evaluar todas las funcionalidades del sistema.',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'max_users' => 2,
                'max_suppliers' => 10,
                'max_customers' => 20,
                'max_lots_per_month' => 50,
                'max_sales_per_month' => 75,
                'storage_limit_gb' => 1,
                'features' => [
                    'basic_inventory',
                    'sales_management',
                    'payment_tracking',
                    'basic_reports',
                    'email_support',
                    'trial_limitations'
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 0
            ],
            [
                'name' => 'custom',
                'display_name' => 'Plan Personalizado',
                'description' => 'Solución a medida para empresas con necesidades específicas. Contacta con nuestro equipo comercial.',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'max_users' => -1,
                'max_suppliers' => -1,
                'max_customers' => -1,
                'max_lots_per_month' => -1,
                'max_sales_per_month' => -1,
                'storage_limit_gb' => -1,
                'features' => [
                    'all_features',
                    'custom_development',
                    'dedicated_infrastructure',
                    'sla_guarantee',
                    'on_premise_option',
                    'dedicated_support',
                    'custom_training'
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4
            ]
        ];

        foreach ($plans as $plan) {
            \App\Models\TenantPlan::create($plan);
        }

        $this->command->info('✅ Planes de suscripción creados exitosamente');
    }
}
