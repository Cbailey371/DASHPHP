<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;

use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PaymentDistributionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Distribución de Cartera por Pago';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $data = Quote::approvedWithoutWorkOrder()
            ->when($startDate, fn($q) => $q->whereDate('Date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('Date', '<=', $endDate))
            ->selectRaw('SalesTerm, count(*) as total')
            ->groupBy('SalesTerm')
            ->pluck('total', 'SalesTerm');

        return [
            'datasets' => [
                [
                    'label' => 'Cotizaciones',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#4ade80', // green-400
                        '#facc15', // yellow-400
                        '#f87171', // red-400
                    ],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
