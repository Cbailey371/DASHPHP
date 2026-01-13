<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SchemaService
{
    protected string $connection = 'erp_db';

    /**
     * Get all tables from the ERP database.
     * 
     * @return array List of table names or detailed objects
     */
    public function getTables(): array
    {
        try {
            // Intento vía Laravel moderno
            return Schema::connection($this->connection)->getTables();
        } catch (\Throwable $e) {
            // Fallback robusto para MySQL
            $tables = DB::connection($this->connection)->select('SHOW TABLES');
            return array_map(function ($table) {
                return (string) array_values((array) $table)[0];
            }, $tables);
        }
    }

    /**
     * Get columns for a specific table.
     * 
     * @param string $table
     * @return array List of columns
     */
    public function getColumns(string $table): array
    {
        return Schema::connection($this->connection)->getColumns($table);
    }

    /**
     * Get basic statistics or sample data (Optional)
     */
    public function getSampleData(string $table, int $limit = 5): array
    {
        return DB::connection($this->connection)
            ->table($table)
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
