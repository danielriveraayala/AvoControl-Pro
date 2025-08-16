<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ $companyName }} - Factura Incluida</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
        }
        
        .email-wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 20px 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .welcome-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .payment-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Content */
        .email-content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        
        .content-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
            border-left: 4px solid #10b981;
            padding-left: 15px;
        }
        
        /* Payment Summary Box */
        .payment-summary {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border: 2px solid #10b981;
            text-align: center;
        }
        
        .payment-summary h3 {
            color: #065f46;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .payment-amount {
            font-size: 36px;
            font-weight: bold;
            color: #059669;
            margin: 10px 0;
        }
        
        .payment-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
            text-align: left;
        }
        
        .payment-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #d1fae5;
        }
        
        .payment-label {
            font-size: 12px;
            color: #065f46;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .payment-value {
            font-size: 14px;
            color: #047857;
            font-weight: 500;
        }
        
        /* Invoice Info Box */
        .invoice-info {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
            text-align: center;
        }
        
        .invoice-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .invoice-title {
            font-size: 18px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
        }
        
        .invoice-description {
            color: #a16207;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .invoice-number {
            background: white;
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: bold;
            color: #92400e;
            border: 1px solid #fbbf24;
        }
        
        /* Account Info Box */
        .account-info {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #0ea5e9;
        }
        
        .account-info h3 {
            color: #0c4a6e;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #0369a1;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 14px;
            color: #0c4a6e;
            font-weight: 500;
        }
        
        /* Plan Features */
        .features-list {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
        
        .features-list li:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            color: #10b981;
            margin-right: 10px;
            font-size: 16px;
        }
        
        /* Quick Start Steps */
        .quick-start-steps {
            list-style: none;
            padding: 0;
        }
        
        .step-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #10b981;
            transition: transform 0.2s ease;
        }
        
        .step-item:hover {
            transform: translateX(5px);
        }
        
        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .step-icon {
            font-size: 24px;
            margin-right: 12px;
        }
        
        .step-title {
            font-weight: 600;
            color: #2d3748;
            font-size: 16px;
        }
        
        .step-description {
            color: #4a5568;
            font-size: 14px;
            margin-left: 36px;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            margin: 10px 10px 10px 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Footer */
        .email-footer {
            background: #2d3748;
            color: #a0aec0;
            padding: 30px;
            text-align: center;
        }
        
        .footer-content {
            margin-bottom: 20px;
        }
        
        .support-info {
            background: #4a5568;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .support-title {
            color: white;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .support-contact {
            color: #cbd5e0;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .footer-links {
            margin-top: 20px;
        }
        
        .footer-links a {
            color: #10b981;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            color: #34d399;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .email-container {
                margin: 0 10px;
            }
            
            .email-header, .email-content, .email-footer {
                padding: 20px;
            }
            
            .info-grid, .payment-details {
                grid-template-columns: 1fr;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
            
            .step-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .step-icon {
                margin-bottom: 8px;
            }
            
            .step-description {
                margin-left: 0;
            }
            
            .payment-badge {
                position: static;
                display: inline-block;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="payment-badge">✅ PAGADO</div>
                <div class="logo">🥑 {{ $companyName }}</div>
                <h1 class="welcome-title">¡Pago Confirmado!</h1>
                <p class="welcome-subtitle">
                    Tu suscripción {{ ucfirst($subscription->plan) }} está activa y lista para usar
                </p>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                <p class="greeting">Hola <strong>{{ $user->name }}</strong>,</p>
                
                <p style="margin-bottom: 20px;">
                    ¡Excelente! Tu pago ha sido procesado exitosamente y tu cuenta de AvoControl Pro está completamente configurada. 
                    A continuación encontrarás toda la información importante sobre tu suscripción.
                </p>
                
                <!-- Payment Summary -->
                <div class="payment-summary">
                    <h3>💳 Resumen de Pago</h3>
                    <div class="payment-amount">
                        ${{ number_format($paymentAmount, 2) }} {{ strtoupper($paymentCurrency) }}
                    </div>
                    <div style="color: #059669; font-weight: 600;">
                        Facturación {{ $billingCycle }} • Plan {{ ucfirst($subscription->plan) }}
                    </div>
                    
                    <div class="payment-details">
                        <div class="payment-item">
                            <div class="payment-label">Fecha de Pago</div>
                            <div class="payment-value">{{ $paymentDate->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="payment-item">
                            <div class="payment-label">Método de Pago</div>
                            <div class="payment-value">PayPal</div>
                        </div>
                        @if($tenant)
                        <div class="payment-item">
                            <div class="payment-label">Empresa</div>
                            <div class="payment-value">{{ $tenant->name }}</div>
                        </div>
                        @endif
                        <div class="payment-item">
                            <div class="payment-label">Estado</div>
                            <div class="payment-value">✅ Confirmado</div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Information -->
                <div class="invoice-info">
                    <div class="invoice-icon">📄</div>
                    <h3 class="invoice-title">Factura Adjunta</h3>
                    <p class="invoice-description">
                        Tu factura oficial está adjunta a este correo para tus registros contables y fiscales.
                    </p>
                    <div class="invoice-number">
                        Factura No. {{ $invoiceNumber }}
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="account-info">
                    <h3>🔐 Credenciales de Acceso</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Email de Acceso</span>
                            <span class="info-value">{{ $user->email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Plan Activo</span>
                            <span class="info-value">{{ ucfirst($subscription->plan) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Próxima Facturación</span>
                            <span class="info-value">
                                @if($subscription->current_period_end)
                                    {{ \Carbon\Carbon::parse($subscription->current_period_end)->format('d/m/Y') }}
                                @else
                                    {{ \Carbon\Carbon::now()->addMonth()->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Estado de Cuenta</span>
                            <span class="info-value" style="color: #059669; font-weight: bold;">✅ Activa</span>
                        </div>
                    </div>
                </div>
                
                <!-- Plan Features -->
                @if(!empty($planFeatures))
                <div class="content-section">
                    <h2 class="section-title">✨ Tu Plan Incluye</h2>
                    <ul class="features-list">
                        @foreach($planFeatures as $feature)
                        <li>
                            <span class="feature-icon">✓</span>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Login Instructions -->
                <div class="content-section">
                    <h2 class="section-title">🚀 Accede a tu Cuenta</h2>
                    <p style="margin-bottom: 20px;">
                        Todo está listo. Puedes acceder inmediatamente a tu panel de control:
                    </p>
                    
                    <a href="{{ $loginUrl }}" class="btn btn-primary">Iniciar Sesión Ahora</a>
                    <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Ir al Dashboard</a>
                </div>
                
                <!-- Quick Start Guide -->
                @if(!empty($quickStartSteps))
                <div class="content-section">
                    <h2 class="section-title">📚 Siguientes Pasos</h2>
                    <p style="margin-bottom: 20px;">
                        Te recomendamos seguir estos pasos para aprovechar al máximo tu suscripción:
                    </p>
                    
                    <div class="quick-start-steps">
                        @foreach($quickStartSteps as $index => $step)
                        <div class="step-item">
                            <div class="step-header">
                                <div class="step-icon">{{ $step['icon'] }}</div>
                                <div class="step-title">{{ $index + 1 }}. {{ $step['title'] }}</div>
                            </div>
                            <div class="step-description">{{ $step['description'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Important Notice -->
                <div class="content-section">
                    <h2 class="section-title">⚠️ Información Importante</h2>
                    <p style="margin-bottom: 15px;">
                        <strong>Facturación Automática:</strong> Tu suscripción se renovará automáticamente cada {{ $billingCycle === 'anual' ? 'año' : 'mes' }} 
                        a través de PayPal. Puedes gestionar tu suscripción desde tu panel de usuario.
                    </p>
                    <p style="margin-bottom: 15px;">
                        <strong>Soporte Incluido:</strong> Tu plan incluye soporte técnico. No dudes en contactarnos si necesitas ayuda.
                    </p>
                    <p>
                        <strong>Respaldo de Datos:</strong> Realizamos respaldos automáticos diarios de toda tu información para garantizar su seguridad.
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-content">
                    <div class="support-info">
                        <div class="support-title">🛟 Soporte 24/7</div>
                        <div class="support-contact">📧 {{ $supportEmail }}</div>
                        <div class="support-contact">📞 {{ $supportPhone }}</div>
                        <div class="support-contact">🌐 {{ $companyUrl }}</div>
                    </div>
                    
                    <p style="font-size: 14px; color: #a0aec0;">
                        <strong>{{ $companyName }}</strong> - Sistema de Gestión de Centros de Acopio
                    </p>
                    <p style="font-size: 12px; margin-top: 10px;">
                        Copyright © {{ date('Y') }} Kreativos Pro. Todos los derechos reservados.
                    </p>
                    
                    <div class="footer-links">
                        <a href="{{ $companyUrl }}">Sitio Web</a>
                        <a href="{{ $companyUrl }}/legal#privacy">Privacidad</a>
                        <a href="{{ $companyUrl }}/legal#terms">Términos</a>
                        <a href="mailto:{{ $supportEmail }}">Soporte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>