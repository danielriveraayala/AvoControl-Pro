<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ $companyName }}</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
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
            border-left: 4px solid #667eea;
            padding-left: 15px;
        }
        
        /* Account Info Box */
        .account-info {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        
        .account-info h3 {
            color: #2d3748;
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
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }
        
        /* Plan Features */
        .features-list {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
        
        .features-list li:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            color: #48bb78;
            margin-right: 10px;
            font-size: 16px;
        }
        
        /* Quick Start Steps */
        .quick-start-steps {
            list-style: none;
            padding: 0;
        }
        
        .step-item {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
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
            background: #667eea;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }
        
        .step-title {
            font-weight: 600;
            color: #2d3748;
            font-size: 16px;
        }
        
        .step-description {
            color: #4a5568;
            font-size: 14px;
            margin-left: 44px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #81c784;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            color: #a5d6a7;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .email-container {
                margin: 0 10px;
            }
            
            .email-header, .email-content, .email-footer {
                padding: 20px;
            }
            
            .info-grid {
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
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo">🥑 {{ $companyName }}</div>
                <h1 class="welcome-title">¡Bienvenido a bordo!</h1>
                <p class="welcome-subtitle">
                    @if($subscription)
                        Tu cuenta {{ ucfirst($subscription->plan) }} está lista para usar
                    @else
                        Tu cuenta ha sido creada exitosamente
                    @endif
                </p>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                <p class="greeting">Hola <strong>{{ $user->name }}</strong>,</p>
                
                <p style="margin-bottom: 20px;">
                    ¡Gracias por unirte a AvoControl Pro! Estamos emocionados de ayudarte a optimizar la gestión de tu centro de acopio de aguacate.
                </p>
                
                <!-- Account Information -->
                <div class="account-info">
                    <h3>📋 Información de tu Cuenta</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Email de Acceso</span>
                            <span class="info-value">{{ $user->email }}</span>
                        </div>
                        @if($subscription)
                        <div class="info-item">
                            <span class="info-label">Plan Seleccionado</span>
                            <span class="info-value">{{ ucfirst($subscription->plan) }} - ${{ number_format($subscription->amount, 2) }}/{{ $subscription->billing_cycle === 'yearly' ? 'año' : 'mes' }}</span>
                        </div>
                        @endif
                        @if($tenant)
                        <div class="info-item">
                            <span class="info-label">Empresa</span>
                            <span class="info-value">{{ $tenant->name }}</span>
                        </div>
                        @endif
                        <div class="info-item">
                            <span class="info-label">Fecha de Registro</span>
                            <span class="info-value">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Plan Features -->
                @if($subscription && !empty($planFeatures))
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
                        Tu cuenta está lista para usar. Haz click en el botón de abajo para iniciar sesión:
                    </p>
                    
                    <a href="{{ $loginUrl }}" class="btn btn-primary">Iniciar Sesión Ahora</a>
                    <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Ir al Dashboard</a>
                    
                    @if($temporaryPassword)
                    <div style="background: #fed7d7; border: 1px solid #feb2b2; border-radius: 8px; padding: 15px; margin: 20px 0;">
                        <strong>⚠️ Contraseña Temporal:</strong> {{ $temporaryPassword }}
                        <br><small>Por favor cambia esta contraseña después del primer inicio de sesión.</small>
                    </div>
                    @endif
                </div>
                
                <!-- Quick Start Guide -->
                @if(!empty($quickStartSteps))
                <div class="content-section">
                    <h2 class="section-title">📚 Primeros Pasos</h2>
                    <p style="margin-bottom: 20px;">
                        Sigue estos pasos para configurar tu sistema rápidamente:
                    </p>
                    
                    <div class="quick-start-steps">
                        @foreach($quickStartSteps as $index => $step)
                        <div class="step-item">
                            <div class="step-header">
                                <div class="step-icon">
                                    <i class="{{ $step['icon'] ?? 'fas fa-check' }}"></i>
                                </div>
                                <div class="step-title">{{ $index + 1 }}. {{ $step['title'] }}</div>
                            </div>
                            <div class="step-description">{{ $step['description'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Next Steps -->
                <div class="content-section">
                    <h2 class="section-title">💡 ¿Necesitas Ayuda?</h2>
                    <p>
                        Nuestro equipo está aquí para ayudarte. Si tienes preguntas o necesitas asistencia, no dudes en contactarnos:
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