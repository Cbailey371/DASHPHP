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

        $tables = $schemaContext['tables'] ?? [];
        $tablesList = implode(', ', $tables);

        $systemPrompt = "Eres un experto en SQL para MySQL. Tu tarea es generar únicamente el código SQL SELECT a partir de la petición del usuario.
Base de datos ERP (MySQL).
Tablas disponibles: {$tablesList}.
Reglas críticas: 
1. Responde SOLO con el código SQL. 
2. NO incluyas explicaciones ni bloques de código markdown (```sql).
3. Usa solo comandos SELECT.
4. Si necesitas adivinar columnas comunes usa: id, Date, Client, Total, Status.";

        try {
            $url = rtrim($apiBase, '/') . '/chat/completions';

            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(60)
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return trim($content);
            }

            $error = $response->json('error.message') ?? $response->body();
            return "-- Error API: " . $error;

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
