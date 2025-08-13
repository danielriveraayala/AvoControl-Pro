@extends('emails.layout')

@section('title', 'Reporte de Suscripciones ' . ucfirst($period))

@section('content')
<div class="header">
    <h1>📊 Reporte de Suscripciones {{ ucfirst($period) }}</h1>
    <p class="subtitle">Generado el {{ $reportDate }}</p>
</div>

<div class="content">
    <p>Hola Super Admin,</p>
    
    <p>A continuación encontrarás el resumen del reporte de suscripciones para el período <strong>{{ $period }}</strong>:</p>
    
    <!-- Overview Metrics -->
    <div class="info-box info">
        <h3>📈 Resumen General</h3>
        <div class="metrics-grid">
            <div class="metric">
                <strong>Total Suscripciones:</strong> {{ number_format($overview['total_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Suscripciones Activas:</strong> {{ number_format($overview['active_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Trials Activos:</strong> {{ number_format($overview['trial_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Suscripciones Pagadas:</strong> {{ number_format($overview['paid_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Suspendidas:</strong> {{ number_format($overview['suspended_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Canceladas:</strong> {{ number_format($overview['cancelled_subscriptions']) }}
            </div>
        </div>
    </div>
    
    <!-- Revenue Metrics -->
    <div class="info-box success">
        <h3>💰 Métricas de Revenue</h3>
        <div class="metrics-grid">
            <div class="metric">
                <strong>MRR:</strong> ${{ number_format($revenue['mrr'], 2) }}
            </div>
            <div class="metric">
                <strong>ARR:</strong> ${{ number_format($revenue['arr'], 2) }}
            </div>
            <div class="metric">
                <strong>Revenue del Período:</strong> ${{ number_format($revenue['period_revenue'], 2) }}
            </div>
            <div class="metric">
                <strong>ARPU:</strong> ${{ number_format($revenue['average_revenue_per_user'], 2) }}
            </div>
        </div>
    </div>
    
    <!-- Plan Distribution -->
    @if(!empty($plans['by_plan']))
    <div class="info-box warning">
        <h3>📦 Distribución por Planes</h3>
        <ul>
            @foreach($plans['by_plan'] as $plan => $count)
            <li><strong>{{ ucfirst($plan) }}:</strong> {{ number_format($count) }} suscripciones</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <!-- Period Metrics -->
    <div class="info-box info">
        <h3>📊 Métricas del Período</h3>
        <div class="metrics-grid">
            <div class="metric">
                <strong>Nuevas Suscripciones:</strong> {{ number_format($periodMetrics['new_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Suscripciones Canceladas:</strong> {{ number_format($periodMetrics['cancelled_subscriptions']) }}
            </div>
            <div class="metric">
                <strong>Trials Convertidos:</strong> {{ number_format($periodMetrics['trial_conversions']['trials_converted']) }}
            </div>
            <div class="metric">
                <strong>Tasa de Conversión:</strong> {{ $periodMetrics['trial_conversions']['conversion_rate'] }}%
            </div>
            <div class="metric">
                <strong>Tasa de Churn:</strong> {{ $periodMetrics['churn_rate'] }}%
            </div>
        </div>
    </div>
    
    <!-- Payment Metrics -->
    <div class="info-box success">
        <h3>💳 Métricas de Pagos</h3>
        <div class="metrics-grid">
            <div class="metric">
                <strong>Pagos Exitosos:</strong> {{ number_format($payments['successful_payments']) }}
            </div>
            <div class="metric">
                <strong>Pagos Fallidos:</strong> {{ number_format($payments['failed_payments']) }}
            </div>
            <div class="metric">
                <strong>Monto Total Procesado:</strong> ${{ number_format($payments['total_payment_amount'], 2) }}
            </div>
        </div>
    </div>
    
    <!-- Tenant Metrics -->
    <div class="info-box info">
        <h3>🏢 Métricas de Tenants</h3>
        <div class="metrics-grid">
            <div class="metric">
                <strong>Total Tenants:</strong> {{ number_format($tenants['total_tenants']) }}
            </div>
            <div class="metric">
                <strong>Tenants Activos:</strong> {{ number_format($tenants['active_tenants']) }}
            </div>
            <div class="metric">
                <strong>Tenants Suspendidos:</strong> {{ number_format($tenants['suspended_tenants']) }}
            </div>
            <div class="metric">
                <strong>Nuevos Tenants:</strong> {{ number_format($tenants['new_tenants']) }}
            </div>
        </div>
    </div>
    
    <div class="cta-container">
        <a href="{{ route('developer.subscriptions.index') }}" class="btn btn-primary">
            📊 Ver Dashboard de Suscripciones
        </a>
    </div>
    
    <p><strong>Notas importantes:</strong></p>
    <ul>
        <li>Este reporte se genera automáticamente según la programación configurada</li>
        <li>Si está adjunto, encontrarás el reporte detallado en formato JSON</li>
        <li>Para consultas específicas, accede al panel de desarrollador</li>
    </ul>
    
    <p>Si necesitas información adicional o tienes alguna pregunta sobre estos datos, no dudes en revisar los logs del sistema.</p>
</div>

<div class="footer">
    <p>Reporte generado automáticamente por {{ config('app.name') }}</p>
    <p><small>Panel de Desarrollador - Sistema de Monitoreo de Suscripciones</small></p>
</div>

<style>
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.metric {
    background: rgba(255,255,255,0.7);
    padding: 10px;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

.info-box {
    margin: 20px 0;
    padding: 20px;
    border-radius: 8px;
    border-left: 5px solid;
}

.info-box.info {
    background-color: #d1ecf1;
    border-color: #17a2b8;
    color: #0c5460;
}

.info-box.success {
    background-color: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.info-box.warning {
    background-color: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.info-box h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
}

.info-box ul {
    margin: 0;
    padding-left: 20px;
}

.info-box ul li {
    margin: 5px 0;
}
</style>
@endsection