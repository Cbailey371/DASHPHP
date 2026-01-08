<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LogisticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Tablero Logística';
    protected static ?string $title = 'Logística';
    protected static ?string $slug = 'logistica';

    protected static string $view = 'filament.pages.logistics-dashboard';

    public static function canAccess(): bool
    {
        return auth()->user()->can('view_logistics_dashboard') || auth()->user()->hasRole('Admin');
    }
}
