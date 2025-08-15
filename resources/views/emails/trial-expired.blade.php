@extends('emails.layout')

@section('title', '🚨 Tu período de prueba ha expirado')

@section('content')
<div class="header">
    <h1>🚨 Tu período de prueba ha expirado</h1>
    <p class="subtitle">{{ $tenantName }}</p>
</div>

<div class="content">
    <p>Hola,</p>
    
    <p>Tu período de prueba gratuito de {{ config('app.name') }} expiró el <strong>{{ $trialEndDate }}</strong>.</p>
    
    <div class="info-box error">
        <h3>⛔ ¿Qué significa esto?</h3>
        <ul>
            <li>Tu cuenta ha sido suspendida temporalmente</li>
            <li>No puedes acceder a tus datos hasta suscribirte a un plan</li>
            <li>Tus datos están seguros y se mantendrán por {{ $dataRetentionDays }} días</li>
        </ul>
    </div>
    
    <p>¡Pero no te preocupes! Puedes reactivar tu cuenta inmediatamente seleccionando el plan que mejor se adapte a tus necesidades.</p>
    
    <div class="cta-container">
        <a href="{{ $subscribeUrl }}" class="btn btn-primary">
            💳 Ver Planes de Suscripción
        </a>
    </div>
    
    <h3>📦 Nuestros planes incluyen:</h3>
    <ul>
        <li><strong>Plan Basic ($29/mes):</strong> 5 usuarios, 500 lotes/mes</li>
        <li><strong>Plan Premium ($79/mes):</strong> 25 usuarios, 2,000 lotes/mes</li>
        <li><strong>Plan Enterprise ($199/mes):</strong> 100 usuarios, lotes ilimitados</li>
    </ul>
    
    <div class="info-box info">
        <h3>💾 Tus datos están seguros</h3>
        <p>Mantenemos todos tus datos seguros por <strong>{{ $dataRetentionDays }} días</strong> después del vencimiento del trial. Una vez que te suscribas, tendrás acceso inmediato a toda tu información.</p>
    </div>
    
    <p>Si tienes alguna pregunta sobre los planes o necesitas ayuda con el proceso de suscripción, nuestro equipo de soporte está listo para ayudarte.</p>
    
    <div class="cta-container">
        <a href="{{ $supportUrl }}" class="btn btn-secondary">
            📞 Contactar Soporte
        </a>
    </div>
    
    <p><strong>¡Gracias por probar {{ config('app.name') }}!</strong> Esperamos que hayas disfrutado de la experiencia y te animes a continuar con nosotros.</p>
</div>

<div class="footer">
    <p>Atentamente,<br>El equipo de {{ config('app.name') }}</p>
    <p><small>Este es un correo automático, por favor no responder directamente.</small></p>
</div>
@endsection