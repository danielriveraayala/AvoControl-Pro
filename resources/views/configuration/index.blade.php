@extends('layouts.admin')

@section('title', 'Configuración')
@section('page-title', 'Configuración del Sistema')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Configuración</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        <li class="pt-2 px-3">
                            <h3 class="card-title">
                                <i class="fas fa-cog mr-1"></i>
                                Configuraciones
                            </h3>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="quality-tab" data-toggle="pill" href="#quality" role="tab"
                               aria-controls="quality" aria-selected="true">
                                <i class="fas fa-star"></i> Calidades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="company-tab" data-toggle="pill" href="#company" role="tab"
                               aria-controls="company" aria-selected="false">
                                <i class="fas fa-building"></i> Empresa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-tab" data-toggle="pill" href="#email" role="tab"
                               aria-controls="email" aria-selected="false">
                                <i class="fas fa-envelope"></i> Email/SMTP
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="push-tab" data-toggle="pill" href="#push" role="tab"
                               aria-controls="push" aria-selected="false">
                                <i class="fas fa-bell"></i> Push Notifications
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-two-tabContent">
                        <!-- Quality Configuration Tab -->
                        <div class="tab-pane fade show active" id="quality" role="tabpanel"
                             aria-labelledby="quality-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-star text-warning"></i> Gestión de Calidades</h4>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                        data-target="#qualityModal" onclick="openCreateQualityModal()">
                                    <i class="fas fa-plus"></i> Nueva Calidad
                                </button>
                            </div>
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped" id="qualityTable">
                                    <thead>
                                        <tr>
                                            <th>Orden</th>
                                            <th>Nombre</th>
                                            <th>Color</th>
                                            <th>Calibre</th>
                                            <th>Peso (g)</th>
                                            <th>Estado</th>
                                            <th width="100">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Company Configuration Tab -->
                        <div class="tab-pane fade" id="company" role="tabpanel" aria-labelledby="company-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-building text-info"></i> Configuración de la Empresa</h4>
                            </div>
                            
                            <form id="companyForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_name">Nombre de la Empresa <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" id="company_name" class="form-control" 
                                                   placeholder="AvoControl-Pro" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_rfc">RFC</label>
                                            <input type="text" name="company_rfc" id="company_rfc" class="form-control" 
                                                   placeholder="RFC123456789" maxlength="13">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="company_address">Dirección</label>
                                            <textarea name="company_address" id="company_address" class="form-control" 
                                                      rows="2" placeholder="Dirección completa de la empresa"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_city">Ciudad</label>
                                            <input type="text" name="company_city" id="company_city" class="form-control" 
                                                   placeholder="Uruapan">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_state">Estado</label>
                                            <input type="text" name="company_state" id="company_state" class="form-control" 
                                                   value="Michoacán">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_postal_code">Código Postal</label>
                                            <input type="text" name="company_postal_code" id="company_postal_code" class="form-control" 
                                                   placeholder="60000">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_phone">Teléfono</label>
                                            <input type="text" name="company_phone" id="company_phone" class="form-control" 
                                                   placeholder="(452) 123-4567">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_email">Email</label>
                                            <input type="email" name="company_email" id="company_email" class="form-control" 
                                                   placeholder="contacto@empresa.com">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_website">Sitio Web</label>
                                            <input type="url" name="company_website" id="company_website" class="form-control" 
                                                   placeholder="https://www.empresa.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_logo">Logo (URL)</label>
                                            <input type="url" name="company_logo" id="company_logo" class="form-control" 
                                                   placeholder="https://www.empresa.com/logo.png">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="company_description">Descripción</label>
                                    <textarea name="company_description" id="company_description" class="form-control" 
                                              rows="3" placeholder="Descripción de la empresa para usar en reportes"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Configuración
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" onclick="loadCompanyConfig()">
                                            <i class="fas fa-undo"></i> Recargar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Email/SMTP Configuration Tab -->
                        <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-envelope text-info"></i> Configuración de Email y SMTP</h4>
                            </div>
                            
                            <form id="emailForm">
                                @csrf
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Información:</strong> Configure los datos SMTP para el envío de notificaciones por email del sistema.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_mailer">Proveedor de Email <span class="text-danger">*</span></label>
                                            <select name="mail_mailer" id="mail_mailer" class="form-control" required>
                                                <option value="smtp">SMTP Personalizado</option>
                                                <option value="gmail">Gmail</option>
                                                <option value="sendgrid">SendGrid</option>
                                                <option value="mailgun">Mailgun</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_host">Servidor SMTP <span class="text-danger">*</span></label>
                                            <input type="text" name="mail_host" id="mail_host" class="form-control" 
                                                   placeholder="smtp.gmail.com" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="mail_port">Puerto SMTP <span class="text-danger">*</span></label>
                                            <input type="number" name="mail_port" id="mail_port" class="form-control" 
                                                   placeholder="587" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="mail_encryption">Encriptación</label>
                                            <select name="mail_encryption" id="mail_encryption" class="form-control">
                                                <option value="">Sin encriptación</option>
                                                <option value="tls">TLS</option>
                                                <option value="ssl">SSL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="notification_email_enabled">Estado</label>
                                            <select name="notification_email_enabled" id="notification_email_enabled" class="form-control">
                                                <option value="1">Habilitado</option>
                                                <option value="0">Deshabilitado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_username">Usuario SMTP</label>
                                            <input type="text" name="mail_username" id="mail_username" class="form-control" 
                                                   placeholder="tu-email@gmail.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_password">Contraseña SMTP</label>
                                            <input type="password" name="mail_password" id="mail_password" class="form-control" 
                                                   placeholder="Tu contraseña o token de aplicación">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_from_address">Email Remitente <span class="text-danger">*</span></label>
                                            <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" 
                                                   placeholder="noreply@avocontrol.pro" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mail_from_name">Nombre Remitente</label>
                                            <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" 
                                                   placeholder="AvoControl Pro">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Importante:</strong> Para Gmail, necesitas usar una contraseña de aplicación en lugar de tu contraseña normal.
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Configuración SMTP
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" onclick="loadEmailConfig()">
                                            <i class="fas fa-undo"></i> Recargar
                                        </button>
                                        <button type="button" class="btn btn-info ml-2" onclick="testEmailConfig()">
                                            <i class="fas fa-paper-plane"></i> Enviar Email de Prueba
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Push Notifications Configuration Tab -->
                        <div class="tab-pane fade" id="push" role="tabpanel" aria-labelledby="push-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-bell text-warning"></i> Configuración de Push Notifications</h4>
                                <div id="push-status" class="ml-2">
                                    <span class="badge badge-secondary">⭕ Cargando...</span>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Información:</strong> Las notificaciones push permiten enviar alertas en tiempo real al navegador del usuario, incluso cuando no está activamente usando la aplicación.
                            </div>
                            
                            <!-- Push Notification Controls -->
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-cog"></i> Control de Notificaciones
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Estado de Suscripción</h6>
                                                    <p class="text-muted mb-3">Administra tu suscripción a notificaciones push para este dispositivo.</p>
                                                    
                                                    <button type="button" id="enable-push-btn" class="btn btn-success btn-sm mb-2" style="display: none;">
                                                        <i class="fas fa-bell"></i> Activar Notificaciones
                                                    </button>
                                                    
                                                    <button type="button" id="disable-push-btn" class="btn btn-danger btn-sm mb-2" style="display: none;">
                                                        <i class="fas fa-bell-slash"></i> Desactivar Notificaciones
                                                    </button>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <h6>Prueba de Sistema</h6>
                                                    <p class="text-muted mb-3">Envía una notificación de prueba para verificar el funcionamiento.</p>
                                                    
                                                    <button type="button" id="test-push-btn" class="btn btn-info btn-sm mb-2" style="display: none;">
                                                        <i class="fas fa-paper-plane"></i> Enviar Prueba
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="alert alert-warning" id="browser-unsupported" style="display: none;">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <strong>Navegador no soportado:</strong> Tu navegador no soporta notificaciones push. Considera actualizarlo.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-bar"></i> Estadísticas
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="push-stats-loading" class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                                            </div>
                                            
                                            <div id="push-stats" style="display: none;">
                                                <div class="stat-item mb-2">
                                                    <small class="text-muted">Total Subscripciones</small>
                                                    <div class="h5 mb-0" id="total-subscriptions">0</div>
                                                </div>
                                                
                                                <div class="stat-item mb-2">
                                                    <small class="text-muted">Subscripciones Activas</small>
                                                    <div class="h5 mb-0" id="active-subscriptions">0</div>
                                                </div>
                                                
                                                <div class="stat-item mb-2">
                                                    <small class="text-muted">Usuarios con Notificaciones</small>
                                                    <div class="h5 mb-0" id="users-with-subscriptions">0</div>
                                                </div>
                                                
                                                <div class="stat-item">
                                                    <small class="text-muted">Nuevas esta semana</small>
                                                    <div class="h5 mb-0" id="recent-subscriptions">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Subscriptions Table -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-mobile-alt"></i> Mis Dispositivos Suscritos
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm" id="user-subscriptions-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Navegador</th>
                                                            <th>IP</th>
                                                            <th>Fecha Registro</th>
                                                            <th>Último Uso</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="user-subscriptions-tbody">
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">
                                                                <i class="fas fa-spinner fa-spin"></i> Cargando subscripciones...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Admin Tools (only for admin users) -->
                            @if(auth()->user()->role === 'admin')
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning">
                                            <h5 class="card-title mb-0 text-dark">
                                                <i class="fas fa-tools"></i> Herramientas de Administración
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted">Herramientas avanzadas para gestionar las notificaciones push del sistema.</p>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Notificaciones de Prueba</h6>
                                                    <div class="btn-group-vertical d-block">
                                                        <button type="button" class="btn btn-outline-info btn-sm mb-1" onclick="sendTestNotification('low_inventory')">
                                                            📦 Inventario Bajo
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning btn-sm mb-1" onclick="sendTestNotification('payment_reminder')">
                                                            💰 Recordatorio de Pago
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success btn-sm mb-1" onclick="sendTestNotification('daily_report')">
                                                            📊 Reporte Diario
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="sendTestNotification('new_lot')">
                                                            🚚 Nuevo Lote
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <h6>Gestión del Sistema</h6>
                                                    <div class="btn-group-vertical d-block">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="refreshPushStats()">
                                                            <i class="fas fa-sync"></i> Actualizar Estadísticas
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm mb-1" onclick="cleanupInactiveSubscriptions()">
                                                            <i class="fas fa-broom"></i> Limpiar Subscripciones Inactivas
                                                        </button>
                                                        <button type="button" class="btn btn-outline-dark btn-sm mb-1" onclick="exportPushStats()">
                                                            <i class="fas fa-download"></i> Exportar Estadísticas
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Quality Modal -->
    <div class="modal fade" id="qualityModal" tabindex="-1" aria-labelledby="qualityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="qualityModalTitle">Nueva Calidad</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="qualityForm" data-mode="create">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quality_name">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="quality_name" class="form-control"
                                           placeholder="Ej: Primera, Segunda" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Orden de Presentación</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control"
                                           placeholder="0" min="0">
                                    <small class="text-muted">Orden en que aparece en las listas</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rango de Calibre</label>
                                    <div class="input-group">
                                        <input type="number" name="caliber_min" id="caliber_min" class="form-control"
                                               placeholder="Min" min="1">
                                        <div class="input-group-prepend input-group-append">
                                            <span class="input-group-text">-</span>
                                        </div>
                                        <input type="number" name="caliber_max" id="caliber_max" class="form-control"
                                               placeholder="Max" min="1">
                                    </div>
                                    <small class="text-muted">Rango de calibre para esta calidad</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rango de Peso (gramos)</label>
                                    <div class="input-group">
                                        <input type="number" name="weight_min" id="weight_min" class="form-control"
                                               placeholder="Min" min="1">
                                        <div class="input-group-prepend input-group-append">
                                            <span class="input-group-text">-</span>
                                        </div>
                                        <input type="number" name="weight_max" id="weight_max" class="form-control"
                                               placeholder="Max" min="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">g</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Rango de peso en gramos</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description">Descripción</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"
                                              placeholder="Descripción adicional de la calidad (opcional)"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" name="color" id="color" class="form-control form-control-color"
                                               value="#6c757d" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-palette"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Color para identificar visualmente esta calidad</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vista Previa del Color</label>
                                    <div class="d-flex align-items-center">
                                        <div id="colorPreview" class="color-preview me-3"
                                             style="width: 40px; height: 40px; background-color: #6c757d; border-radius: 8px; border: 2px solid #ddd;"></div>
                                        <span id="colorValue" class="text-muted">#6c757d</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="activeRow" style="display: none;">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="active" name="active"
                                               checked>
                                        <label class="custom-control-label" for="active">Calidad Activa</label>
                                    </div>
                                    <small class="text-muted">Las calidades inactivas no aparecen en los
                                        formularios</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let qualityTable;

        $(document).ready(function () {
            // Wait a bit for all scripts to load
            setTimeout(() => {
                initializeQualityTable();
            }, 200);

            // Color picker event handler
            $('#color').on('input change', function() {
                updateColorPreview($(this).val());
            });
        });

        function initializeQualityTable() {
            // Check if table exists
            if (!$('#qualityTable').length) {
                console.warn('Quality table not found');
                return;
            }

            // Initialize DataTable
            if (typeof $.fn.DataTable !== 'undefined') {
                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#qualityTable')) {
                    $('#qualityTable').DataTable().destroy();
                }

                // Initialize new DataTable
                qualityTable = $('#qualityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("configuration.qualities.table") }}',
                        data: function(d) {
                            d.ajax = 1;
                        }
                    },
                    columns: [
                        { data: 'sort_order', name: 'sort_order', width: '80px' },
                        { data: 'name', name: 'name' },
                        { data: 'color', name: 'color', orderable: false },
                        { data: 'caliber', name: 'caliber', orderable: false },
                        { data: 'weight', name: 'weight', orderable: false },
                        { data: 'status', name: 'active', orderable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'asc']]
                });
            }
        }

        function loadQualityTable() {
            if (qualityTable) {
                qualityTable.ajax.reload();
            }
        }

        function updateColorPreview(color) {
            $('#colorPreview').css('background-color', color);
            $('#colorValue').text(color);
        }

        function openCreateQualityModal() {
            $('#qualityModalTitle').text('Nueva Calidad');
            $('#qualityForm')[0].reset();
            $('#qualityForm').attr('data-mode', 'create');
            $('#qualityForm').attr('data-id', '');
            $('#activeRow').hide();

            // Reset color to default
            $('#color').val('#6c757d');
            updateColorPreview('#6c757d');

            $('#qualityModal').modal('show');
        }

        function editQuality(id) {
            $('#qualityModalTitle').text('Editar Calidad');
            $('#qualityForm').attr('data-mode', 'edit');
            $('#qualityForm').attr('data-id', id);
            $('#activeRow').show();

            // Load quality data
            fetch(`{{ url('configuration/quality') }}/${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    $('#quality_name').val(data.name);
                    $('#caliber_min').val(data.caliber_min);
                    $('#caliber_max').val(data.caliber_max);
                    $('#weight_min').val(data.weight_min);
                    $('#weight_max').val(data.weight_max);
                    $('#description').val(data.description);
                    $('#sort_order').val(data.sort_order);
                    $('#color').val(data.color || '#6c757d');
                    $('#active').prop('checked', data.active);

                    // Update color preview
                    updateColorPreview(data.color || '#6c757d');

                    $('#qualityModal').modal('show');
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Error al cargar los datos de la calidad');
                });
        }

        function deleteQuality(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar la calidad "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('configuration/quality') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success('Calidad eliminada correctamente');
                                loadQualityTable();
                            } else {
                                toastr.error(data.message || 'Error al eliminar la calidad');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toastr.error('Error al eliminar la calidad');
                        });
                }
            });
        }

        // Form submission
        $('#qualityForm').submit(function (e) {
            e.preventDefault();

            const mode = $(this).attr('data-mode');
            const id = $(this).attr('data-id');
            const formData = new FormData(this);

            let url = '{{ route("configuration.quality.store") }}';

            if (mode === 'edit') {
                url = `{{ url('configuration/quality') }}/${id}`;
                formData.append('_method', 'PUT');
            }

            // Handle checkbox value
            if (!$('#active').is(':checked')) {
                formData.set('active', '0');
            } else {
                formData.set('active', '1');
            }

            // Debug: Log the color value being sent
            console.log('Color value being sent:', formData.get('color'));

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(mode === 'create' ? 'Calidad creada correctamente' : 'Calidad actualizada correctamente');
                        $('#qualityModal').modal('hide');
                        loadQualityTable();
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                toastr.error(data.errors[key][0]);
                            });
                        } else {
                            toastr.error(data.message || 'Error al guardar la calidad');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    if (error.errors) {
                        Object.keys(error.errors).forEach(key => {
                            console.error(`Error en campo ${key}:`, error.errors[key]);
                            toastr.error(`${key}: ${error.errors[key][0]}`);
                        });
                    } else {
                        toastr.error(error.message || 'Error al guardar la calidad');
                    }
                });
        });

        // Company configuration functions
        function loadCompanyConfig() {
            fetch('{{ route("configuration.company.get") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const config = data.config || {};
                    $('#company_name').val(config.company_name || 'AvoControl-Pro');
                    $('#company_rfc').val(config.company_rfc || '');
                    $('#company_address').val(config.company_address || '');
                    $('#company_city').val(config.company_city || 'Uruapan');
                    $('#company_state').val(config.company_state || 'Michoacán');
                    $('#company_postal_code').val(config.company_postal_code || '');
                    $('#company_phone').val(config.company_phone || '');
                    $('#company_email').val(config.company_email || '');
                    $('#company_website').val(config.company_website || '');
                    $('#company_logo').val(config.company_logo || '');
                    $('#company_description').val(config.company_description || '');
                } else {
                    toastr.error('Error al cargar la configuración de la empresa');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al cargar la configuración de la empresa');
            });
        }

        // Company form submission
        $('#companyForm').submit(function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("configuration.company.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Configuración de empresa guardada correctamente');
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            toastr.error(data.errors[key][0]);
                        });
                    } else {
                        toastr.error(data.message || 'Error al guardar la configuración');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al guardar la configuración de la empresa');
            });
        });

        // Load company config when company tab is shown
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') === '#company') {
                loadCompanyConfig();
            } else if ($(e.target).attr('href') === '#email') {
                loadEmailConfig();
            } else if ($(e.target).attr('href') === '#push') {
                loadPushNotificationConfig();
            }
        });

        // Email configuration functions
        function loadEmailConfig() {
            fetch('{{ route("configuration.email.get") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const config = data.config || {};
                    $('#mail_mailer').val(config.mail_mailer || 'smtp');
                    $('#mail_host').val(config.mail_host || '');
                    $('#mail_port').val(config.mail_port || '587');
                    $('#mail_encryption').val(config.mail_encryption || 'tls');
                    $('#mail_username').val(config.mail_username || '');
                    $('#mail_password').val(config.mail_password || '');
                    $('#mail_from_address').val(config.mail_from_address || 'noreply@avocontrol.pro');
                    $('#mail_from_name').val(config.mail_from_name || 'AvoControl Pro');
                    $('#notification_email_enabled').val(config.notification_email_enabled || '1');
                    
                    // Update fields based on provider
                    updateEmailProvider();
                } else {
                    toastr.error('Error al cargar la configuración de email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al cargar la configuración de email');
            });
        }

        // Update email fields based on selected provider
        function updateEmailProvider() {
            const provider = $('#mail_mailer').val();
            
            switch (provider) {
                case 'gmail':
                    $('#mail_host').val('smtp.gmail.com');
                    $('#mail_port').val('587');
                    $('#mail_encryption').val('tls');
                    break;
                case 'sendgrid':
                    $('#mail_host').val('smtp.sendgrid.net');
                    $('#mail_port').val('587');
                    $('#mail_encryption').val('tls');
                    break;
                case 'mailgun':
                    $('#mail_host').val('smtp.mailgun.org');
                    $('#mail_port').val('587');
                    $('#mail_encryption').val('tls');
                    break;
            }
        }

        // Email provider change handler
        $('#mail_mailer').change(function() {
            updateEmailProvider();
        });

        // Email form submission
        $('#emailForm').submit(function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("configuration.email.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Configuración de email guardada correctamente');
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            toastr.error(data.errors[key][0]);
                        });
                    } else {
                        toastr.error(data.message || 'Error al guardar la configuración');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al guardar la configuración de email');
            });
        });

        // Test email configuration
        function testEmailConfig() {
            const testEmail = prompt('Ingresa el email de destino para la prueba:', $('#mail_from_address').val() || 'admin@avocontrol.com');
            
            if (!testEmail || !validateEmail(testEmail)) {
                toastr.error('Por favor ingresa un email válido');
                return;
            }

            const formData = new FormData();
            formData.append('test_email', testEmail);

            fetch('{{ route("configuration.email.test") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message || 'Email de prueba enviado correctamente');
                } else {
                    toastr.error(data.message || 'Error al enviar email de prueba');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al enviar email de prueba');
            });
        }

        // Email validation function
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Push Notification Functions
        function loadPushNotificationConfig() {
            // Load push notification stats
            loadPushStats();
            
            // Load user subscriptions
            loadUserSubscriptions();
            
            // Check push notification support and status
            checkPushSupport();
        }

        function loadPushStats() {
            fetch('/push/stats', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    $('#total-subscriptions').text(stats.total_subscriptions || 0);
                    $('#active-subscriptions').text(stats.active_subscriptions || 0);
                    $('#users-with-subscriptions').text(stats.users_with_subscriptions || 0);
                    $('#recent-subscriptions').text(stats.recent_subscriptions || 0);
                    
                    $('#push-stats-loading').hide();
                    $('#push-stats').show();
                } else {
                    $('#push-stats-loading').html('<i class="fas fa-exclamation-triangle text-warning"></i> Error al cargar estadísticas');
                }
            })
            .catch(error => {
                console.error('Error loading push stats:', error);
                $('#push-stats-loading').html('<i class="fas fa-exclamation-triangle text-danger"></i> Error de conexión');
            });
        }

        function loadUserSubscriptions() {
            fetch('/push/status', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const subscriptions = data.subscriptions || [];
                    const tbody = $('#user-subscriptions-tbody');
                    tbody.empty();
                    
                    if (subscriptions.length === 0) {
                        tbody.append(`
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="fas fa-mobile-alt"></i> No tienes dispositivos suscritos
                                </td>
                            </tr>
                        `);
                    } else {
                        subscriptions.forEach(sub => {
                            tbody.append(`
                                <tr>
                                    <td>
                                        <i class="fas fa-globe"></i> ${sub.browser}
                                    </td>
                                    <td>${sub.ip_address}</td>
                                    <td>${sub.created_at}</td>
                                    <td>${sub.last_used_at || 'Nunca'}</td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Activo
                                        </span>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                } else {
                    $('#user-subscriptions-tbody').html(`
                        <tr>
                            <td colspan="5" class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle"></i> Error al cargar subscripciones
                            </td>
                        </tr>
                    `);
                }
            })
            .catch(error => {
                console.error('Error loading user subscriptions:', error);
                $('#user-subscriptions-tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error de conexión
                        </td>
                    </tr>
                `);
            });
        }

        function checkPushSupport() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                $('#browser-unsupported').show();
                $('#push-status').html('<span class="badge badge-danger">❌ No Soportado</span>');
                return;
            }
            
            // Check permission status
            if (Notification.permission === 'denied') {
                $('#push-status').html('<span class="badge badge-danger">🚫 Permisos Denegados</span>');
            } else if (window.avoControlPush && window.avoControlPush.isSubscribed) {
                $('#push-status').html('<span class="badge badge-success">✅ Notificaciones Activas</span>');
            } else {
                $('#push-status').html('<span class="badge badge-secondary">⭕ Notificaciones Inactivas</span>');
            }
        }

        function refreshPushStats() {
            $('#push-stats').hide();
            $('#push-stats-loading').show().html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
            
            setTimeout(() => {
                loadPushStats();
                loadUserSubscriptions();
                toastr.success('Estadísticas actualizadas');
            }, 1000);
        }

        function sendTestNotification(type) {
            const messages = {
                'low_inventory': '⚠️ Hay productos con inventario crítico que requieren atención inmediata',
                'payment_reminder': '💰 Tienes pagos pendientes que requieren tu atención', 
                'daily_report': '📊 Tu reporte diario de operaciones está listo para revisar',
                'new_lot': '🚚 Se ha registrado un nuevo lote de aguacates en el sistema'
            };

            fetch('/push/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: messages[type] || 'Notificación de prueba desde AvoControl Pro',
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Notificación de prueba enviada correctamente');
                } else {
                    toastr.error(data.message || 'Error al enviar notificación de prueba');
                }
            })
            .catch(error => {
                console.error('Error sending test notification:', error);
                toastr.error('Error al enviar notificación de prueba');
            });
        }

        function cleanupInactiveSubscriptions() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción eliminará todas las subscripciones inactivas permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/push/cleanup', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success(`Se eliminaron ${data.deleted_count} subscripciones inactivas`);
                            refreshPushStats();
                        } else {
                            toastr.error(data.message || 'Error al limpiar subscripciones');
                        }
                    })
                    .catch(error => {
                        console.error('Error cleaning subscriptions:', error);
                        toastr.error('Error al limpiar subscripciones');
                    });
                }
            });
        }

        function exportPushStats() {
            const stats = {
                total_subscriptions: $('#total-subscriptions').text(),
                active_subscriptions: $('#active-subscriptions').text(),
                users_with_subscriptions: $('#users-with-subscriptions').text(),
                recent_subscriptions: $('#recent-subscriptions').text(),
                generated_at: new Date().toISOString()
            };
            
            const blob = new Blob([JSON.stringify(stats, null, 2)], {type: 'application/json'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `push-notifications-stats-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            toastr.success('Estadísticas exportadas correctamente');
        }
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
@endpush
