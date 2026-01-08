<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" size="lg">
                Guardar Configuración
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>