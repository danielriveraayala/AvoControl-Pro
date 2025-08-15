<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advertencia de Suspensión</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $isUrgent ? '#dc3545' : '#ffc107' }}; color: {{ $isUrgent ? 'white' : '#212529' }}; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-danger { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-success { background: #28a745; }
        .info-box { background: white; border-left: 4px solid {{ $isUrgent ? '#dc3545' : '#ffc107' }}; padding: 15px; margin: 20px 0; }
        .countdown { font-size: 24px; font-weight: bold; color: {{ $isUrgent ? '#dc3545' : '#ffc107' }}; text-align: center; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
        h1, h2 { margin-top: 0; }
        .status-badge { background: #ffc107; color: #212529; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .urgent { animation: blink 1s infinite; }
        @keyframes blink { 0%, 50% { opacity: 1; } 51%, 100% { opacity: 0.5; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($isUrgent)
                <h1 class="urgent">🚨 ADVERTENCIA URGENTE</h1>
                <p>Su cuenta será suspendida en menos de 24 horas</p>
            @else
                <h1>⚠️ Advertencia de Suspensión</h1>
                <p>Acción requerida para evitar la suspensión</p>
            @endif
        </div>
        
        <div class="content">
            <p>Estimado usuario de <strong>{{ $tenant->name }}</strong>,</p>
            
            @if($isUrgent)
                <div class="alert alert-danger">
                    <strong>🚨 ACCIÓN INMEDIATA REQUERIDA:</strong> Su cuenta será suspendida automáticamente si no se resuelve el problema de pago.
                </div>
            @else
                <div class="alert">
                    <strong>⚠️ Su cuenta está en período de gracia</strong> debido a problemas con el pago de su suscripción.
                </div>
            @endif
            
            <div class="countdown">
                ⏰ {{ $daysLeft }} {{ $daysLeft === 1 ? 'día restante' : 'días restantes' }}
            </div>
            
            <div class="info-box">
                <h3>📋 Estado de su Suscripción</h3>
                <ul>
                    <li><strong>Empresa:</strong> {{ $tenant->name }}</li>
                    <li><strong>Plan:</strong> <span class="status-badge">{{ $subscription->getPlanNameFormatted() }}</span></li>
                    <li><strong>Intentos de pago fallidos:</strong> {{ $failedPaymentCount }}</li>
                    <li><strong>Período de gracia termina:</strong> {{ $gracePeriodEnds->format('d/m/Y \a \l\a\s H:i') }}</li>
                </ul>
            </div>
            
            <h3>🎯 Qué debe hacer ahora:</h3>
            <ol>
                <li><strong>Verifique su método de pago</strong> en PayPal</li>
                <li><strong>Asegúrese</strong> de tener fondos suficientes</li>
                <li><strong>Actualice</strong> su información de facturación si es necesario</li>
                <li><strong>Contacte soporte</strong> si necesita asistencia</li>
            </ol>
            
            @if($isUrgent)
                <div class="alert alert-danger">
                    <h3>🚨 Si no actúa en las próximas {{ $daysLeft === 1 ? '24 horas' : ($daysLeft . ' días') }}:</h3>
                    <ul>
                        <li>Su cuenta será suspendida automáticamente</li>
                        <li>Los usuarios no podrán acceder al sistema</li>
                        <li>Necesitará reactivar manualmente su suscripción</li>
                    </ul>
                </div>
            @endif
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/subscription/manage" class="btn {{ $isUrgent ? 'btn-danger' : 'btn-warning' }}">
                    {{ $isUrgent ? 'Resolver AHORA' : 'Gestionar Suscripción' }}
                </a>
                <a href="mailto:soporte@avocontrol.pro" class="btn">Contactar Soporte</a>
            </div>
            
            <h3>💡 Información Importante:</h3>
            <ul>
                <li>Sus datos están completamente seguros</li>
                <li>La suspensión es temporal y reversible</li>
                <li>No perderá ninguna información</li>
                <li>El acceso se restaurará inmediatamente tras el pago exitoso</li>
            </ul>
            
            <h3>📞 Soporte Técnico</h3>
            <div class="info-box" style="border-color: #28a745;">
                <p><strong>🆘 ¿Necesita ayuda urgente?</strong></p>
                <ul>
                    <li><strong>Email:</strong> soporte@avocontrol.pro</li>
                    <li><strong>Teléfono:</strong> +52 443 123 4567</li>
                    <li><strong>WhatsApp:</strong> +52 443 123 4567</li>
                    <li><strong>Horario:</strong> Lunes a Viernes 9:00 AM - 6:00 PM</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>Este email fue enviado automáticamente por {{ config('app.name') }}</p>
            <p>&copy; {{ date('Y') }} AvoControl Pro - Todos los derechos reservados</p>
            <p>Desarrollado por <a href="https://about.me/danielriveraayala">Kreativos Pro</a></p>
        </div>
    </div>
</body>
</html>