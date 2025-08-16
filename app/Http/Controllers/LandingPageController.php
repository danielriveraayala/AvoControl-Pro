<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;

class LandingPageController extends Controller
{
    /**
     * Display the landing page
     */
    public function index(Request $request)
    {
        // Check if we're on a subdomain - if so, redirect to login or dashboard
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // If we're on a subdomain (3+ parts), don't show landing page
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            
            // Skip for special subdomains
            if (!in_array($subdomain, ['dev', 'www', 'api'])) {
                // This is a tenant subdomain
                if (auth()->check()) {
                    // User is logged in, redirect to dashboard
                    return redirect('/dashboard');
                } else {
                    // User not logged in, redirect to main domain login
                    session(['url.intended' => $request->url()]);
                    return redirect('//avocontrol.pro/login')
                        ->with('info', 'Por favor inicia sesión para acceder a esta empresa.');
                }
            }
        }
        // SEO meta data
        $seo = [
            'title' => 'AvoControl Pro - Sistema de Gestión para Centros de Acopio de Aguacate',
            'description' => 'Software especializado para la administración completa de centros de acopio de aguacate. Control de inventarios, ventas, proveedores y reportes en tiempo real.',
            'keywords' => 'software aguacate, centro acopio, gestión aguacate, control inventario aguacate, sistema acopio, avocado management',
            'og_image' => 'https://picsum.photos/1200/630',
            'twitter_image' => 'https://picsum.photos/1200/600'
        ];

        // Get active plans from database
        $dbPlans = SubscriptionPlan::visibleOnLanding()
            ->ordered()
            ->get();

        // Check if any plan has annual pricing for the switch
        $hasAnnualPlans = $dbPlans->some(function ($plan) {
            return $plan->hasAnnualPricing();
        });

        // Calculate discount range for the badge
        $discountBadge = 'Ahorra hasta 15%'; // Default
        if ($hasAnnualPlans) {
            $discounts = $dbPlans->filter(function ($plan) {
                return $plan->hasAnnualPricing();
            })->pluck('annual_discount_percentage')->filter()->unique()->values();

            if ($discounts->count() === 1) {
                // All plans have the same discount
                $discountBadge = "Ahorra hasta {$discounts->first()}%";
            } elseif ($discounts->count() > 1) {
                // Different discounts - show the maximum
                $maxDiscount = $discounts->max();
                $discountBadge = "Ahorra hasta {$maxDiscount}%";
            }
        }

        // Prepare plans data for the view
        $plans = [
            'list' => $dbPlans,
            'hasAnnualPlans' => $hasAnnualPlans,
            'discountBadge' => $discountBadge
        ];

        // Features sections
        $features = [
            [
                'icon' => 'fas fa-boxes',
                'title' => 'Control de Inventario',
                'description' => 'Gestiona lotes de aguacate con trazabilidad completa desde el proveedor hasta el cliente final.',
                'items' => [
                    'Trazabilidad completa por lote',
                    'Registro de calidades y pesos',
                    'Control de stock en tiempo real',
                    'Historial de movimientos'
                ]
            ],
            [
                'icon' => 'fas fa-chart-line',
                'title' => 'Reportes en Tiempo Real',
                'description' => 'Analítica avanzada con dashboards interactivos para tomar decisiones basadas en datos.',
                'items' => [
                    'Dashboard ejecutivo interactivo',
                    'Reportes de rentabilidad',
                    'Análisis de proveedores y clientes',
                    'Exportación a PDF y Excel'
                ]
            ],
            [
                'icon' => 'fas fa-users',
                'title' => 'Multi-Usuario',
                'description' => 'Sistema de roles y permisos para controlar el acceso de tu equipo de trabajo.',
                'items' => [
                    'Roles y permisos granulares',
                    'Usuarios ilimitados',
                    'Auditoría de actividades',
                    'Acceso por módulos'
                ]
            ],
            [
                'icon' => 'fas fa-bell',
                'title' => 'Notificaciones Automáticas',
                'description' => 'Alertas por email, push y SMS para eventos importantes del negocio.',
                'items' => [
                    'Notificaciones push en navegador',
                    'Alertas por email automáticas',
                    'Recordatorios de pagos',
                    'Alertas de inventario bajo'
                ]
            ],
            [
                'icon' => 'fas fa-cloud',
                'title' => 'Basado en la Nube',
                'description' => 'Accede desde cualquier dispositivo con conexión a internet. Sin instalación.',
                'items' => [
                    'Acceso desde cualquier dispositivo',
                    'Sin instalación requerida',
                    'Actualizaciones automáticas',
                    'Disponibilidad 99.9%'
                ]
            ],
            [
                'icon' => 'fas fa-shield-alt',
                'title' => 'Seguro y Confiable',
                'description' => 'Respaldos automáticos diarios y encriptación de datos sensibles.',
                'items' => [
                    'Respaldos automáticos diarios',
                    'Encriptación de datos',
                    'Certificados SSL',
                    'Cumplimiento de estándares'
                ]
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

    /**
     * Display a specific plan page for sharing
     */
    public function showPlan($key)
    {
        $plan = SubscriptionPlan::where('key', $key)
            ->where('is_active', true)
            ->firstOrFail();

        $formattedPlan = $this->formatPlanForLanding($plan, 'monthly');

        // SEO for specific plan
        $seo = [
            'title' => "Plan {$plan->name} - AvoControl Pro",
            'description' => $plan->description,
            'keywords' => 'software aguacate, plan ' . strtolower($plan->name) . ', centro acopio',
            'og_image' => 'https://picsum.photos/1200/630',
            'twitter_image' => 'https://picsum.photos/1200/600'
        ];

        return view('landing.plan', compact('plan', 'formattedPlan', 'seo'));
    }

    /**
     * Format plan data for landing page display
     */
    private function formatPlanForLanding($plan, $cycle = 'monthly')
    {
        $availableFeatures = SubscriptionPlan::getAvailableFeatures();
        $planFeatures = [];

        // Convert feature keys to readable format
        foreach ($plan->features ?? [] as $featureKey) {
            foreach ($availableFeatures as $category => $categoryFeatures) {
                if (isset($categoryFeatures[$featureKey])) {
                    $planFeatures[] = $categoryFeatures[$featureKey];
                }
            }
        }

        // Add limit-based features
        if ($plan->max_users > 0) {
            $planFeatures[] = ($plan->max_users == -1 ? 'Usuarios ilimitados' : "{$plan->max_users} usuarios");
        }
        if ($plan->max_lots_per_month > 0) {
            $planFeatures[] = ($plan->max_lots_per_month == -1 ? 'Lotes ilimitados' : "{$plan->max_lots_per_month} lotes/mes");
        }
        if ($plan->max_storage_gb > 0) {
            $planFeatures[] = ($plan->max_storage_gb == -1 ? 'Almacenamiento ilimitado' : "{$plan->max_storage_gb}GB almacenamiento");
        }
        if ($plan->max_locations > 0) {
            $planFeatures[] = ($plan->max_locations == -1 ? 'Ubicaciones ilimitadas' : "{$plan->max_locations} ubicaciones");
        }

        // Get price and PayPal ID based on cycle
        $price = $plan->getPriceForCycle($cycle);
        $paypalPlanId = $plan->getPayPalPlanId($cycle);
        $duration = $cycle === 'annual' ? 'año' : 'mes';

        // Calculate savings for annual plans
        $savings = null;
        $monthlyEquivalent = null;
        if ($cycle === 'annual' && $plan->hasAnnualPricing()) {
            $savings = $plan->getAnnualSavings();
            $monthlyEquivalent = $plan->getMonthlyEquivalent();
        }

        return [
            'id' => $plan->id,
            'key' => $plan->key,
            'name' => $plan->name,
            'price' => $price,
            'currency' => $plan->currency,
            'billing_cycle' => $cycle,
            'duration' => $duration,
            'features' => array_slice($planFeatures, 0, 8), // Limit to 8 features for display
            'all_features' => $planFeatures,
            'highlighted' => $plan->is_featured,
            'cta' => $plan->button_text ?? 'Comenzar',
            'badge' => $cycle === 'annual' && $savings ? "AHORRA $" . number_format($savings, 0) : $plan->popular_badge,
            'color' => $plan->color ?? '#3B82F6',
            'icon' => $plan->icon ?? 'fas fa-star',
            'trial_days' => $plan->trial_days,
            'description' => $plan->description,
            'paypal_plan_id' => $paypalPlanId,
            'metadata' => $plan->metadata,
            'show_on_landing' => $plan->show_on_landing ?? true,
            'annual_savings' => $savings,
            'monthly_equivalent' => $monthlyEquivalent,
            'discount_percentage' => $plan->annual_discount_percentage
        ];
    }

    /**
     * Get default plans if database is empty
     */
    private function getDefaultPlans()
    {
        return [
            'monthly' => collect([
                [
                    'key' => 'trial',
                    'name' => 'Trial',
                    'price' => 0,
                    'duration' => '7 días',
                    'features' => [
                        '1 usuario',
                        '50 lotes máximo',
                        '500MB almacenamiento',
                        'Reportes básicos',
                        'Soporte por email'
                    ],
                    'highlighted' => false,
                    'cta' => 'Prueba Gratis',
                    'badge' => null,
                    'color' => '#10B981',
                    'show_on_landing' => true
                ],
                [
                    'key' => 'basic',
                    'name' => 'Basic',
                    'price' => 29,
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
                    'cta' => 'Comenzar',
                    'badge' => null,
                    'color' => '#3B82F6',
                    'show_on_landing' => true
                ],
                [
                    'key' => 'premium',
                    'name' => 'Premium',
                    'price' => 79,
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
                    'cta' => 'Más Popular',
                    'badge' => 'MÁS POPULAR',
                    'color' => '#8B5CF6',
                    'show_on_landing' => true
                ],
                [
                    'key' => 'enterprise',
                    'name' => 'Enterprise',
                    'price' => 199,
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
                    'cta' => 'Contactar',
                    'badge' => null,
                    'color' => '#F59E0B',
                    'show_on_landing' => true
                ]
            ]),
            'yearly' => collect([]),
            'hasAnnualPlans' => false
        ];
    }
}