<?php

namespace App\Http\Controllers;

use App\Services\AsistenteIAService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Exception;

class SimulacionController extends Controller
{
    protected AsistenteIAService $iaService;

    public function __construct(AsistenteIAService $iaService)
    {
        $this->iaService = $iaService;
    }

    /**
     * Procesa un PDF subido, extrae el texto de las primeras páginas y consulta a Google Gemini.
     */
    public function procesarPDF(Request $request): JsonResponse
    {
        $request->validate([
            'documento' => 'required|mimes:pdf|max:20480' // Máx 20 MB
        ]);

        try {
            $archivo = $request->file('documento');

            // 1. Extracción local del texto con Smalot\PdfParser
            $parser = new Parser();
            $pdf = $parser->parseFile($archivo->getPathname());
            
            // Extraer solo las primeras 5 páginas para eficiencia y límite de tokens
            $paginas = $pdf->getPages();
            $textoPlano = '';
            $limite = min(count($paginas), 5);
            
            for ($i = 0; $i < $limite; $i++) {
                $textoPlano .= $paginas[$i]->getText() . "\n\n";
            }

            if (empty(trim($textoPlano))) {
                throw new Exception('No se pudo extraer texto del PDF o el documento está escaneado como imagen.');
            }

            // Truncar si excede un largo máximo preventivo
            $textoTruncado = substr($textoPlano, 0, 6000);

            // 2. Consulta al servicio de IA aislado
            $metadatos = $this->iaService->extraerMetadatos($textoTruncado);

            return response()->json([
                'exito' => true,
                'mensaje' => 'Metadatos extraídos exitosamente por BRIANA IA',
                'metadatos' => $metadatos,
                'detalles' => [
                    'archivo' => $archivo->getClientOriginalName(),
                    'paginas_analizadas' => $limite,
                    'caracteres_extraidos' => strlen($textoTruncado)
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error en Prototipo BRIANA al procesar PDF', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'exito' => false,
                'error' => $e->getMessage(),
                // Devuelve datos vacíos para activar la resiliencia en el frontend y permitir llenado manual
                'metadatos' => [
                    'titulo' => null,
                    'autores' => [],
                    'tutor' => null,
                    'anio' => null
                ]
            ], 422);
        }
    }
}
