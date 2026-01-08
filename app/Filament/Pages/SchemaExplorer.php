<?php

namespace App\Filament\Pages;

use App\Services\SchemaService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SchemaExplorer extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()->can('view_schema_explorer');
    }

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Reportes Avanzados';
    protected static ?string $title = 'Explorador de Esquema';
    protected static ?string $slug = 'schema-explorer';

    protected static string $view = 'filament.pages.schema-explorer';

    public $tables = [];
    public $selectedTable = null;
    public $columns = [];
    public $search = '';

    public function getFilteredTablesProperty()
    {
        return collect($this->tables)
            ->filter(fn($table) => empty($this->search) || str_contains(strtolower($table), strtolower($this->search)))
            ->values()
            ->toArray();
    }

    public function mount(SchemaService $service)
    {
        // Fetch raw tables (usually arrays of objects)
        $rawTables = $service->getTables();

        // Extract table names
        $this->tables = collect($rawTables)->map(function ($table) {
            // Laravel returns object with properties like "Tables_in_erp"
            // We just cast to array and take the first value
            return array_values((array) $table)[0];
        })->sort()->values()->toArray();
    }

    public function selectTable($tableName)
    {
        $this->selectedTable = $tableName;
        $service = app(SchemaService::class);

        $this->columns = $service->getColumns($tableName);
    }
}
