<?php

namespace App\Filament\Pages;

use App\Models\Dashboard as DashboardModel;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Request;

class DynamicDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static bool $shouldRegisterNavigation = false; // Se registrarán dinámicamente o se accederá por URL
    protected static string $view = 'filament.pages.dynamic-dashboard';

    public $dashboard;

    public function mount($slug = null)
    {
        $this->dashboard = DashboardModel::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function getHeading(): string
    {
        return $this->dashboard->title;
    }
}
