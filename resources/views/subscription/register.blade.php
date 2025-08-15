<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrarse - {{ $plan->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Crear tu cuenta</h2>
            <p class="text-gray-600">Completa tu registro para {{ $plan->name }}</p>
            
            <!-- Plan Info -->
            <div class="mt-4 p-4 bg-white rounded-lg shadow-sm border-l-4" style="border-left-color: {{ $plan->color }}">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-gray-900">{{ $plan->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $plan->description }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold" style="color: {{ $plan->color }}">
                            ${{ number_format($plan->price, 0) }}
                        </div>
                        <div class="text-sm text-gray-500">USD/mes</div>
                    </div>
                </div>
                @if($plan->trial_days > 0)
                    <div class="mt-2 text-sm text-green-600 bg-green-50 px-2 py-1 rounded">
                        <i class="fas fa-gift mr-1"></i>
                        {{ $plan->trial_days }} días de prueba gratis
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Registration Container -->
        <div class="bg-white py-8 px-6 shadow-lg rounded-lg sm:px-10">
            
            <!-- Billing Cycle Selection -->
            @if($plan->hasAnnualPricing())
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Ciclo de facturación</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 billing-option">
                        <input type="radio" name="billing_cycle" value="monthly" checked 
                               class="mr-3 text-indigo-600 focus:ring-indigo-500"
                               onchange="updatePayPalButton()"
                               @if(!$plan->paypal_plan_id) disabled @endif>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">Mensual</div>
                            <div class="text-sm text-gray-600">${{ number_format($plan->price, 0) }} USD/mes</div>
                            @if(!$plan->paypal_plan_id)
                                <div class="text-xs text-amber-600 mt-1">⚠️ Requiere sincronización con PayPal</div>
                            @endif
                        </div>
                    </label>
                    <label class="relative flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 billing-option">
                        <input type="radio" name="billing_cycle" value="yearly" 
                               class="mr-3 text-indigo-600 focus:ring-indigo-500"
                               onchange="updatePayPalButton()"
                               @if(!$plan->paypal_annual_plan_id) disabled @endif>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">Anual</div>
                            <div class="text-sm text-gray-600">${{ number_format($plan->annual_price, 0) }} USD/año</div>
                            <div class="text-xs text-green-600 font-medium">{{ $plan->annual_discount_percentage }}% descuento</div>
                            @if(!$plan->paypal_annual_plan_id)
                                <div class="text-xs text-amber-600 mt-1">⚠️ Requiere sincronización con PayPal</div>
                            @endif
                        </div>
                    </label>
                </div>
            </div>
            @else
            <div class="mb-6">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Facturación Mensual</h4>
                            <p class="text-sm text-gray-500">${{ number_format($plan->price, 0) }} USD/mes</p>
                            @if(!$plan->paypal_plan_id)
                                <p class="text-xs text-amber-600 mt-1">⚠️ Este plan requiere sincronización con PayPal</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Two Column Layout: Form + PayPal -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Registration Form -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información de registro</h3>
                    
                    <form id="registrationForm">
                        @csrf
                        <input type="hidden" name="plan_key" value="{{ $plan->key }}">
                        <input type="hidden" name="billing_cycle" id="selected_billing_cycle" value="monthly">

                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre completo *</label>
                                <input id="name" name="name" type="text" required autocomplete="name"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('name') }}" placeholder="Tu nombre completo"
                                       onblur="validateField(this)">
                                <div class="field-error hidden text-sm text-red-600 mt-1"></div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico *</label>
                                <input id="email" name="email" type="email" required autocomplete="email"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('email') }}" placeholder="tu@email.com"
                                       onblur="validateField(this)" oninput="checkEmailAvailability(this)">
                                <div class="field-error hidden text-sm text-red-600 mt-1"></div>
                                <div id="email-status" class="hidden text-sm mt-1"></div>
                            </div>

                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Nombre de la empresa *</label>
                                <input id="company_name" name="company_name" type="text" required autocomplete="organization"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('company_name') }}" placeholder="Nombre de tu empresa"
                                       onblur="validateField(this)">
                                <div class="field-error hidden text-sm text-red-600 mt-1"></div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña *</label>
                                <input id="password" name="password" type="password" required autocomplete="new-password"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Mínimo 8 caracteres"
                                       onblur="validateField(this)">
                                <div class="field-error hidden text-sm text-red-600 mt-1"></div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar contraseña *</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Confirma tu contraseña"
                                       onblur="validateField(this)">
                                <div class="field-error hidden text-sm text-red-600 mt-1"></div>
                            </div>

                            <!-- Terms -->
                            <div class="pt-2">
                                <label class="flex items-start">
                                    <input id="terms" type="checkbox" required 
                                           class="mt-1 mr-2 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="text-sm text-gray-600">
                                        Acepto los <a href="#" class="text-indigo-600 hover:text-indigo-500">términos y condiciones</a> 
                                        y la <a href="#" class="text-indigo-600 hover:text-indigo-500">política de privacidad</a>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- PayPal Payment Section -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Proceder al pago</h3>
                    
                    <!-- Validation Status -->
                    <div id="validation-status" class="hidden p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-sm text-yellow-800">Completa todos los campos para continuar</span>
                        </div>
                    </div>
                    
                    <!-- PayPal Button Container -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center" id="paypal-container">
                        <div id="paypal-button-container" class="w-full"></div>
                        <div id="paypal-loading" class="hidden">
                            <i class="fas fa-spinner fa-spin text-indigo-600 text-2xl"></i>
                            <p class="text-sm text-gray-600 mt-2">Cargando opciones de pago...</p>
                        </div>
                        <div id="paypal-disabled" class="text-gray-500">
                            <i class="fas fa-lock text-2xl mb-2"></i>
                            <p class="text-sm">Completa el formulario para habilitar el pago</p>
                        </div>
                        <div id="paypal-error" class="hidden text-red-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p class="text-sm font-medium" id="paypal-error-title">Error</p>
                            <p class="text-xs" id="paypal-error-message">Mensaje de error</p>
                        </div>
                    </div>

                    <!-- PayPal Info -->
                    <div class="text-center">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Pago 100% seguro procesado por PayPal
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            SSL cifrado • No almacenamos información de tarjetas
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to plans -->
        <div class="mt-4 text-center">
            <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-gray-500">
                ← Volver a ver todos los planes
            </a>
        </div>
    </div>
</div>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&vault=true&intent=subscription"></script>

<script>
// Global variables
let currentPlanData = {
    key: '{{ $plan->key }}',
    monthly_plan_id: '{{ $plan->paypal_plan_id }}',
    yearly_plan_id: '{{ $plan->paypal_annual_plan_id ?? "" }}',
    monthly_price: {{ $plan->price }},
    yearly_price: {{ $plan->annual_price ?? 0 }}
};

let formValidated = false;
let paypalButtonInitialized = false;
let validationTimeout = null;
let emailCheckTimeout = null;
let emailAvailable = false;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updatePayPalButton();
    setupFormValidation();
});

// Update PayPal button when billing cycle changes
function updatePayPalButton() {
    const billingCycleInput = document.querySelector('input[name="billing_cycle"]:checked');
    const billingCycle = billingCycleInput ? billingCycleInput.value : 'monthly';
    
    const selectedBillingCycleField = document.getElementById('selected_billing_cycle');
    if (selectedBillingCycleField) {
        selectedBillingCycleField.value = billingCycle;
    }
    
    // Determine which plan ID to use
    let planId = null;
    let errorMsg = null;
    
    if (billingCycle === 'yearly') {
        if (currentPlanData.yearly_plan_id) {
            planId = currentPlanData.yearly_plan_id;
        } else {
            errorMsg = 'El plan anual no está sincronizado con PayPal. Por favor contacta al administrador.';
        }
    } else {
        if (currentPlanData.monthly_plan_id) {
            planId = currentPlanData.monthly_plan_id;
        } else {
            errorMsg = 'El plan mensual no está sincronizado con PayPal. Por favor contacta al administrador.';
        }
    }
    
    console.log('updatePayPalButton called:', {
        billingCycle,
        planId,
        formValidated,
        yearlyPlanId: currentPlanData.yearly_plan_id,
        monthlyPlanId: currentPlanData.monthly_plan_id
    });
    
    // Show error if no plan ID is available
    if (!planId && formValidated) {
        showPayPalError('Plan no configurado', errorMsg);
        return;
    }
    
    // Always try to update PayPal button if form is validated and plan ID exists
    if (planId && formValidated) {
        const container = document.getElementById('paypal-button-container');
        if (container) {
            const oldPlanId = container.dataset.planId;
            
            // Only initialize if plan actually changed
            if (oldPlanId !== planId) {
                console.log('Plan change detected:', { oldPlanId, newPlanId: planId });
                initializePayPalButton(planId);
            } else {
                console.log('Same plan, no update needed:', planId);
            }
        } else {
            // No container yet, initialize
            initializePayPalButton(planId);
        }
    } else {
        console.log('PayPal button not updated:', { planId: !!planId, formValidated });
    }
}

// Setup real-time form validation
function setupFormValidation() {
    const form = document.getElementById('registrationForm');
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            if (validationTimeout) clearTimeout(validationTimeout);
            validationTimeout = setTimeout(checkFormValidation, 300);
        });
        input.addEventListener('blur', () => validateField(input));
    });
    
    // Add debounced validation for terms checkbox
    const termsCheckbox = document.getElementById('terms');
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', () => {
            if (validationTimeout) clearTimeout(validationTimeout);
            validationTimeout = setTimeout(checkFormValidation, 300);
        });
    }
    
    checkFormValidation();
}

// Validate individual field
function validateField(field) {
    // Skip validation if field is null or undefined
    if (!field) return true;
    
    const errorDiv = field.parentNode ? field.parentNode.querySelector('.field-error') : null;
    let isValid = true;
    let errorMessage = '';
    
    if (field.type === 'email') {
        if (!field.value.includes('@') || !field.value.includes('.')) {
            isValid = false;
            errorMessage = 'Ingresa un email válido';
        }
    } else if (field.id === 'password') {
        if (field.value.length < 8) {
            isValid = false;
            errorMessage = 'La contraseña debe tener al menos 8 caracteres';
        }
    } else if (field.id === 'password_confirmation') {
        const password = document.getElementById('password');
        if (password && field.value !== password.value) {
            isValid = false;
            errorMessage = 'Las contraseñas no coinciden';
        }
    } else if (field.value.trim() === '') {
        isValid = false;
        errorMessage = 'Este campo es requerido';
    }
    
    // Update field styling only if elements exist
    if (field.classList) {
        if (isValid) {
            field.classList.remove('border-red-300');
            field.classList.add('border-green-300');
        } else {
            field.classList.remove('border-green-300');
            field.classList.add('border-red-300');
        }
    }
    
    // Update error div only if it exists
    if (errorDiv && errorDiv.classList) {
        if (isValid) {
            errorDiv.classList.add('hidden');
        } else {
            errorDiv.textContent = errorMessage;
            errorDiv.classList.remove('hidden');
        }
    }
    
    // Debounce form validation to avoid rapid PayPal button changes
    if (validationTimeout) clearTimeout(validationTimeout);
    validationTimeout = setTimeout(checkFormValidation, 300);
    
    return isValid;
}

// Check overall form validation
function checkFormValidation() {
    const form = document.getElementById('registrationForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[required]');
    const termsCheckbox = document.getElementById('terms');
    const termsChecked = termsCheckbox ? termsCheckbox.checked : false;
    
    let allValid = true;
    
    // Check all required fields
    inputs.forEach(input => {
        if (input.type === 'checkbox') return; // Skip checkbox (handled separately)
        
        if (input.value.trim() === '') {
            allValid = false;
        } else if (input.type === 'email' && (!input.value.includes('@') || !input.value.includes('.'))) {
            allValid = false;
        } else if (input.id === 'password' && input.value.length < 8) {
            allValid = false;
        } else if (input.id === 'password_confirmation') {
            const password = document.getElementById('password');
            if (password && input.value !== password.value) {
                allValid = false;
            }
        }
    });
    
    // Check terms acceptance
    if (!termsChecked) {
        allValid = false;
    }
    
    // Check email availability
    if (!emailAvailable) {
        allValid = false;
    }
    
    formValidated = allValid;
    
    // Update PayPal button state
    const paypalContainer = document.getElementById('paypal-container');
    const paypalDisabled = document.getElementById('paypal-disabled');
    const validationStatus = document.getElementById('validation-status');
    
    if (allValid) {
        if (paypalDisabled) paypalDisabled.style.display = 'none';
        if (validationStatus) validationStatus.classList.add('hidden');
        updatePayPalButton();
    } else {
        if (paypalDisabled) paypalDisabled.style.display = 'block';
        if (validationStatus) validationStatus.classList.remove('hidden');
        // Clear PayPal button safely
        if (paypalButtonInitialized) {
            const paypalButtonContainer = document.getElementById('paypal-button-container');
            if (paypalButtonContainer) {
                paypalButtonInitialized = false;
                // Clear plan ID to force reinit when form becomes valid
                delete paypalButtonContainer.dataset.planId;
                setTimeout(() => {
                    paypalButtonContainer.innerHTML = '';
                }, 100);
            }
        }
    }
}

// Initialize PayPal button
let paypalButtonInstance = null;
let isRenderingButton = false;

function initializePayPalButton(planId) {
    console.log('initializePayPalButton called:', { planId, paypalButtonInitialized, isRenderingButton });
    
    // Prevent multiple simultaneous renders
    if (isRenderingButton) {
        console.log('Already rendering a button, will retry after current render completes');
        // Retry after a delay to allow current render to complete
        setTimeout(() => {
            if (!isRenderingButton) {
                initializePayPalButton(planId);
            }
        }, 500);
        return;
    }
    
    if (!planId) {
        console.log('No planId provided, showing error message');
        showPayPalError('Plan no disponible', 'Este plan no está configurado para suscripción automática. Por favor contacta al soporte.');
        return;
    }
    
    const container = document.getElementById('paypal-button-container');
    if (!container) {
        console.log('PayPal container not found');
        return;
    }
    
    // Check if plan has changed or button is not initialized
    const currentPlanId = container.dataset.planId;
    const planChanged = currentPlanId !== planId;
    
    console.log('PayPal button state:', {
        currentPlanId,
        newPlanId: planId,
        planChanged,
        paypalButtonInitialized
    });
    
    if (paypalButtonInitialized && currentPlanId === planId) {
        console.log('Exact same plan and button already initialized, skipping');
        return; // Exact same plan, button already initialized
    }
    
    // Set rendering flag
    isRenderingButton = true;
    
    // Show loading immediately
    const loadingDiv = document.getElementById('paypal-loading');
    if (loadingDiv) loadingDiv.style.display = 'block';
    
    // Hide any previous error messages
    const errorDiv = document.getElementById('paypal-error');
    if (errorDiv) errorDiv.style.display = 'none';
    
    // Clean up existing button if needed
    if (paypalButtonInstance) {
        console.log('Closing existing PayPal button instance');
        try {
            paypalButtonInstance.close();
        } catch(e) {
            console.log('Error closing PayPal button:', e);
        }
        paypalButtonInstance = null;
        paypalButtonInitialized = false;
    }
    
    // Clear container and render new button
    setTimeout(() => {
        // Only proceed if container still exists
        const containerCheck = document.getElementById('paypal-button-container');
        if (!containerCheck) {
            console.log('Container removed, aborting render');
            isRenderingButton = false;
            return;
        }
        
        // Clear the container
        containerCheck.innerHTML = '';
        
        // Store the new plan ID before rendering
        containerCheck.dataset.planId = planId;
        
        // Render the new button
        renderPayPalButton(planId, containerCheck);
    }, 100);
}

// Render PayPal button
function renderPayPalButton(planId, container) {
    // Show loading
    const loadingDiv = document.getElementById('paypal-loading');
    if (loadingDiv) loadingDiv.style.display = 'block';
    
    try {
        paypalButtonInstance = paypal.Buttons({
            style: {
                shape: 'rect',
                color: 'blue',
                layout: 'vertical',
                label: 'subscribe',
                height: 45
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    'plan_id': planId
                });
            },
            onApprove: function(data, actions) {
                // Show success message
                Swal.fire({
                    title: 'Creando tu cuenta...',
                    text: 'Por favor espera mientras procesamos tu registro.',
                    icon: 'success',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Create user with form data
                createUserAccount(data.subscriptionID);
            },
            onCancel: function(data) {
                Swal.fire({
                    title: 'Pago cancelado',
                    text: 'Puedes intentar nuevamente cuando desees.',
                    icon: 'info',
                    confirmButtonText: 'Entendido'
                });
            },
            onError: function(err) {
                console.error('PayPal error:', err);
                Swal.fire({
                    title: 'Error en el pago',
                    text: 'Hubo un problema con el procesamiento del pago. Por favor intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Reintentar'
                });
            }
        });
        
        paypalButtonInstance.render('#paypal-button-container').then(() => {
            const loadingDiv = document.getElementById('paypal-loading');
            if (loadingDiv) loadingDiv.style.display = 'none';
            paypalButtonInitialized = true;
            isRenderingButton = false;
            console.log('PayPal button rendered successfully for plan:', planId);
        }).catch((err) => {
            console.error('PayPal render error:', err);
            const loadingDiv = document.getElementById('paypal-loading');
            if (loadingDiv) loadingDiv.style.display = 'none';
            paypalButtonInitialized = false;
            isRenderingButton = false;
            
            // Show user-friendly error
            if (err.message && err.message.includes('INVALID_PLAN')) {
                showPayPalError('Plan no válido', 'Este plan necesita ser sincronizado con PayPal. Por favor contacta al soporte.');
            } else if (err.message && err.message.includes('container element removed')) {
                console.log('Container was removed during render, ignoring error');
            } else {
                showPayPalError('Error de conexión', 'No se pudo cargar PayPal. Verifica tu conexión a internet e intenta nuevamente.');
            }
        });
    } catch(err) {
        console.error('PayPal button creation error:', err);
        isRenderingButton = false;
        showPayPalError('Error de inicialización', 'No se pudo inicializar el botón de PayPal. Por favor recarga la página.');
    }
}

// Show PayPal error message
function showPayPalError(title, message) {
    const errorDiv = document.getElementById('paypal-error');
    const errorTitle = document.getElementById('paypal-error-title');
    const errorMessage = document.getElementById('paypal-error-message');
    const loadingDiv = document.getElementById('paypal-loading');
    const disabledDiv = document.getElementById('paypal-disabled');
    
    // Hide loading and disabled states
    if (loadingDiv) loadingDiv.style.display = 'none';
    if (disabledDiv) disabledDiv.style.display = 'none';
    
    // Show error with message
    if (errorDiv && errorTitle && errorMessage) {
        errorTitle.textContent = title;
        errorMessage.textContent = message;
        errorDiv.style.display = 'block';
    }
}

// Create user account after PayPal approval
function createUserAccount(subscriptionId) {
    const form = document.getElementById('registrationForm');
    const formData = new FormData(form);
    formData.append('subscription_id', subscriptionId);
    
    fetch('{{ route("subscription.register.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: '¡Cuenta creada exitosamente!',
                text: 'Serás redirigido a tu dashboard.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '/subscription/success?subscription_id=' + subscriptionId;
            });
        } else {
            // Handle different error types
            let title = 'Error en el registro';
            let text = data.message || 'Hubo un problema al crear tu cuenta.';
            let icon = 'error';
            let confirmButtonText = 'Reintentar';
            
            if (data.type === 'pre_registration_exists') {
                title = '⏳ Pre-registro existente';
                icon = 'warning';
                confirmButtonText = 'Entendido';
                text += '\n\nPuedes intentar completar tu pago o esperar a que expire el pre-registro.';
            } else if (data.type === 'email_exists') {
                title = '📧 Email ya registrado';
                icon = 'info';
                confirmButtonText = 'Iniciar Sesión';
                text += '\n\n¿Quieres iniciar sesión en su lugar?';
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: confirmButtonText,
                showCancelButton: data.type === 'email_exists',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && data.type === 'email_exists') {
                    // Redirect to login
                    window.location.href = '/login';
                }
            });
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        Swal.fire({
            title: 'Error de conexión',
            text: 'No se pudo completar el registro. Por favor verifica tu conexión a internet e intenta nuevamente.',
            icon: 'error',
            confirmButtonText: 'Reintentar'
        });
    });
}

// Check email availability in real-time
function checkEmailAvailability(emailInput) {
    // Clear previous timeout
    if (emailCheckTimeout) {
        clearTimeout(emailCheckTimeout);
    }
    
    const email = emailInput.value.trim();
    const statusDiv = document.getElementById('email-status');
    
    // Hide status if email is empty or invalid
    if (!email || !email.includes('@')) {
        statusDiv.classList.add('hidden');
        emailAvailable = false;
        return;
    }
    
    // Debounce the API call
    emailCheckTimeout = setTimeout(() => {
        // Show checking status
        statusDiv.className = 'text-sm mt-1 text-blue-600';
        statusDiv.textContent = 'Verificando disponibilidad...';
        statusDiv.classList.remove('hidden');
        
        fetch('{{ route("subscription.check-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                emailAvailable = true;
                statusDiv.className = 'text-sm mt-1 text-green-600';
                statusDiv.textContent = '✓ Email disponible';
                
                if (data.expired_pre_registration) {
                    statusDiv.textContent = '✓ Email disponible (pre-registro anterior expirado)';
                }
            } else {
                emailAvailable = false;
                statusDiv.className = 'text-sm mt-1 text-orange-600';
                
                if (data.type === 'pre_registration') {
                    statusDiv.innerHTML = `⏳ ${data.message} <br><small>Puedes intentar completar tu pago o esperar a que expire.</small>`;
                } else if (data.type === 'registered_user') {
                    statusDiv.innerHTML = `📧 ${data.message} <br><small><a href="/login" class="text-blue-600 hover:underline">¿Quieres iniciar sesión?</a></small>`;
                } else {
                    statusDiv.textContent = `⚠️ ${data.message}`;
                }
            }
            
            statusDiv.classList.remove('hidden');
            
            // Trigger form validation update
            if (validationTimeout) clearTimeout(validationTimeout);
            validationTimeout = setTimeout(checkFormValidation, 300);
        })
        .catch(error => {
            console.error('Email check error:', error);
            emailAvailable = false;
            statusDiv.className = 'text-sm mt-1 text-gray-500';
            statusDiv.textContent = 'No se pudo verificar el email';
            statusDiv.classList.remove('hidden');
        });
    }, 800);
}
</script>
</body>
</html>