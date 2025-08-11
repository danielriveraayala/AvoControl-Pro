@extends('emails.layouts.app')

@section('content')
    @php
        $priorityClass = 'alert-info';
        $priorityIcon = 'ℹ️';
        $priorityLabel = 'Información';
        
        if (($alert['priority'] ?? 'normal') === 'critical') {
            $priorityClass = 'alert-danger';
            $priorityIcon = '🚨';
            $priorityLabel = 'CRÍTICO';
        } elseif (($alert['priority'] ?? 'normal') === 'high') {
            $priorityClass = 'alert-warning';
            $priorityIcon = '⚠️';
            $priorityLabel = 'URGENTE';
        } elseif (($alert['priority'] ?? 'normal') === 'normal') {
            $priorityClass = 'alert-info';
            $priorityIcon = '📢';
            $priorityLabel = 'IMPORTANTE';
        }
    @endphp

    <div class="{{ $priorityClass }}">
        <strong>{{ $priorityIcon }} Alerta del Sistema - {{ $priorityLabel }}</strong><br>
        Se ha detectado un evento que requiere {{ ($alert['priority'] ?? 'normal') === 'critical' ? 'atención inmediata' : 'su atención' }}.
    </div>

    <h2>🔔 {{ $alert['title'] ?? 'Notificación del Sistema' }}</h2>
    
    <p><strong>Fecha y hora:</strong> {{ $alert['timestamp'] ?? date('d/m/Y H:i:s') }}</p>
    
    @if(isset($alert['description']) && !empty($alert['description']))
        <h3>📋 Descripción del Evento</h3>
        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #28a745; border-radius: 0 6px 6px 0;">
            {!! $alert['description'] !!}
        </div>
    @endif

    <!-- Detalles Técnicos -->
    @if(isset($alert['technical_details']) && !empty($alert['technical_details']))
        <h3>🔧 Detalles Técnicos</h3>
        <table class="table table-striped">
            @foreach($alert['technical_details'] as $key => $value)
                <tr>
                    <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                    <td>{{ is_array($value) ? implode(', ', $value) : $value }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <!-- Métricas Relevantes -->
    @if(isset($alert['metrics']) && !empty($alert['metrics']))
        <h3>📊 Métricas del Sistema</h3>
        <div class="stats-grid">
            @foreach($alert['metrics'] as $metric)
                <div class="stat-box" style="border-left-color: {{ $metric['color'] ?? '#28a745' }};">
                    <span class="value">{{ $metric['value'] }}</span>
                    <span class="label">{{ $metric['label'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Impacto del Problema -->
    @if(isset($alert['impact']) && !empty($alert['impact']))
        <h3>⚡ Impacto en el Sistema</h3>
        <div class="{{ $priorityClass }}">
            @if(is_array($alert['impact']))
                <ul style="margin-bottom: 0;">
                    @foreach($alert['impact'] as $impact_item)
                        <li>{{ $impact_item }}</li>
                    @endforeach
                </ul>
            @else
                {{ $alert['impact'] }}
            @endif
        </div>
    @endif

    <!-- Usuarios Afectados -->
    @if(isset($alert['affected_users']) && !empty($alert['affected_users']))
        <h3>👥 Usuarios Afectados</h3>
        <p><strong>{{ count($alert['affected_users']) }}</strong> usuarios han sido identificados como potencialmente afectados:</p>
        <ul>
            @foreach($alert['affected_users'] as $user)
                <li>{{ $user['name'] ?? 'Usuario' }} ({{ $user['role'] ?? 'Sin rol' }}) - {{ $user['status'] ?? 'Activo' }}</li>
            @endforeach
        </ul>
    @endif

    <!-- Acciones Realizadas -->
    @if(isset($alert['actions_taken']) && !empty($alert['actions_taken']))
        <h3>✅ Acciones Realizadas Automáticamente</h3>
        <ul>
            @foreach($alert['actions_taken'] as $action)
                <li>{{ $action }}</li>
            @endforeach
        </ul>
    @endif

    <!-- Acciones Requeridas -->
    <h3>🎯 Acciones Requeridas</h3>
    @if(isset($alert['required_actions']) && !empty($alert['required_actions']))
        <ol>
            @foreach($alert['required_actions'] as $action)
                <li><strong>{{ $action['title'] ?? 'Acción requerida' }}</strong><br>
                    <small style="color: #6c757d;">{{ $action['description'] ?? '' }}</small>
                    @if(isset($action['urgency']))
                        <br><span class="{{ $action['urgency'] === 'immediate' ? 'priority-critical' : 'priority-normal' }}">
                            Urgencia: {{ $action['urgency'] === 'immediate' ? 'INMEDIATA' : 'Normal' }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    @else
        <div class="alert alert-info">
            <strong>ℹ️ Información:</strong> No se requieren acciones específicas en este momento. Este es un evento informativo del sistema.
        </div>
    @endif

    <!-- Recomendaciones de Prevención -->
    @if(isset($alert['prevention_tips']) && !empty($alert['prevention_tips']))
        <h3>🛡️ Recomendaciones de Prevención</h3>
        <ul>
            @foreach($alert['prevention_tips'] as $tip)
                <li>{{ $tip }}</li>
            @endforeach
        </ul>
    @endif

    <!-- Información de Contacto -->
    @if(($alert['priority'] ?? 'normal') === 'critical')
        <div class="alert alert-danger">
            <strong>🆘 Soporte Urgente:</strong><br>
            Si necesita asistencia inmediata, contacte al administrador del sistema:
            <ul style="margin-top: 10px; margin-bottom: 0;">
                <li><strong>Email:</strong> admin@avocontrol.com</li>
                <li><strong>Teléfono:</strong> +52 (443) 123-4567</li>
                <li><strong>WhatsApp:</strong> +52 1 443 123 4567</li>
            </ul>
        </div>
    @endif

    <!-- Botones de Acción -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
            📊 Ir al Dashboard
        </a>
        @if(isset($alert['direct_link']) && !empty($alert['direct_link']))
            <a href="{{ $alert['direct_link'] }}" class="btn btn-info">
                🔧 Resolver Problema
            </a>
        @endif
        @if(($alert['priority'] ?? 'normal') === 'critical')
            <a href="{{ url('/configuration') }}" class="btn btn-secondary">
                ⚙️ Configuración del Sistema
            </a>
        @endif
    </div>

    <!-- Información de Seguimiento -->
    <div class="alert alert-info" style="margin-top: 30px;">
        <strong>📝 Seguimiento:</strong>
        @if(isset($alert['tracking_id']))
            ID de seguimiento: <code>{{ $alert['tracking_id'] }}</code><br>
        @endif
        @if(isset($alert['next_check']))
            Próxima verificación automática: {{ $alert['next_check'] }}<br>
        @endif
        Esta alerta fue generada automáticamente por el sistema de monitoreo de AvoControl Pro.
    </div>

    <!-- Footer específico para alertas críticas -->
    @if(($alert['priority'] ?? 'normal') === 'critical')
        <div style="background-color: #dc3545; color: white; padding: 15px; text-align: center; margin-top: 20px; border-radius: 5px;">
            <strong>⚠️ ALERTA CRÍTICA ⚠️</strong><br>
            Este mensaje requiere atención inmediata. No ignore esta notificación.
        </div>
    @endif
@endsection