<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ProcessProductsCsv extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Herramientas';
    protected static ?string $title = 'Enriquecer CSV de Productos';

    protected static string $view = 'filament.pages.process-products-csv';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración del Archivo CSV')
                    ->description('Sube tu archivo para buscar y cruzar con la base de datos del ERP (enx_logistica.products).')
                    ->schema([
                        FileUpload::make('csv_file')
                            ->label('Archivo CSV')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'])
                            ->disk('local')
                            ->directory('csv-imports'),

                        TextInput::make('csv_key_column')
                            ->label('Columna identificadora en el CSV (Ej: Código, SKU, ID)')
                            ->default('Codigo')
                            ->required()
                            ->helperText('Nombre exacto de la columna en tu archivo CSV que usaremos para buscar.'),

                        TextInput::make('erp_key_column')
                            ->label('Columna identificadora en la Base de Datos del ERP')
                            ->default('sku')
                            ->required()
                            ->helperText('Nombre del campo en la tabla products del ERP (ej: id, codigo, sku).'),
                    ])
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('process')
                ->label('Procesar y Descargar CSV')
                ->submit('process')
                ->color('primary'),
        ];
    }

    public function process()
    {
        $data = $this->form->getState();

        $filePath = Storage::disk('local')->path($data['csv_file']);
        $csvKey = trim($data['csv_key_column']);
        $erpKey = trim($data['erp_key_column']);

        if (!file_exists($filePath)) {
            Notification::make()->title('Archivo no encontrado')->danger()->send();
            return;
        }

        ini_set('auto_detect_line_endings', true);
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            Notification::make()->title('Archivo vacío o ilegible')->danger()->send();
            return;
        }

        // Detectar delimitador de la primera línea
        $firstLine = $lines[0];
        $delimiter = ',';
        if (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) {
            $delimiter = ';';
        } elseif (strpos($firstLine, "\t") !== false) {
            $delimiter = "\t";
        }

        $headers = [];
        $rows = [];
        $firstRow = true;

        $keyIndex = -1;
        $nameIndex = -1;

        // Fase 1: Parseo de datos y recolección de llaves
        $keysToSearch = [];

        foreach ($lines as $lineStr) {
            $row = str_getcsv($lineStr, $delimiter);

            if ($firstRow) {
                // Eliminar posibles BOM o caracteres ocultos en el inicio del archivo
                foreach ($row as &$cell) {
                    $cell = trim($cell);
                    // Forza traducción de codificaciones legacy a UTF-8 seguro
                    $cell = @mb_convert_encoding($cell, 'UTF-8', 'UTF-8, ISO-8859-1, WINDOWS-1252');
                    // Remove ZERO WIDTH NO-BREAK SPACE y UTF-8 BOM characters
                    $cell = preg_replace('/^[\xEF\xBB\xBF\xE2\x80\x8B\x00-\x1F\x7F]+|[\xE2\x80\x8B\x00-\x1F\x7F]+$/i', '', $cell);
                    // Remove quotes if present explicitly after trimming
                    $cell = trim($cell, '"\'');
                }

                $headers = $row;
                $firstRow = false;

                // Buscar ignorando MAYUSCULAS/minusculas
                $lowerKey = trim(strtolower($csvKey));
                foreach ($headers as $idx => $headerName) {
                    if (trim(strtolower($headerName)) === $lowerKey) {
                        $keyIndex = $idx;
                        break;
                    }
                }

                // Buscar "Nombre del Producto"
                foreach ($headers as $index => $colName) {
                    if (stripos($colName, 'Nombre del Producto') !== false || stripos($colName, 'Product') !== false) {
                        $nameIndex = $index;
                        break;
                    }
                }

                if ($nameIndex === -1) {
                    $nameIndex = 0; // Si no lo encuentra, lo pone despues de la primera columna
                }

                if ($keyIndex === -1) {
                    $safeHeadersMsg = mb_convert_encoding("Columnas detectadas: " . implode(', ', $headers), 'UTF-8', 'UTF-8');
                    $safeTitle = mb_convert_encoding("Columna '$csvKey' no encontrada en el CSV.", 'UTF-8', 'UTF-8');
                    Notification::make()->title($safeTitle)
                        ->body($safeHeadersMsg)
                        ->danger()->send();
                    return;
                }

                // Insertar nuevos headers
                array_splice($headers, $nameIndex + 1, 0, ['marca', 'Unidad_Empresarial', 'Unidad_Estrategica']);
                continue;
            }

            if (empty($row) || (count($row) === 1 && empty(trim($row[0])))) {
                continue;
            }

            $keyValue = $keyIndex !== false && isset($row[$keyIndex]) ? trim($row[$keyIndex]) : null;
            if ($keyValue) {
                $keysToSearch[] = $keyValue;
            }

            $rows[] = $row; // Guardamos temporalmente la fila sin enriquecer
        }

        // Fase 2: Pre-cargar datos del ERP (Evitar timeout "N+1 Problem")
        $erpDataMap = [];
        if (count($keysToSearch) > 0) {
            try {
                // Chunk the query para no reventar los limites de whereIn() de MySQL por si el CSV es mayor a 10.000 líneas.
                $chunks = array_chunk(array_unique($keysToSearch), 1000);

                foreach ($chunks as $chunk) {
                    $results = DB::connection('erp_db')->table('products')
                        ->whereIn($erpKey, $chunk)
                        ->get([$erpKey, 'marca', 'Unidad_Empresarial', 'Unidad_Estrategica']);

                    foreach ($results as $res) {
                        // Guardamos en diccionario asociativo O(1)
                        $erpDataMap[(string) $res->$erpKey] = $res;
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Procesamiento CSV ERP Error: " . $e->getMessage());
                Notification::make()->title('Error consultando la BD ERP.')
                    ->body(mb_convert_encoding('Mensaje interno: ' . $e->getMessage(), 'UTF-8', 'UTF-8'))
                    ->danger()->send();
                return;
            }
        }

        // Fase 3: Enriquecimiento de filas en memoria
        $enrichedRows = [];
        foreach ($rows as $row) {
            $keyValue = $keyIndex !== false && isset($row[$keyIndex]) ? trim($row[$keyIndex]) : null;

            $marca = '';
            $unidadEmpresarial = '';
            $unidadEstrategica = '';

            if ($keyValue && isset($erpDataMap[(string) $keyValue])) {
                $dataTuple = $erpDataMap[(string) $keyValue];
                $marca = $dataTuple->marca ?? '';
                $unidadEmpresarial = $dataTuple->Unidad_Empresarial ?? '';
                $unidadEstrategica = $dataTuple->Unidad_Estrategica ?? '';
            }

            while (count($row) < count($headers) - 3) {
                $row[] = '';
            }

            // Insertar nuevas columnas
            array_splice($row, $nameIndex + 1, 0, [$marca, $unidadEmpresarial, $unidadEstrategica]);

            $enrichedRows[] = $row;
        }

        // Fase 4: Exportación
        $outPath = storage_path('app/csv-exports');
        if (!is_dir($outPath)) {
            mkdir($outPath, 0755, true);
        }

        $fileName = 'productos_enriquecidos_' . time() . '.csv';
        $fullPath = $outPath . '/' . $fileName;

        $outFile = fopen($fullPath, 'w');
        fputs($outFile, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($outFile, $headers, $delimiter);
        foreach ($enrichedRows as $r) {
            fputcsv($outFile, $r, $delimiter);
        }
        fclose($outFile);

        Storage::disk('local')->delete($data['csv_file']); // Limpiar original
        $this->form->fill();

        return response()->download($fullPath)->deleteFileAfterSend(true);
    }
}
