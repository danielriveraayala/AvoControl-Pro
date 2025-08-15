<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $is_last_day ? 'Tu trial expira hoy' : 'Recordatorio de trial' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: {{ $is_last_day ? 'linear-gradient(135deg, #dc2626, #b91c1c)' : 'linear-gradient(135deg, #f59e0b, #d97706)' }};
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            color: {{ $is_last_day ? '#dc2626' : '#d97706' }};
            margin-bottom: 20px;
            font-weight: 600;
        }
        .company-info {
            background-color: #f8fafc;
            border: 2px solid {{ $is_last_day ? '#dc2626' : '#f59e0b' }};
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: {{ $is_last_day ? '#dc2626' : '#d97706' }};
            margin-bottom: 5px;
        }
        .urgency-info {
            background: {{ $is_last_day ? 'linear-gradient(135deg, #fee2e2, #fecaca)' : 'linear-gradient(135deg, #fef3c7, #fcd34d)' }};
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
            border: 2px solid {{ $is_last_day ? '#dc2626' : '#f59e0b' }};
        }
        .days-left {
            font-size: 48px;
            font-weight: bold;
            color: {{ $is_last_day ? '#dc2626' : '#d97706' }};
            margin: 0;
        }
        .urgency-text {
            color: {{ $is_last_day ? '#991b1b' : '#92400e' }};
            font-weight: 600;
            margin: 5px 0 0 0;
            font-size: 18px;
        }
        .expiry-date {
            font-size: 14px;
            color: {{ $is_last_day ? '#7f1d1d' : '#78350f' }};
            margin-top: 10px;
        }
        .features {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .features h3 {
            color: #16a34a;
            margin-top: 0;
        }
        .plans-grid {
            display: table;
            width: 100%;
            margin: 25px 0;
        }
        .plan-item {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
        }
        .plan-name {
            font-weight: bold;
            color: #1f2937;
            font-size: 16px;
        }
        .plan-price {
            font-size: 24px;
            font-weight: bold;
            color: #16a34a;
            margin: 5px 0;
        }
        .plan-features {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }
        .cta-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 18px 35px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        .btn-primary {
            background: {{ $is_last_day ? 'linear-gradient(135deg, #dc2626, #b91c1c)' : 'linear-gradient(135deg, #22c55e, #16a34a)' }};
            color: white;
        }
        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 2px solid #d1d5db;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .support {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .support h3 {
            color: #1d4ed8;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $is_last_day ? '⚠️ ¡Tu trial expira hoy!' : '⏰ Recordatorio de trial' }}</h1>
            <p style="margin: 10px 0 0 0; font-size: 18px;">
                {{ $is_last_day ? 'Actualiza ahora para continuar usando AvoControl Pro' : 'Tu trial está próximo a vencer' }}
            </p>
        </div>
        
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $user->name }}!
            </div>
            
            <p>{{ $is_last_day ? 'Tu trial de AvoControl Pro expira hoy.' : 'Queríamos recordarte que tu trial de AvoControl Pro está próximo a vencer.' }}</p>
            
            <div class="company-info">
                <div class="company-name">{{ $tenant->name }}</div>
                <p><strong>Plan actual:</strong> {{ ucfirst($subscription->plan) }} (Trial)</p>
                <p><strong>Trial expira:</strong> {{ $trial_end_date }}</p>
            </div>
            
            <div class="urgency-info">
                @if($is_last_day)
                    <div class="days-left">¡HOY!</div>
                    <div class="urgency-text">Tu trial expira hoy</div>
                @else
                    <div class="days-left">{{ $days_left }}</div>
                    <div class="urgency-text">{{ $days_left === 1 ? 'día restante' : 'días restantes' }}</div>
                @endif
                <div class="expiry-date">Expira: {{ $trial_end_date }}</div>
            </div>
            
            @if($is_last_day)
                <div style="background-color: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 20px; margin: 25px 0;">
                    <h3 style="color: #dc2626; margin-top: 0;">⚠️ Acción requerida</h3>
                    <p>Para continuar usando AvoControl Pro sin interrupciones, actualiza tu plan antes de que expire tu trial.</p>
                    <p><strong>Sin una actualización, perderás acceso a:</strong></p>
                    <ul style="color: #991b1b;">
                        <li>Gestión de lotes y ventas</li>
                        <li>Reportes y análisis</li>
                        <li>Configuraciones de empresa</li>
                        <li>Datos almacenados</li>
                    </ul>
                </div>
            @endif
            
            <div class="plans-grid">
                <h3 style="text-align: center; color: #1f2937;">Planes disponibles</h3>
                
                <div class="plan-item">
                    <div class="plan-name">BASIC</div>
                    <div class="plan-price">$29/mes</div>
                    <div class="plan-features">5 usuarios • 500 lotes/mes • 2GB almacenamiento</div>
                </div>
                
                <div class="plan-item">
                    <div class="plan-name">PREMIUM</div>
                    <div class="plan-price">$79/mes</div>
                    <div class="plan-features">25 usuarios • 2,000 lotes/mes • 10GB + Notificaciones SMS</div>
                </div>
                
                <div class="plan-item">
                    <div class="plan-name">ENTERPRISE</div>
                    <div class="plan-price">$199/mes</div>
                    <div class="plan-features">100 usuarios • Lotes ilimitados • 50GB + API completo</div>
                </div>
            </div>
            
            <div class="cta-buttons">
                <a href="{{ $plans_url }}" class="btn btn-primary">
                    {{ $is_last_day ? '🚨 Actualizar ahora' : 'Ver planes y precios' }}
                </a>
                <a href="{{ $dashboard_url }}" class="btn btn-secondary">Ir al dashboard</a>
            </div>
            
            <div class="support">
                <h3>¿Tienes preguntas?</h3>
                <p>Nuestro equipo está listo para ayudarte a elegir el plan perfecto para tu negocio.</p>
                <p><strong>Email de soporte:</strong> <a href="mailto:{{ $support_email }}">{{ $support_email }}</a></p>
            </div>
            
            <p style="margin-top: 30px;">
                Gracias por probar AvoControl Pro,<br>
                <strong>El equipo de AvoControl Pro</strong>
            </p>
        </div>
        
        <div class="footer">
            <p><strong>AvoControl Pro</strong></p>
            <p>Sistema de gestión para centros de acopio de aguacate</p>
            <p>© {{ date('Y') }} Kreativos Pro. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>