<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Reactivada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .alert { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 20px 0; border-radius: 5px; color: #0c5460; }
        .success-box { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px; color: #155724; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn-success { background: #28a745; }
        .info-box { background: white; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; }
        .celebration { font-size: 48px; text-align: center; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
        h1, h2 { margin-top: 0; }
        .status-badge { background: #28a745; color: white; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .welcome-back { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Cuenta Reactivada!</h1>
            <p>Su acceso ha sido restaurado completamente</p>
        </div>
        
        <div class="content">
            <div class="celebration">🎊✨🎈</div>
            
            <div class="welcome-back">
                <h2>¡Bienvenido de vuelta!</h2>
                <p>Nos alegra tenerlo nuevamente en <strong>{{ $tenant->name }}</strong></p>
            </div>
            
            <div class="success-box">
                <strong>✅ Su cuenta ha sido reactivada exitosamente el {{ $reactivatedAt->format('d/m/Y \a \l\a\s H:i') }}</strong>
            </div>
            
            <div class="info-box">
                <h3>📋 Detalles de la Reactivación</h3>
                <ul>
                    <li><strong>Empresa:</strong> {{ $tenant->name }}</li>
                    <li><strong>Plan:</strong> <span class="status-badge">{{ $subscription->getPlanNameFormatted() }}</span></li>
                    <li><strong>Motivo:</strong> {{ $reactivationReason }}</li>
                    <li><strong>Estado actual:</strong> <span class="status-badge">ACTIVA</span></li>
                    @if($nextBillingDate)
                        <li><strong>Próximo cobro:</strong> {{ $nextBillingDate->format('d/m/Y') }}</li>
                    @endif
                </ul>
            </div>
            
            <h3>🚀 Qué puede hacer ahora:</h3>
            <ul>
                <li>✅ Acceder al sistema con normalidad</li>
                <li>✅ Todos sus usuarios pueden iniciar sesión</li>
                <li>✅ Sus datos están intactos y disponibles</li>
                <li>✅ Todas las funciones están activas</li>
                <li>✅ Las notificaciones han sido reactivadas</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/dashboard" class="btn btn-success">Acceder al Sistema</a>
                <a href="{{ config('app.url') }}/subscription/manage" class="btn">Ver Mi Suscripción</a>
            </div>
            
            <div class="alert">
                <h3>💡 Para evitar futuras suspensiones:</h3>
                <ul>
                    <li>Mantenga actualizada su información de pago</li>
                    <li>Asegúrese de tener fondos suficientes antes de cada cobro</li>
                    <li>Revise periódicamente el estado de su suscripción</li>
                    <li>Configure notificaciones de recordatorio</li>
                </ul>
            </div>
            
            <h3>📊 Estado de su Suscripción</h3>
            <div class="info-box">
                <ul>
                    <li><strong>Plan activo:</strong> {{ $subscription->getPlanNameFormatted() }}</li>
                    <li><strong>Precio mensual:</strong> {{ $subscription->getAmountFormatted() }}</li>
                    <li><strong>Estado:</strong> Activa y funcionando</li>
                    <li><strong>Usuarios permitidos:</strong> {{ $subscription->getFeatureLimit('users') ?? 'Ilimitados' }}</li>
                    <li><strong>Lotes mensuales:</strong> {{ $subscription->getFeatureLimit('lots_per_month') ?? 'Ilimitados' }}</li>
                </ul>
            </div>
            
            <h3>🎯 Próximos Pasos Recomendados</h3>
            <ol>
                <li>Inicie sesión y verifique que todo esté funcionando</li>
                <li>Notifique a sus usuarios que el acceso está restaurado</li>
                <li>Revise sus notificaciones pendientes</li>
                <li>Considere configurar un método de pago de respaldo</li>
            </ol>
            
            <h3>📞 Soporte Técnico</h3>
            <div class="info-box" style="border-color: #17a2b8;">
                <p><strong>🤝 Estamos aquí para ayudarle:</strong></p>
                <ul>
                    <li><strong>Email:</strong> soporte@avocontrol.pro</li>
                    <li><strong>Teléfono:</strong> +52 443 123 4567</li>
                    <li><strong>WhatsApp:</strong> +52 443 123 4567</li>
                    <li><strong>Horario:</strong> Lunes a Viernes 9:00 AM - 6:00 PM</li>
                </ul>
            </div>
            
            <div class="welcome-back">
                <h3>🙏 Gracias por su confianza</h3>
                <p>Nos complace tenerle como cliente de AvoControl Pro. Estamos comprometidos en brindarle el mejor servicio para la gestión de su centro de acopio de aguacate.</p>
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