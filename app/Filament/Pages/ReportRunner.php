<?php

namespace App\Filament\Pages;

use App\Models\SqlReport;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRunner extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_sql_reports');
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false; // Hidden from sidebar
    protected static ?string $title = 'Ejecutar Reporte';
    protected static ?string $slug = 'report-runner';

    protected static string $view = 'filament.pages.report-runner';

    public ?int $reportId = null;
    public ?SqlReport $report = null;
    public $results = [];
    public $columns = [];
    public $error = null;

    // Chart Data
    public $chartData = [];
    public $chartType = null;

    public function mount()
    {
        $this->reportId = request()->query('report_id');
        if ($this->reportId) {
            $this->runReport($this->reportId);
        }
    }

    public function runReport($id)
    {
        $this->report = SqlReport::find($id);

        if (!$this->report) {
            $this->error = "Reporte no encontrado.";
            return;
        }

        try {
            // Paginate manually? For now, let's allow a LIMIT interaction or fetch all (dangerous)
            // Ideally we modify the query to include LIMIT OFFSET
            // Or access raw Pdo but that's complex for pagination.
            // Simplified: Fetch up to 1000 rows.

            $query = $this->report->sql_query;

            // Basic protection again just in case
            if (preg_match('/\b(update|delete|drop|alter|insert)\b/i', $query)) {
                $this->error = "Consulta no permitida.";
                return;
            }

            // Execute on ERP DB
            $rawResults = DB::connection('erp_db')->select($query);

            if (count($rawResults) > 0) {
                // Get columns from first row
                $this->columns = array_keys((array) $rawResults[0]);
                $this->results = $rawResults; // Array of objects

                // Prepare Chart if config exists
                if ($this->report->chart_config) {
                    $this->prepareChartData($this->results, $this->report->chart_config);
                }
            } else {
                $this->results = [];
                $this->columns = [];
            }

        } catch (\Exception $e) {
            $this->error = "Error SQL: " . $e->getMessage();
        }
    }

    protected function prepareChartData($results, $config)
    {
        $type = $config['type'] ?? 'line';
        $labelCol = $config['label_column'] ?? $this->columns[0];
        $dataCol = $config['data_column'] ?? ($this->columns[1] ?? null);

        if (!$dataCol)
            return;

        $labels = [];
        $dataPoints = [];

        foreach ($results as $row) {
            $rowArray = (array) $row;
            $labels[] = $rowArray[$labelCol] ?? '-';
            $dataPoints[] = $rowArray[$dataCol] ?? 0;
        }

        $this->chartType = $type;
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $this->report->title,
                    'data' => $dataPoints,
                    'backgroundColor' => '#6366f1',
                    'borderColor' => '#6366f1',
                ]
            ]
        ];
    }
}
