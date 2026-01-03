<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class QuoteCompletionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Progreso de Cotizaciones: Con WO vs Sin WO';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1; // Ancho completo para que se aprecie mejor

    protected function getData(): array
    {
        $start = $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : now()->subMonths(11);
        $end = $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : now();

        // 1. Datos: Aprobadas CON Orden de Trabajo (Completadas)
        $withWO = Quote::query()
            ->select(DB::raw("DATE_FORMAT(Date, '%Y-%m') as month_key"), DB::raw('COUNT(*) as count'))
            ->whereIn('Status', ['APROVED', 'ACTIVE']) // Status corregido
            ->has('workOrder')
            ->whereDate('Date', '>=', $start)
            ->whereDate('Date', '<=', $end)
            ->groupBy('month_key')
            ->pluck('count', 'month_key');

        // 2. Datos: Aprobadas SIN Orden de Trabajo (Incompletas)
        $withoutWO = Quote::query()
            ->select(DB::raw("DATE_FORMAT(Date, '%Y-%m') as month_key"), DB::raw('COUNT(*) as count'))
            ->whereIn('Status', ['APROVED', 'ACTIVE']) // Status corregido
            ->doesntHave('workOrder')
            ->whereDate('Date', '>=', $start)
            ->whereDate('Date', '<=', $end)
            ->groupBy('month_key')
            ->pluck('count', 'month_key');

        $dataWith = [];
        $dataWithout = [];
        $labels = [];

        $period = CarbonPeriod::create($start, '1 month', $end);

        foreach ($period as $date) {
            $key = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y');
            $dataWith[] = $withWO[$key] ?? 0;
            $dataWithout[] = $withoutWO[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Completadas (Con WO)',
                    'data' => $dataWith,
                    'borderColor' => '#8b5cf6', // Violeta intenso (similar a la imagen)
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)', // Relleno transparente
                    'fill' => true,
                    'tension' => 0.4, // Curvas suaves
                ],
                [
                    'label' => 'Pendientes (Sin WO)',
                    'data' => $dataWithout,
                    'borderColor' => '#a78bfa', // Violeta claro
                    'backgroundColor' => 'rgba(167, 139, 250, 0.2)', // Relleno transparente
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
