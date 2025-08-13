<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class LandingPageController extends Controller
{
    /**
     * Display the landing page
     */
    public function index()
    {
        // SEO meta data
        $seo = [
            'title' => 'AvoControl Pro - Sistema de Gestión para Centros de Acopio de Aguacate',
            'description' => 'Software especializado para la administración completa de centros de acopio de aguacate. Control de inventarios, ventas, proveedores y reportes en tiempo real.',
            'keywords' => 'software aguacate, centro acopio, gestión aguacate, control inventario aguacate, sistema acopio, avocado management',
            'og_image' => 'https://picsum.photos/1200/630',
            'twitter_image' => 'https://picsum.photos/1200/600'
        ];

        // Pricing plans
        $plans = [
            'trial' => [
                'name' => 'Trial',
                'price' => '0',
                'duration' => '7 días',
                'features' => [
                    '1 usuario',
                    '50 lotes máximo',
                    '500MB almacenamiento',
                    'Reportes básicos',
                    'Soporte por email'
                ],
                'highlighted' => false,
                'cta' => 'Prueba Gratis'
            ],
            'basic' => [
                'name' => 'Basic',
                'price' => '29',
                'duration' => 'mes',
                'features' => [
                    '5 usuarios',
                    '500 lotes/mes',
                    '2GB almacenamiento',
                    'Todos los reportes',
                    'Notificaciones email',
                    'Soporte por email'
                ],
                'highlighted' => false,
                'cta' => 'Comenzar'
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => '79',
                'duration' => 'mes',
                'features' => [
                    '25 usuarios',
                    '2,000 lotes/mes',
                    '10GB almacenamiento',
                    'Reportes avanzados',
                    'Notificaciones push + SMS',
                    'API access',
                    'Backup automático',
                    'Soporte prioritario'
                ],
                'highlighted' => true,
                'cta' => 'Más Popular'
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => '199',
                'duration' => 'mes',
                'features' => [
                    '100 usuarios',
                    'Lotes ilimitados',
                    '50GB almacenamiento',
                    'Reportes personalizados',
                    'Multi-ubicación',
                    'API completo',
                    'Marca personalizada',
                    'Soporte 24/7'
                ],
                'highlighted' => false,
                'cta' => 'Contactar'
            ]
        ];

        // Features sections
        $features = [
            [
                'icon' => 'fas fa-boxes',
                'title' => 'Control de Inventario',
                'description' => 'Gestiona lotes de aguacate con trazabilidad completa desde el proveedor hasta el cliente final.'
            ],
            [
                'icon' => 'fas fa-chart-line',
                'title' => 'Reportes en Tiempo Real',
                'description' => 'Analítica avanzada con dashboards interactivos para tomar decisiones basadas en datos.'
            ],
            [
                'icon' => 'fas fa-users',
                'title' => 'Multi-Usuario',
                'description' => 'Sistema de roles y permisos para controlar el acceso de tu equipo de trabajo.'
            ],
            [
                'icon' => 'fas fa-bell',
                'title' => 'Notificaciones Automáticas',
                'description' => 'Alertas por email, push y SMS para eventos importantes del negocio.'
            ],
            [
                'icon' => 'fas fa-cloud',
                'title' => 'Basado en la Nube',
                'description' => 'Accede desde cualquier dispositivo con conexión a internet. Sin instalación.'
            ],
            [
                'icon' => 'fas fa-shield-alt',
                'title' => 'Seguro y Confiable',
                'description' => 'Respaldos automáticos diarios y encriptación de datos sensibles.'
            ]
        ];

        // Testimonials
        $testimonials = [
            [
                'name' => 'Juan Pérez',
                'company' => 'Acopio El Aguacatal',
                'role' => 'Director General',
                'content' => 'AvoControl Pro transformó completamente la forma en que manejamos nuestro centro de acopio. La trazabilidad y los reportes son excepcionales.',
                'rating' => 5,
                'image' => 'https://picsum.photos/100/100?random=1'
            ],
            [
                'name' => 'María García',
                'company' => 'Exportadora Verde',
                'role' => 'Gerente de Operaciones',
                'content' => 'El sistema de notificaciones automáticas nos ayuda a estar al día con pagos y entregas. Ha mejorado nuestra eficiencia en un 40%.',
                'rating' => 5,
                'image' => 'https://picsum.photos/100/100?random=2'
            ],
            [
                'name' => 'Carlos Rodríguez',
                'company' => 'Aguacates Premium',
                'role' => 'Administrador',
                'content' => 'La facilidad de uso y la capacidad de trabajar desde cualquier lugar han sido fundamentales para nuestro crecimiento.',
                'rating' => 5,
                'image' => 'https://picsum.photos/100/100?random=3'
            ]
        ];

        // FAQ
        $faqs = [
            [
                'question' => '¿Puedo probar el sistema antes de contratar?',
                'answer' => 'Sí, ofrecemos un período de prueba gratuito de 7 días con acceso completo a las funciones básicas del sistema.'
            ],
            [
                'question' => '¿Necesito instalar algo en mi computadora?',
                'answer' => 'No, AvoControl Pro es un sistema basado en la nube. Solo necesitas un navegador web moderno y conexión a internet.'
            ],
            [
                'question' => '¿Puedo cambiar de plan en cualquier momento?',
                'answer' => 'Por supuesto. Puedes actualizar o cambiar tu plan en cualquier momento desde tu panel de administración.'
            ],
            [
                'question' => '¿Mis datos están seguros?',
                'answer' => 'Absolutamente. Utilizamos encriptación SSL, respaldos automáticos diarios y cumplimos con los estándares de seguridad más estrictos.'
            ],
            [
                'question' => '¿Ofrecen capacitación?',
                'answer' => 'Sí, todos los planes incluyen videos tutoriales y documentación. Los planes Premium y Enterprise incluyen capacitación personalizada.'
            ],
            [
                'question' => '¿Puedo exportar mis datos?',
                'answer' => 'Sí, puedes exportar todos tus datos en formatos Excel, PDF y CSV en cualquier momento.'
            ]
        ];

        return view('landing.index', compact('seo', 'plans', 'features', 'testimonials', 'faqs'));
    }

    /**
     * Display pricing page
     */
    public function pricing()
    {
        return redirect('/#pricing');
    }

    /**
     * Display features page
     */
    public function features()
    {
        return redirect('/#features');
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        return view('landing.contact');
    }

    /**
     * Process contact form
     */
    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
            'plan' => 'nullable|string|in:basic,premium,enterprise,corporate'
        ]);

        // TODO: Send email notification to admin
        // TODO: Store contact in database

        return redirect()->back()->with('success', 'Gracias por contactarnos. Te responderemos en menos de 24 horas.');
    }
}