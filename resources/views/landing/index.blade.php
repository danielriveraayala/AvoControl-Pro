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
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .pricing-card.highlighted {
                transform: none;
            }
            
            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
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
                        <small class="text-muted">
                            <i class="fas fa-check-circle text-success"></i> Sin tarjeta de crédito
                            <i class="fas fa-check-circle text-success ms-3"></i> 7 días gratis
                            <i class="fas fa-check-circle text-success ms-3"></i> Cancela cuando quieras
                        </small>
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
                        <li><a href="#">Privacidad</a></li>
                        <li><a href="#">Términos</a></li>
                        <li><a href="#">Cookies</a></li>
                        <li><a href="#">Licencias</a></li>
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
        
        // Smooth scroll for anchor links
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
    </script>
    
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