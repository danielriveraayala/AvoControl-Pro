/**
 * AvoControl Pro - Service Worker para Push Notifications
 * Sistema nativo de notificaciones push sin dependencias externas
 */

// Versión del service worker para cache management
const CACHE_VERSION = 'avocontrol-v1.0.0';
const NOTIFICATION_CACHE = 'avocontrol-notifications';

// Configuración de notificaciones
const NOTIFICATION_CONFIG = {
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    vibrate: [200, 100, 200],
    requireInteraction: false,
    silent: false,
    tag: 'avocontrol-notification'
};

// Tipos de notificaciones con configuraciones específicas
const NOTIFICATION_TYPES = {
    'low_inventory': {
        icon: '📦',
        color: '#ffc107',
        urgency: 'high',
        actions: [
            { action: 'view_inventory', title: '📦 Ver Inventario', icon: '/icons/inventory.png' },
            { action: 'dismiss', title: '❌ Cerrar', icon: '/icons/close.png' }
        ]
    },
    'payment_reminder': {
        icon: '💰',
        color: '#dc3545',
        urgency: 'high',
        actions: [
            { action: 'view_payments', title: '💰 Ver Pagos', icon: '/icons/payments.png' },
            { action: 'dismiss', title: '❌ Cerrar', icon: '/icons/close.png' }
        ]
    },
    'daily_report': {
        icon: '📊',
        color: '#28a745',
        urgency: 'normal',
        actions: [
            { action: 'view_dashboard', title: '📊 Ver Dashboard', icon: '/icons/dashboard.png' },
            { action: 'dismiss', title: '❌ Cerrar', icon: '/icons/close.png' }
        ]
    },
    'new_lot': {
        icon: '🚚',
        color: '#17a2b8',
        urgency: 'normal',
        actions: [
            { action: 'view_lot', title: '👁️ Ver Lote', icon: '/icons/lot.png' },
            { action: 'view_inventory', title: '📦 Inventario', icon: '/icons/inventory.png' },
            { action: 'dismiss', title: '❌ Cerrar', icon: '/icons/close.png' }
        ]
    },
    'system_alert': {
        icon: '🚨',
        color: '#dc3545',
        urgency: 'critical',
        actions: [
            { action: 'view_system', title: '⚙️ Ver Sistema', icon: '/icons/system.png' },
            { action: 'dismiss', title: '❌ Cerrar', icon: '/icons/close.png' }
        ]
    },
    'default': {
        icon: '📢',
        color: '#6c757d',
        urgency: 'normal',
        actions: [
            { action: 'view_dashboard', title: '📊 Ver Dashboard', icon: '/icons/dashboard.png' },
            { action: 'dismiss', title: '❌ Cerrar', icon: '/icons/close.png' }
        ]
    }
};

/**
 * Event Listener: Push - Maneja notificaciones entrantes
 */
self.addEventListener('push', function(event) {
    console.log('[SW] Push notification received', event);
    
    // Para desarrollo local, mostrar notificación de prueba directamente
    const testPayload = {
        title: '🎉 AvoControl Pro - Notificación de Prueba',
        body: 'Esta notificación se generó desde el Service Worker local',
        type: 'default',
        priority: 'normal'
    };
    
    if (event.data) {
        try {
            const payload = event.data.json();
            console.log('[SW] Push payload:', payload);
            
            const notificationData = prepareNotificationData(payload);
            
            event.waitUntil(
                showNotification(notificationData)
            );
        } catch (error) {
            console.error('[SW] Error processing push notification:', error);
            
            // Mostrar notificación de fallback
            event.waitUntil(
                showNotification(prepareNotificationData(testPayload))
            );
        }
    } else {
        // Sin datos - mostrar notificación de prueba
        console.log('[SW] Push event but no data - showing test notification');
        event.waitUntil(
            showNotification(prepareNotificationData(testPayload))
        );
    }
});

/**
 * Event Listener: Notification Click - Maneja clicks en notificaciones
 */
self.addEventListener('notificationclick', function(event) {
    console.log('[SW] Notification click received', event);
    
    event.notification.close();
    
    const action = event.action;
    const data = event.notification.data || {};
    
    event.waitUntil(
        handleNotificationClick(action, data)
    );
});

/**
 * Event Listener: Notification Close - Maneja cierre de notificaciones
 */
self.addEventListener('notificationclose', function(event) {
    console.log('[SW] Notification closed', event);
    
    // Opcional: Enviar analytics sobre notificaciones cerradas
    const data = event.notification.data || {};
    
    // Aquí puedes enviar datos de tracking al servidor
    if (data.tracking_id) {
        fetch('/api/notifications/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tracking_id: data.tracking_id,
                action: 'closed',
                timestamp: new Date().toISOString()
            })
        }).catch(err => console.log('[SW] Tracking error:', err));
    }
});

/**
 * Preparar datos de notificación basado en el payload
 */
function prepareNotificationData(payload) {
    const type = payload.type || 'default';
    const config = NOTIFICATION_TYPES[type] || NOTIFICATION_TYPES.default;
    
    // Datos base de la notificación
    const notificationData = {
        title: payload.title || 'AvoControl Pro',
        body: payload.body || 'Nueva notificación del sistema',
        icon: payload.icon || NOTIFICATION_CONFIG.icon,
        badge: NOTIFICATION_CONFIG.badge,
        tag: payload.tag || `avocontrol-${type}-${Date.now()}`,
        data: {
            type: type,
            url: payload.url || '/dashboard',
            tracking_id: payload.tracking_id || null,
            priority: payload.priority || 'normal',
            timestamp: new Date().toISOString(),
            payload: payload
        },
        vibrate: getVibrationPattern(payload.priority || 'normal'),
        requireInteraction: payload.priority === 'critical',
        silent: payload.silent || false,
        actions: config.actions || []
    };

    // Personalización por tipo
    if (payload.image) {
        notificationData.image = payload.image;
    }

    // Personalización de acciones específicas
    if (payload.actions && Array.isArray(payload.actions)) {
        notificationData.actions = payload.actions;
    }

    return notificationData;
}

/**
 * Mostrar notificación con configuración personalizada
 */
function showNotification(data) {
    return self.registration.showNotification(data.title, {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        tag: data.tag,
        data: data.data,
        vibrate: data.vibrate,
        requireInteraction: data.requireInteraction,
        silent: data.silent,
        actions: data.actions,
        image: data.image
    });
}

/**
 * Manejar clicks en notificaciones y acciones
 */
function handleNotificationClick(action, data) {
    let targetUrl = data.url || '/dashboard';
    
    // Determinar URL basada en la acción
    switch (action) {
        case 'view_inventory':
            targetUrl = '/acopio';
            break;
        case 'view_payments':
            targetUrl = '/payments';
            break;
        case 'view_dashboard':
            targetUrl = '/dashboard';
            break;
        case 'view_lot':
            targetUrl = data.lot_id ? `/lots/${data.lot_id}` : '/lots';
            break;
        case 'view_system':
            targetUrl = '/configuration';
            break;
        case 'dismiss':
            // Solo cerrar la notificación, no navegar
            return Promise.resolve();
        default:
            // Click en el cuerpo de la notificación
            targetUrl = data.url || '/dashboard';
            break;
    }
    
    // Enviar tracking si está disponible
    if (data.tracking_id) {
        fetch('/api/notifications/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tracking_id: data.tracking_id,
                action: action || 'clicked',
                url: targetUrl,
                timestamp: new Date().toISOString()
            })
        }).catch(err => console.log('[SW] Tracking error:', err));
    }
    
    // Abrir o enfocar ventana del navegador
    return clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then(function(clientList) {
        // Buscar ventana existente de AvoControl
        for (let client of clientList) {
            if (client.url.includes('/dashboard') || 
                client.url.includes('/lots') || 
                client.url.includes('/acopio') ||
                client.url.includes('/payments')) {
                
                // Enfocar ventana existente y navegar
                return client.focus().then(() => {
                    return client.navigate(targetUrl);
                });
            }
        }
        
        // Abrir nueva ventana si no existe
        if (clients.openWindow) {
            return clients.openWindow(targetUrl);
        }
    });
}

/**
 * Obtener patrón de vibración basado en prioridad
 */
function getVibrationPattern(priority) {
    const patterns = {
        'low': [100, 50, 100],
        'normal': [200, 100, 200],
        'high': [300, 100, 300, 100, 300],
        'critical': [500, 200, 500, 200, 500, 200, 500]
    };
    
    return patterns[priority] || patterns.normal;
}

/**
 * Event Listener: Install - Instalación del Service Worker
 */
self.addEventListener('install', function(event) {
    console.log('[SW] Service Worker installing');
    
    // Activar inmediatamente sin esperar
    self.skipWaiting();
    
    event.waitUntil(
        caches.open(CACHE_VERSION).then(function(cache) {
            console.log('[SW] Cache opened');
            // Precache recursos esenciales si es necesario
            return cache.addAll([
                '/favicon.ico'
                // Agregar otros recursos críticos aquí
            ]);
        }).catch(err => {
            console.log('[SW] Cache error:', err);
        })
    );
});

/**
 * Event Listener: Activate - Activación del Service Worker
 */
self.addEventListener('activate', function(event) {
    console.log('[SW] Service Worker activating');
    
    event.waitUntil(
        Promise.all([
            // Tomar control inmediatamente
            self.clients.claim(),
            
            // Limpiar caches antiguos
            caches.keys().then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if (cacheName !== CACHE_VERSION && cacheName !== NOTIFICATION_CACHE) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
        ])
    );
});

/**
 * Event Listener: Message - Comunicación con la aplicación principal
 */
self.addEventListener('message', function(event) {
    console.log('[SW] Message received:', event.data);
    
    const data = event.data;
    
    switch (data.type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'GET_VERSION':
            event.ports[0].postMessage({
                type: 'VERSION',
                version: CACHE_VERSION
            });
            break;
            
        case 'CLEAR_NOTIFICATIONS':
            // Limpiar todas las notificaciones
            self.registration.getNotifications().then(notifications => {
                notifications.forEach(notification => notification.close());
            });
            break;
            
        case 'TEST_NOTIFICATION':
            // Mostrar notificación de prueba
            self.registration.showNotification('Prueba de Notificación', {
                body: 'Esta es una notificación de prueba desde AvoControl Pro',
                icon: NOTIFICATION_CONFIG.icon,
                tag: 'test-notification',
                actions: [
                    { action: 'view_dashboard', title: '📊 Dashboard' },
                    { action: 'dismiss', title: '❌ Cerrar' }
                ]
            });
            break;
    }
});

console.log('[SW] AvoControl Pro Service Worker loaded successfully');