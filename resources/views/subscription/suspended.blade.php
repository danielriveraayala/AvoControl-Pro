<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suscripción Suspendida - AvoControl Pro</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-yellow-50 via-orange-50 to-red-50 min-h-screen font-inter">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full mb-4">
                    <i class="fas fa-pause-circle text-2xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Suscripción Suspendida</h1>
                <p class="text-gray-600">Tu acceso a AvoControl Pro ha sido temporalmente suspendido</p>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Account Information -->
                <div class="lg:col-span-2">
                    <!-- User Profile Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                        <div class="p-6">
                            <div class="flex items-center space-x-4 mb-6">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f59e0b&color=fff&size=80" 
                                     alt="Avatar" class="w-16 h-16 rounded-full">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                                    <p class="text-gray-600">{{ auth()->user()->email }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                        <i class="fas fa-pause mr-1"></i>
                                        Suscripción Suspendida
                                    </span>
                                </div>
                            </div>

                            @if(isset($subscription))
                            <!-- Subscription Details -->
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-credit-card mr-2 text-gray-600"></i>
                                    Detalles de la Suscripción
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Plan Actual</p>
                                        <p class="font-medium text-gray-900">{{ $subscription->getPlanNameFormatted() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Estado</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </div>
                                    @if($subscription->suspended_at)
                                    <div>
                                        <p class="text-sm text-gray-500">Fecha de Suspensión</p>
                                        <p class="font-medium text-gray-900">{{ $subscription->suspended_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    @endif
                                    <div>
                                        <p class="text-sm text-gray-500">Último Pago</p>
                                        <p class="font-medium text-gray-900">${{ number_format($subscription->amount, 2) }} {{ $subscription->currency }}</p>
                                    </div>
                                </div>
                                
                                @if($subscription->suspension_reason)
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm text-yellow-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Motivo:</strong> {{ $subscription->suspension_reason }}
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Account Usage Stats -->
                            @if(auth()->user()->tenant)
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                                    Resumen de tu Cuenta
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-blue-600">{{ auth()->user()->tenant->users()->count() }}</p>
                                        <p class="text-xs text-gray-600">Usuarios</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-green-600">{{ auth()->user()->tenant->lots()->count() ?? 0 }}</p>
                                        <p class="text-xs text-gray-600">Lotes</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-purple-600">{{ auth()->user()->tenant->sales()->count() ?? 0 }}</p>
                                        <p class="text-xs text-gray-600">Ventas</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-orange-600">{{ auth()->user()->tenant->created_at->diffInDays() }}</p>
                                        <p class="text-xs text-gray-600">Días activo</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                        <div class="p-6">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-500"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">¿Por qué está suspendida mi cuenta?</h3>
                                    <p class="text-gray-700 mb-4">{{ $message }}</p>
                                    
                                    @if($canReactivate ?? false)
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <h4 class="font-medium text-green-900 mb-2">
                                            <i class="fas fa-lightbulb mr-1"></i>
                                            Buenas noticias: Puedes reactivar tu cuenta
                                        </h4>
                                        <ul class="text-sm text-green-800 space-y-1">
                                            <li class="flex items-center"><i class="fas fa-check-circle mr-2 text-green-600"></i> Elige un nuevo plan de suscripción</li>
                                            <li class="flex items-center"><i class="fas fa-check-circle mr-2 text-green-600"></i> Completa el pago de forma segura</li>
                                            <li class="flex items-center"><i class="fas fa-check-circle mr-2 text-green-600"></i> Recupera el acceso inmediatamente</li>
                                        </ul>
                                    </div>
                                    @else
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <h4 class="font-medium text-red-900 mb-2">
                                            <i class="fas fa-ban mr-1"></i>
                                            Suspensión Permanente
                                        </h4>
                                        <p class="text-sm text-red-800">Esta suscripción requiere intervención manual. Contacta a nuestro equipo de soporte para resolver la situación.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">¿Necesitas Ayuda?</h3>
                            <div class="space-y-3">
                                <a href="mailto:avocontrol@kreativos.pro" 
                                   class="w-full bg-yellow-600 text-white text-center py-3 px-4 rounded-lg hover:bg-yellow-700 transition-colors inline-flex items-center justify-center">
                                    <i class="fas fa-envelope mr-2"></i>
                                    Contactar Soporte
                                </a>

                                <a href="https://wa.me/+524431234567?text=Hola, necesito ayuda para reactivar mi suscripción de AvoControl Pro" 
                                   target="_blank"
                                   class="w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition-colors inline-flex items-center justify-center">
                                    <i class="fab fa-whatsapp mr-2"></i>
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Support Info -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6">
                        <div class="text-center">
                            <i class="fas fa-headset text-2xl text-blue-600 mb-3"></i>
                            <h4 class="font-semibold text-gray-900 mb-2">Soporte 24/7</h4>
                            <p class="text-sm text-gray-600 mb-4">Nuestro equipo está aquí para ayudarte a resolver cualquier problema</p>
                            <div class="space-y-2 text-sm">
                                <p class="flex items-center justify-center text-gray-700">
                                    <i class="fas fa-envelope mr-2 text-blue-600"></i>
                                    avocontrol@kreativos.pro
                                </p>
                                <p class="flex items-center justify-center text-gray-700">
                                    <i class="fas fa-phone mr-2 text-blue-600"></i>
                                    +52 443 123 4567
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Actions -->
                <div class="space-y-6">
                    
                    @if($canReactivate ?? false)
                    <!-- Subscription Plans -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reactivar Suscripción</h3>
                            <p class="text-gray-600 mb-6 text-sm">Elige un plan para continuar usando AvoControl Pro</p>
                            
                            <div class="space-y-4">
                                @forelse($availablePlans ?? [] as $index => $plan)
                                    @php
                                        // Determine if this plan should be highlighted (typically the middle plan)
                                        $isRecommended = $index === 1 && $availablePlans->count() >= 3;
                                        
                                        // Color scheme based on plan
                                        $colorScheme = match($plan->key) {
                                            'basic' => ['border' => 'border-blue-300', 'bg' => 'bg-blue-600 hover:bg-blue-700', 'text' => 'text-blue-600'],
                                            'premium' => ['border' => 'border-orange-300', 'bg' => 'bg-orange-600 hover:bg-orange-700', 'text' => 'text-orange-600'],
                                            'enterprise' => ['border' => 'border-gray-300', 'bg' => 'bg-gray-800 hover:bg-gray-900', 'text' => 'text-gray-600'],
                                            default => ['border' => 'border-indigo-300', 'bg' => 'bg-indigo-600 hover:bg-indigo-700', 'text' => 'text-indigo-600']
                                        };
                                    @endphp
                                    
                                    <div class="@if($isRecommended) border-2 {{ $colorScheme['border'] }} bg-orange-50 relative @else border border-gray-200 hover:{{ $colorScheme['border'] }} @endif rounded-lg p-4 transition-colors">
                                        @if($isRecommended)
                                        <div class="absolute -top-2 left-4">
                                            <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full">Recomendado</span>
                                        </div>
                                        @endif
                                        
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-semibold text-gray-900">{{ $plan->name }}</h4>
                                            <div class="text-right">
                                                <span class="text-xl font-bold text-gray-900">${{ number_format($plan->price, 0) }}</span>
                                                <span class="text-sm text-gray-500">/mes</span>
                                                @if($plan->annual_price)
                                                    <div class="text-xs text-green-600">
                                                        Ahorra ${{ number_format(($plan->price * 12) - $plan->annual_price, 0) }}/año
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($plan->description)
                                        <p class="text-sm text-gray-600 mb-3">{{ $plan->description }}</p>
                                        @endif
                                        
                                        <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                            @if($plan->max_users)
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2"></i> 
                                                    @if($plan->max_users == -1)
                                                        Usuarios ilimitados
                                                    @else
                                                        {{ number_format($plan->max_users) }} usuarios
                                                    @endif
                                                </li>
                                            @endif
                                            
                                            @if($plan->max_lots_per_month)
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2"></i> 
                                                    @if($plan->max_lots_per_month == -1)
                                                        Lotes ilimitados
                                                    @else
                                                        {{ number_format($plan->max_lots_per_month) }} lotes/mes
                                                    @endif
                                                </li>
                                            @endif
                                            
                                            @if($plan->max_storage_gb)
                                                <li class="flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2"></i> 
                                                    @if($plan->max_storage_gb == -1)
                                                        Almacenamiento ilimitado
                                                    @else
                                                        {{ $plan->max_storage_gb }}GB almacenamiento
                                                    @endif
                                                </li>
                                            @endif
                                            
                                            @if($plan->features && is_array($plan->features))
                                                @foreach(array_slice($plan->features, 0, 3) as $feature)
                                                    <li class="flex items-center">
                                                        <i class="fas fa-check text-green-500 mr-2"></i> 
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                        
                                        <a href="{{ route('subscription.register', ['plan' => $plan->key]) }}" 
                                           class="w-full {{ $colorScheme['bg'] }} text-white text-center py-2 px-4 rounded-lg transition-colors inline-block">
                                            Elegir {{ $plan->name }}
                                        </a>
                                    </div>
                                @empty
                                    <!-- Fallback if no plans are available -->
                                    <div class="text-center py-8">
                                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-3"></i>
                                        <p class="text-gray-600 mb-4">No hay planes disponibles actualmente.</p>
                                        <a href="mailto:avocontrol@kreativos.pro" 
                                           class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                                            Contactar Soporte
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 pt-6 border-t border-gray-200">
                <p class="text-gray-600 mb-2">
                    <strong>AvoControl Pro</strong> - Sistema de Gestión de Centros de Acopio
                </p>
                <p class="text-sm text-gray-500">
                    Copyright © {{ date('Y') }} <a href="https://kreativos.pro" class="text-blue-600 hover:text-blue-700">Kreativos Pro</a>. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Redirigiendo...</span>
        </div>
    </div>

    <script>
        // Show loading when clicking subscription links
        document.querySelectorAll('a[href*="subscription/register"]').forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('loading').classList.remove('hidden');
            });
        });
    </script>
</body>
</html>