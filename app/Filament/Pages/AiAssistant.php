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

        // Mensaje de bienvenida
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $this->hasApiKey
                ? 'Hola, soy tu asistente de datos. ¿Qué reporte necesitas hoy?'
                : 'Hola. Para empezar a generar reportes reales, por favor configura tu API Key de OpenAI en la sección de Configuración IA.',
            'sql' => null
        ];
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
            // extract names if objects
            $tableNames = collect($tables)->map(function ($t) {
                $t = (array) $t; // Convertir a array si es objeto
                return (string) array_values($t)[0]; // Tomar el primer valor (nombre de la tabla)
            })->toArray();

            $schemaContext = ['tables' => $tableNames];

            // Generar respuesta
            $sqlResponse = $orchestrator->generateSqlFromPrompt($userMessage, $schemaContext);

            // Analizar si la respuesta es realmente un SQL o un mensaje de error/texto
            // El orchestrator placeholder actual devuelve SQL directo o un mensaje con "--"

            $isSql = !Str::startsWith($sqlResponse, '-- AI Orchestrator: No pude');

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
