<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class QuoteAgingChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Antigüedad: Cotizaciones Aprobadas sin WO';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        // Definir rangos
        $ranges = [
            '0-7 días' => 0,
            '8-15 días' => 0,
            '16-30 días' => 0,
            '+30 días' => 0,
        ];

        // Obtener datos crudos
        $quotes = Quote::approvedWithoutWorkOrder()
            ->when($startDate, fn($q) => $q->whereDate('Date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('Date', '<=', $endDate))
            ->select('Date')
            ->get();

        foreach ($quotes as $quote) {
            $days = $quote->days_old; // Usar el accessor existente

            if ($days <= 7) {
                $ranges['0-7 días']++;
            } elseif ($days <= 15) {
                $ranges['8-15 días']++;
            } elseif ($days <= 30) {
                $ranges['16-30 días']++;
            } else {
                $ranges['+30 días']++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de Cotizaciones',
                    'data' => array_values($ranges),
                    'backgroundColor' => [
                        '#10b981', // success (0-7)
                        '#f59e0b', // warning (8-15)
                        '#f97316', // orange (16-30)
                        '#ef4444', // danger (+30)
                    ],
                ],
            ],
            'labels' => array_keys($ranges),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Gráfico de dona para ver distribución
    }
}
