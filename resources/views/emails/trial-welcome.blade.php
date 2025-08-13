<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Bienvenido a AvoControl Pro!</title>
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
            background: linear-gradient(135deg, #22c55e, #16a34a);
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
            color: #16a34a;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .company-info {
            background-color: #f8fafc;
            border: 2px solid #22c55e;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 5px;
        }
        .trial-info {
            background: linear-gradient(135deg, #fef3c7, #fcd34d);
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .trial-days {
            font-size: 32px;
            font-weight: bold;
            color: #d97706;
            margin: 0;
        }
        .trial-text {
            color: #92400e;
            font-weight: 600;
            margin: 5px 0 0 0;
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
        .features ul {
            padding-left: 20px;
        }
        .features li {
            margin: 8px 0;
        }
        .cta-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #16a34a);
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
            <h1>¡Bienvenido a AvoControl Pro!</h1>
            <p style="margin: 10px 0 0 0; font-size: 18px;">Tu sistema de gestión de centros de acopio de aguacate</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                ¡Hola {{ $user->name }}! 👋
            </div>
            
            <p>Nos emociona tenerte en <strong>AvoControl Pro</strong>. Tu cuenta ha sido creada exitosamente y tu trial gratuito ha comenzado.</p>
            
            <div class="company-info">
                <div class="company-name">{{ $tenant->name }}</div>
                <p><strong>Plan:</strong> {{ ucfirst($subscription->plan) }} (Trial)</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
            </div>
            
            <div class="trial-info">
                <div class="trial-days">{{ $trial_days }}</div>
                <div class="trial-text">días de trial gratuito</div>
            </div>
            
            <div class="features">
                <h3>¿Qué puedes hacer durante tu trial?</h3>
                <ul>
                    <li>✅ Gestionar proveedores y clientes</li>
                    <li>✅ Registrar lotes de aguacate</li>
                    <li>✅ Procesar ventas y pagos</li>
                    <li>✅ Generar reportes básicos</li>
                    <li>✅ Configurar tu empresa</li>
                    <li>✅ Agregar hasta {{ config('paypal.plans.trial.features.max_users', 1) }} usuario(s)</li>
                </ul>
            </div>
            
            <div class="cta-buttons">
                <a href="{{ $dashboard_url }}" class="btn btn-primary">Comenzar ahora</a>
                <a href="{{ $plans_url }}" class="btn btn-secondary">Ver planes</a>
            </div>
            
            <div class="support">
                <h3>¿Necesitas ayuda?</h3>
                <p>Nuestro equipo está aquí para ayudarte a sacar el máximo provecho de AvoControl Pro.</p>
                <p><strong>Email de soporte:</strong> <a href="mailto:{{ $support_email }}">{{ $support_email }}</a></p>
            </div>
            
            <p>¡Esperamos que disfrutes usando AvoControl Pro!</p>
            
            <p style="margin-top: 30px;">
                Saludos cordiales,<br>
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