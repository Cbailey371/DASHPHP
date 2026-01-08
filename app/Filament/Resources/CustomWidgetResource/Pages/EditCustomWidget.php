<?php

namespace App\Filament\Resources\CustomWidgetResource\Pages;

use App\Filament\Resources\CustomWidgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomWidget extends EditRecord
{
    protected static string $resource = CustomWidgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
