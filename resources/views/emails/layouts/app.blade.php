<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Notificación AvoControl Pro' }}</title>
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333333;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f8f9fa;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header styles */
        .email-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .email-header .subtitle {
            margin: 5px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        /* Logo styles */
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* Content styles */
        .email-content {
            padding: 40px;
        }

        .email-content h2 {
            color: #28a745;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }

        .email-content h3 {
            color: #495057;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }

        .email-content p {
            margin-bottom: 16px;
            color: #555555;
        }

        /* Alert styles */
        .alert {
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 6px;
            border-left: 4px solid;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .btn-primary:hover {
            background-color: #218838;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        /* Table styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        /* Stats box styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .stat-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            text-align: center;
            border-radius: 0 6px 6px 0;
        }

        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            display: block;
        }

        .stat-box .label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* Footer styles */
        .email-footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 30px 40px;
            text-align: center;
        }

        .email-footer p {
            margin: 0;
            font-size: 13px;
            opacity: 0.8;
        }

        .email-footer a {
            color: #20c997;
            text-decoration: none;
        }

        .email-footer a:hover {
            color: #17a085;
        }

        /* Responsive design */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0 10px;
                max-width: calc(100% - 20px);
            }

            .email-header,
            .email-content,
            .email-footer {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px;
            }
        }

        /* Priority indicators */
        .priority-critical {
            color: #dc3545;
            font-weight: bold;
        }

        .priority-high {
            color: #fd7e14;
            font-weight: bold;
        }

        .priority-normal {
            color: #0066cc;
        }

        .priority-low {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo">
                    🥑
                </div>
                <h1>{{ config('app.name', 'AvoControl Pro') }}</h1>
                <p class="subtitle">Sistema de Gestión de Centros de Acopio</p>
            </div>

            <!-- Content -->
            <div class="email-content">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p><strong>{{ config('app.name', 'AvoControl Pro') }}</strong></p>
                <p>Sistema profesional de gestión de aguacate para Centros de Acopio</p>
                <p style="margin-top: 15px;">
                    Este email fue generado automáticamente por el sistema.<br>
                    Para soporte técnico, contacte al administrador.
                </p>
                <p style="margin-top: 15px; font-size: 11px;">
                    Desarrollado por <a href="https://about.me/danielriveraayala">Daniel Rivera</a> - Kreativos Pro<br>
                    🤖 Implementado con <a href="https://claude.ai/code">Claude Code</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>