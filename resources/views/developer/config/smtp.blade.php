@extends('layouts.admin')

@section('title', 'Configuración SMTP')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Configuración SMTP</h1>
                        <p class="text-sm text-gray-600">Configura el servidor de correo electrónico</p>
                    </div>
                    <a href="{{ route('developer.config.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- SMTP Configuration Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('developer.config.update-smtp') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Configuración del Servidor SMTP</h3>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Mail Driver -->
                        <div>
                            <label for="mail_mailer" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Servidor <span class="text-red-500">*</span>
                            </label>
                            <select name="mail_mailer" id="mail_mailer" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_mailer') border-red-500 @enderror">
                                <option value="smtp" {{ old('mail_mailer', $smtpConfig['mailer']) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ old('mail_mailer', $smtpConfig['mailer']) == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ old('mail_mailer', $smtpConfig['mailer']) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ old('mail_mailer', $smtpConfig['mailer']) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="postmark" {{ old('mail_mailer', $smtpConfig['mailer']) == 'postmark' ? 'selected' : '' }}>Postmark</option>
                            </select>
                            @error('mail_mailer')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SMTP Host -->
                        <div>
                            <label for="mail_host" class="block text-sm font-medium text-gray-700 mb-2">
                                Servidor SMTP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $smtpConfig['host']) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_host') border-red-500 @enderror"
                                   placeholder="smtp.gmail.com">
                            @error('mail_host')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SMTP Port -->
                        <div>
                            <label for="mail_port" class="block text-sm font-medium text-gray-700 mb-2">
                                Puerto <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="mail_port" id="mail_port" value="{{ old('mail_port', $smtpConfig['port']) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_port') border-red-500 @enderror"
                                   placeholder="587">
                            @error('mail_port')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Encryption -->
                        <div>
                            <label for="mail_encryption" class="block text-sm font-medium text-gray-700 mb-2">
                                Encriptación
                            </label>
                            <select name="mail_encryption" id="mail_encryption" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_encryption') border-red-500 @enderror">
                                <option value="">Sin encriptación</option>
                                <option value="tls" {{ old('mail_encryption', $smtpConfig['encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', $smtpConfig['encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            @error('mail_encryption')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="mail_username" class="block text-sm font-medium text-gray-700 mb-2">
                                Usuario SMTP
                            </label>
                            <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $smtpConfig['username']) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_username') border-red-500 @enderror"
                                   placeholder="tu-email@gmail.com">
                            @error('mail_username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="mail_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña SMTP
                            </label>
                            <input type="password" name="mail_password" id="mail_password" value="{{ old('mail_password') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_password') border-red-500 @enderror"
                                   placeholder="{{ $smtpConfig['password'] ? '••••••••' : 'Contraseña del servidor' }}">
                            @error('mail_password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- From Address -->
                        <div>
                            <label for="mail_from_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Remitente <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $smtpConfig['from_address']) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_from_address') border-red-500 @enderror"
                                   placeholder="noreply@avocontrol.com">
                            @error('mail_from_address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- From Name -->
                        <div>
                            <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre Remitente <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $smtpConfig['from_name']) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mail_from_name') border-red-500 @enderror"
                                   placeholder="AvoControl Pro">
                            @error('mail_from_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Common Configurations -->
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Configuraciones Comunes</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <h5 class="font-semibold text-gray-700 mb-2">Gmail</h5>
                                    <ul class="space-y-1 text-gray-600">
                                        <li>Servidor: smtp.gmail.com</li>
                                        <li>Puerto: 587</li>
                                        <li>Encriptación: TLS</li>
                                        <li class="text-xs text-red-600">*Requiere contraseña de aplicación</li>
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="font-semibold text-gray-700 mb-2">Outlook/Hotmail</h5>
                                    <ul class="space-y-1 text-gray-600">
                                        <li>Servidor: smtp-mail.outlook.com</li>
                                        <li>Puerto: 587</li>
                                        <li>Encriptación: TLS</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <button type="button" onclick="testConfiguration()" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                        Probar Configuración
                    </button>
                    <div class="space-x-3">
                        <a href="{{ route('developer.config.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Guardar Configuración
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Configuration Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Probar Configuración SMTP</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email de prueba:</label>
                <input type="email" id="testEmailInput" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="test@example.com">
            </div>
            <p class="text-sm text-gray-600 mb-4">Se enviará un email con la configuración actual para verificar que funciona correctamente.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeTestModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </button>
                <button onclick="sendTestEmail()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Enviar Prueba
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function testConfiguration() {
    document.getElementById('testModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}

function sendTestEmail() {
    const email = document.getElementById('testEmailInput').value;
    if (!email) {
        alert('Por favor ingresa un email válido');
        return;
    }

    fetch('{{ route("developer.config.test-smtp") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeTestModal();
        }
    })
    .catch(error => {
        alert('Error al enviar email de prueba');
        console.error('Error:', error);
    });
}

// Auto-fill port based on encryption
document.getElementById('mail_encryption').addEventListener('change', function() {
    const portField = document.getElementById('mail_port');
    const currentPort = portField.value;
    
    if (!currentPort || currentPort === '587' || currentPort === '465' || currentPort === '25') {
        switch(this.value) {
            case 'tls':
                portField.value = '587';
                break;
            case 'ssl':
                portField.value = '465';
                break;
            default:
                portField.value = '25';
        }
    }
});
</script>
@endsection