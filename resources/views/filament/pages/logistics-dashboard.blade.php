<x-filament-panels::page>
    @if ($headerWidgets = $this->getVisibleHeaderWidgets())
        <x-filament-widgets::widgets :widgets="$headerWidgets" :columns="$this->getHeaderWidgetsColumns()" />
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @foreach(\App\Models\CustomWidget::where('is_active', true)->where('section', 'logistica')->orderBy('sort_order')->get() as $widget)
            @livewire(\App\Livewire\DynamicChartWidget::class, ['widgetId' => $widget->id], key('widget-' . $widget->id))
        @endforeach
    </div>

    @if ($footerWidgets = $this->getVisibleFooterWidgets())
        <x-filament-widgets::widgets :widgets="$footerWidgets" :columns="$this->getFooterWidgetsColumns()" />
    @endif
</x-filament-panels::page>