<?php

if (!function_exists('getNotificationIcon')) {
    function getNotificationIcon($type) {
        $icons = [
            'test_daily' => 'fas fa-info-circle text-info',
            'inventory_low' => 'fas fa-exclamation-triangle text-warning',
            'payment_overdue' => 'fas fa-dollar-sign text-danger',
            'sale_completed' => 'fas fa-check-circle text-success',
            'system' => 'fas fa-cog text-secondary'
        ];
        return $icons[$type] ?? 'fas fa-bell text-primary';
    }
}

if (!function_exists('getNotificationBadgeClass')) {
    function getNotificationBadgeClass($type) {
        $classes = [
            'test_daily' => 'info',
            'inventory_low' => 'warning',
            'payment_overdue' => 'danger',
            'sale_completed' => 'success',
            'system' => 'secondary'
        ];
        return $classes[$type] ?? 'primary';
    }
}

if (!function_exists('getPriorityBadgeClass')) {
    function getPriorityBadgeClass($priority) {
        $classes = [
            'critical' => 'danger',
            'high' => 'warning',
            'normal' => 'info',
            'low' => 'secondary'
        ];
        return $classes[$priority] ?? 'primary';
    }
}