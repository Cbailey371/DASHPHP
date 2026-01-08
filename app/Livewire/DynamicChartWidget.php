<?php

namespace App\Livewire;

use App\Models\CustomWidget;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class DynamicChartWidget extends ChartWidget
{
    public ?int $widgetId = null;

    protected function getData(): array
    {
        if (!$this->widgetId) {
            return [];
        }

        $config = CustomWidget::find($this->widgetId);
        if (!$config) {
            return [];
        }

        // Si el widget usa un Reporte SQL personalizado
        if ($config->sql_report_id && $config->sqlReport) {
            try {
                $rawResults = \Illuminate\Support\Facades\DB::connection('erp_db')
                    ->select($config->sqlReport->sql_query);

                if (empty($rawResults)) {
                    return ['datasets' => [], 'labels' => []];
                }

                $columns = array_keys((array) $rawResults[0]);
                $labelCol = $columns[0];
                $dataCol = $columns[1] ?? $columns[0];

                $labels = [];
                $dataPoints = [];

                foreach ($rawResults as $row) {
                    $rowArray = (array) $row;
                    $labels[] = $rowArray[$labelCol] ?? '-';
                    $dataPoints[] = $rowArray[$dataCol] ?? 0;
                }

                return [
                    'datasets' => [
                        [
                            'label' => $config->title,
                            'data' => $dataPoints,
                            'backgroundColor' => $this->getColorHex($config->color),
                            'borderColor' => $this->getColorHex($config->color),
                        ],
                    ],
                    'labels' => $labels,
                ];
            } catch (\Exception $e) {
                return ['datasets' => [['label' => 'Error SQL', 'data' => []]], 'labels' => []];
            }
        }

        // Si el widget usa el flujo estándar de Modelos Eloquent
        $modelClass = 'App\\Models\\' . $config->model;
        if (!class_exists($modelClass)) {
            return [];
        }

        $query = Trend::model($modelClass)
            ->dateColumn($config->date_column ?? 'created_at')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth();

        if ($config->aggregate_function === 'sum') {
            $data = $query->sum($config->aggregate_column ?? 'id');
        } elseif ($config->aggregate_function === 'avg') {
            $data = $query->average($config->aggregate_column ?? 'id');
        } else {
            $data = $query->count();
        }

        return [
            'datasets' => [
                [
                    'label' => $config->title,
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'backgroundColor' => $this->getColorHex($config->color),
                    'borderColor' => $this->getColorHex($config->color),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => Carbon::parse($value->date)->format('M')),
        ];
    }

    public function getHeading(): ?string
    {
        if (!$this->widgetId) {
            return 'Dynamic Widget';
        }
        return CustomWidget::find($this->widgetId)?->title ?? 'Dynamic Widget';
    }

    protected function getType(): string
    {
        if (!$this->widgetId) {
            return 'line';
        }
        $config = CustomWidget::find($this->widgetId);

        // Mapear tipos de DB a Chart.js types
        return match ($config?->type) {
            'chart_bar' => 'bar',
            'chart_pie' => 'pie',
            default => 'line',
        };
    }

    protected function getColorHex($colorName): string
    {
        return match ($colorName) {
            'success' => '#10b981', // green-500
            'warning' => '#f59e0b', // amber-500
            'danger' => '#ef4444', // red-500
            'info' => '#3b82f6', // blue-500
            default => '#6366f1', // indigo-500 (primary)
        };
    }
}
