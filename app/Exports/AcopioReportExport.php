<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class AcopioReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $reporte;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($reporte, $fechaInicio, $fechaFin)
    {
        $this->reporte = $reporte;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        $data = collect();

        // HEADER SECTION
        $data->push(['REPORTE DE ACOPIO Y ANÁLISIS', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['AvoControl Pro - Sistema de Gestión de Aguacates', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['Período: ' . \Carbon\Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($this->fechaFin)->format('d/m/Y'), '', '', '', '', '', '', '', 'Generado:', now()->format('d/m/Y H:i:s'), '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']); // Empty row

        // EXECUTIVE SUMMARY
        $totalLotes = $this->reporte['resumen']->sum('lotes_ingresados');
        $totalPeso = $this->reporte['resumen']->sum('peso_ingresado');
        $totalInversion = $this->reporte['resumen']->sum('inversion_total');

        // Calculate sales metrics
        $totalVentas = 0;
        $totalIngresoVentas = 0;
        $totalCostoVendido = 0;

        if (is_array($this->reporte['ventas']) && count($this->reporte['ventas']) > 0) {
            foreach ($this->reporte['ventas'] as $calidad => $ventasCalidad) {
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

        // DASHBOARD METRICS
        $data->push(['📊 RESUMEN EJECUTIVO', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['MÉTRICA', 'VALOR', '', 'MÉTRICA', 'VALOR', '', 'MÉTRICA', 'VALOR', '', '', '', '']);
        $data->push(['Total Lotes', number_format($totalLotes), '', 'Peso Total (kg)', number_format($totalPeso, 2), '', 'Inversión Total', '$' . number_format($totalInversion, 2), '', '', '', '']);
        $data->push(['Ingresos Ventas', '$' . number_format($totalIngresoVentas, 2), '', 'Margen Bruto', '$' . number_format($margenTotal, 2), '', 'Margen %', number_format($porcentajeMargen, 1) . '%', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']); // Empty row

        // INGRESOS POR CALIDAD SECTION
        $data->push(['🌱 INGRESOS POR CALIDAD', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['Calidad', 'Lotes', '% Lotes', 'Peso (kg)', '% Peso', 'Inversión', '% Inversión', 'Precio/kg', '', '', '', '']);

        foreach($this->reporte['resumen'] as $resumen) {
            $qualityName = $resumen->qualityGrade ? $resumen->qualityGrade->name : 'Sin calidad';
            $data->push([
                $qualityName,
                $resumen->lotes_ingresados, // Sin decimales (columna B)
                number_format(($resumen->lotes_ingresados / $totalLotes) * 100, 1) . '%',
                $resumen->peso_ingresado, // Sin decimales (columna D)
                number_format(($resumen->peso_ingresado / $totalPeso) * 100, 1) . '%',
                '$' . number_format($resumen->inversion_total, 2),
                number_format(($resumen->inversion_total / $totalInversion) * 100, 1) . '%',
                '$' . number_format($resumen->inversion_total / $resumen->peso_ingresado, 2),
                '', '', '', ''
            ]);
        }

        $data->push(['TOTAL', $totalLotes, '100%', $totalPeso, '100%', '$' . number_format($totalInversion, 2), '100%', '$' . number_format($totalInversion / $totalPeso, 2), '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']); // Empty row

        // ANÁLISIS DE VENTAS SECTION
        $data->push(['💰 ANÁLISIS DE VENTAS POR CALIDAD', '', '', '', '', '', '', '', '', '', '', '']);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']);

        if (is_array($this->reporte['ventas']) && count($this->reporte['ventas']) > 0) {
            $data->push(['Calidad', 'Ventas', 'Peso Vendido', 'Ingresos', 'Precio/kg', 'Costo', 'Margen', 'Margen %', '', '', '', '']);

            $totalVentasCount = 0;
            $totalPesoVendido = 0;

            foreach ($this->reporte['ventas'] as $calidad => $ventasCalidad) {
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

                $totalVentasCount += $ventasUnicas;
                $totalPesoVendido += $pesoVendido;

                $data->push([
                    $calidad,
                    $ventasUnicas,
                    $pesoVendido,
                    '$' . number_format($ingresoVentas, 2),
                    '$' . number_format($precioPromedio, 2),
                    '$' . number_format($costoVendido, 2),
                    '$' . number_format($margenBruto, 2),
                    number_format($ingresoVentas > 0 ? ($margenBruto / $ingresoVentas) * 100 : 0, 1) . '%',
                    '', '', '', ''
                ]);
            }

            $data->push([
                'TOTAL',
                $totalVentasCount,
                $totalPesoVendido,
                '$' . number_format($totalIngresoVentas, 2),
                '$' . number_format($totalPesoVendido > 0 ? $totalIngresoVentas / $totalPesoVendido : 0, 2),
                '$' . number_format($totalCostoVendido, 2),
                '$' . number_format($margenTotal, 2),
                number_format($porcentajeMargen, 1) . '%',
                '', '', '', ''
            ]);
        } else {
            $data->push(['No se registraron ventas en el período', '', '', '', '', '', '', '', '', '', '', '']);
        }

        $data->push(['', '', '', '', '', '', '', '', '', '', '', '']); // Empty row

        // VENTAS MENSUALES SECTION
        if (isset($this->reporte['ventas_mensuales']) && count($this->reporte['ventas_mensuales']) > 0) {
            $totalVentasAnio = 0;
            foreach ($this->reporte['ventas_mensuales'] as $mes => $calidades) {
                $totalVentasAnio += array_sum($calidades->toArray());
            }

            $data->push(['📈 VENTAS MENSUALES POR CALIDAD - ' . date('Y'), '', '', '', '', '', '', '', '', '', '', '']);
            $data->push(['Total Ventas del Año: $' . number_format($totalVentasAnio, 2), '', '', '', '', '', '', '', '', '', '', '']);
            $data->push(['', '', '', '', '', '', '', '', '', '', '', '']);

            // Headers for months
            $headerRow = ['Calidad'];
            $mesesKeys = [];
            $startDate = \Carbon\Carbon::parse('2025-01-01');
            $endDate = now();

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $headerRow[] = $current->format('M Y');
                $mesesKeys[] = $current->format('Y-m');
                $current->addMonth();
            }
            $headerRow[] = 'TOTAL';

            // Fill remaining columns
            while (count($headerRow) < 12) {
                $headerRow[] = '';
            }

            $data->push($headerRow);

            // Data rows
            $calidades = \App\Models\QualityGrade::where('active', true)->orderBy('name')->get();
            foreach ($calidades as $calidad) {
                $row = [$calidad->name];
                $totalCalidad = 0;

                foreach ($mesesKeys as $mesKey) {
                    $ventaMes = $this->reporte['ventas_mensuales'][$mesKey][$calidad->name] ?? 0;
                    $totalCalidad += $ventaMes;
                    $row[] = $ventaMes > 0 ? '$' . number_format($ventaMes, 0) : '-';
                }

                $row[] = '$' . number_format($totalCalidad, 2);

                // Fill remaining columns
                while (count($row) < 12) {
                    $row[] = '';
                }

                $data->push($row);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return []; // We handle headers in the collection method
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 12,
            'C' => 12,
            'D' => 15,
            'E' => 12,
            'F' => 15,
            'G' => 12,
            'H' => 15,
            'I' => 12,
            'J' => 12,
            'K' => 12,
            'L' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ESTRUCTURA DINÁMICA DE FILAS:
        // 1: Título principal
        // 2: Subtítulo
        // 3: Información del período
        // 4: Vacía
        // 5: 📊 RESUMEN EJECUTIVO
        // 6: Vacía
        // 7: MÉTRICA headers
        // 8-9: Datos del resumen ejecutivo (2 filas)
        // 10: Vacía
        // 11: 🌱 INGRESOS POR CALIDAD
        // 12: Vacía
        // 13: Headers (Calidad, Lotes, etc.)
        // 14+: Datos dinámicos por calidad
        // X: TOTAL (después de los datos)
        // X+1: Vacía
        // X+2: 💰 ANÁLISIS DE VENTAS POR CALIDAD
        // X+3: Vacía
        // X+4: Headers de ventas
        // X+5+: Datos dinámicos de ventas
        // Y: TOTAL ventas
        // Y+1: Vacía
        // Y+2: 📈 VENTAS MENSUALES POR CALIDAD
        // Y+3: Resumen del año
        // Y+4: Vacía
        // Y+5: Headers mensuales
        // Y+6+: Datos dinámicos mensuales por calidad

        // Calcular posiciones dinámicamente
        $resumenCount = count($this->reporte['resumen']); // Número de calidades
        $ventasCount = is_array($this->reporte['ventas']) ? count($this->reporte['ventas']) : 0;
        $ventasMensualesExist = isset($this->reporte['ventas_mensuales']) && count($this->reporte['ventas_mensuales']) > 0;
        $calidadesActivasCount = \App\Models\QualityGrade::where('active', true)->count();

        // POSICIONES DE FILAS CALCULADAS DINÁMICAMENTE
        $currentRow = 1;

        // Header section (fijo)
        $titleRow = $currentRow++; // 1
        $subtitleRow = $currentRow++; // 2
        $periodRow = $currentRow++; // 3
        $emptyRow1 = $currentRow++; // 4

        // Resumen ejecutivo section
        $resumenTitleRow = $currentRow++; // 5
        $emptyRow2 = $currentRow++; // 6
        $metricasHeaderRow = $currentRow++; // 7
        $currentRow += 2; // 8-9 (datos ejecutivos)
        $emptyRow3 = $currentRow++; // 10

        // Ingresos section
        $ingresosTitleRow = $currentRow++; // 11
        $emptyRow4 = $currentRow++; // 12
        $ingresosHeaderRow = $currentRow++; // 13
        $ingresosDataStartRow = $currentRow; // 14
        $currentRow += $resumenCount; // Datos dinámicos de calidades
        $ingresosTotalRow = $currentRow++; // TOTAL
        $emptyRow5 = $currentRow++; // Vacía

        // Ventas section
        $ventasTitleRow = $currentRow++; // Título ventas
        $emptyRow6 = $currentRow++; // Vacía

        $ventasHeaderRow = 0;
        $ventasDataStartRow = 0;
        if ($ventasCount > 0) {
            $ventasHeaderRow = $currentRow++; // Headers ventas
            $ventasDataStartRow = $currentRow; // Datos ventas
            $currentRow += $ventasCount; // Datos dinámicos
            $ventasTotalRow = $currentRow++; // TOTAL ventas
        } else {
            $currentRow++; // "No se registraron ventas"
        }
        $emptyRow7 = $currentRow++; // Vacía

        // Ventas mensuales section
        $ventasMensualesTitleRow = 0;
        $ventasMensualesHeaderRow = 0;
        if ($ventasMensualesExist) {
            $ventasMensualesTitleRow = $currentRow++; // Título ventas mensuales
            $ventasMensualesResumenRow = $currentRow++; // Resumen del año
            $emptyRow8 = $currentRow++; // Vacía
            $ventasMensualesHeaderRow = $currentRow++; // Headers meses
            // Datos dinámicos por calidad
            $currentRow += $calidadesActivasCount;
        }

        // APLICAR ESTILOS CON POSICIONES DINÁMICAS

        // Main Title
        $sheet->mergeCells("A{$titleRow}:L{$titleRow}");
        $sheet->getStyle("A{$titleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 20, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF1f4e79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension($titleRow)->setRowHeight(35);

        // Subtitle
        $sheet->mergeCells("A{$subtitleRow}:L{$subtitleRow}");
        $sheet->getStyle("A{$subtitleRow}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 14, 'color' => ['argb' => 'FF666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Period info
        $sheet->getStyle("A{$periodRow}:L{$periodRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF333333']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF0F8FF']]
        ]);

        // 📊 RESUMEN EJECUTIVO
        $sheet->mergeCells("A{$resumenTitleRow}:L{$resumenTitleRow}");
        $sheet->getStyle("A{$resumenTitleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF28a745']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension($resumenTitleRow)->setRowHeight(25);

        // MÉTRICA headers con letra negra
        $sheet->getStyle("A{$metricasHeaderRow}:H{$metricasHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']], // Letra negra
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFE3F2FD']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF666666']]
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // 🌱 INGRESOS POR CALIDAD
        $sheet->mergeCells("A{$ingresosTitleRow}:L{$ingresosTitleRow}");
        $sheet->getStyle("A{$ingresosTitleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF28a745']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension($ingresosTitleRow)->setRowHeight(25);

        // Headers Ingresos
        $sheet->getStyle("A{$ingresosHeaderRow}:H{$ingresosHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF007bff']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // 💰 ANÁLISIS DE VENTAS POR CALIDAD (mismo estilo que ingresos)
        $sheet->mergeCells("A{$ventasTitleRow}:L{$ventasTitleRow}");
        $sheet->getStyle("A{$ventasTitleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF28a745']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension($ventasTitleRow)->setRowHeight(25);

        // Headers de Ventas (mismo estilo que headers ingresos)
        if ($ventasHeaderRow > 0) {
            $sheet->getStyle("A{$ventasHeaderRow}:H{$ventasHeaderRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF007bff']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // 📈 VENTAS MENSUALES section header (mismo estilo que ingresos)
        if ($ventasMensualesTitleRow > 0) {
            $sheet->mergeCells("A{$ventasMensualesTitleRow}:L{$ventasMensualesTitleRow}");
            $sheet->getStyle("A{$ventasMensualesTitleRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF28a745']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension($ventasMensualesTitleRow)->setRowHeight(25);
        }

        // Headers mensuales (mismo estilo que headers ingresos)
        if ($ventasMensualesHeaderRow > 0) {
            $sheet->getStyle("A{$ventasMensualesHeaderRow}:L{$ventasMensualesHeaderRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF007bff']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // FORMATEO DINÁMICO DE COLUMNAS

        // Columna B sin decimales para lotes (desde datos de ingresos hasta el final)
        $sheet->getStyle("B{$ingresosDataStartRow}:B{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Columna D sin formato y sin decimales para peso (desde datos de ingresos hasta el final)
        $sheet->getStyle("D{$ingresosDataStartRow}:D{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');

        // ESTILOS DE DATOS

        // Alternating row colors solo para datos de ingresos
        $ingresosDataEndRow = $ingresosDataStartRow + $resumenCount - 1;
        for ($i = $ingresosDataStartRow; $i <= $ingresosDataEndRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF8F9FA']]
                ]);
            }
        }

        // Limpiar estilos de datos estadísticos de ventas (bordes básicos y texto normal)
        if ($ventasDataStartRow > 0) {
            $ventasDataEndRow = $ventasDataStartRow + $ventasCount - 1;
            for ($i = $ventasDataStartRow; $i <= $ventasDataEndRow; $i++) {
                $sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                    'font' => ['bold' => false, 'color' => ['argb' => 'FF000000']],
                    'fill' => ['fillType' => Fill::FILL_NONE],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]
                    ]
                ]);
            }
        }

        return [];
    }
}

class ResumenSheet implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell
{
    protected $reporte;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($reporte, $fechaInicio, $fechaFin)
    {
        $this->reporte = $reporte;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        $data = collect();

        // Header information
        $data->push(['REPORTE DE ACOPIO Y ANÁLISIS']);
        $data->push(['Período:', \Carbon\Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($this->fechaFin)->format('d/m/Y')]);
        $data->push(['Generado:', now()->format('d/m/Y H:i:s')]);
        $data->push(['']); // Empty row

        // Resumen
        $data->push(['RESUMEN GENERAL']);
        $totalLotes = $this->reporte['resumen']->sum('lotes_ingresados');
        $totalPeso = $this->reporte['resumen']->sum('peso_ingresado');
        $totalInversion = $this->reporte['resumen']->sum('inversion_total');

        $data->push(['Total de Lotes:', $totalLotes]);
        $data->push(['Peso Total (kg):', number_format($totalPeso, 2)]);
        $data->push(['Inversión Total:', '$' . number_format($totalInversion, 2)]);

        return $data;
    }

    public function headings(): array
    {
        return []; // No headings needed as we handle them in collection
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            5 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFE2E2E2']]
            ]
        ];
    }
}

class IngresosSheet implements FromCollection, WithHeadings, WithStyles
{
    protected $reporte;

    public function __construct($reporte)
    {
        $this->reporte = $reporte;
    }

    public function collection()
    {
        return $this->reporte['resumen']->map(function($item) {
            return [
                'calidad' => $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad',
                'lotes_ingresados' => $item->lotes_ingresados,
                'peso_ingresado' => number_format($item->peso_ingresado, 2),
                'inversion_total' => '$' . number_format($item->inversion_total, 2),
                'precio_promedio' => '$' . number_format($item->inversion_total / $item->peso_ingresado, 2)
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Calidad',
            'Lotes Ingresados',
            'Peso Ingresado (kg)',
            'Inversión Total',
            'Precio Promedio/kg'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF28A745']],
                'font' => ['color' => ['argb' => 'FFFFFFFF']]
            ]
        ];
    }
}

class VentasSheet implements FromCollection, WithHeadings, WithStyles
{
    protected $reporte;

    public function __construct($reporte)
    {
        $this->reporte = $reporte;
    }

    public function collection()
    {
        $data = collect();

        if (is_array($this->reporte['ventas']) && count($this->reporte['ventas']) > 0) {
            foreach ($this->reporte['ventas'] as $calidad => $ventasCalidad) {
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

                $data->push([
                    'calidad' => $calidad,
                    'ventas_realizadas' => $ventasUnicas,
                    'peso_vendido' => number_format($pesoVendido, 2),
                    'ingresos_ventas' => '$' . number_format($ingresoVentas, 2),
                    'precio_promedio' => '$' . number_format($precioPromedio, 2),
                    'margen_bruto' => '$' . number_format($margenBruto, 2),
                    'margen_porcentaje' => number_format($ingresoVentas > 0 ? ($margenBruto / $ingresoVentas) * 100 : 0, 1) . '%'
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Calidad',
            'Ventas Realizadas',
            'Peso Vendido (kg)',
            'Ingresos por Ventas',
            'Precio Promedio/kg',
            'Margen Bruto',
            'Margen %'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF17A2B8']],
                'font' => ['color' => ['argb' => 'FFFFFFFF']]
            ]
        ];
    }
}

class VentasMensualesSheet implements FromCollection, WithHeadings, WithStyles
{
    protected $reporte;

    public function __construct($reporte)
    {
        $this->reporte = $reporte;
    }

    public function collection()
    {
        $data = collect();

        if (isset($this->reporte['ventas_mensuales'])) {
            // Crear header con los meses
            $meses = [];
            $startDate = \Carbon\Carbon::parse('2025-01-01');
            $endDate = now();

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $meses[$current->format('Y-m')] = $current->format('M Y');
                $current->addMonth();
            }

            // Obtener todas las calidades
            $calidades = \App\Models\QualityGrade::where('active', true)->orderBy('name')->get();

            foreach ($calidades as $calidad) {
                $row = [$calidad->name];

                foreach ($meses as $mesKey => $mesLabel) {
                    $ventaMes = $this->reporte['ventas_mensuales'][$mesKey][$calidad->name] ?? 0;
                    $row[] = '$' . number_format($ventaMes, 2);
                }

                $data->push($row);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        $headers = ['Calidad'];

        // Agregar meses desde enero 2025 hasta ahora
        $startDate = \Carbon\Carbon::parse('2025-01-01');
        $endDate = now();

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $headers[] = $current->format('M Y');
            $current->addMonth();
        }

        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF007BFF']],
                'font' => ['color' => ['argb' => 'FFFFFFFF']]
            ]
        ];
    }
}
