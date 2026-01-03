<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class QuotesTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Tendencia Mensual: Aprobadas sin Orden de Trabajo';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $start = $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : now()->subMonths(11);
        $end = $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : now();

        // Asegurar inicio del mes para agrupación correcta si se precisa
        // $start->startOfMonth(); $end->endOfMonth();

        // Obtener datos en rango
        $data = Quote::query()
            ->select(DB::raw("DATE_FORMAT(Date, '%Y-%m') as month_key"), DB::raw('COUNT(*) as count'))
            ->whereIn('Status', ['APROVED', 'ACTIVE']) // Status corregido
            ->doesntHave('workOrder')
            ->whereDate('Date', '>=', $start)
            ->whereDate('Date', '<=', $end)
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get();

        $labels = [];
        $values = [];

        // Generar periodo de meses
        $period = CarbonPeriod::create($start, '1 month', $end);

        foreach ($period as $date) {
            $key = $date->format('Y-m');
            $label = $date->translatedFormat('M Y');

            $record = $data->firstWhere('month_key', $key);

            $labels[] = $label;
            $values[] = $record ? $record->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sin Orden de Trabajo',
                    'data' => $values,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
