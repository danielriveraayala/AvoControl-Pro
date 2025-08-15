<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mantenimiento - AvoControl Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .animate-bounce-slow {
            animation: bounce 2s infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center border border-gray-100">
            <!-- Logo/Icon -->
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center animate-pulse-slow">
                    <i class="fas fa-tools text-white text-2xl"></i>
                </div>
            </div>
            
            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-800 mb-4">
                Sistema en Mantenimiento
            </h1>
            
            <!-- Message -->
            <p class="text-gray-600 mb-6 leading-relaxed">
                {{ $message }}
            </p>
            
            <!-- Status -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-center text-yellow-800">
                    <i class="fas fa-clock mr-2 animate-bounce-slow"></i>
                    <span class="text-sm font-medium">
                        Tiempo estimado: {{ ceil($retry_after / 60) }} minutos
                    </span>
                </div>
            </div>
            
            <!-- Progress bar animation -->
            <div class="mb-6">
                <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-green-400 h-full rounded-full animate-pulse"></div>
                </div>
            </div>
            
            <!-- Info -->
            <div class="text-sm text-gray-500 space-y-2">
                <p>
                    <i class="fas fa-info-circle mr-1"></i>
                    Estamos mejorando nuestros servicios
                </p>
                <p>
                    <i class="fas fa-envelope mr-1"></i>
                    Soporte: admin@avocontrol.com
                </p>
            </div>
            
            <!-- Auto refresh -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <button onclick="location.reload()" 
                        class="bg-gradient-to-r from-blue-500 to-green-500 hover:from-blue-600 hover:to-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Intentar de nuevo
                </button>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-xs">
            <p>© {{ date('Y') }} AvoControl Pro - Centro de Acopio de Aguacate</p>
            <p class="mt-1">Desarrollado por Kreativos Pro</p>
        </div>
    </div>

    <!-- Auto refresh script -->
    <script>
        // Auto refresh every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
        
        // Check connectivity every 30 seconds
        setInterval(() => {
            fetch('/ping')
                .then(() => location.reload())
                .catch(() => {}); // Ignore errors
        }, 30000);
    </script>
</body>
</html>