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
        // Laravel Schema::getTables() returns array of objects with name, schema, etc.
        // Available in newer Laravel versions.
        return Schema::connection($this->connection)->getTables();
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
