<x-filament-panels::page>
    @if($error)
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Error:</span> {{ $error }}
        </div>
    @endif

    {{-- Header del Reporte --}}
    @if($report)
        <div class="mb-6">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $report->title }}</h2>
            <p class="text-gray-500">{{ $report->description }}</p>
        </div>
    @endif

    {{-- Gráfico Opcional --}}
    @if(!empty($chartData))
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-4 mb-6 border dark:border-gray-700">
            <canvas id="reportChart" height="100"></canvas>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const ctx = document.getElementById('reportChart');
                new Chart(ctx, {
                    type: '{{ $chartType }}',
                    data: @json($chartData),
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: true, text: '{{ $report->title }}' }
                        }
                    }
                });
            });
        </script>
    @endif

    {{-- Tabla de Resultados --}}
    @if(!empty($results))
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        @foreach($columns as $col)
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                {{ $col }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $row)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            @foreach($columns as $col)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ is_object($row) ? ($row->$col ?? '-') : ($row[$col] ?? '-') }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            Mostrando {{ count($results) }} resultados.
        </div>
    @elseif($report && !$error)
        <div class="text-center p-10 text-gray-500">
            No se encontraron resultados para esta consulta.
        </div>
    @endif

    @if(!$report && !$error)
        <div class="text-center p-10 text-gray-500">
            Selecciona un reporte para ejecutar.
        </div>
    @endif

</x-filament-panels::page>