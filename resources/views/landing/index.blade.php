<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Meta Tags -->
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <meta name="author" content="AvoControl Pro">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:image" content="{{ $seo['og_image'] }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AvoControl Pro">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['twitter_image'] }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #66BB6A;
            --accent-color: #FFC107;
            --dark-color: #1B5E20;
            --light-color: #F1F8E9;
            --text-dark: #333333;
            --text-light: #666666;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            padding: 0.5rem 0;
            background: rgba(255,255,255,0.98);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .navbar-nav .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            background: var(--dark-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,125,50,0.3);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--light-color) 0%, #ffffff 100%);
            padding: 120px 0 80px;
            margin-top: 76px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--secondary-color) 0%, transparent 70%);
            opacity: 0.1;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.25rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        
        .hero-image {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .hero-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background: white;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: var(--text-light);
        }
        
        /* Pricing Section */
        .pricing {
            padding: 80px 0;
            background: var(--light-color);
        }
        
        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .pricing-card.highlighted {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(46,125,50,0.2);
            border: 2px solid var(--primary-color);
        }
        
        .pricing-card.highlighted::before {
            content: 'MÁS POPULAR';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent-color);
            color: var(--dark-color);
            padding: 0.25rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        
        .pricing-header {
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            margin-bottom: 1.5rem;
        }
        
        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .plan-price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .plan-price sup {
            font-size: 1.5rem;
        }
        
        .plan-price span {
            font-size: 1rem;
            color: var(--text-light);
        }
        
        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem;
            flex-grow: 1;
        }
        
        .pricing-features li {
            padding: 0.75rem 0;
            color: var(--text-dark);
            position: relative;
            padding-left: 1.5rem;
        }
        
        .pricing-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--secondary-color);
            font-weight: 700;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 0;
            background: white;
        }
        
        .testimonial-card {
            background: var(--light-color);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 30px;
            font-size: 4rem;
            color: var(--primary-color);
            opacity: 0.3;
        }
        
        .testimonial-content {
            font-style: italic;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .testimonial-info h4 {
            margin: 0;
            color: var(--dark-color);
            font-size: 1.1rem;
        }
        
        .testimonial-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .testimonial-rating {
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        /* FAQ Section */
        .faq {
            padding: 80px 0;
            background: var(--light-color);
        }
        
        .accordion-button {
            background: white;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            box-shadow: none !important;
        }
        
        .accordion-button:not(.collapsed) {
            background: white;
            color: var(--primary-color);
        }
        
        .accordion-item {
            border: none;
            margin-bottom: 1rem;
            border-radius: 10px !important;
            overflow: hidden;
        }
        
        .accordion-body {
            color: var(--text-light);
            background: white;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            padding: 80px 0;
            text-align: center;
            color: white;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn-cta {
            background: white;
            color: var(--primary-color);
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        /* Footer */
        footer {
            background: var(--dark-color);
            color: white;
            padding: 60px 0 30px;
        }
        
        footer h5 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: var(--accent-color);
        }
        
        footer ul {
            list-style: none;
            padding: 0;
        }
        
        footer ul li {
            margin-bottom: 0.5rem;
        }
        
        footer ul li a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        footer ul li a:hover {
            color: var(--accent-color);
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 3rem;
            padding-top: 2rem;
            text-align: center;
            color: rgba(255,255,255,0.6);
        }
        
        /* Responsive - Tablets */
        @media (max-width: 991px) {
            .hero {
                padding: 100px 0 60px;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .pricing-card.highlighted {
                transform: scale(1.02);
            }
            
            .features, .pricing, .testimonials, .faq, .cta {
                padding: 60px 0;
            }
            
            footer {
                padding: 40px 0 20px;
            }
        }
        
        /* Responsive - Mobile Landscape & Small Tablets */
        @media (max-width: 768px) {
            .hero {
                padding: 80px 0 40px;
                margin-top: 60px;
            }
            
            .hero h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            
            .hero p {
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            
            .hero-image {
                margin-top: 2rem;
            }
            
            .section-title {
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
            }
            
            .section-subtitle {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            
            .features, .pricing, .testimonials, .faq, .cta {
                padding: 50px 0;
            }
            
            .feature-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .pricing-card {
                margin-bottom: 2rem;
                padding: 1.5rem;
            }
            
            .pricing-card.highlighted {
                transform: none;
                margin-bottom: 2rem;
            }
            
            .plan-price {
                font-size: 2.5rem;
            }
            
            .testimonial-card {
                margin-bottom: 1.5rem;
            }
            
            .cta h2 {
                font-size: 2rem;
            }
            
            .cta p {
                font-size: 1rem;
            }
            
            .btn-cta {
                padding: 0.75rem 2rem;
                font-size: 1rem;
            }
            
            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
                background: rgba(255,255,255,0.98);
            }
            
            .navbar-nav .nav-item {
                margin: 0.5rem 0;
            }
            
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            footer h5 {
                margin-top: 2rem;
            }
        }
        
        /* Responsive - Mobile Portrait */
        @media (max-width: 576px) {
            .hero {
                padding: 70px 0 30px;
            }
            
            .hero h1 {
                font-size: 1.75rem;
                line-height: 1.3;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .hero .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .section-subtitle {
                font-size: 0.9rem;
                padding: 0 15px;
            }
            
            .features, .pricing, .testimonials, .faq, .cta {
                padding: 40px 0;
            }
            
            .feature-card h3 {
                font-size: 1.1rem;
            }
            
            .feature-card p {
                font-size: 0.9rem;
            }
            
            .pricing-card {
                padding: 1.25rem;
            }
            
            .plan-name {
                font-size: 1.3rem;
            }
            
            .plan-price {
                font-size: 2rem;
            }
            
            .pricing-features li {
                font-size: 0.9rem;
                padding: 0.5rem 0;
            }
            
            .testimonial-content {
                font-size: 0.95rem;
            }
            
            .testimonial-author img {
                width: 50px;
                height: 50px;
            }
            
            .accordion-button {
                font-size: 1rem;
                padding: 1rem;
            }
            
            .accordion-body {
                font-size: 0.9rem;
            }
            
            .cta h2 {
                font-size: 1.5rem;
            }
            
            .cta p {
                font-size: 0.9rem;
            }
            
            .btn-cta {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
                width: 100%;
                max-width: 300px;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .footer-bottom {
                font-size: 0.85rem;
            }
            
            /* Stack buttons on mobile */
            .hero .d-flex {
                flex-direction: column;
                align-items: stretch;
            }
            
            .hero .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        /* Responsive - Very Small Devices */
        @media (max-width: 375px) {
            .hero h1 {
                font-size: 1.5rem;
            }
            
            .hero p {
                font-size: 0.9rem;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
            
            .plan-price {
                font-size: 1.75rem;
            }
            
            .btn-primary-custom {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
        
        /* Touch-friendly adjustments */
        @media (hover: none) {
            .feature-card:hover,
            .pricing-card:hover {
                transform: none;
            }
            
            .btn-primary-custom:hover {
                transform: none;
            }
            
            .social-links a:hover {
                transform: none;
            }
        }
        
        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Loading Animation */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">
                <i class="fas fa-leaf"></i> AvoControl Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Precios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contacto</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-primary-custom" href="{{ route('login') }}">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero" id="page-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1>Sistema de Gestión Integral para Centros de Acopio de Aguacate</h1>
                    <p>Optimiza tu operación, controla inventarios, gestiona ventas y maximiza tus ganancias con la plataforma más completa del mercado.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#pricing" class="btn btn-primary-custom btn-lg">
                            Comenzar Prueba Gratis
                        </a>
                        <a href="#features" class="btn btn-outline-success btn-lg">
                            Ver Características
                        </a>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex flex-column gap-1">
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success"></i> Sin tarjeta de crédito
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success"></i> 7 días gratis
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success"></i> Cancela cuando quieras
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image mt-5 mt-lg-0">
                        <img src="https://picsum.photos/600/400?random=10" alt="Dashboard AvoControl Pro">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Características Principales</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Todo lo que necesitas para administrar tu centro de acopio de manera eficiente
            </p>
            
            <div class="row g-4">
                @foreach($features as $index => $feature)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ 200 + ($loop->index * 100) }}">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="{{ $feature['icon'] }}"></i>
                        </div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['description'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Planes y Precios</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Elige el plan que mejor se adapte a las necesidades de tu negocio
            </p>
            
            <div class="row g-4 align-items-center">
                @foreach($plans as $plan)
                <div class="col-lg-3" data-aos="zoom-in" data-aos-delay="{{ 200 + ($loop->index * 100) }}">
                    <div class="pricing-card {{ $plan['highlighted'] ? 'highlighted' : '' }}">
                        <div class="pricing-header">
                            <div class="plan-name">{{ $plan['name'] }}</div>
                            <div class="plan-price">
                                @if($plan['price'] == '0')
                                    Gratis
                                @else
                                    <sup>$</sup>{{ $plan['price'] }}
                                @endif
                                <span>/{{ $plan['duration'] }}</span>
                            </div>
                        </div>
                        <ul class="pricing-features">
                            @foreach($plan['features'] as $feature)
                            <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="#" class="btn btn-primary-custom w-100">
                            {{ $plan['cta'] }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="text-center mt-5">
                <p class="text-muted">
                    ¿Necesitas algo más personalizado? 
                    <a href="#contact" class="text-primary fw-bold">Contáctanos para plan corporativo</a>
                </p>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Lo que Dicen Nuestros Clientes</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Miles de centros de acopio confían en AvoControl Pro
            </p>
            
            <div class="row">
                @foreach($testimonials as $testimonial)
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ 200 + ($loop->index * 100) }}">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            @for($i = 0; $i < $testimonial['rating']; $i++)
                            <i class="fas fa-star"></i>
                            @endfor
                        </div>
                        <p class="testimonial-content">
                            "{{ $testimonial['content'] }}"
                        </p>
                        <div class="testimonial-author">
                            <img src="{{ $testimonial['image'] }}" alt="{{ $testimonial['name'] }}">
                            <div class="testimonial-info">
                                <h4>{{ $testimonial['name'] }}</h4>
                                <p>{{ $testimonial['role'] }}, {{ $testimonial['company'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section class="faq" id="faq">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Preguntas Frecuentes</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Resolvemos tus dudas sobre AvoControl Pro
            </p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        @foreach($faqs as $faq)
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="{{ 200 + ($loop->index * 50) }}">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->index > 0 ? 'collapsed' : '' }}" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq{{ $loop->index }}" 
                                        aria-expanded="{{ $loop->index == 0 ? 'true' : 'false' }}">
                                    {{ $faq['question'] }}
                                </button>
                            </h2>
                            <div id="faq{{ $loop->index }}" 
                                 class="accordion-collapse collapse {{ $loop->index == 0 ? 'show' : '' }}" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta" id="cta">
        <div class="container">
            <div data-aos="zoom-in">
                <h2>¿Listo para Transformar tu Centro de Acopio?</h2>
                <p>Únete a cientos de empresas que ya confían en AvoControl Pro</p>
                <a href="#pricing" class="btn btn-cta">
                    Comenzar Prueba Gratuita de 7 Días
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>AvoControl Pro</h5>
                    <p>
                        La solución integral para la gestión eficiente de centros de acopio de aguacate.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Producto</h5>
                    <ul>
                        <li><a href="#features">Características</a></li>
                        <li><a href="#pricing">Precios</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Integraciones</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Empresa</h5>
                    <ul>
                        <li><a href="#">Acerca de</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Empleos</a></li>
                        <li><a href="#">Partners</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Soporte</h5>
                    <ul>
                        <li><a href="#">Centro de Ayuda</a></li>
                        <li><a href="#">Documentación</a></li>
                        <li><a href="#">Guías</a></li>
                        <li><a href="#">Status</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="privacy">Privacidad</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="terms">Términos</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="cookies">Cookies</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="licenses">Licencias</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 AvoControl Pro. Todos los derechos reservados. | Desarrollado por Kreativos Pro</p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll for anchor links and close mobile menu
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                // Close mobile navbar if open
                const navbarCollapse = document.getElementById('navbarNav');
                const navbarToggler = document.querySelector('.navbar-toggler');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    navbarToggler.click(); // Trigger Bootstrap collapse
                }
            });
        });
        
        // Add fade-in animation to elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);
        
        // Observe all sections
        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });
        
        // Handle legal modal tab switching
        document.addEventListener('DOMContentLoaded', function() {
            const legalModal = document.getElementById('legalModal');
            
            legalModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const targetTab = button.getAttribute('data-bs-tab');
                
                if (targetTab) {
                    // Find and activate the corresponding tab
                    const tabButton = document.getElementById(targetTab + '-tab');
                    const tabPane = document.getElementById(targetTab);
                    
                    if (tabButton && tabPane) {
                        // Remove active from all tabs and panes
                        document.querySelectorAll('#legalTabs .nav-link').forEach(tab => {
                            tab.classList.remove('active');
                        });
                        document.querySelectorAll('#legalTabsContent .tab-pane').forEach(pane => {
                            pane.classList.remove('show', 'active');
                        });
                        
                        // Activate target tab
                        tabButton.classList.add('active');
                        tabPane.classList.add('show', 'active');
                    }
                }
            });
        });
    </script>
    
    <!-- Legal Information Modal -->
    <div class="modal fade" id="legalModal" tabindex="-1" aria-labelledby="legalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="legalModalLabel">Información Legal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="legalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab">
                                Privacidad
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button" role="tab">
                                Términos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cookies-tab" data-bs-toggle="tab" data-bs-target="#cookies" type="button" role="tab">
                                Cookies
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="licenses-tab" data-bs-toggle="tab" data-bs-target="#licenses" type="button" role="tab">
                                Licencias
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="legalTabsContent">
                        <!-- Privacy Policy -->
                        <div class="tab-pane fade show active" id="privacy" role="tabpanel">
                            <h6 class="fw-bold">Política de Privacidad</h6>
                            <p><small class="text-muted">Última actualización: 13 de agosto de 2025</small></p>
                            
                            <h6>1. Información que Recopilamos</h6>
                            <p><strong>Información Personal:</strong></p>
                            <ul>
                                <li>Nombre completo y información de contacto</li>
                                <li>Dirección de correo electrónico</li>
                                <li>Número de teléfono</li>
                                <li>Información de la empresa</li>
                                <li>Datos de uso del sistema</li>
                            </ul>

                            <h6>2. Cómo Usamos su Información</h6>
                            <ul>
                                <li>Proporcionar y mantener nuestro servicio</li>
                                <li>Procesar transacciones y pagos</li>
                                <li>Enviar notificaciones técnicas y actualizaciones</li>
                                <li>Proporcionar soporte técnico</li>
                                <li>Mejorar nuestros servicios</li>
                                <li>Cumplir con obligaciones legales</li>
                            </ul>

                            <h6>3. Compartir Información</h6>
                            <p>No vendemos, comercializamos ni transferimos su información personal a terceros, excepto:</p>
                            <ul>
                                <li>Cuando sea necesario para proporcionar el servicio</li>
                                <li>Para cumplir con la ley</li>
                                <li>Para proteger nuestros derechos</li>
                                <li>Con su consentimiento expreso</li>
                            </ul>

                            <h6>4. Seguridad de Datos</h6>
                            <p>Implementamos medidas de seguridad técnicas y organizativas apropiadas:</p>
                            <ul>
                                <li>Encriptación SSL/TLS</li>
                                <li>Respaldos automáticos diarios</li>
                                <li>Control de acceso basado en roles</li>
                                <li>Monitoreo continuo de seguridad</li>
                            </ul>

                            <h6>5. Sus Derechos</h6>
                            <ul>
                                <li>Acceso a sus datos personales</li>
                                <li>Rectificación de datos inexactos</li>
                                <li>Eliminación de sus datos</li>
                                <li>Portabilidad de datos</li>
                                <li>Oposición al procesamiento</li>
                            </ul>

                            <h6>6. Contacto</h6>
                            <p>Para consultas sobre privacidad: <a href="mailto:privacy@avocontrol.pro">privacy@avocontrol.pro</a></p>
                        </div>

                        <!-- Terms of Service -->
                        <div class="tab-pane fade" id="terms" role="tabpanel">
                            <h6 class="fw-bold">Términos y Condiciones de Uso</h6>
                            <p><small class="text-muted">Última actualización: 13 de agosto de 2025</small></p>
                            
                            <h6>1. Aceptación de Términos</h6>
                            <p>Al acceder y usar AvoControl Pro, usted acepta estar sujeto a estos términos y condiciones. Si no está de acuerdo, no use nuestro servicio.</p>

                            <h6>2. Descripción del Servicio</h6>
                            <p>AvoControl Pro es un sistema de gestión para centros de acopio de aguacate que incluye:</p>
                            <ul>
                                <li>Gestión de inventarios y lotes</li>
                                <li>Control de proveedores y clientes</li>
                                <li>Procesamiento de ventas y pagos</li>
                                <li>Generación de reportes y análisis</li>
                                <li>Sistema de notificaciones automáticas</li>
                            </ul>

                            <h6>3. Cuentas de Usuario</h6>
                            <ul>
                                <li>Debe proporcionar información precisa y completa</li>
                                <li>Es responsable de mantener la seguridad de su cuenta</li>
                                <li>Debe notificar inmediatamente cualquier uso no autorizado</li>
                                <li>Una persona o entidad puede tener solo una cuenta</li>
                            </ul>

                            <h6>4. Planes y Pagos</h6>
                            <ul>
                                <li><strong>Trial:</strong> 7 días gratis, sin compromiso</li>
                                <li><strong>Suscripciones:</strong> Facturación mensual automática</li>
                                <li><strong>Cancelación:</strong> Puede cancelar en cualquier momento</li>
                                <li><strong>Reembolsos:</strong> No se otorgan reembolsos por meses parciales</li>
                            </ul>

                            <h6>5. Uso Aceptable</h6>
                            <p>No debe usar el servicio para:</p>
                            <ul>
                                <li>Actividades ilegales o fraudulentas</li>
                                <li>Interferir con la operación del servicio</li>
                                <li>Intentar acceder a cuentas de otros usuarios</li>
                                <li>Transmitir malware o código malicioso</li>
                                <li>Violar derechos de propiedad intelectual</li>
                            </ul>

                            <h6>6. Limitación de Responsabilidad</h6>
                            <p>El servicio se proporciona "tal como está". No garantizamos que será ininterrumpido o libre de errores.</p>

                            <h6>7. Terminación</h6>
                            <p>Podemos suspender o terminar su cuenta si viola estos términos o por otras razones comerciales legítimas.</p>

                            <h6>8. Ley Aplicable</h6>
                            <p>Estos términos se rigen por las leyes de México.</p>
                        </div>

                        <!-- Cookie Policy -->
                        <div class="tab-pane fade" id="cookies" role="tabpanel">
                            <h6 class="fw-bold">Política de Cookies</h6>
                            <p><small class="text-muted">Última actualización: 13 de agosto de 2025</small></p>
                            
                            <h6>¿Qué son las Cookies?</h6>
                            <p>Las cookies son pequeños archivos de texto que se almacenan en su dispositivo cuando visita nuestro sitio web.</p>

                            <h6>Cookies que Utilizamos</h6>
                            
                            <h6>1. Cookies Esenciales</h6>
                            <p>Necesarias para el funcionamiento básico del sitio:</p>
                            <ul>
                                <li><strong>Sesión:</strong> Mantiene su sesión de usuario activa</li>
                                <li><strong>Autenticación:</strong> Verifica su identidad</li>
                                <li><strong>CSRF:</strong> Protege contra ataques de falsificación</li>
                                <li><strong>Tenant:</strong> Mantiene el contexto de su empresa</li>
                            </ul>

                            <h6>2. Cookies de Funcionalidad</h6>
                            <ul>
                                <li><strong>Preferencias:</strong> Guarda sus configuraciones</li>
                                <li><strong>Idioma:</strong> Recuerda su idioma preferido</li>
                                <li><strong>Tema:</strong> Mantiene su preferencia de tema</li>
                            </ul>

                            <h6>3. Cookies de Rendimiento</h6>
                            <ul>
                                <li><strong>Análisis:</strong> Ayudan a mejorar el rendimiento</li>
                                <li><strong>Monitoreo:</strong> Detectan errores y problemas</li>
                                <li><strong>Uso:</strong> Comprenden cómo usa el servicio</li>
                            </ul>

                            <h6>4. Cookies de Terceros</h6>
                            <ul>
                                <li><strong>PayPal:</strong> Para procesamiento de pagos</li>
                                <li><strong>CDN:</strong> Para entrega de contenido</li>
                            </ul>

                            <h6>Control de Cookies</h6>
                            <p>Puede controlar las cookies a través de:</p>
                            <ul>
                                <li>Configuración de su navegador</li>
                                <li>Herramientas de privacidad</li>
                                <li>Nuestro panel de preferencias</li>
                            </ul>

                            <p><strong>Nota:</strong> Deshabilitar cookies esenciales puede afectar la funcionalidad del sitio.</p>
                        </div>

                        <!-- Licenses -->
                        <div class="tab-pane fade" id="licenses" role="tabpanel">
                            <h6 class="fw-bold">Información de Licencias</h6>
                            <p><small class="text-muted">Última actualización: 13 de agosto de 2025</small></p>
                            
                            <h6>AvoControl Pro</h6>
                            <p><strong>Software Propietario</strong><br>
                            © 2025 Kreativos Pro - Agencia de Marketing Digital y Desarrollo Web<br>
                            Todos los derechos reservados.</p>
                            
                            <p>Este software es propiedad exclusiva de Kreativos Pro y está protegido por las leyes de derechos de autor de México y tratados internacionales.</p>

                            <h6>Licencia de Uso</h6>
                            <p>Se le otorga una licencia limitada, no exclusiva e intransferible para:</p>
                            <ul>
                                <li>Usar el software según su plan de suscripción</li>
                                <li>Acceder a las funciones incluidas en su plan</li>
                                <li>Almacenar y procesar sus datos comerciales</li>
                            </ul>

                            <h6>Restricciones</h6>
                            <p>No puede:</p>
                            <ul>
                                <li>Copiar, modificar o distribuir el software</li>
                                <li>Realizar ingeniería inversa</li>
                                <li>Crear trabajos derivados</li>
                                <li>Sublicenciar o transferir la licencia</li>
                                <li>Usar el software para desarrollar productos competidores</li>
                            </ul>

                            <h6>Tecnologías de Terceros</h6>
                            <p>Este software utiliza las siguientes tecnologías bajo sus respectivas licencias:</p>
                            
                            <div class="mt-3">
                                <h6>Backend</h6>
                                <ul class="small">
                                    <li><strong>Laravel Framework</strong> - MIT License</li>
                                    <li><strong>PHP</strong> - PHP License v3.01</li>
                                    <li><strong>MySQL</strong> - GPL v2</li>
                                    <li><strong>Composer Dependencies</strong> - Various licenses</li>
                                </ul>
                            </div>

                            <div class="mt-3">
                                <h6>Frontend</h6>
                                <ul class="small">
                                    <li><strong>Bootstrap</strong> - MIT License</li>
                                    <li><strong>Font Awesome</strong> - SIL OFL 1.1 / MIT License</li>
                                    <li><strong>Alpine.js</strong> - MIT License</li>
                                    <li><strong>Chart.js</strong> - MIT License</li>
                                    <li><strong>AOS (Animate On Scroll)</strong> - MIT License</li>
                                </ul>
                            </div>

                            <div class="mt-3">
                                <h6>Servicios Externos</h6>
                                <ul class="small">
                                    <li><strong>PayPal API</strong> - Términos de servicio de PayPal</li>
                                    <li><strong>Web Push Protocol</strong> - W3C Standard</li>
                                </ul>
                            </div>

                            <h6>Propiedad Intelectual</h6>
                            <p>Todas las marcas comerciales, logos y nombres de productos mencionados son propiedad de sus respectivos dueños.</p>

                            <h6>Contacto Legal</h6>
                            <p>Para consultas sobre licencias: <a href="mailto:legal@avocontrol.pro">legal@avocontrol.pro</a></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "AvoControl Pro",
        "description": "{{ $seo['description'] }}",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "AggregateOffer",
            "lowPrice": "0",
            "highPrice": "199",
            "priceCurrency": "USD",
            "offerCount": "4"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "127"
        }
    }
    </script>
</body>
</html>