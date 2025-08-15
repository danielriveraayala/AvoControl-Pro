<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:image" content="{{ $seo['og_image'] }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['twitter_image'] }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #4CAF50;
            --accent-color: #FFC107;
            --dark-color: #1B5E20;
            --light-color: #F1F8E9;
            --text-dark: #212121;
            --text-light: #757575;
            --plan-color: {{ $formattedPlan['color'] }};
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--light-color) 0%, #fff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 2rem;
        }

        /* Plan Hero Section */
        .plan-hero {
            padding: 80px 0;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .plan-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 60%;
            height: 200%;
            background: var(--plan-color);
            opacity: 0.05;
            border-radius: 50%;
            transform: rotate(-15deg);
        }

        .plan-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            border-top: 5px solid var(--plan-color);
        }

        .plan-header {
            padding: 3rem 2rem;
            text-align: center;
            background: linear-gradient(135deg, var(--plan-color)10 0%, transparent 100%);
            position: relative;
        }

        .plan-icon {
            font-size: 4rem;
            color: var(--plan-color);
            margin-bottom: 1rem;
        }

        .plan-name {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .plan-description {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .plan-price {
            font-size: 4rem;
            font-weight: 800;
            color: var(--plan-color);
            margin-bottom: 0.5rem;
        }

        .plan-price sup {
            font-size: 2rem;
        }

        .plan-price span {
            font-size: 1.2rem;
            color: var(--text-light);
        }

        .plan-trial {
            display: inline-block;
            background: var(--accent-color);
            color: var(--dark-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 1rem;
        }

        .plan-features {
            padding: 2rem;
        }

        .features-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 2rem;
            text-align: center;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: var(--plan-color)05;
            padding-left: 1.5rem;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-icon {
            color: var(--secondary-color);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .feature-text {
            color: var(--text-dark);
            font-size: 1rem;
        }

        .plan-action {
            padding: 2rem;
            background: linear-gradient(135deg, var(--plan-color)05 0%, transparent 100%);
        }

        .btn-subscribe {
            width: 100%;
            padding: 1.25rem 2rem;
            font-size: 1.2rem;
            font-weight: 700;
            background: var(--plan-color);
            color: white;
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .paypal-button-container {
            min-height: 60px;
        }

        /* Info Section */
        .info-section {
            padding: 60px 0;
        }

        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            border-left: 4px solid var(--plan-color);
        }

        .info-card h3 {
            color: var(--dark-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card h3 i {
            color: var(--plan-color);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .info-item {
            padding: 1rem;
            background: var(--light-color);
            border-radius: 10px;
            text-align: center;
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--plan-color);
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: var(--dark-color);
            color: white;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-cta-primary {
            background: var(--accent-color);
            color: var(--dark-color);
        }

        .btn-cta-primary:hover {
            background: #FFD54F;
            transform: translateY(-2px);
        }

        .btn-cta-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-cta-secondary:hover {
            background: white;
            color: var(--dark-color);
        }

        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .footer-links {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .footer-links a:hover {
            opacity: 1;
        }

        .footer-copy {
            text-align: center;
            opacity: 0.6;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .plan-name {
                font-size: 2rem;
            }
            
            .plan-price {
                font-size: 3rem;
            }
            
            .cta-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/" class="logo">
                    <i class="fas fa-leaf"></i>
                    AvoControl Pro
                </a>
                <div>
                    <a href="/#pricing" class="btn btn-outline-primary me-2">Ver Todos los Planes</a>
                    <a href="/login" class="btn btn-primary">Iniciar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Plan Hero Section -->
    <section class="plan-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="plan-card" data-aos="zoom-in">
                        <div class="plan-header">
                            @if(!empty($formattedPlan['badge']))
                                <div class="plan-trial">{{ $formattedPlan['badge'] }}</div>
                            @endif
                            <i class="{{ $formattedPlan['icon'] }} plan-icon"></i>
                            <h1 class="plan-name">Plan {{ $formattedPlan['name'] }}</h1>
                            <p class="plan-description">{{ $formattedPlan['description'] }}</p>
                            <div class="plan-price">
                                @if($formattedPlan['price'] == 0)
                                    Gratis
                                @else
                                    <sup>$</sup>{{ number_format($formattedPlan['price'], 0) }}
                                    <span>/{{ $formattedPlan['duration'] }}</span>
                                @endif
                            </div>
                            @if(!empty($formattedPlan['trial_days']) && $formattedPlan['trial_days'] > 0)
                                <div class="plan-trial">
                                    <i class="fas fa-gift"></i> {{ $formattedPlan['trial_days'] }} días de prueba gratis
                                </div>
                            @endif
                        </div>
                        
                        <div class="plan-features">
                            <h2 class="features-title">Todo lo que incluye</h2>
                            <ul class="feature-list">
                                @foreach($formattedPlan['all_features'] as $feature)
                                <li class="feature-item">
                                    <i class="fas fa-check-circle feature-icon"></i>
                                    <span class="feature-text">{{ $feature }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="plan-action">
                            @if(!empty($formattedPlan['paypal_plan_id']))
                                <!-- PayPal Button Container -->
                                <div id="paypal-button-container" class="paypal-button-container"></div>
                            @else
                                <button class="btn-subscribe">
                                    {{ $formattedPlan['cta'] }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="info-card">
                        <h3><i class="fas fa-cog"></i> Configuración del Plan</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Usuarios</div>
                                <div class="info-value">
                                    {{ $plan->max_users == -1 ? '∞' : $plan->max_users }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Lotes/Mes</div>
                                <div class="info-value">
                                    {{ $plan->max_lots_per_month == -1 ? '∞' : number_format($plan->max_lots_per_month) }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Almacenamiento</div>
                                <div class="info-value">
                                    {{ $plan->max_storage_gb == -1 ? '∞' : $plan->max_storage_gb }}GB
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Ubicaciones</div>
                                <div class="info-value">
                                    {{ $plan->max_locations == -1 ? '∞' : $plan->max_locations }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="info-card">
                        <h3><i class="fas fa-info-circle"></i> Detalles Adicionales</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <strong>Ciclo de Facturación:</strong> 
                                {{ $plan->billing_cycle === 'yearly' ? 'Anual' : 'Mensual' }}
                            </li>
                            <li class="mb-3">
                                <strong>Moneda:</strong> {{ $plan->currency }}
                            </li>
                            @if($plan->billing_cycle === 'yearly' && !empty($formattedPlan['metadata']))
                                <li class="mb-3">
                                    <strong>Equivalente Mensual:</strong> 
                                    ${{ number_format($formattedPlan['metadata']['monthly_equivalent'] ?? 0, 0) }}/mes
                                </li>
                                <li class="mb-3">
                                    <strong>Ahorro Total:</strong> 
                                    ${{ number_format($formattedPlan['metadata']['total_savings'] ?? 0, 0) }}
                                </li>
                            @endif
                            <li class="mb-3">
                                <strong>Soporte:</strong> 
                                @if(in_array('phone_support', $formattedPlan['all_features']))
                                    24/7 Teléfono y Email
                                @elseif(in_array('priority_support', $formattedPlan['all_features']))
                                    Prioritario por Email
                                @else
                                    Email en horario laboral
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title" data-aos="fade-up">¿Listo para empezar?</h2>
            <p class="cta-subtitle" data-aos="fade-up" data-aos-delay="100">
                Únete a miles de centros de acopio que ya confían en AvoControl Pro
            </p>
            <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                <a href="/register" class="btn-cta btn-cta-primary">
                    Comenzar Ahora
                </a>
                <a href="/#pricing" class="btn-cta btn-cta-secondary">
                    Comparar Planes
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-links">
                <a href="/">Inicio</a>
                <a href="/#features">Características</a>
                <a href="/#pricing">Precios</a>
                <a href="/#contact">Contacto</a>
                <a href="/terms">Términos</a>
                <a href="/privacy">Privacidad</a>
            </div>
            <div class="footer-copy">
                © {{ date('Y') }} AvoControl Pro. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    @if(!empty($formattedPlan['paypal_plan_id']))
    <!-- PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&vault=true&intent=subscription"></script>
    <script>
        paypal.Buttons({
            style: {
                shape: 'rect',
                color: 'gold',
                layout: 'vertical',
                label: 'subscribe'
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    'plan_id': '{{ $formattedPlan['paypal_plan_id'] }}'
                });
            },
            onApprove: function(data, actions) {
                console.log('Subscription ID:', data.subscriptionID);
                // Redirect to registration or dashboard
                window.location.href = '/register?subscription=' + data.subscriptionID;
            },
            onError: function(err) {
                console.error('PayPal Error:', err);
                alert('Ocurrió un error al procesar el pago. Por favor intenta nuevamente.');
            }
        }).render('#paypal-button-container');
    </script>
    @endif
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>