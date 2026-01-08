<?php

namespace App\Filament\Resources\SqlReportResource\Pages;

use App\Filament\Resources\SqlReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSqlReport extends EditRecord
{
    protected static string $resource = SqlReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
