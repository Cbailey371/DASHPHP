<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AiReportOrchestrator
{
    /**
     * Generate SQL from natural language prompt.
     * This is a placeholder for future MCP integration.
     *
     * @param string $prompt
     * @param array $schemaContext
     * @return string Generated SQL
     */
    /**
     * Generate SQL from natural language prompt using OpenAI.
     */
    public function generateSqlFromPrompt(string $prompt, array $schemaContext = []): string
    {
        $apiKey = \App\Models\AppSetting::where('key', 'openai_api_key')->first()?->value;
        $model = \App\Models\AppSetting::where('key', 'openai_model')->first()?->value ?? 'gpt-4o';
        $apiBase = \App\Models\AppSetting::where('key', 'openai_api_base')->first()?->value ?? 'https://api.openai.com/v1';

        if (!$apiKey) {
            return $this->simulation($prompt);
        }

        $tablesArr = collect($schemaContext['tables'] ?? [])
            ->map(fn($t) => is_array($t) ? json_encode($t) : (string) $t)
            ->filter()
            ->toArray();

        $tablesList = implode(', ', $tablesArr);

        // Prompt ultra-estricto para modelos locales (Mistral/Ollama)
        $contextualPrompt = "### ANALISTA SQL MYSQL
REGLA: RESPONDE EXCLUSIVAMENTE CON CÓDIGO SQL SELECT.
PROHIBIDO: EXPLICACIONES, SALUDOS O TEXTO ADICIONAL.

### ESQUEMA ERP (TABLAS DISPONIBLES):
{$tablesList}

### EJEMPLO:
Usuario: ultimas cotizaciones
SQL: SELECT * FROM quotes ORDER BY id DESC LIMIT 5;

### PETICIÓN:
Usuario: {$prompt}
SQL:";

        try {
            $url = rtrim($apiBase, '/') . '/chat/completions';

            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(60)
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $contextualPrompt],
                    ],
                    'temperature' => 0.0,
                    'stop' => ["Usuario:", "###"],
                    'stream' => false,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content') ?? $response->json('message.content') ?? $response->json('response');

                if (is_array($content)) {
                    $content = json_encode($content);
                }

                $cleanContent = (string) $content;

                // Extraer SQL de bloques de código markdown si el modelo los incluyó
                if (preg_match('/```sql\s*(.*?)\s*```/is', $cleanContent, $matches)) {
                    $cleanContent = $matches[1];
                } elseif (preg_match('/```\s*(.*?)\s*```/is', $cleanContent, $matches)) {
                    $cleanContent = $matches[1];
                }

                return trim($cleanContent);
            }

            $errorData = $response->json('error.message') ?? $response->json('error') ?? $response->body();
            $errorString = is_array($errorData) ? json_encode($errorData) : (string) $errorData;

            return "-- Error API: " . $errorString;

        } catch (\Exception $e) {
            return "-- Excepción: " . $e->getMessage();
        }
    }

    private function simulation(string $prompt): string
    {
        if (str_contains(strtolower($prompt), 'ventas')) {
            return "SELECT date_format(Date, '%Y-%m') as mes, SUM(Total) as total FROM quotes GROUP BY mes ORDER BY mes DESC LIMIT 12";
        }
        return "-- AI Orchestrator (Modo Simulación): No se detectó API Key para consulta real.";
    }
}
