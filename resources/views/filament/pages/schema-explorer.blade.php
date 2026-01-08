<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Lista de Tablas --}}
        <div
            class="md:col-span-1 bg-white dark:bg-gray-900 rounded-lg shadow p-4 border dark:border-gray-700 h-[calc(100vh-12rem)] overflow-y-auto">
            <h3
                class="text-lg font-bold mb-4 text-gray-700 dark:text-gray-200 sticky top-0 bg-white dark:bg-gray-900 z-10">
                Tablas ERP</h3>

            <div class="mb-4 sticky top-8 z-10 bg-white dark:bg-gray-900 pt-2 text-black">
                <input type="text" wire:model.live="search" placeholder="Buscar tabla..."
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
            </div>

            <div class="space-y-1">
                @foreach($this->filteredTables as $table)
                    <button wire:click="selectTable('{{ $table }}')"
                        class="w-full text-left px-3 py-2 rounded text-sm transition-colors {{ $selectedTable === $table ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 font-medium' : 'hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400' }}">
                        {{ $table }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Detalles de Columnas --}}
        <div
            class="md:col-span-2 bg-white dark:bg-gray-900 rounded-lg shadow p-4 border dark:border-gray-700 h-[calc(100vh-12rem)] overflow-y-auto">
            @if($selectedTable)
                <h3
                    class="text-lg font-bold mb-4 text-gray-700 dark:text-gray-200 sticky top-0 bg-white dark:bg-gray-900 z-10">
                    Esquema: <span class="text-primary-600 dark:text-primary-400">{{ $selectedTable }}</span>
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Columna</th>
                                <th scope="col" class="px-6 py-3">Tipo</th>
                                <th scope="col" class="px-6 py-3">Nullable</th>
                                <th scope="col" class="px-6 py-3">Key</th>
                                <th scope="col" class="px-6 py-3">Default</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($columns as $column)
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $column['name'] ?? $column->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $column['type_name'] ?? $column->type_name ?? $column['type'] ?? $column->type ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ ($column['nullable'] ?? $column->nullable ?? false) ? 'YES' : 'NO' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{-- Mostrar PK si existe (Laravel no siempre lo devuelve fácil en getColumns standard,
                                        pero intentamos) --}}
                                        -
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $column['default'] ?? $column->default ?? 'NULL' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex items-center justify-center h-full text-gray-400">
                    <p>Selecciona una tabla para ver su estructura.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>