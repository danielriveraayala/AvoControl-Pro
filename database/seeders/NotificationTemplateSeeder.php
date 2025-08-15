<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Notificación de Prueba Diaria',
                'slug' => 'test-daily',
                'type' => 'test_daily',
                'channel' => 'all',
                'priority' => 'normal',
                'title_template' => '🔔 Prueba Diaria - {date}',
                'message_template' => 'Sistema de notificaciones funcionando correctamente. {message}',
                'email_subject_template' => 'AvoControl Pro - Prueba Diaria {date}',
                'email_body_template' => '<h2>🥑 AvoControl Pro</h2><p>El sistema de notificaciones está funcionando correctamente.</p><p><strong>Detalles:</strong></p><ul><li>Fecha: {date}</li><li>Usuario: {user_name}</li><li>Mensaje: {message}</li></ul>',
                'push_title_template' => '🔔 AvoControl - Prueba',
                'push_body_template' => 'Sistema funcionando: {message}',
                'variables' => [
                    'date' => ['type' => 'string', 'description' => 'Fecha actual', 'required' => true],
                    'user_name' => ['type' => 'string', 'description' => 'Nombre del usuario', 'required' => false],
                    'message' => ['type' => 'string', 'description' => 'Mensaje personalizado', 'required' => false]
                ],
                'conditions' => ['schedule' => 'daily'],
                'settings' => ['auto_send' => true],
                'is_active' => true,
                'is_system' => true
            ],
            [
                'name' => 'Inventario Bajo',
                'slug' => 'inventory-low',
                'type' => 'inventory_low',
                'channel' => 'all',
                'priority' => 'high',
                'title_template' => '⚠️ Inventario Bajo - {product_name}',
                'message_template' => 'El producto {product_name} tiene stock bajo: {current_stock} unidades restantes.',
                'email_subject_template' => 'ALERTA: Inventario Bajo - {product_name}',
                'email_body_template' => '<h2>⚠️ Alerta de Inventario</h2><p>El siguiente producto requiere atención:</p><ul><li><strong>Producto:</strong> {product_name}</li><li><strong>Stock actual:</strong> {current_stock} unidades</li><li><strong>Stock mínimo:</strong> {minimum_stock} unidades</li></ul><p>Se recomienda reabastecer el inventario.</p>',
                'push_title_template' => '⚠️ Stock Bajo',
                'push_body_template' => '{product_name}: {current_stock} unidades',
                'variables' => [
                    'product_name' => ['type' => 'string', 'description' => 'Nombre del producto', 'required' => true],
                    'current_stock' => ['type' => 'number', 'description' => 'Stock actual', 'required' => true],
                    'minimum_stock' => ['type' => 'number', 'description' => 'Stock mínimo', 'required' => false]
                ],
                'conditions' => ['trigger' => 'stock_level'],
                'settings' => ['threshold' => 10],
                'is_active' => true,
                'is_system' => true
            ]
        ];

        foreach ($templates as $templateData) {
            \App\Models\NotificationTemplate::create($templateData);
        }

        $this->command->info('✅ Notification templates seeded successfully!');
    }
}
