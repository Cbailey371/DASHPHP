<?php

namespace App\Filament\Resources\CustomWidgetResource\Pages;

use App\Filament\Resources\CustomWidgetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomWidget extends CreateRecord
{
    protected static string $resource = CustomWidgetResource::class;
}
