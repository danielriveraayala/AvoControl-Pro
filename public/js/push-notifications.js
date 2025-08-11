/**
 * AvoControl Pro - Push Notifications Manager
 * Sistema nativo de notificaciones push sin dependencias externas
 */

class AvoControlPush {
    constructor() {
        this.swRegistration = null;
        this.isSubscribed = false;
        this.vapidPublicKey = null;
        this.apiEndpoints = {
            subscribe: '/push/subscribe',
            unsubscribe: '/push/unsubscribe',
            test: '/push/test',
            status: '/push/status'
        };
        
        this.init();
    }

    /**
     * Inicializar el sistema de push notifications
     */
    async init() {
        console.log('[Push] Initializing AvoControl Push Notifications');
        
        // Verificar soporte del navegador
        if (!this.browserSupported()) {
            console.warn('[Push] Browser does not support push notifications');
            this.showUnsupportedMessage();
            return;
        }

        try {
            // Obtener VAPID key del servidor
            await this.getVapidKey();
            
            // Registrar service worker
            await this.registerServiceWorker();
            
            // Verificar estado de subscripción
            await this.checkSubscriptionStatus();
            
            // Configurar UI
            this.setupUI();
            
            console.log('[Push] Push notifications initialized successfully');
        } catch (error) {
            console.error('[Push] Error initializing push notifications:', error);
            this.showErrorMessage('Error al inicializar notificaciones push');
        }
    }

    /**
     * Verificar soporte del navegador
     */
    browserSupported() {
        return 'serviceWorker' in navigator && 
               'PushManager' in window && 
               'Notification' in window;
    }

    /**
     * Obtener VAPID key del servidor
     */
    async getVapidKey() {
        try {
            const response = await fetch('/push/vapid-key', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.vapidPublicKey = data.vapid_key;
                console.log('[Push] VAPID key retrieved successfully');
            } else {
                throw new Error('Failed to get VAPID key: ' + data.message);
            }
        } catch (error) {
            console.error('[Push] Error getting VAPID key:', error);
            throw error;
        }
    }

    /**
     * Registrar service worker
     */
    async registerServiceWorker() {
        try {
            this.swRegistration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });
            
            console.log('[Push] Service worker registered successfully');
            
            // Manejar actualizaciones del service worker
            this.swRegistration.addEventListener('updatefound', () => {
                console.log('[Push] Service worker update found');
            });
            
        } catch (error) {
            console.error('[Push] Service worker registration failed:', error);
            throw error;
        }
    }

    /**
     * Verificar estado actual de subscripción
     */
    async checkSubscriptionStatus() {
        try {
            const subscription = await this.swRegistration.pushManager.getSubscription();
            this.isSubscribed = subscription !== null;
            
            console.log('[Push] Subscription status:', this.isSubscribed);
            
            if (this.isSubscribed) {
                console.log('[Push] User is subscribed:', subscription);
            }
            
        } catch (error) {
            console.error('[Push] Error checking subscription status:', error);
        }
    }

    /**
     * Configurar interfaz de usuario
     */
    setupUI() {
        // Actualizar botones de subscripción
        this.updateSubscriptionButtons();
        
        // Configurar event listeners
        this.setupEventListeners();
        
        // Mostrar estado actual
        this.updateNotificationStatus();
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Botón de activar notificaciones
        const enableBtn = document.getElementById('enable-push-btn');
        if (enableBtn) {
            enableBtn.addEventListener('click', () => this.enablePushNotifications());
        }

        // Botón de desactivar notificaciones
        const disableBtn = document.getElementById('disable-push-btn');
        if (disableBtn) {
            disableBtn.addEventListener('click', () => this.disablePushNotifications());
        }

        // Botón de prueba
        const testBtn = document.getElementById('test-push-btn');
        if (testBtn) {
            console.log('[Push] Test button found, adding event listener');
            testBtn.addEventListener('click', () => {
                console.log('[Push] Test button clicked!');
                this.sendTestNotification();
            });
        } else {
            console.log('[Push] Test button not found');
        }

        // Listener para cambios de permisos
        if ('permissions' in navigator) {
            navigator.permissions.query({ name: 'notifications' }).then((permission) => {
                permission.addEventListener('change', () => {
                    console.log('[Push] Permission changed:', permission.state);
                    this.updateNotificationStatus();
                });
            });
        }
    }

    /**
     * Habilitar push notifications
     */
    async enablePushNotifications() {
        try {
            console.log('[Push] Enabling push notifications');
            
            // Solicitar permisos
            const permission = await this.requestNotificationPermission();
            
            if (permission !== 'granted') {
                throw new Error('Notification permission denied');
            }

            // Crear subscripción
            await this.subscribeToPush();
            
            this.showSuccessMessage('¡Notificaciones push activadas correctamente!');
            
        } catch (error) {
            console.error('[Push] Error enabling push notifications:', error);
            this.showErrorMessage('Error al activar notificaciones: ' + error.message);
        }
    }

    /**
     * Deshabilitar push notifications
     */
    async disablePushNotifications() {
        try {
            console.log('[Push] Disabling push notifications');
            
            await this.unsubscribeFromPush();
            
            this.showSuccessMessage('Notificaciones push desactivadas');
            
        } catch (error) {
            console.error('[Push] Error disabling push notifications:', error);
            this.showErrorMessage('Error al desactivar notificaciones: ' + error.message);
        }
    }

    /**
     * Solicitar permisos de notificación
     */
    async requestNotificationPermission() {
        const permission = await Notification.requestPermission();
        console.log('[Push] Notification permission:', permission);
        return permission;
    }

    /**
     * Subscribirse a push notifications
     */
    async subscribeToPush() {
        try {
            console.log('[Push] Starting subscription process...');
            console.log('[Push] VAPID Public Key:', this.vapidPublicKey);
            console.log('[Push] Service Worker Registration:', this.swRegistration);
            
            // Convert VAPID key
            let applicationServerKey;
            try {
                applicationServerKey = this.urlB64ToUint8Array(this.vapidPublicKey);
                console.log('[Push] Converted key length:', applicationServerKey.length);
                console.log('[Push] Converted key:', applicationServerKey);
            } catch (keyError) {
                console.error('[Push] Error converting VAPID key:', keyError);
                throw new Error('Invalid VAPID key format: ' + keyError.message);
            }
            
            // Try to subscribe
            console.log('[Push] Attempting to subscribe with pushManager...');
            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });

            console.log('[Push] User subscribed successfully:', subscription);

            // Enviar subscripción al servidor
            console.log('[Push] Sending subscription to server...');
            await this.sendSubscriptionToServer(subscription, 'subscribe');
            
            this.isSubscribed = true;
            this.updateSubscriptionButtons();
            this.updateNotificationStatus();
            
            console.log('[Push] Subscription process completed successfully');
            
        } catch (error) {
            console.error('[Push] Failed to subscribe - Full error:', error);
            console.error('[Push] Error name:', error.name);
            console.error('[Push] Error message:', error.message);
            console.error('[Push] Error stack:', error.stack);
            throw error;
        }
    }

    /**
     * Desubscribirse de push notifications
     */
    async unsubscribeFromPush() {
        try {
            const subscription = await this.swRegistration.pushManager.getSubscription();
            
            if (subscription) {
                // Informar al servidor sobre la desubscripción
                await this.sendSubscriptionToServer(subscription, 'unsubscribe');
                
                // Cancelar subscripción localmente
                await subscription.unsubscribe();
                
                console.log('[Push] User unsubscribed');
            }
            
            this.isSubscribed = false;
            this.updateSubscriptionButtons();
            this.updateNotificationStatus();
            
        } catch (error) {
            console.error('[Push] Failed to unsubscribe:', error);
            throw error;
        }
    }

    /**
     * Enviar subscripción al servidor
     */
    async sendSubscriptionToServer(subscription, action) {
        const endpoint = action === 'subscribe' ? this.apiEndpoints.subscribe : this.apiEndpoints.unsubscribe;
        
        console.log('[Push] Sending to endpoint:', endpoint);
        console.log('[Push] Subscription data:', subscription.toJSON());
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('[Push] CSRF Token found:', !!csrfToken);
        
        const requestBody = {
            subscription: subscription.toJSON(),
            user_agent: navigator.userAgent,
            timestamp: new Date().toISOString()
        };
        
        console.log('[Push] Request body:', requestBody);
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestBody)
        });

        console.log('[Push] Server response status:', response.status);
        console.log('[Push] Server response headers:', response.headers);
        
        if (!response.ok) {
            // Para unsubscribe, no tratamos 404 como error ya que significa que ya estaba desuscrito
            if (action === 'unsubscribe' && response.status === 404) {
                console.log('[Push] Subscription not found on server (already unsubscribed)');
                return { success: true, message: 'Already unsubscribed' };
            }
            
            const text = await response.text();
            console.error('[Push] Server error response:', text);
            throw new Error(`Server returned ${response.status}: ${text}`);
        }
        
        const data = await response.json();
        console.log('[Push] Server response data:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Server error');
        }
        
        console.log('[Push] Subscription sent to server successfully');
        return data;
    }

    /**
     * Enviar notificación de prueba
     */
    async sendTestNotification() {
        try {
            console.log('[Push] Sending test notification');
            console.log('[Push] API endpoint:', this.apiEndpoints.test);
            console.log('[Push] Is subscribed:', this.isSubscribed);
            
            console.log('[Push] Making fetch request to:', this.apiEndpoints.test);
            const fullUrl = window.location.origin + this.apiEndpoints.test;
            console.log('[Push] Full URL will be:', fullUrl);
            console.log('[Push] Window location origin:', window.location.origin);
            console.log('[Push] About to call fetch...');
            const response = await fetch(this.apiEndpoints.test, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                },
                body: JSON.stringify({
                    message: 'Esta es una notificación de prueba desde AvoControl Pro',
                    timestamp: Date.now()
                })
            });
            
            console.log('[Push] Response received:', response.status, response.statusText);
            console.log('[Push] Response URL:', response.url);
            console.log('[Push] Response type:', response.type);
            console.log('[Push] Response redirected:', response.redirected);
            console.log('[Push] Response ok:', response.ok);
            
            // Log headers properly
            const headers = {};
            for (let [key, value] of response.headers.entries()) {
                headers[key] = value;
            }
            console.log('[Push] Response headers:', headers);
            
            console.log('[Push] About to parse JSON...');
            console.log('[Push] Response body readable?:', response.body !== null);

            const responseText = await response.text();
            console.log('[Push] Response text:', responseText);
            console.log('[Push] Trying to parse as JSON...');
            const data = JSON.parse(responseText);
            console.log('[Push] JSON parsed:', data);
            
            if (data.success) {
                this.showSuccessMessage('Notificación de prueba enviada');
                
                // Si estamos en modo local, mostrar notificación directamente
                if (data.local_mode) {
                    console.log('[Push] Local mode detected - showing direct notification');
                    
                    // Mostrar notificación directa del navegador
                    if ('serviceWorker' in navigator && 'Notification' in window) {
                        navigator.serviceWorker.getRegistration().then(registration => {
                            if (registration && Notification.permission === 'granted') {
                                registration.showNotification('🎉 AvoControl Pro - ¡Funciona!', {
                                    body: '¡Las notificaciones push están funcionando correctamente en desarrollo local!',
                                    icon: '/favicon.ico',
                                    badge: '/favicon.ico',
                                    tag: 'local-test-success',
                                    requireInteraction: true,
                                    actions: [
                                        { action: 'view_dashboard', title: '📊 Ver Dashboard' },
                                        { action: 'dismiss', title: '✓ ¡Perfecto!' }
                                    ],
                                    vibrate: [300, 100, 300, 100, 300]
                                });
                                console.log('[Push] ¡Notificación local mostrada exitosamente!');
                            }
                        }).catch(err => {
                            console.error('[Push] Error showing local notification:', err);
                        });
                    }
                }
            } else {
                throw new Error(data.message);
            }
            
        } catch (error) {
            console.error('[Push] Error sending test notification:', error);
            console.error('[Push] Error name:', error.name);
            console.error('[Push] Error message:', error.message);
            console.error('[Push] Error stack:', error.stack);
            this.showErrorMessage('Error al enviar notificación de prueba: ' + error.message);
        }
    }

    /**
     * Actualizar botones de subscripción
     */
    updateSubscriptionButtons() {
        const enableBtn = document.getElementById('enable-push-btn');
        const disableBtn = document.getElementById('disable-push-btn');
        const testBtn = document.getElementById('test-push-btn');

        if (enableBtn && disableBtn) {
            if (this.isSubscribed) {
                enableBtn.style.display = 'none';
                disableBtn.style.display = 'inline-block';
                if (testBtn) testBtn.style.display = 'inline-block';
            } else {
                enableBtn.style.display = 'inline-block';
                disableBtn.style.display = 'none';
                if (testBtn) testBtn.style.display = 'none';
            }
        }
    }

    /**
     * Actualizar estado de notificaciones en la UI
     */
    updateNotificationStatus() {
        const statusElement = document.getElementById('push-status');
        
        if (statusElement) {
            if (this.isSubscribed) {
                statusElement.innerHTML = '<span class="badge badge-success">✅ Notificaciones Activas</span>';
            } else {
                statusElement.innerHTML = '<span class="badge badge-secondary">⭕ Notificaciones Inactivas</span>';
            }
        }
    }

    /**
     * Mostrar mensaje de éxito
     */
    showSuccessMessage(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }

    /**
     * Mostrar mensaje de error
     */
    showErrorMessage(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    /**
     * Mostrar mensaje de navegador no soportado
     */
    showUnsupportedMessage() {
        const message = 'Tu navegador no soporta notificaciones push. Considera actualizar a una versión más reciente.';
        
        const statusElement = document.getElementById('push-status');
        if (statusElement) {
            statusElement.innerHTML = '<span class="badge badge-warning">⚠️ Navegador no soportado</span>';
        }
        
        this.showErrorMessage(message);
    }

    /**
     * Convertir VAPID key a Uint8Array
     */
    urlB64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Obtener información del navegador para analytics
     */
    getBrowserInfo() {
        return {
            user_agent: navigator.userAgent,
            platform: navigator.platform,
            language: navigator.language,
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            },
            screen: {
                width: window.screen.width,
                height: window.screen.height
            }
        };
    }

    /**
     * Método público para verificar si las notificaciones están soportadas
     */
    static isSupported() {
        return 'serviceWorker' in navigator && 
               'PushManager' in window && 
               'Notification' in window;
    }

    /**
     * Método público para obtener el estado de los permisos
     */
    static async getPermissionStatus() {
        if (!this.isSupported()) {
            return 'unsupported';
        }
        
        return Notification.permission;
    }
}

// Auto-inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (AvoControlPush.isSupported()) {
        window.avoControlPush = new AvoControlPush();
    } else {
        console.warn('[Push] Push notifications not supported in this browser');
    }
});

// Exportar para uso global
window.AvoControlPush = AvoControlPush;