<?php

namespace App\Filament\Pages;

use App\Services\AiReportOrchestrator;
use App\Services\SchemaService;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class AiAssistant extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()->can('use_ai_assistant');
    }

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Reportes Avanzados';
    protected static ?string $title = 'Asistente IA';
    protected static ?string $slug = 'ai-assistant';

    protected static string $view = 'filament.pages.ai-assistant';

    public $messages = [];
    public $newMessage = '';
    public $isThinking = false;
    public $hasApiKey = false;

    public function mount()
    {
        $this->hasApiKey = !empty(\App\Models\AppSetting::where('key', 'openai_api_key')->first()?->value);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Iniciando asistente y verificando conexión con ERP...',
            'sql' => null
        ];
    }

    public function initAssistant(SchemaService $schemaService)
    {
        $tableCount = 0;
        $errorMessage = null;

        try {
            // Intentar obtener tablas con un límite de tiempo implícito por la conexión
            $tables = $schemaService->getTables();
            $tableCount = count($tables);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        // Actualizar el primer mensaje (el de bienvenida)
        if (!$this->hasApiKey) {
            $welcome = 'Hola. Por favor configura tu API Key en la sección de Configuración IA para empezar.';
        } elseif ($tableCount === 0) {
            $welcome = '⚠️ Conexión establecida con la IA, pero no detecto tablas en la base de datos ERP. ' .
                ($errorMessage ? "Error técnico: {$errorMessage}. " : '') .
                'Revisa la conexión erp_db en tu archivo .env';
        } else {
            $welcome = "Hola, estoy listo. He detectado {$tableCount} tablas en tu base de datos ERP. ¿Qué reporte necesitas hoy?";
        }

        $this->messages[0]['content'] = $welcome;
    }

    public function sendMessage(AiReportOrchestrator $orchestrator, SchemaService $schemaService)
    {
        if (empty(trim($this->newMessage))) {
            return;
        }

        // Agregar mensaje del usuario
        $userMessage = $this->newMessage;
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'sql' => null
        ];
        $this->newMessage = '';
        $this->isThinking = true;

        // Simular pensamiento (o llamar API real)
        // En una app real, esto debería ser asíncrono o stream

        // Obtener contexto del esquema para la IA
        try {
            // Nota: Pasar todo el esquema puede ser pesado para el prompt si hay muchas tablas.
            // Por ahora pasamos solo nombres de tablas como contexto simple.
            $tables = $schemaService->getTables();
            $tableNames = collect($tables)
                ->map(function ($t) {
                    if (is_string($t))
                        return $t;
                    $tArray = (array) $t;
                    $name = $tArray['name'] ?? array_values($tArray)[0] ?? '';
                    return is_array($name) ? json_encode($name) : (string) $name;
                })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $schemaContext = ['tables' => $tableNames];

            // Generar respuesta
            $sqlResponse = (string) $orchestrator->generateSqlFromPrompt($userMessage, $schemaContext);

            // Si la respuesta empieza con --, es un mensaje del orquestador o un error
            $isError = Str::startsWith($sqlResponse, '--');
            $isSql = !$isError;

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $isSql ? 'Aquí tienes la consulta SQL que generé para ti:' : 'Lo siento, no pude generar una consulta exacta.',
                'sql' => $isSql ? $sqlResponse : null,
                'error' => !$isSql ? $sqlResponse : null
            ];

        } catch (\Exception $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Ocurrió un error al procesar tu solicitud: ' . $e->getMessage(),
                'sql' => null
            ];
        }

        $this->dispatch('message-received');
        $this->isThinking = false;
    }

    public function createReportFromSql($sql)
    {
        // Decodificar entidades HTML si vienen codificadas
        $sql = html_entity_decode($sql);

        $report = \App\Models\SqlReport::create([
            'title' => 'Reporte IA - ' . now()->format('d/m H:i'),
            'description' => 'Generado automáticamente por el Asistente IA',
            'sql_query' => $sql,
            'user_id' => auth()->id(),
            'is_public' => false,
        ]);

        return redirect()->to(\App\Filament\Pages\ReportRunner::getUrl(['report_id' => $report->id]));
    }
}
