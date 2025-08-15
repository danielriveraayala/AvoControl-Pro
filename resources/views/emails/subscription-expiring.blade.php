@extends('emails.layout')

@section('title', '⚠️ Tu suscripción expira pronto')

@section('content')
<div class="header">
    <h1>⚠️ Tu suscripción expira pronto</h1>
    <p class="subtitle">{{ $tenantName }}</p>
</div>

<div class="content">
    <p>Hola,</p>
    
    <p>Te escribimos para recordarte que tu suscripción al <strong>Plan {{ $plan }}</strong> expira en <strong>{{ $daysLeft }} día{{ $daysLeft == 1 ? '' : 's' }}</strong>.</p>
    
    <div class="info-box warning">
        <h3>📋 Detalles de tu suscripción</h3>
        <ul>
            <li><strong>Plan:</strong> {{ $plan }}</li>
            <li><strong>Costo:</strong> ${{ $amount }} {{ $currency }}</li>
            <li><strong>Fecha de vencimiento:</strong> {{ $expirationDate }}</li>
        </ul>
    </div>
    
    <p>Para mantener tu cuenta activa y evitar interrupciones en el servicio:</p>
    
    <div class="cta-container">
        <a href="{{ $renewalUrl }}" class="btn btn-primary">
            🔄 Renovar Suscripción
        </a>
    </div>
    
    @if($daysLeft <= 1)
    <div class="info-box error">
        <h3>🚨 ¡Atención!</h3>
        <p>Tu suscripción expira muy pronto. Si no renuevas a tiempo, tu cuenta será suspendida temporalmente hasta que se procese el pago.</p>
    </div>
    @endif
    
    <h3>💡 ¿Qué sucede si mi suscripción expira?</h3>
    <ul>
        <li>Tu cuenta será suspendida temporalmente</li>
        <li>No podrás acceder a tus datos hasta renovar</li>
        <li>Tus datos se mantendrán seguros por 30 días</li>
        <li>Podrás reactivar tu cuenta pagando la suscripción pendiente</li>
    </ul>
    
    <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>
    
    <div class="cta-container">
        <a href="{{ $supportUrl }}" class="btn btn-secondary">
            📞 Contactar Soporte
        </a>
    </div>
</div>

<div class="footer">
    <p>Gracias por confiar en {{ config('app.name') }}</p>
    <p><small>Este es un correo automático, por favor no responder directamente.</small></p>
</div>
@endsection