<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Acopio - AvoControl Pro</title>
    <style>
        @page {
            margin: 0cm;
            size: A4;
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-family: Arial, sans-serif;
                font-size: 8px;
                color: #666;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            background: white;
            margin: 2.5cm 2.5cm 2.5cm 2.5cm;
        }

        /* ============== HEADER ============== */
        .company-header {
            text-align: center;
            padding: 8px 0;
            border-bottom: 2px solid #2d3748;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 3px;
        }

        .company-subtitle {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .generation-info {
            font-size: 9px;
            color: #4a5568;
        }

        /* ============== TITLE SECTION ============== */
        .report-title {
            text-align: center;
            padding: 10px 8px;
            margin-bottom: 10px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
        }

        .report-title h1 {
            font-size: 16px;
            font-weight: bold;
            color: #000000; /* NEGRO COMO SOLICITÓ */
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .period-info {
            font-size: 10px;
            color: #4a5568;
            margin-bottom: 5px;
        }

        /* ============== METRICS GRID ============== */
        .metrics-container {
            margin-bottom: 15px;
        }

        .metrics-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .metric-item {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            background: white;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .metric-item:first-child {
            border-left: 4px solid #3182ce;
        }

        .metric-item:nth-child(2) {
            border-left: 4px solid #38a169;
        }

        .metric-item:nth-child(3) {
            border-left: 4px solid #d69e2e;
        }

        .metric-item:last-child {
            border-left: 4px solid #e53e3e;
        }

        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
            display: block;
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 8px;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* ============== SECTION HEADERS ============== */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-header {
            background: #2d3748;
            color: white;
            padding: 12px 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
        }

        .section-subtitle {
            background: #f7fafc;
            padding: 5px 15px;
            font-size: 9px;
            color: #4a5568;
            font-style: italic;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 8px;
        }

        /* ============== TABLES ============== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: white;
            border: 1px solid #e2e8f0;
        }

        .data-table thead {
            background: #4a5568;
        }

        .data-table th {
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border-right: 1px solid #718096;
        }

        .data-table th:last-child {
            border-right: none;
        }

        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            font-size: 9px;
        }

        .data-table td:last-child {
            border-right: none;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .data-table tfoot {
            background: #2d3748;
            color: white;
            font-weight: bold;
        }

        .data-table tfoot td {
            padding: 10px 8px;
            border-color: #4a5568;
        }

        /* ============== QUALITY TAGS ============== */
        .quality-tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: white;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .status-positive {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-negative {
            background: #fed7d7;
            color: #822727;
        }

        /* ============== INSIGHTS BOX ============== */
        .insights-box {
            background: #ebf8ff;
            border: 1px solid #bee3f8;
            border-left: 4px solid #3182ce;
            padding: 10px;
            margin: 12px 0;
        }

        .insights-title {
            font-weight: bold;
            color: #1a365d;
            font-size: 10px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .insights-content {
            font-size: 9px;
            color: #2d3748;
            line-height: 1.5;
        }

        /* ============== UTILITIES ============== */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .page-break { page-break-before: always; }

        /* ============== FOOTER ============== */
        .document-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #718096;
        }
    </style>
</head>
<body>
    <!-- ============== COMPANY HEADER ============== -->
    <div class="company-header">
        <div class="company-name">AvoControl Pro</div>
        <div class="company-subtitle">Sistema de Gestión Empresarial de Aguacates</div>
        <div class="generation-info">
            Generado: {{ now()->format('d/m/Y H:i:s') }} | Uruapan, Michoacán
        </div>
    </div>

    <!-- ============== REPORT TITLE ============== -->
    <div class="report-title">
        <h1>Reporte Ejecutivo de Acopio y Análisis de Operaciones</h1>
        <div class="period-info">
            <strong>Período de Análisis:</strong>
            {{ \Carbon\Carbon::parse($reporte['periodo']['inicio'])->format('d/m/Y') }} -
            {{ \Carbon\Carbon::parse($reporte['periodo']['fin'])->format('d/m/Y') }}
            ({{ \Carbon\Carbon::parse($reporte['periodo']['inicio'])->diffInDays(\Carbon\Carbon::parse($reporte['periodo']['fin'])) + 1 }} días)
        </div>
    </div>

    <!-- ============== EXECUTIVE METRICS ============== -->
    @php
        $totalLotes = $reporte['resumen']->sum('lotes_ingresados');
        $totalPeso = $reporte['resumen']->sum('peso_ingresado');
        $totalInversion = $reporte['resumen']->sum('inversion_total');

        $totalIngresoVentas = 0;
        $totalCostoVendido = 0;

        if (is_array($reporte['ventas']) && count($reporte['ventas']) > 0) {
            foreach ($reporte['ventas'] as $calidad => $ventasCalidad) {
                $ingresoVentas = $ventasCalidad->sum(function($item) {
                    return $item->allocated_weight * $item->saleItem->price_per_kg;
                });
                $costoVendido = $ventasCalidad->sum(function($item) {
                    return $item->allocated_weight * ($item->lot->purchase_price_per_kg ?? 0);
                });

                $totalIngresoVentas += $ingresoVentas;
                $totalCostoVendido += $costoVendido;
            }
        }

        $margenTotal = $totalIngresoVentas - $totalCostoVendido;
        $porcentajeMargen = $totalIngresoVentas > 0 ? ($margenTotal / $totalIngresoVentas) * 100 : 0;
    @endphp

    <div class="metrics-container">
        <div class="metrics-row">
            <div class="metric-item">
                <span class="metric-value">{{ number_format($totalLotes) }}</span>
                <span class="metric-label">Lotes Procesados</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">{{ number_format($totalPeso, 0) }} kg</span>
                <span class="metric-label">Peso Total</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">${{ number_format($totalInversion, 0) }}</span>
                <span class="metric-label">Inversión Total</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">{{ number_format($porcentajeMargen, 1) }}%</span>
                <span class="metric-label">Margen de Ganancia</span>
            </div>
        </div>
    </div>

    <!-- ============== SUMMARY BOX ============== -->
    <div class="insights-box">
        <div class="insights-title">Resumen Ejecutivo</div>
        <div class="insights-content">
            <strong>Ingresos por Ventas:</strong> ${{ number_format($totalIngresoVentas, 0) }} •
            <strong>Margen Bruto:</strong> ${{ number_format($margenTotal, 0) }} •
            <strong>ROI:</strong> {{ number_format($porcentajeMargen, 1) }}%
        </div>
    </div>

    <!-- ============== INGRESOS SECTION ============== -->
    <div class="section">
        <div class="section-header">Análisis de Ingresos por Calidad</div>
        <div class="section-subtitle">Distribución de inversiones, lotes y participación por tipo de aguacate</div>

        @if($reporte['resumen']->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Calidad</th>
                    <th class="text-center">Lotes</th>
                    <th class="text-right">Peso (kg)</th>
                    <th class="text-right">Inversión Total</th>
                    <th class="text-right">Precio/kg</th>
                    <th class="text-center">Participación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['resumen'] as $resumen)
                    @php
                        $qualityName = $resumen->qualityGrade ? $resumen->qualityGrade->name : 'Sin calidad';
                        $qualityColor = $resumen->qualityGrade ? $resumen->qualityGrade->color : '#6c757d';
                    @endphp
                    <tr>
                        <td>
                            <span class="quality-tag" style="background-color: {{ $qualityColor }};">
                                {{ $qualityName }}
                            </span>
                        </td>
                        <td class="text-center">{{ number_format($resumen->lotes_ingresados) }}</td>
                        <td class="text-right">{{ number_format($resumen->peso_ingresado, 0) }}</td>
                        <td class="text-right">${{ number_format($resumen->inversion_total, 0) }}</td>
                        <td class="text-right">${{ number_format($resumen->inversion_total / $resumen->peso_ingresado, 2) }}</td>
                        <td class="text-center">
                            <span class="status-badge status-positive">
                                {{ number_format(($resumen->peso_ingresado / $totalPeso) * 100, 1) }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-center"><strong>{{ number_format($totalLotes) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalPeso, 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totalInversion, 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totalInversion / $totalPeso, 2) }}</strong></td>
                    <td class="text-center"><strong>100%</strong></td>
                </tr>
            </tfoot>
        </table>

        @php
            $calidadPrincipal = $reporte['resumen']->sortByDesc('peso_ingresado')->first();
            $calidadPrincipalNombre = $calidadPrincipal->qualityGrade ? $calidadPrincipal->qualityGrade->name : 'Sin calidad';
            $participacionPrincipal = round(($calidadPrincipal->peso_ingresado / $totalPeso) * 100, 1);
        @endphp

        <div class="insights-box">
            <div class="insights-title">Análisis de Distribución</div>
            <div class="insights-content">
                <strong> {{ $calidadPrincipalNombre }} </strong> representa la mayor participación con {{ $participacionPrincipal }}% del peso total ingresado ({{ number_format($calidadPrincipal->peso_ingresado, 0) }} kg).
                La inversión promedio por kilogramo es de <strong> ${{ number_format($totalInversion / $totalPeso, 2) }} </strong>.
                Se procesaron {{ number_format($totalLotes) }} lotes en total durante el período analizado.
            </div>
        </div>
        @endif
    </div>

    <!-- ============== PAGE BREAK ============== -->
    <div class="page-break"></div>

    <!-- ============== VENTAS SECTION ============== -->
    <div class="section">
        <div class="section-header">Análisis de Ventas y Rentabilidad</div>
        <div class="section-subtitle">Rendimiento comercial, márgenes y análisis de ROI por calidad</div>

        @if(is_array($reporte['ventas']) && count($reporte['ventas']) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Calidad</th>
                    <th class="text-center">Ventas</th>
                    <th class="text-right">Peso Vendido (kg)</th>
                    <th class="text-right">Ingresos Totales</th>
                    <th class="text-right">Precio/kg</th>
                    <th class="text-right">Margen Bruto</th>
                    <th class="text-center">ROI %</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalVentas = 0;
                    $totalPesoVendido = 0;
                    $mejorRoi = 0;
                    $mejorCalidad = '';
                @endphp
                @foreach($reporte['ventas'] as $calidad => $ventasCalidad)
                    @php
                        $pesoVendido = $ventasCalidad->sum('allocated_weight');
                        $ingresoVentas = $ventasCalidad->sum(function($item) {
                            return $item->allocated_weight * $item->saleItem->price_per_kg;
                        });
                        $costoVendido = $ventasCalidad->sum(function($item) {
                            return $item->allocated_weight * ($item->lot->purchase_price_per_kg ?? 0);
                        });
                        $margenBruto = $ingresoVentas - $costoVendido;
                        $precioPromedio = $pesoVendido > 0 ? $ingresoVentas / $pesoVendido : 0;

                        $ventasUnicas = $ventasCalidad->groupBy(function($item) {
                            return $item->saleItem->sale_id;
                        })->count();

                        $totalVentas += $ventasUnicas;
                        $totalPesoVendido += $pesoVendido;

                        $qualityColor = $qualityColors[$calidad] ?? '#6c757d';
                        $roi = $ingresoVentas > 0 ? ($margenBruto / $ingresoVentas) * 100 : 0;

                        if ($roi > $mejorRoi) {
                            $mejorRoi = $roi;
                            $mejorCalidad = $calidad;
                        }
                    @endphp
                    <tr>
                        <td>
                            <span class="quality-tag" style="background-color: {{ $qualityColor }};">
                                {{ $calidad }}
                            </span>
                        </td>
                        <td class="text-center">{{ $ventasUnicas }}</td>
                        <td class="text-right">{{ number_format($pesoVendido, 0) }}</td>
                        <td class="text-right">${{ number_format($ingresoVentas, 0) }}</td>
                        <td class="text-right">${{ number_format($precioPromedio, 2) }}</td>
                        <td class="text-right">
                            @if($margenBruto >= 0)
                                <span class="status-badge status-positive">
                                    +${{ number_format($margenBruto, 0) }}
                                </span>
                            @else
                                <span class="status-badge status-negative">
                                    -${{ number_format(abs($margenBruto), 0) }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="status-badge {{ $roi >= 15 ? 'status-positive' : 'status-negative' }}">
                                {{ number_format($roi, 1) }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-center"><strong>{{ $totalVentas }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalPesoVendido, 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totalIngresoVentas, 0) }}</strong></td>
                    <td class="text-right">
                        <strong>${{ $totalPesoVendido > 0 ? number_format($totalIngresoVentas / $totalPesoVendido, 2) : '0.00' }}</strong>
                    </td>
                    <td class="text-right">
                        <strong>
                            @if($margenTotal >= 0)
                                +${{ number_format($margenTotal, 0) }}
                            @else
                                -${{ number_format(abs($margenTotal), 0) }}
                            @endif
                        </strong>
                    </td>
                    <td class="text-center"><strong>{{ number_format($porcentajeMargen, 1) }}%</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="insights-box">
            <div class="insights-title">Análisis de Rentabilidad</div>
            <div class="insights-content">
                @if($mejorCalidad)
                    <strong>{{ $mejorCalidad }}</strong> presenta el mejor ROI con {{ number_format($mejorRoi, 1) }}% de rentabilidad.
                @endif
                El margen bruto total es de <strong> ${{ number_format($margenTotal, 0) }} </strong> con un ROI promedio del {{ number_format($porcentajeMargen, 1) }}%.
                Se vendieron {{ number_format($totalPesoVendido, 0) }} kg distribuidos en {{ $totalVentas }} transacciones.
            </div>
        </div>
        @else
        <div class="insights-box">
            <div class="insights-title">Sin Datos de Ventas</div>
            <div class="insights-content">
                No se registraron ventas en el período seleccionado para análisis.
            </div>
        </div>
        @endif
    </div>

    <!-- ============== VENTAS MENSUALES SECTION ============== -->
    @if(isset($reporte['ventas_mensuales']) && count($reporte['ventas_mensuales']) > 0)
    <div class="page-break"></div>
    <div class="section">
        <div class="section-header">Análisis Temporal - Ventas {{ date('Y') }}</div>
        <div class="section-subtitle">Evolución mensual de ventas por calidad durante el año fiscal</div>

        @php
            $totalVentasAnio = 0;
            foreach ($reporte['ventas_mensuales'] as $mes => $calidades) {
                $totalVentasAnio += array_sum($calidades->toArray());
            }
        @endphp

        <div class="insights-box">
            <div class="insights-title">Resumen Anual {{ date('Y') }}</div>
            <div class="insights-content">
                <strong>Total de Ventas:</strong> ${{ number_format($totalVentasAnio, 0) }} •
                <strong>Promedio Mensual:</strong> ${{ number_format($totalVentasAnio / max(1, count($reporte['ventas_mensuales'])), 0) }}
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Calidad</th>
                    @php
                        $mesesLabels = [];
                        $startDate = \Carbon\Carbon::parse('2025-01-01');
                        $endDate = now();

                        $current = $startDate->copy();
                        while ($current->lte($endDate)) {
                            $mesesLabels[] = $current->format('M');
                            $current->addMonth();
                        }
                    @endphp
                    @foreach($mesesLabels as $mes)
                        <th class="text-right">{{ $mes }}</th>
                    @endforeach
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $calidades = \App\Models\QualityGrade::where('active', true)->orderBy('name')->get();
                    $totalesMensuales = [];
                @endphp
                @foreach($calidades as $calidad)
                    @php
                        $totalCalidad = 0;
                        $mesesKeys = [];
                        $startDate = \Carbon\Carbon::parse('2025-01-01');
                        $endDate = now();

                        $current = $startDate->copy();
                        while ($current->lte($endDate)) {
                            $mesesKeys[] = $current->format('Y-m');
                            $current->addMonth();
                        }
                    @endphp
                    <tr>
                        <td>
                            <span class="quality-tag" style="background-color: {{ $calidad->color ?: '#6c757d' }};">
                                {{ $calidad->name }}
                            </span>
                        </td>
                        @foreach($mesesKeys as $mesKey)
                            @php
                                $ventaMes = $reporte['ventas_mensuales'][$mesKey][$calidad->name] ?? 0;
                                $totalCalidad += $ventaMes;

                                if (!isset($totalesMensuales[$mesKey])) {
                                    $totalesMensuales[$mesKey] = 0;
                                }
                                $totalesMensuales[$mesKey] += $ventaMes;
                            @endphp
                            <td class="text-right">
                                @if($ventaMes > 0)
                                    ${{ number_format($ventaMes, 0) }}
                                @else
                                    <span style="color: #cbd5e0;">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-right"><strong>${{ number_format($totalCalidad, 0) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL MENSUAL</strong></td>
                    @foreach($mesesKeys as $mesKey)
                        @php
                            $totalMes = $totalesMensuales[$mesKey] ?? 0;
                        @endphp
                        <td class="text-right"><strong>${{ number_format($totalMes, 0) }}</strong></td>
                    @endforeach
                    <td class="text-right"><strong>${{ number_format($totalVentasAnio, 0) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        @php
            $mejorMesGeneral = '';
            $mejorVentaMesGeneral = 0;
            foreach ($totalesMensuales as $mesKey => $total) {
                if ($total > $mejorVentaMesGeneral) {
                    $mejorVentaMesGeneral = $total;
                    $mejorMesGeneral = \Carbon\Carbon::parse($mesKey . '-01')->format('F');
                }
            }
        @endphp

        <div class="insights-box">
            <div class="insights-title">Análisis de Tendencias</div>
            <div class="insights-content">
                @if($mejorMesGeneral)
                    <strong>{{ $mejorMesGeneral }}</strong> fue el mes con mejores ventas totalizando ${{ number_format($mejorVentaMesGeneral, 0) }}.
                @endif
                Se registraron ventas en {{ count($totalesMensuales) }} meses del año actual.
            </div>
        </div>
    </div>
    @endif

    <!-- ============== FOOTER ============== -->
    <div class="document-footer">
        <strong>AvoControl Pro</strong> - Sistema de Gestión Empresarial<br>
        Uruapan, Michoacán • {{ now()->format('d/m/Y H:i:s') }}<br>
        © {{ date('Y') }} Todos los derechos reservados
    </div>
</body>
</html>
