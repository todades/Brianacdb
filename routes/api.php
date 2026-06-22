<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimulacionController;

// Ruta REST para el Prototipo BRIANA — Simulación Independiente
Route::post('/briana/procesar-pdf', [SimulacionController::class, 'procesarPDF']);
