<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Suspendida</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn-danger { background: #dc3545; }
        .info-box { background: white; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
        h1, h2 { margin-top: 0; }
        .status-badge { background: #dc3545; color: white; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 Cuenta Suspendida</h1>
            <p>Su cuenta ha sido temporalmente suspendida</p>
        </div>
        
        <div class="content">
            <p>Estimado usuario de <strong>{{ $tenant->name }}</strong>,</p>
            
            <div class="alert">
                <strong>⚠️ Su cuenta ha sido suspendida el {{ $suspendedAt->format('d/m/Y \a \l\a\s H:i') }}</strong>
            </div>
            
            <div class="info-box">
                <h3>📋 Detalles de la Suspensión</h3>
                <ul>
                    <li><strong>Empresa:</strong> {{ $tenant->name }}</li>
                    <li><strong>Plan:</strong> <span class="status-badge">{{ $subscription->getPlanNameFormatted() }}</span></li>
                    <li><strong>Motivo:</strong> {{ $suspensionReason }}</li>
                    <li><strong>Estado:</strong> <span class="status-badge">{{ $subscription->getStatusBadge()['text'] }}</span></li>
                </ul>
            </div>
            
            <h3>🔒 Qué significa esto:</h3>
            <ul>
                <li>Su acceso al sistema ha sido temporalmente restringido</li>
                <li>Los datos de su empresa están seguros y respaldados</li>
                <li>Tiene {{ $subscription->canBeReactivated() ? '30 días' : 'tiempo limitado' }} para reactivar su cuenta</li>
                <li>Sus usuarios no podrán acceder al sistema hasta la reactivación</li>
            </ul>
            
            @if($canReactivate)
                <div class="alert" style="background: #d1ecf1; border-color: #bee5eb;">
                    <h3>💡 ¿Cómo reactivar su cuenta?</h3>
                    <ol>
                        <li>Realice el pago pendiente de su suscripción</li>
                        <li>La reactivación será automática tras confirmar el pago</li>
                        <li>Recibirá un email de confirmación cuando su cuenta esté activa</li>
                    </ol>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ config('app.url') }}/login" class="btn">Ir al Sistema</a>
                    <a href="mailto:soporte@avocontrol.pro" class="btn btn-danger">Contactar Soporte</a>
                </div>
            @endif
            
            <h3>📞 ¿Necesita ayuda?</h3>
            <p>Si tiene preguntas sobre su suspensión o necesita asistencia para reactivar su cuenta:</p>
            <ul>
                <li><strong>Email:</strong> soporte@avocontrol.pro</li>
                <li><strong>Teléfono:</strong> +52 443 123 4567</li>
                <li><strong>Horario:</strong> Lunes a Viernes 9:00 AM - 6:00 PM (horario de México)</li>
            </ul>
            
            <div class="info-box" style="border-color: #28a745;">
                <p><strong>💾 Sus datos están seguros:</strong> Toda su información está respaldada y será restaurada completamente al reactivar su cuenta.</p>
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