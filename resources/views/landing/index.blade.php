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
            --gradient-primary: linear-gradient(135deg, #2E7D32 0%, #66BB6A 100%);
            --gradient-accent: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%);
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
            overflow-x: hidden;
        }
        
        /* Navigation - Optimized for all devices */
        .navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 0.75rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            padding: 0.5rem 0;
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
        }
        
        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        
        /* Responsive navbar for tablets and mobile */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: white;
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
        }
        
        @media (min-width: 768px) and (max-width: 991px) {
            /* Tablet specific styles */
            .navbar-nav {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .navbar-nav .nav-item {
                margin: 0.25rem 0.5rem;
            }
        }
        
        .navbar-nav .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            border-radius: 5px;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(46,125,50,0.05);
        }
        
        /* Mobile menu improvements */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .navbar-toggler-icon {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        /* CTA Buttons - Sales Funnel Optimized */
        .btn-primary-custom {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary-custom:hover::before {
            left: 100%;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 10px 30px rgba(46,125,50,0.3);
        }
        
        .btn-secondary-custom {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary-custom:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Pulse animation for CTAs */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(46,125,50,0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(46,125,50,0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(46,125,50,0);
            }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Hero Section - Sales Funnel Optimized */
        .hero {
            background: linear-gradient(135deg, var(--light-color) 0%, #ffffff 100%);
            padding: 100px 0 60px;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
            min-height: 80vh;
            display: flex;
            align-items: center;
        }
        
        @media (min-width: 992px) {
            .hero {
                padding: 120px 0 80px;
                margin-top: 76px;
                min-height: 90vh;
            }
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
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        @media (min-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (min-width: 992px) {
            .hero h1 {
                font-size: 3rem;
                margin-bottom: 1.5rem;
            }
        }
        
        .hero p {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            line-height: 1.7;
        }
        
        @media (min-width: 768px) {
            .hero p {
                font-size: 1.25rem;
            }
        }
        
        /* Trust indicators */
        .trust-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
            align-items: center;
        }
        
        .trust-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .trust-badge i {
            color: var(--secondary-color);
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
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }
        
        .feature-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .feature-icon-small {
            font-size: 1.25rem;
            color: #3b82f6;
        }
        
        .feature-card h4 {
            font-size: 1.1rem;
            color: #111827;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .feature-card p {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .feature-card ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .feature-card ul li {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .feature-card ul li i {
            color: #10b981;
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }
        
        /* Pricing Section */
        .pricing {
            padding: 80px 0;
            background: var(--light-color);
        }
        
        .pricing-card {
            background: white;
            border-radius: 24px;
            padding: 0;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .pricing-card.featured {
            border: 2px solid var(--plan-color);
            transform: scale(1.02);
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
        }
        
        .pricing-card.featured:hover {
            transform: scale(1.02) translateY(-8px);
            box-shadow: 0 24px 60px rgba(0,0,0,0.2);
        }

        .plan-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--plan-color);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .plan-header {
            padding: 32px 24px 24px;
            text-align: center;
            background: linear-gradient(135deg, var(--plan-color)10, var(--plan-color)05);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        
        .plan-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            background: var(--plan-color);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        .plan-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0 0 16px;
            line-height: 1.2;
        }
        
        .plan-price {
            margin-bottom: 12px;
        }
        
        .price-amount {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 4px;
            margin-bottom: 4px;
        }
        
        .currency {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--plan-color);
        }
        
        .amount {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--dark-color);
            line-height: 1;
        }
        
        .price-period {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .plan-subtitle {
            margin-top: 12px;
        }
        
        .trial-info, .savings-info {
            font-size: 0.875rem;
            color: var(--plan-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        /* Pricing Toggle Switch Styles */
        .pricing-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .toggle-label {
            font-size: 1rem;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
            text-decoration: none;
        }

        .toggle-label.active {
            color: var(--primary-color);
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .pricing-toggle {
            margin-bottom: 3rem !important;
        }
        
        .plan-features {
            padding: 24px;
            flex-grow: 1;
            background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(0,0,0,0.02));
            border-radius: 12px;
            margin: 8px;
        }
        
        .features-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        
        .feature-category {
            margin-bottom: 24px;
        }
        
        .feature-category:last-child {
            margin-bottom: 0;
        }
        
        .feature-category-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            color: var(--text-dark);
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 0 -8px;
            padding-left: 8px;
            padding-right: 8px;
        }
        
        .feature-item:hover {
            background: rgba(99, 102, 241, 0.08);
            transform: translateX(4px);
        }
        
        .feature-icon {
            width: 18px;
            height: 18px;
            color: var(--plan-color);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--plan-color-rgb), 0.1);
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .plan-action {
            padding: 24px;
            border-top: 1px solid rgba(0,0,0,0.06);
            background: rgba(248, 250, 252, 0.5);
        }
        
        .plan-button {
            display: block;
            width: 100%;
            padding: 18px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .plan-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .plan-button:hover::before {
            left: 100%;
        }
        
        .trial-button {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }
        
        .trial-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5);
            color: white;
            background: linear-gradient(135deg, #059669, #047857);
        }
        
        .primary-button {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }
        
        .primary-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.5);
            color: white;
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        }
        
        .contact-button {
            background: linear-gradient(135deg, var(--plan-color), color-mix(in srgb, var(--plan-color) 80%, black));
            color: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .contact-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
            color: white;
            filter: brightness(1.1);
        }
        
        .plan-button:active {
            transform: translateY(-1px) scale(1.01);
        }
        
        @media (max-width: 640px) {
            .plan-button {
                padding: 16px 24px;
                font-size: 1rem;
                letter-spacing: 0.3px;
            }
        }
        
        .plan-button-paypal {
            min-height: 56px;
            margin-bottom: 12px;
        }
        
        .plan-note {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary-color);
        }

        input:checked + .slider:before {
            transform: translateX(30px);
        }

        .pricing-plans {
            display: none;
        }

        .pricing-plans.active {
            display: block;
        }

        .pricing-footer {
            margin-top: auto;
        }

        .paypal-button-container {
            min-height: 45px;
            margin-bottom: 0.5rem;
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
            
            .pricing-card.featured {
                transform: none;
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
            }
            
            .pricing-card.featured {
                transform: none;
                margin-bottom: 2rem;
            }
            
            .plan-header {
                padding: 24px 20px 20px;
            }
            
            .plan-icon {
                width: 56px;
                height: 56px;
                font-size: 24px;
            }
            
            .amount {
                font-size: 2.8rem;
            }
            
            .plan-features, .plan-action {
                padding: 20px;
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
            
            .plan-header {
                padding: 20px 16px 16px;
            }
            
            .plan-title {
                font-size: 1.3rem;
            }
            
            .plan-icon {
                width: 48px;
                height: 48px;
                font-size: 20px;
                margin-bottom: 16px;
            }
            
            .amount {
                font-size: 2.2rem;
            }
            
            .currency {
                font-size: 1.2rem;
            }
            
            .plan-features, .plan-action {
                padding: 16px;
            }
            
            .feature-item {
                gap: 10px;
                padding: 6px 0;
            }
            
            .feature-item span {
                font-size: 0.9rem;
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
        
        /* Alineación a la derecha para feature-items en fichas de planes */
        .pricing-card .feature-item {
            flex-direction: row-reverse;
            justify-content: flex-start;
            text-align: right;
        }
        
        .pricing-card .feature-item .feature-icon {
            margin-left: 8px;
            margin-right: 0;
        }
        
        .pricing-card .feature-item span {
            text-align: right;
            flex: 1;
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
    <!-- Navigation - Optimized for All Devices -->
    <nav class="navbar navbar-expand-lg" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">
                <i class="fas fa-leaf"></i> 
                <span>AvoControl Pro</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show">Precios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show">Testimonios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq" data-bs-toggle="collapse" data-bs-target=".navbar-collapse.show">FAQ</a>
                    </li>
                    <li class="nav-item ms-0 ms-lg-3 mt-3 mt-lg-0">
                        <a class="btn btn-primary-custom pulse" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Iniciar Sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section - Sales Funnel Optimized -->
    <section class="hero" id="page-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <!-- Urgency Badge -->
                    <div class="mb-3">
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fas fa-fire"></i> Oferta Limitada: 50% de descuento este mes
                        </span>
                    </div>
                    
                    <h1 class="mb-3">Aumenta tus Ganancias <span class="text-primary">30%</span> con el Sistema #1 para Centros de Acopio</h1>
                    <p class="lead mb-4">Más de <strong>500 centros de acopio</strong> ya confían en AvoControl Pro para gestionar inventarios, optimizar ventas y maximizar utilidades.</p>
                    
                    <!-- CTA Buttons -->
                    <div class="d-flex gap-3 flex-column flex-sm-row mb-4">
                        <a href="#pricing" class="btn btn-primary-custom btn-lg pulse">
                            <i class="fas fa-rocket"></i>
                            Comenzar Prueba Gratis
                        </a>
                        <a href="#demo" class="btn btn-secondary-custom btn-lg">
                            <i class="fas fa-play-circle"></i>
                            Ver Demo
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="trust-badges">
                        <div class="trust-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Sin tarjeta de crédito</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-clock"></i>
                            <span>Configuración en 5 min</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-headset"></i>
                            <span>Soporte 24/7</span>
                        </div>
                    </div>
                    
                    <!-- Social Proof -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="d-flex">
                                    <img src="https://i.pravatar.cc/30?img=1" class="rounded-circle" style="width: 30px; margin-right: -10px; border: 2px solid white;">
                                    <img src="https://i.pravatar.cc/30?img=2" class="rounded-circle" style="width: 30px; margin-right: -10px; border: 2px solid white;">
                                    <img src="https://i.pravatar.cc/30?img=3" class="rounded-circle" style="width: 30px; margin-right: -10px; border: 2px solid white;">
                                    <img src="https://i.pravatar.cc/30?img=4" class="rounded-circle" style="width: 30px; border: 2px solid white;">
                                </div>
                            </div>
                            <div>
                                <div class="text-warning mb-1">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <small class="text-muted">+500 empresas activas</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image position-relative">
                        <img src="https://picsum.photos/600/400?random=10" alt="Dashboard AvoControl Pro" class="img-fluid">
                        <!-- Play button overlay for demo video -->
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <a href="#demo" class="btn btn-white rounded-circle" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                <i class="fas fa-play text-primary" style="font-size: 24px; margin-left: 5px;"></i>
                            </a>
                        </div>
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
                        <div class="d-flex align-items-center justify-between mb-2">
                            <h4 class="mb-0">{{ $feature['title'] }}</h4>
                            <div class="feature-icon-small">
                                <i class="{{ $feature['icon'] }}"></i>
                            </div>
                        </div>
                        
                        <p class="mb-3">{{ $feature['description'] }}</p>
                        
                        @if(isset($feature['items']) && is_array($feature['items']))
                        <ul class="list-unstyled mb-0">
                            @foreach($feature['items'] as $item)
                                <li class="d-flex align-items-center mb-1">
                                    <i class="fas fa-check me-2"></i> 
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        @endif
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

            {{-- Switch de precios: solo mostrar si hay planes con precio anual --}}
            @if($plans['hasAnnualPlans'] ?? false)
            <div class="pricing-toggle text-center mb-4" data-aos="fade-up" data-aos-delay="150">
                <div class="d-inline-flex align-items-center p-1 bg-light rounded-pill shadow-sm">
                    <span class="toggle-label active px-3 py-2 rounded-pill fw-semibold" id="monthlyLabel">Mensual</span>
                    <label class="switch mx-2">
                        <input type="checkbox" id="pricingToggle">
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label px-3 py-2 rounded-pill fw-semibold" id="yearlyLabel">
                        Anual <small class="badge bg-success ms-2">{{ $plans['discountBadge'] ?? 'Ahorra hasta 15%' }}</small>
                    </span>
                </div>
            </div>
            @endif
            
            {{-- Spacer entre switch y planes --}}
            <div class="mb-5"></div>
            
            {{-- Sección de Planes --}}
            <div class="pricing-plans mt-4">
                
                <div class="row g-4 align-items-stretch justify-content-center">
                    @if(isset($plans['list']) && $plans['list']->count() > 0)
                        @foreach($plans['list'] as $plan)
                        <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 200 + ($loop->index * 100) }}">
                            <div class="pricing-card {{ $plan->is_featured ? 'featured' : '' }}" 
                                 data-plan-key="{{ $plan->key }}"
                                 data-has-annual="{{ $plan->hasAnnualPricing() ? 'true' : 'false' }}"
                                 style="--plan-color: {{ $plan->color ?? '#3B82F6' }}">
                                
                                {{-- Badge Popular --}}
                                @if($plan->popular_badge)
                                    <div class="plan-badge">
                                        {{ $plan->popular_badge }}
                                    </div>
                                @endif
                                
                                {{-- Card Header --}}
                                <div class="plan-header">
                                    <div class="plan-icon">
                                        @if($plan->icon)
                                            <i class="{{ $plan->icon }}"></i>
                                        @else
                                            <i class="fas fa-box"></i>
                                        @endif
                                    </div>
                                    
                                    <h3 class="plan-title">{{ $plan->name }}</h3>
                                    
                                    <div class="plan-price" data-price-container>
                                        @if($plan->price == 0)
                                            <div class="price-amount">
                                                <span class="currency">Gratis</span>
                                            </div>
                                        @else
                                            <div class="price-amount">
                                                <span class="currency">$</span>
                                                <span class="amount" data-price-value>{{ number_format($plan->price, 0) }}</span>
                                            </div>
                                            <div class="price-period" data-price-duration>/mes USD</div>
                                        @endif
                                    </div>
                                    
                                    {{-- Trial o Savings Info --}}
                                    <div class="plan-subtitle">
                                        @if($plan->trial_days > 0)
                                            <div class="trial-info" data-trial-info>
                                                <i class="fas fa-gift me-1"></i>
                                                {{ $plan->trial_days }} días de prueba gratis
                                            </div>
                                        @endif
                                        
                                        @if($plan->hasAnnualPricing())
                                            <div class="savings-info d-none" data-savings-info>
                                                <i class="fas fa-tag me-1"></i>
                                                Ahorras ${{ number_format($plan->getAnnualSavings(), 0) }} 
                                                ({{ $plan->annual_discount_percentage }}%)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Plan Features --}}
                                <div class="plan-features">
                                    {{-- Características principales mejoradas --}}
                                    <div class="features-list">
                                        {{-- Categoría: Límites optimizada --}}
                                        <div class="feature-category">
                                            <div class="feature-category-title">Capacidad & Límites</div>
                                            <div class="feature-items-grid">
                                                @if($plan->max_users > 0)
                                                    <div class="feature-item">
                                                        <i class="fas fa-users feature-icon"></i>
                                                        <span>{{ $plan->max_users == -1 ? 'Usuarios ilimitados' : $plan->max_users . ' usuarios' }}</span>
                                                    </div>
                                                @endif
                                                @if($plan->max_lots_per_month > 0)
                                                    <div class="feature-item">
                                                        <i class="fas fa-boxes feature-icon"></i>
                                                        <span>{{ $plan->max_lots_per_month == -1 ? 'Lotes ilimitados' : number_format($plan->max_lots_per_month) . ' lotes/mes' }}</span>
                                                    </div>
                                                @endif
                                                @if($plan->max_storage_gb > 0)
                                                    <div class="feature-item">
                                                        <i class="fas fa-cloud feature-icon"></i>
                                                        <span>{{ $plan->max_storage_gb == -1 ? 'Almacenamiento ilimitado' : $plan->max_storage_gb . 'GB almacenamiento' }}</span>
                                                    </div>
                                                @endif
                                                @if($plan->max_locations > 0)
                                                    <div class="feature-item">
                                                        <i class="fas fa-map-marker-alt feature-icon"></i>
                                                        <span>{{ $plan->max_locations == -1 ? 'Ubicaciones ilimitadas' : $plan->max_locations . ' ubicaciones' }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Categoría: Funcionalidades destacadas --}}
                                        @if($plan->features && is_array($plan->features))
                                        <div class="feature-category">
                                            <div class="feature-category-title">Funcionalidades Destacadas</div>
                                            <div class="feature-items-grid">
                                                @php
                                                    $availableFeatures = \App\Models\SubscriptionPlan::getAvailableFeatures();
                                                    $featureIcons = [
                                                        'basic_reports' => 'fas fa-chart-line',
                                                        'advanced_reports' => 'fas fa-chart-bar', 
                                                        'custom_reports' => 'fas fa-chart-pie',
                                                        'export_excel' => 'fas fa-file-excel',
                                                        'export_pdf' => 'fas fa-file-pdf',
                                                        'email_notifications' => 'fas fa-envelope',
                                                        'push_notifications' => 'fas fa-bell',
                                                        'sms_notifications' => 'fas fa-sms',
                                                        'api_access' => 'fas fa-code',
                                                        'automatic_backups' => 'fas fa-shield-alt',
                                                        'cloud_storage' => 'fas fa-cloud-upload-alt',
                                                        'multi_location' => 'fas fa-sitemap',
                                                        'custom_branding' => 'fas fa-palette',
                                                        'priority_support' => 'fas fa-headset',
                                                        'phone_support' => 'fas fa-phone',
                                                        'dedicated_manager' => 'fas fa-user-tie',
                                                        'email_support' => 'fas fa-envelope-open',
                                                        'manual_backups' => 'fas fa-save'
                                                    ];
                                                    $displayedFeatures = array_slice($plan->features, 0, 6);
                                                @endphp
                                                @foreach($displayedFeatures as $feature)
                                                    @php
                                                        $featureLabel = null;
                                                        foreach($availableFeatures as $categoryFeatures) {
                                                            if(isset($categoryFeatures[$feature])) {
                                                                $featureLabel = $categoryFeatures[$feature];
                                                                break;
                                                            }
                                                        }
                                                        $featureIcon = $featureIcons[$feature] ?? 'fas fa-check';
                                                    @endphp
                                                    @if($featureLabel)
                                                        <div class="feature-item">
                                                            <i class="{{ $featureIcon }} feature-icon"></i>
                                                            <span>{{ $featureLabel }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                                @if(count($plan->features) > 6)
                                                    <div class="feature-item">
                                                        <i class="fas fa-plus feature-icon"></i>
                                                        <span>Y {{ count($plan->features) - 6 }} funcionalidades más</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Plan Action --}}
                                <div class="plan-action">
                                    @if($plan->key === 'trial')
                                        {{-- Botón Trial Gratuito --}}
                                        <a href="{{ route('subscription.register', $plan->key) }}" class="plan-button trial-button" data-cta-button>
                                            <span data-cta-text>
                                                <i class="fas fa-rocket me-2"></i>
                                                Comenzar Gratis
                                            </span>
                                        </a>
                                        <div class="plan-note" data-trial-info>
                                            <i class="fas fa-check-circle me-1"></i>
                                            Sin tarjeta • {{ $plan->trial_days }} días gratis
                                        </div>
                                        @if($plan->hasAnnualPricing())
                                        <div class="plan-note d-none" data-savings-info>
                                            <i class="fas fa-piggy-bank me-1"></i>
                                            Ahorra ${{ number_format($plan->getAnnualSavings(), 0) }} anuales
                                        </div>
                                        @endif
                                    @elseif($plan->key !== 'trial')
                                        {{-- Botón de Registro (TODOS los planes van a registro) --}}
                                        <a href="{{ route('subscription.register', $plan->key) }}" class="plan-button primary-button" data-cta-button>
                                            <span data-cta-text>
                                                <i class="fas fa-credit-card me-2"></i>
                                                {{ $plan->button_text ?? 'Contratar Ahora' }}
                                            </span>
                                        </a>
                                        <div class="plan-note" data-trial-info>
                                            <i class="fas fa-gift me-1"></i>
                                            @if($plan->trial_days > 0)
                                                {{ $plan->trial_days }} días gratis • Sin tarjeta
                                            @else
                                                Pago seguro
                                            @endif
                                        </div>
                                        @if($plan->hasAnnualPricing())
                                        <div class="plan-note d-none" data-savings-info>
                                            <i class="fas fa-piggy-bank me-1"></i>
                                            Ahorra ${{ number_format($plan->getAnnualSavings(), 0) }} anuales
                                        </div>
                                        @endif
                                    @else
                                        {{-- Botón Contactar --}}
                                        <a href="{{ route('subscription.register', $plan->key) }}" class="plan-button contact-button" data-cta-button>
                                            <span data-cta-text>
                                                <i class="fas fa-envelope me-2"></i>
                                                {{ $plan->button_text ?? 'Contactar' }}
                                            </span>
                                        </a>
                                        <div class="plan-note" data-trial-info>
                                            <i class="fas fa-phone me-1"></i>
                                            @if($plan->trial_days > 0)
                                                {{ $plan->trial_days }} días gratis • Consulta personalizada
                                            @else
                                                Consulta personalizada
                                            @endif
                                        </div>
                                        @if($plan->hasAnnualPricing())
                                        <div class="plan-note d-none" data-savings-info>
                                            <i class="fas fa-piggy-bank me-1"></i>
                                            Ahorra ${{ number_format($plan->getAnnualSavings(), 0) }} anuales
                                        </div>
                                        @endif
                                    @endif
                                </div>
                                
                                {{-- Data attributes para JavaScript --}}
                                <script type="application/json" data-plan-data>
                                {!! json_encode([
                                    'key' => $plan->key,
                                    'name' => $plan->name,
                                    'monthly' => [
                                        'price' => $plan->price,
                                        'duration' => 'mes',
                                        'paypal_plan_id' => $plan->paypal_plan_id
                                    ],
                                    'annual' => $plan->hasAnnualPricing() ? [
                                        'price' => $plan->annual_price,
                                        'duration' => 'año',
                                        'savings' => $plan->getAnnualSavings(),
                                        'monthly_equivalent' => $plan->getMonthlyEquivalent(),
                                        'discount_percentage' => $plan->annual_discount_percentage,
                                        'paypal_plan_id' => $plan->paypal_annual_plan_id
                                    ] : null
                                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
                                </script>
                            </div>
                        </div>
                        @endforeach
                    @else
                        {{-- No hay planes disponibles --}}
                        <div class="col-12 text-center">
                            <div class="alert alert-warning">
                                <h4>⚠️ No hay planes disponibles</h4>
                                <p>No se encontraron planes activos para mostrar.</p>
                                <p class="mt-3">
                                    <a href="#contact" class="btn btn-primary">Contáctanos</a>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="text-center mt-5">
                <p class="text-muted">
                    ¿Necesitas algo más personalizado? 
                    <a href="#contact" class="text-primary fw-bold">Contáctanos para soluciones empresariales</a>
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
    
    <!-- CTA Section - Sales Funnel Optimized -->
    <section class="cta" id="cta">
        <div class="container">
            <div class="text-center" data-aos="zoom-in">
                <!-- Urgency Indicator -->
                <div class="mb-3">
                    <span class="badge bg-warning text-dark px-4 py-2 fs-6">
                        <i class="fas fa-clock"></i> Oferta válida por tiempo limitado
                    </span>
                </div>
                
                <h2 class="mb-3">¿Listo para Aumentar tus Ganancias 30%?</h2>
                <p class="lead mb-4">Únete a <strong>+500 centros de acopio</strong> que ya transformaron su negocio</p>
                
                <!-- Benefits List -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap justify-content-center gap-3 text-start">
                            <div class="text-white">
                                <i class="fas fa-check-circle text-warning"></i> Setup en 5 minutos
                            </div>
                            <div class="text-white">
                                <i class="fas fa-check-circle text-warning"></i> Sin tarjeta de crédito
                            </div>
                            <div class="text-white">
                                <i class="fas fa-check-circle text-warning"></i> Soporte 24/7 incluido
                            </div>
                            <div class="text-white">
                                <i class="fas fa-check-circle text-warning"></i> Cancela cuando quieras
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CTA Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-center">
                    <a href="#pricing" class="btn btn-cta btn-lg pulse">
                        <i class="fas fa-rocket me-2"></i>
                        Comenzar Prueba Gratuita de 7 Días
                    </a>
                    <a href="#demo" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-play-circle me-2"></i>
                        Ver Demo en Vivo
                    </a>
                </div>
                
                <!-- Trust Element -->
                <div class="mt-4">
                    <small class="text-white-50">
                        <i class="fas fa-shield-alt"></i> 
                        Garantía de satisfacción de 30 días o te devolvemos tu dinero
                    </small>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer - Simplified -->
    <footer id="contact" class="py-4">
        <div class="container">
            <div class="text-center">
                <p class="mb-2 text-white-50">
                    <a href="#" class="text-white-50 text-decoration-none me-3" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="privacy">Privacidad</a>
                    <a href="#" class="text-white-50 text-decoration-none me-3" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="terms">Términos</a>
                    <a href="#" class="text-white-50 text-decoration-none" data-bs-toggle="modal" data-bs-target="#legalModal" data-bs-tab="cookies">Cookies</a>
                </p>
                <p class="mb-0 text-white">
                    &copy; 2025 <strong>AvoControl Pro</strong>. Todos los derechos reservados. 
                    <span class="d-none d-sm-inline">|</span>
                    <span class="d-block d-sm-inline">Desarrollado con ❤️ por <a href="https://kreativos.pro" target="_blank" class="text-white">Kreativos Pro</a></span>
                </p>
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

        // Pricing Toggle Switch Functionality
        const pricingToggle = document.getElementById('pricingToggle');
        const monthlyLabel = document.getElementById('monthlyLabel');
        const yearlyLabel = document.getElementById('yearlyLabel');

        if (pricingToggle) {
            pricingToggle.addEventListener('change', function() {
                const isYearly = this.checked;
                
                // Update label states
                if (isYearly) {
                    monthlyLabel?.classList.remove('active');
                    yearlyLabel?.classList.add('active');
                } else {
                    monthlyLabel?.classList.add('active');
                    yearlyLabel?.classList.remove('active');
                }
                
                // Update all pricing cards
                updatePricingCards(isYearly);
            });
        }

        function updatePricingCards(isYearly) {
            const pricingCards = document.querySelectorAll('.pricing-card');
            
            pricingCards.forEach(card => {
                const planDataScript = card.querySelector('script[data-plan-data]');
                if (!planDataScript) return;
                
                try {
                    const planData = JSON.parse(planDataScript.textContent);
                    
                    // Skip cards that don't have annual pricing when yearly is selected
                    if (isYearly && !planData.annual) {
                        return;
                    }
                    
                    const currentData = isYearly && planData.annual ? planData.annual : planData.monthly;
                
                // Update price
                const priceValue = card.querySelector('[data-price-value]');
                const priceDuration = card.querySelector('[data-price-duration]');
                
                if (priceValue && priceDuration) {
                    if (currentData.price == 0) {
                        priceValue.textContent = 'Gratis';
                    } else {
                        priceValue.textContent = Number(currentData.price).toLocaleString();
                    }
                    priceDuration.textContent = '/' + currentData.duration + ' USD';
                }
                
                // Update badge
                const badgeContainer = card.querySelector('.plan-badge-container');
                if (badgeContainer) {
                    const existingBadge = badgeContainer.querySelector('.plan-badge');
                    if (existingBadge) {
                        existingBadge.remove();
                    }
                    
                    if (currentData.badge) {
                        const newBadge = document.createElement('div');
                        newBadge.className = 'plan-badge';
                        newBadge.style.background = currentData.color || '#3B82F6';
                        newBadge.textContent = currentData.badge;
                        badgeContainer.appendChild(newBadge);
                    }
                }
                
                // Update trial info and savings (handle multiple elements)
                const trialInfoElements = card.querySelectorAll('[data-trial-info]');
                const savingsInfoElements = card.querySelectorAll('[data-savings-info]');
                const planKey = planData.key;
                
                if (trialInfoElements.length > 0 || savingsInfoElements.length > 0) {
                    // FORZAR QUE SIEMPRE SE MUESTREN LOS DÍAS DE PRUEBA - NUNCA OCULTAR
                    trialInfoElements.forEach(el => {
                        el.classList.remove('d-none');
                        el.style.display = 'block'; // FORZAR
                        el.style.visibility = 'visible'; // FORZAR
                    });
                    
                    // Mostrar ahorros ADICIONALMENTE (sin ocultar trial)
                    if (isYearly && currentData.annual_savings && currentData.price > 0) {
                        savingsInfoElements.forEach(el => {
                            el.classList.remove('d-none');
                            // Only update content for elements in the plan header/subtitle
                            if (el.closest('.plan-subtitle')) {
                                el.innerHTML = `<i class="fas fa-tag me-1"></i>Ahorras $${Number(currentData.annual_savings).toLocaleString()} (${currentData.discount_percentage}%)`;
                            }
                        });
                    } else {
                        savingsInfoElements.forEach(el => el.classList.add('d-none'));
                    }
                }
                
                // Update CTA text
                const ctaText = card.querySelector('[data-cta-text]');
                if (ctaText && currentData.cta) {
                    ctaText.textContent = currentData.cta;
                }
                
                // Update PayPal button ID if needed
                @if(config('app.debug'))
                const debugInfo = card.querySelector('[data-debug-info]');
                if (debugInfo) {
                    debugInfo.textContent = `Debug: PayPal ID = ${currentData.paypal_plan_id || 'NULL'}`;
                }
                @endif
                
                } catch (error) {
                    console.error('Error updating pricing card:', error, card);
                }
            });
        }
        
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

        // Dynamic Pricing Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Make pricing plans visible
            const pricingPlans = document.querySelector('.pricing-plans');
            if (pricingPlans) {
                pricingPlans.classList.add('active');
            }
            
            const pricingToggle = document.getElementById('pricingToggle');
            const monthlyLabel = document.getElementById('monthlyLabel');
            const yearlyLabel = document.getElementById('yearlyLabel');
            
            if (pricingToggle) {
                pricingToggle.addEventListener('change', function() {
                    const isAnnual = this.checked;
                    updatePricing(isAnnual);
                    updateToggleLabels(isAnnual);
                    
                    // PayPal buttons REMOVED - No need to reinitialize anything
                });
            }
            
            // Initialize PayPal buttons after SDK loads
            function initPayPalWithRetry(attempts = 0) {
                if (window.paypal) {
                    initializePayPalButtons(false);
                } else if (attempts < 5) {
                    setTimeout(() => {
                        initPayPalWithRetry(attempts + 1);
                    }, 500 + (attempts * 200)); // Progressive delay
                } else {
                    console.error('PayPal SDK failed to load after 5 attempts');
                    // Show all CTA buttons as fallback
                    document.querySelectorAll('[data-cta-button]').forEach(btn => {
                        btn.style.display = 'block';
                    });
                    document.querySelectorAll('[data-paypal-button]').forEach(container => {
                        container.style.display = 'none';
                    });
                }
            }
            
            initPayPalWithRetry();
        });

        function updatePricing(isAnnual) {
            const planCards = document.querySelectorAll('[data-plan-key]');
            
            planCards.forEach(card => {
                const planDataScript = card.querySelector('script[data-plan-data]');
                if (!planDataScript) return;
                
                try {
                    const planData = JSON.parse(planDataScript.textContent);
                    const cycleData = isAnnual && planData.annual ? planData.annual : planData.monthly;
                    
                    // Update price
                    const priceValue = card.querySelector('[data-price-value]');
                    const priceDuration = card.querySelector('[data-price-duration]');
                    
                    if (priceValue && priceDuration) {
                        if (cycleData.price === 0) {
                            priceValue.textContent = 'Gratis';
                            priceDuration.textContent = '';
                        } else {
                            priceValue.textContent = new Intl.NumberFormat('es-MX').format(cycleData.price);
                            priceDuration.textContent = '/' + cycleData.duration + ' USD';
                        }
                    }
                    
                    // Toggle trial info vs savings info (handle multiple elements)
                    const trialInfoElements = card.querySelectorAll('[data-trial-info]');
                    const savingsInfoElements = card.querySelectorAll('[data-savings-info]');
                    const planKey = planData.key;
                    
                    // FORZAR QUE SIEMPRE SE MUESTREN LOS DÍAS DE PRUEBA - NUNCA OCULTAR JAMAS
                    trialInfoElements.forEach(el => {
                        el.classList.remove('d-none');
                        el.style.display = 'block'; // FORZAR
                        el.style.visibility = 'visible'; // FORZAR
                    });
                    
                    // Solo mostrar ahorros ADICIONALMENTE (sin ocultar trial NUNCA)
                    if (isAnnual && planData.annual && planData.annual.price > 0) {
                        savingsInfoElements.forEach(el => el.classList.remove('d-none'));
                    } else {
                        savingsInfoElements.forEach(el => el.classList.add('d-none'));
                    }
                    
                    // PayPal buttons REMOVED - All buttons are now CTA buttons to registration
                    // No need to update PayPal buttons
                    
                } catch (error) {
                    console.error('Error parsing plan data:', error);
                }
            });
        }

        function updateToggleLabels(isAnnual) {
            const monthlyLabel = document.getElementById('monthlyLabel');
            const yearlyLabel = document.getElementById('yearlyLabel');
            
            if (monthlyLabel && yearlyLabel) {
                if (isAnnual) {
                    monthlyLabel.classList.remove('active');
                    yearlyLabel.classList.add('active');
                } else {
                    monthlyLabel.classList.add('active');
                    yearlyLabel.classList.remove('active');
                }
            }
        }

        function updatePayPalButton(card, cycleData) {
            const paypalContainer = card.querySelector('[data-paypal-button]');
            const ctaButton = card.querySelector('[data-cta-button]');
            
            // Solo procesar tarjetas que tienen contenedor PayPal (planes de pago)
            if (!paypalContainer) {
                // Esta tarjeta no tiene PayPal configurado (ej: plan gratuito)
                if (ctaButton) ctaButton.style.display = 'block';
                return;
            }
            
            // Clear existing PayPal button
            paypalContainer.innerHTML = '';
            
            if (cycleData.paypal_plan_id) {
                // Hide CTA button and show PayPal
                if (ctaButton) ctaButton.style.display = 'none';
                paypalContainer.style.display = 'block';
                
                // Initialize PayPal button
                if (window.paypal) {
                    try {
                        window.paypal.Buttons({
                            style: {
                                shape: 'rect',
                                color: 'blue',
                                layout: 'vertical',
                                label: 'subscribe',
                                height: 45
                            },
                            createSubscription: function(data, actions) {
                                return actions.subscription.create({
                                    'plan_id': cycleData.paypal_plan_id
                                });
                            },
                            onApprove: function(data, actions) {
                                // Redirect to success page or handle subscription activation
                                window.location.href = '/subscription/success?subscription_id=' + data.subscriptionID;
                            },
                            onCancel: function(data) {
                                console.log('PayPal payment cancelled:', data);
                                // No need to show alert for cancellation
                            },
                            onError: function(err) {
                                console.error('PayPal error:', err);
                                alert('Error al procesar el pago. Por favor, inténtalo de nuevo.');
                            }
                        }).render(paypalContainer);
                    } catch (error) {
                        console.error('Error rendering PayPal button:', error);
                        // Show fallback CTA button
                        if (ctaButton) ctaButton.style.display = 'block';
                        paypalContainer.style.display = 'none';
                    }
                } else {
                    console.error('PayPal SDK not available');
                }
            } else {
                // Show CTA button and hide PayPal
                if (ctaButton) ctaButton.style.display = 'block';
                paypalContainer.style.display = 'none';
            }
        }

        function initializePayPalButtons(isAnnual = false) {
            const planCards = document.querySelectorAll('[data-plan-key]');
            
            planCards.forEach((card, index) => {
                const planDataScript = card.querySelector('script[data-plan-data]');
                if (!planDataScript) return;
                
                try {
                    const planData = JSON.parse(planDataScript.textContent);
                    const cycleData = isAnnual && planData.annual ? planData.annual : planData.monthly;
                    
                    updatePayPalButton(card, cycleData);
                } catch (error) {
                    console.error('Error initializing PayPal button:', error);
                }
            });
        }
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
            "highPrice": "499",
            "priceCurrency": "USD",
            "offerCount": "9"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "127"
        }
    }
    </script>

    <!-- Define PayPal functions first -->
    <script>
        function showFallbackButtons() {
            // Show all CTA buttons as fallback when PayPal fails
            document.querySelectorAll('[data-cta-button]').forEach(btn => {
                btn.style.display = 'block';
            });
            document.querySelectorAll('[data-paypal-button]').forEach(container => {
                container.style.display = 'none';
            });
        }

        // Initialize PayPal buttons after SDK loads
        function initPayPalWithRetry(attempts = 0) {
            if (window.paypal) {
                initializePayPalButtons(false);
            } else if (attempts < 5) {
                setTimeout(() => {
                    initPayPalWithRetry(attempts + 1);
                }, 500 + (attempts * 200)); // Progressive delay
            } else {
                console.error('PayPal SDK failed to load after 5 attempts');
                showFallbackButtons();
            }
        }
    </script>

    <!-- PayPal SDK -->
    @if(config('services.paypal.client_id'))
    <script>
        console.log('Loading PayPal SDK with vault=true for subscriptions...');
    </script>
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD&vault=true&intent=subscription" 
            onload="console.log('✅ PayPal SDK loaded successfully', window.paypal); initPayPalWithRetry();"
            onerror="console.error('❌ PayPal SDK failed to load'); showFallbackButtons();"></script>
    @else
        <script>
            console.error('PayPal client_id not configured');
            document.addEventListener('DOMContentLoaded', showFallbackButtons);
        </script>
    @endif
</body>
</html>