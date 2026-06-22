<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Prototipo BRIANA — Simulación Independiente</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-dark': '#0a1120',
                        'brand-surface': '#111827',
                        'brand-card': '#1f2937',
                        'steel-gray': '#4b5563',
                        'steel-light': '#9ca3af',
                        'cyan-tech': '#00f2fe',
                        'cyan-glow': 'rgba(0, 242, 254, 0.15)',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .spinner { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-brand-dark text-gray-100 min-h-screen flex flex-col justify-between">

    <!-- Topbar Dashboard -->
    <header class="bg-brand-surface border-b border-gray-800 px-8 py-4 sticky top-0 z-50 shadow-md">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-tech flex items-center justify-center font-bold text-lg text-brand-dark">
                    B
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                        BRIANA <span class="text-xs font-mono px-2 py-0.5 rounded bg-cyan-glow text-cyan-tech border border-cyan-tech/30">PROTOTIPO</span>
                    </h1>
                    <p class="text-xs text-steel-light">Ingeniería del Software II · Sandbox de Simulación Aislado</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono bg-brand-card px-3 py-1.5 rounded-lg border border-gray-700">
                <span class="w-2 h-2 rounded-full bg-cyan-tech animate-pulse"></span>
                <span class="text-gray-300">Google Gemini 1.5 Flash IA</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto w-full px-6 py-8 flex-1 flex flex-col gap-8">
        
        <!-- Alertas y Resumen -->
        <div id="alertWrap" class="hidden"></div>

        <!-- Zona Superior: Dropzone para el PDF -->
        <section class="bg-brand-card border border-gray-700 rounded-2xl p-8 text-center relative overflow-hidden transition-all duration-200" id="dropzoneContainer">
            <div id="dropzoneInitial">
                <div class="w-16 h-16 rounded-2xl bg-brand-surface border border-gray-700 flex items-center justify-center mx-auto mb-4 cursor-pointer text-cyan-tech hover:bg-cyan-glow hover:border-cyan-tech transition-colors" id="iconSelect">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                </div>
                <h3 class="text-base font-semibold text-white mb-1">Arrastra y suelta un PDF Académico aquí</h3>
                <p class="text-xs text-steel-light mb-6">Tesis, Proyecto Socio-Tecnológico o Trabajo de Grado (Máx. 20 MB)</p>
                <input type="file" id="fileInput" accept="application/pdf" class="hidden" />
                <button type="button" id="browseBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-tech hover:from-blue-500 hover:to-cyan-400 font-semibold text-xs text-brand-dark tracking-wide uppercase transition-all shadow-lg shadow-cyan-tech/10">
                    Seleccionar Archivo Local
                </button>
            </div>

            <!-- Estado de carga animado (Oculto inicialmente) -->
            <div id="dropzoneProcessing" class="hidden py-6 flex flex-col items-center justify-center gap-4">
                <div class="w-12 h-12 rounded-full border-2 border-cyan-tech/20 border-t-cyan-tech spinner"></div>
                <div>
                    <h4 class="font-semibold text-white text-sm mb-1" id="loadingTitle">Procesando Documento Académico...</h4>
                    <p class="text-xs text-steel-light" id="loadingSub">Extrayendo texto plano con Smalot y consultando a Gemini IA</p>
                </div>
            </div>
        </section>

        <!-- Zona Inferior: Formulario de los 4 Campos Obligatorios -->
        <section class="bg-brand-surface border border-gray-800 rounded-2xl p-8 flex flex-col gap-6 shadow-xl relative">
            
            <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-tech" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <h2 class="font-semibold text-sm tracking-wide text-white uppercase">Validación y Edición de Metadatos</h2>
                </div>
                <span id="formStatusTag" class="text-xs font-mono px-2.5 py-1 rounded-full bg-steel-gray text-white">Modo Edición Humana</span>
            </div>

            <form id="brianaForm" class="flex flex-col gap-5">
                
                <!-- Campo 1: Título -->
                <div class="flex flex-col gap-2">
                    <label for="titulo" class="text-xs font-semibold uppercase tracking-wider text-steel-light flex justify-between">
                        1. Título del Proyecto
                        <span class="text-[10px] font-mono text-cyan-tech">Obligatorio</span>
                    </label>
                    <input type="text" id="titulo" name="titulo" placeholder="Ej: Sistema de Gestión Automatizado..." class="w-full bg-brand-card border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-tech transition-colors placeholder:text-steel-gray" />
                </div>

                <!-- Campo 2: Autores (Array manejado en string / tags) -->
                <div class="flex flex-col gap-2">
                    <label for="autores" class="text-xs font-semibold uppercase tracking-wider text-steel-light flex justify-between">
                        2. Autores (Lista / Array)
                        <span class="text-[10px] font-mono text-steel-light">Separados por punto y coma (;)</span>
                    </label>
                    <input type="text" id="autores" name="autores" placeholder="Ej: Juan Díaz; María Pérez" class="w-full bg-brand-card border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-tech transition-colors placeholder:text-steel-gray" />
                </div>

                <!-- Columnas para 3. Tutor y 4. Año -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- Campo 3: Tutor -->
                    <div class="flex flex-col gap-2">
                        <label for="tutor" class="text-xs font-semibold uppercase tracking-wider text-steel-light">
                            3. Tutor o Director
                        </label>
                        <input type="text" id="tutor" name="tutor" placeholder="Ej: Dr. Luis Martínez" class="w-full bg-brand-card border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-tech transition-colors placeholder:text-steel-gray" />
                    </div>

                    <!-- Campo 4: Año de Publicación -->
                    <div class="flex flex-col gap-2">
                        <label for="anio" class="text-xs font-semibold uppercase tracking-wider text-steel-light">
                            4. Año de Publicación
                        </label>
                        <input type="text" id="anio" name="anio" placeholder="Ej: 2026" class="w-full bg-brand-card border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-tech transition-colors placeholder:text-steel-gray font-mono" />
                    </div>

                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800 mt-2">
                    <button type="button" id="cancelBtn" class="px-5 py-2.5 rounded-xl border border-gray-700 hover:border-steel-gray text-xs font-semibold text-gray-300 hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button type="button" id="saveBtn" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold text-xs text-white shadow-lg shadow-blue-600/20 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Guardar Simulación
                    </button>
                </div>

            </form>

        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-brand-surface border-t border-gray-800 py-6 text-center text-xs text-steel-light">
        <p>Prototipo BRIANA © Ingeniería del Software II · Arquitectura Service Pattern (Bajo Acoplamiento)</p>
    </footer>

    <!-- Lógica JavaScript (Fetch API Asíncrona + Resiliencia) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const fileInput = document.getElementById('fileInput');
            const browseBtn = document.getElementById('browseBtn');
            const iconSelect = document.getElementById('iconSelect');
            const dropzoneInitial = document.getElementById('dropzoneInitial');
            const dropzoneProcessing = document.getElementById('dropzoneProcessing');
            const dropzoneContainer = document.getElementById('dropzoneContainer');
            const alertWrap = document.getElementById('alertWrap');
            const formStatusTag = document.getElementById('formStatusTag');

            // Inputs del Formulario
            const inTitulo = document.getElementById('titulo');
            const inAutores = document.getElementById('autores');
            const inTutor = document.getElementById('tutor');
            const inAnio = document.getElementById('anio');

            // Lógica para abrir selector
            const triggerFileSelect = (e) => { e.stopPropagation(); fileInput.click(); };
            browseBtn.addEventListener('click', triggerFileSelect);
            iconSelect.addEventListener('click', triggerFileSelect);

            // Drag & Drop Eventos
            ['dragover', 'dragenter'].forEach(event => {
                dropzoneContainer.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropzoneContainer.classList.add('border-cyan-tech', 'bg-cyan-glow');
                });
            });

            ['dragleave', 'dragend'].forEach(event => {
                dropzoneContainer.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropzoneContainer.classList.remove('border-cyan-tech', 'bg-cyan-glow');
                });
            });

            dropzoneContainer.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzoneContainer.classList.remove('border-cyan-tech', 'bg-cyan-glow');
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    manejarArchivo(e.dataTransfer.files[0]);
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files[0]) {
                    manejarArchivo(fileInput.files[0]);
                }
            });

            // Función para enviar a backend y orquestar
            async function manejarArchivo(archivo) {
                if (archivo.type !== 'application/pdf') {
                    mostrarAlerta('El documento debe ser obligatoriamente un archivo PDF.', false);
                    return;
                }

                // UI Estado Carga
                dropzoneInitial.classList.add('hidden');
                dropzoneProcessing.classList.remove('hidden');
                alertWrap.classList.add('hidden');
                formStatusTag.textContent = 'Analizando con IA...';
                formStatusTag.classList.remove('bg-steel-gray', 'bg-red-900/50');
                formStatusTag.classList.add('bg-blue-600');

                // Resetea inputs
                inTitulo.value = ''; inAutores.value = ''; inTutor.value = ''; inAnio.value = '';

                const formData = new FormData();
                formData.append('documento', archivo);

                try {
                    const response = await fetch('/api/briana/procesar-pdf', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok || !data.exito) {
                        throw new Error(data.error || 'Ocurrió un error en la extracción con IA.');
                    }

                    // ÉXITO: Inyectar datos en los IDs exactos
                    const meta = data.metadatos || {};
                    inTitulo.value = meta.titulo || '';
                    inTutor.value = meta.tutor || '';
                    inAnio.value = meta.anio || '';
                    
                    // Concatena array de autores
                    if (Array.isArray(meta.autores)) {
                        inAutores.value = meta.autores.join('; ');
                    }

                    mostrarAlerta('Análisis exitoso. Verifica y realiza cualquier edición manual necesaria antes de guardar.', true);
                    formStatusTag.textContent = 'Autocompletado IA ✓';
                    formStatusTag.classList.remove('bg-blue-600');
                    formStatusTag.classList.add('bg-green-600');

                } catch (error) {
                    // RESILIENCIA: Desbloquear formulario vacío para llenado manual en caso de fallo API
                    console.error('Error capturado por resiliencia BRIANA:', error);
                    
                    mostrarAlerta('La conexión con Gemini IA falló o el PDF no tiene texto. Se ha habilitado el formulario vacío para llenado manual.', false);
                    formStatusTag.textContent = 'Modo Manual (Resiliencia)';
                    formStatusTag.classList.remove('bg-blue-600');
                    formStatusTag.classList.add('bg-red-900/80', 'border', 'border-red-500/30');
                    
                    // Inyecta valores en blanco listos para escribir
                    inTitulo.value = ''; inAutores.value = ''; inTutor.value = ''; inAnio.value = '';
                    inTitulo.focus();

                } finally {
                    // Restaura vista dropzone
                    dropzoneProcessing.classList.add('hidden');
                    dropzoneInitial.classList.remove('hidden');
                }
            }

            function mostrarAlerta(mensaje, esExito) {
                alertWrap.className = `p-4 rounded-xl border text-xs flex items-center gap-3 ${
                    esExito 
                        ? 'bg-green-950/40 border-green-500/30 text-green-300' 
                        : 'bg-red-950/40 border-red-500/30 text-red-300'
                }`;
                alertWrap.innerHTML = `
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${
                            esExito ? 'M5 13l4 4L19 7' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                        }" />
                    </svg>
                    <span>${mensaje}</span>
                `;
                alertWrap.classList.remove('hidden');
            }

            // Guardar Simulación
            document.getElementById('saveBtn').addEventListener('click', () => {
                if (!inTitulo.value.trim()) {
                    mostrarAlerta('Por favor, ingresa al menos el Título del proyecto para guardar.', false);
                    inTitulo.focus();
                    return;
                }
                mostrarAlerta('Simulación guardada exitosamente en el prototipo BRIANA.', true);
            });

            // Cancelar
            document.getElementById('cancelBtn').addEventListener('click', () => {
                inTitulo.value = ''; inAutores.value = ''; inTutor.value = ''; inAnio.value = '';
                alertWrap.classList.add('hidden');
                formStatusTag.textContent = 'Modo Edición Humana';
                formStatusTag.className = 'text-xs font-mono px-2.5 py-1 rounded-full bg-steel-gray text-white';
            });

        });
    </script>
</body>
</html>
