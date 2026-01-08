<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @php
            $widgets = \App\Models\CustomWidget::where('dashboard_id', $this->dashboard->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        @endphp

        @forelse($widgets as $widget)
            @livewire(\App\Livewire\DynamicChartWidget::class, ['widgetId' => $widget->id], key($widget->id))
        @empty
            <div
                class="col-span-full bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border dark:border-gray-700 text-center">
                <p class="text-gray-500">Este dashboard aún no tiene widgets configurados.</p>
                <a href="{{ \App\Filament\Resources\CustomWidgetResource::getUrl('create') }}"
                    class="text-primary-600 font-bold mt-2 inline-block">
                    + Agregar mi primer widget
                </a>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>