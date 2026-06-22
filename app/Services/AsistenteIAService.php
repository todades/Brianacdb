<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AsistenteIAService
{
    /**
     * Envía el texto extraído a Google Gemini y devuelve los 4 metadatos solicitados.
     *
     * @param string $texto Texto plano extraído de las primeras páginas del PDF
     * @return array Array asociativo con: titulo, autores, tutor, anio
     * @throws Exception Si la API falla o la respuesta JSON es inválida
     */
    public function extraerMetadatos(string $texto): array
    {
        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            throw new Exception('La API Key de Google Gemini no está configurada.');
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        $systemPrompt = "Actúa como un extractor especializado de metadatos académicos para el sistema BRIANA. " .
            "Tu única tarea es analizar el texto plano proporcionado y devolver UNICAMENTE un objeto JSON estricto con las siguientes 4 claves exactas:\n" .
            "{\n" .
            "  \"titulo\": \"string con el título o null\",\n" .
            "  \"autores\": [\"string con nombre de autor\"],\n" .
            "  \"tutor\": \"string con el nombre del tutor/director o null\",\n" .
            "  \"anio\": \"string con el año de publicación o null\"\n" .
            "}\n" .
            "Reglas de extracción:\n" .
            "1. Si no detectas un dato, devuélvelo como null (o un array vacío [] para autores).\n" .
            "2. El JSON debe ser puro y parseable directamente. No incluyas formato markdown (como ```json) ni texto explicativo.";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nTexto extraído del documento:\n" . $texto]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.1,
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($endpoint, $payload);

        if (!$response->successful()) {
            Log::error('Falla en la API de Google Gemini (BRIANA)', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new Exception('Error de conexión con la API de Google Gemini: ' . $response->status());
        }

        $jsonContenido = $response->json('candidates.0.content.parts.0.text');

        if (empty($jsonContenido)) {
            throw new Exception('Google Gemini devolvió una respuesta vacía.');
        }

        $data = json_decode($jsonContenido, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('El resultado devuelto por la IA no es un JSON válido.');
        }

        return [
            'titulo' => $data['titulo'] ?? null,
            'autores' => is_array($data['autores'] ?? null) ? $data['autores'] : [],
            'tutor' => $data['tutor'] ?? null,
            'anio' => $data['anio'] ?? null,
        ];
    }
}
