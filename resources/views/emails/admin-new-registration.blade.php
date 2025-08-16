<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro - AvoControl Pro</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .email-header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .email-content {
            padding: 30px;
        }
        
        .alert-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        
        .info-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        
        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }
        
        .actions {
            margin: 30px 0;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .btn-secondary {
            background: #6b7280;
        }
        
        .footer {
            background: #f3f4f6;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔔 Nuevo Registro en AvoControl Pro</h1>
            <p>Se ha registrado un nuevo usuario en el sistema</p>
        </div>
        
        <div class="email-content">
            <div class="alert-box">
                <strong>📊 Resumen:</strong> Nuevo usuario registrado con plan {{ ucfirst($subscription->plan) }}
            </div>
            
            <h3>👤 Información del Usuario</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nombre</div>
                    <div class="info-value">{{ $user->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $user->email }}</div>
                </div>
                @if($tenant)
                <div class="info-item">
                    <div class="info-label">Empresa</div>
                    <div class="info-value">{{ $tenant->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Plan</div>
                    <div class="info-value">{{ ucfirst($subscription->plan) }}</div>
                </div>
                @endif
            </div>
            
            <h3>💳 Información de Suscripción</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">ID de Suscripción</div>
                    <div class="info-value">#{{ $subscription->id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Monto</div>
                    <div class="info-value">${{ number_format($subscription->amount, 2) }} {{ $subscription->currency }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Estado</div>
                    <div class="info-value">{{ ucfirst($subscription->status) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Registro</div>
                    <div class="info-value">{{ $registrationDate->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            
            @if($subscription->paypal_subscription_id)
            <h3>🔗 PayPal Information</h3>
            <div class="info-item">
                <div class="info-label">PayPal Subscription ID</div>
                <div class="info-value">{{ $subscription->paypal_subscription_id }}</div>
            </div>
            @endif
            
            <div class="actions">
                <a href="{{ $dashboardUrl }}" class="btn">Ver en Panel de Suscripciones</a>
                <a href="{{ $userManagementUrl }}" class="btn btn-secondary">Gestión de Usuarios</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Esta es una notificación automática del sistema AvoControl Pro</p>
            <p>Generada el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>