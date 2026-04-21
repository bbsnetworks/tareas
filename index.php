<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: ../menu/login/index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tareas</title>

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />

  <script>
    tailwind.config = {
      darkMode: 'class'
    }
  </script>

  <style>
    body {
      background:
        radial-gradient(circle at top, rgba(14, 165, 233, 0.10), transparent 32%),
        linear-gradient(180deg, #020817 0%, #071322 45%, #020617 100%);
      min-height: 100vh;
    }

    .glass-card {
      background: linear-gradient(180deg, rgba(2, 32, 71, 0.88) 0%, rgba(2, 18, 41, 0.92) 100%);
      border: 1px solid rgba(14, 165, 233, 0.22);
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
      backdrop-filter: blur(10px);
    }

    .section-head {
      background: rgba(3, 37, 76, 0.78);
      border: 1px solid rgba(14, 165, 233, 0.18);
    }

    .input-dark,
    .textarea-dark {
      width: 100%;
      border-radius: 0.9rem;
      border: 1px solid rgba(148, 163, 184, 0.22);
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(11, 18, 32, 0.96) 100%);
      color: #fff;
      padding: 0.9rem 1rem;
      outline: none;
      transition: all 0.2s ease;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    .input-dark::placeholder,
    .textarea-dark::placeholder {
      color: #94a3b8;
    }

    .input-dark:focus,
    .textarea-dark:focus {
      border-color: rgba(34, 211, 238, 0.65);
      box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.16);
    }

    .select-dark {
      width: 100%;
      border-radius: 0.9rem;
      border: 1px solid rgba(148, 163, 184, 0.22);
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(11, 18, 32, 0.96) 100%);
      color: #fff;
      padding: 0.9rem 1rem;
      outline: none;
      transition: all 0.2s ease;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image:
        linear-gradient(45deg, transparent 50%, #94a3b8 50%),
        linear-gradient(135deg, #94a3b8 50%, transparent 50%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(11, 18, 32, 0.96) 100%);
      background-position:
        calc(100% - 20px) calc(50% - 3px),
        calc(100% - 14px) calc(50% - 3px),
        0 0;
      background-size:
        6px 6px,
        6px 6px,
        100% 100%;
      background-repeat: no-repeat;
      padding-right: 3rem;
    }

    .select-dark:focus {
      border-color: rgba(34, 211, 238, 0.65);
      box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.16);
    }

    .select-dark option {
      background-color: #0f172a;
      color: #ffffff;
    }

    .btn-primary {
      background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%);
      color: #fff;
      border: 1px solid rgba(125, 211, 252, 0.28);
      box-shadow: 0 12px 30px rgba(14, 165, 233, 0.22);
    }

    .btn-primary:hover {
      filter: brightness(1.06);
      transform: translateY(-1px);
    }

    .btn-secondary {
      background: rgba(30, 41, 59, 0.88);
      color: #fff;
      border: 1px solid rgba(148, 163, 184, 0.2);
    }

    .btn-secondary:hover {
      background: rgba(51, 65, 85, 0.95);
    }

    .btn-danger {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      color: #fff;
      border: 1px solid rgba(248, 113, 113, 0.22);
    }

    .btn-danger:hover {
      filter: brightness(1.05);
    }

    .btn-base {
      border-radius: 0.9rem;
      padding: 0.85rem 1.1rem;
      font-weight: 700;
      transition: all 0.2s ease;
    }

    #calendar {
      background: linear-gradient(180deg, rgba(2, 18, 41, 0.88) 0%, rgba(3, 15, 31, 0.96) 100%);
      border: 1px solid rgba(14, 165, 233, 0.18);
      border-radius: 1.4rem;
      padding: 1rem;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.32);
    }

    .fc .fc-toolbar-title {
      color: #fff;
      font-size: 1.35rem;
      font-weight: 800;
      text-transform: capitalize;
    }

    .fc .fc-button {
      background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(11, 18, 32, 0.96) 100%) !important;
      border: 1px solid rgba(148, 163, 184, 0.2) !important;
      color: #e2e8f0 !important;
      border-radius: 0.85rem !important;
      box-shadow: none !important;
      text-transform: capitalize;
    }

    .fc .fc-button:hover,
    .fc .fc-button.fc-button-active {
      background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%) !important;
      color: #fff !important;
      border-color: rgba(125, 211, 252, 0.35) !important;
    }

    .fc-theme-standard td,
    .fc-theme-standard th,
    .fc-theme-standard .fc-scrollgrid {
      border-color: rgba(51, 65, 85, 0.65) !important;
    }

    .fc .fc-col-header-cell-cushion,
    .fc .fc-daygrid-day-number,
    .fc .fc-list-day-text,
    .fc .fc-list-day-side-text {
      color: #e2e8f0 !important;
    }

    .fc .fc-day-today {
      background: rgba(14, 165, 233, 0.08) !important;
    }

    .map-shell {
      overflow: hidden;
      border-radius: 1rem;
      border: 1px solid rgba(14, 165, 233, 0.18);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
    }

    .modal-panel {
      background: linear-gradient(180deg, rgba(2, 18, 41, 0.97) 0%, rgba(3, 15, 31, 0.98) 100%);
      border: 1px solid rgba(14, 165, 233, 0.2);
      box-shadow: 0 18px 45px rgba(0, 0, 0, 0.45);
      border-radius: 1.4rem;
    }

    .scroll-dark::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .scroll-dark::-webkit-scrollbar-thumb {
      background: rgba(71, 85, 105, 0.85);
      border-radius: 999px;
    }

    .scroll-dark::-webkit-scrollbar-track {
      background: transparent;
    }
  </style>
</head>

<body class="dark text-white">
  <?php include_once("includes/sidebar.php") ?>

  <main class="min-h-screen pl-0 md:pl-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">

      <div class="mb-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-cyan-300/90 text-sm font-semibold tracking-[0.18em] uppercase">Gestión de tareas</p>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-1">Mis tareas</h1>
            <p class="text-slate-300 mt-2 max-w-2xl">
              Administra eventos, seguimiento, evidencias y actividades del día desde una sola vista.
            </p>
          </div>

          <div class="hidden md:flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl glass-card text-sm text-slate-300">
              <span class="text-white font-semibold">Panel</span> de calendarización
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-[420px_minmax(0,1fr)] gap-6 items-start">

        <!-- Panel izquierdo -->
        <section class="glass-card rounded-[1.7rem] p-4 sm:p-5 xl:sticky xl:top-6">
          <div class="section-head rounded-[1.35rem] px-5 py-4 mb-5">
            <h2 class="text-2xl font-extrabold text-white flex items-center gap-3">
              <i class="bi bi-plus-square-fill text-cyan-300"></i>
              Nueva tarea
            </h2>
            <p class="text-slate-300 mt-1">Completa la información principal del evento o actividad.</p>
          </div>

          <form id="eventForm" class="space-y-5 relative z-10">
            <div>
              <label for="title" class="block text-sm font-semibold text-slate-200 mb-2">Título del evento</label>
              <input type="text" id="title" class="input-dark" placeholder="Ej. Instalación de equipo en oficina"
                required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2 gap-4">
              <div>
                <label for="start" class="block text-sm font-semibold text-slate-200 mb-2">Fecha de inicio</label>
                <input type="datetime-local" id="start" class="input-dark" required>
              </div>

              <div>
                <label for="end" class="block text-sm font-semibold text-slate-200 mb-2">Fecha de fin</label>
                <input type="datetime-local" id="end" class="input-dark">
              </div>
            </div>

            <div class="relative">
  <label for="clienteSearch" class="block text-sm font-semibold text-slate-200 mb-2">
    Cliente
  </label>

  <input
    type="text"
    id="clienteSearch"
    class="input-dark"
    placeholder="Buscar por nombre o número de cliente"
    autocomplete="off"
  >

  <!-- Aquí se guarda SOLO el número de cliente -->
  <input type="hidden" id="cliente" name="cliente">

  <!-- Lista de resultados -->
  <div
    id="clienteResults"
    class="hidden absolute z-50 mt-2 w-full rounded-xl border border-slate-700 bg-slate-900 shadow-2xl max-h-64 overflow-y-auto"
  ></div>
</div>

            <div>
              <label for="categoria" class="block text-sm font-semibold text-slate-200 mb-2">Categoría</label>
              <select id="categoria" class="select-dark" required>
                <option value="" selected disabled>Selecciona una categoría</option>
                <option value="Cobertura">Cobertura</option>
                <option value="Instalación">Instalación</option>
                <option value="Reporte">Reporte</option>
                <option value="Cambio de domicilio">Cambio de domicilio</option>
                <option value="Cancelación">Cancelación</option>
                <option value="Servicios">Servicios</option>
                <option value="Camaras">Camaras</option>
                <option value="Torniquetes">Torniquetes</option>
                <option value="Otros">Otros</option>
              </select>
            </div>

            <div>
              <label for="here-autocomplete" class="block text-sm font-semibold text-slate-200 mb-2">Ubicación</label>
              <input type="text" id="here-autocomplete" class="input-dark" placeholder="Selecciona una ubicación"
                readonly>
            </div>

            <div class="map-shell">
              <div id="map" class="w-full h-64"></div>
            </div>

            <input type="hidden" id="lat">
            <input type="hidden" id="lng">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <button type="submit" class="btn-base btn-primary w-full flex items-center justify-center gap-2">
                <i class="bi bi-calendar2-plus"></i>
                Agregar evento
              </button>

              <button type="button" class="btn-base btn-secondary w-full flex items-center justify-center gap-2"
                onclick="document.getElementById('eventForm').reset()">
                <i class="bi bi-arrow-counterclockwise"></i>
                Limpiar
              </button>
            </div>
          </form>
        </section>

        <!-- Panel derecho -->
        <section class="space-y-6">
          <div id="calendar" class="mt-0"></div>
        </section>
      </div>
    </div>
  </main>

  <!-- Modal evento -->
  <div id="eventModal" class="hidden fixed inset-0 z-50 flex justify-center items-center px-4 py-4">
  <div id="eventModalOverlay" class="absolute inset-0 bg-black/75 backdrop-blur-[2px]"></div>
    <div class="modal-panel w-full max-w-2xl max-h-[92vh] relative overflow-hidden flex flex-col z-10">
      <div id="eventDetails"
        class="transition-transform duration-500 transform translate-x-0 overflow-y-auto scroll-dark p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
          <div>
            <p id="idTitle" class="text-cyan-300 font-semibold tracking-wide mb-1"></p>
            <h2 class="text-2xl font-extrabold text-white flex items-center gap-3">
              <i class="bi bi-card-checklist text-cyan-300"></i>
              Detalles de la tarea
            </h2>
          </div>
          <button id="closeModal" class="w-11 h-11 rounded-xl btn-secondary flex items-center justify-center text-xl">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="grid gap-4">
          <div class="section-head rounded-2xl p-4">
            <p id="eventTitle" class="text-base text-slate-100 font-bold grid"></p>
            <p id="eventDate" class="text-sm text-slate-300 font-semibold grid mt-3"></p>
            <h2 id="eventAdress" class="text-sm font-bold text-cyan-300 mt-3"></h2>
          </div>

          <div class="map-shell">
            <div id="eventMap" class="w-full h-72"></div>
          </div>

          <div class="flex flex-col gap-4">
            <p id="eventStatus" class="text-lg text-slate-200 font-semibold"></p>
            <div class="grid gap-3 botones" id="botones"></div>
            <div class="text-center" id="botonCancelar"></div>
          </div>

          <div class="pt-2">
            <button id="closeModalButton" class="btn-base btn-primary w-full flex items-center justify-center gap-2">
              <i class="bi bi-box-arrow-left"></i>
              Cerrar
            </button>
          </div>
        </div>
      </div>

      <div id="sliderContainer"
        class="hidden transition-transform duration-500 transform translate-x-full absolute inset-0 w-full h-full modal-panel rounded-[1.4rem] overflow-y-auto scroll-dark p-6">
        <div class="h-full overflow-auto scroll-dark pr-1">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 text-cyan-300 flex items-center justify-center text-xl">
              <i class="bi bi-check2-circle"></i>
            </div>
            <div>
              <h3 class="text-xl font-extrabold text-white">Información adicional</h3>
              <p class="text-slate-300 text-sm">Completa la evidencia y comentarios para finalizar la tarea.</p>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label for="evidence" class="block text-sm font-semibold text-slate-200 mb-2">Evidencia (opcional)</label>
              <input type="file" id="evidence" capture accept="image/*" multiple class="input-dark">
            </div>

            <div>
              <label for="comments" class="block text-sm font-semibold text-slate-200 mb-2">Comentarios
                (obligatorio)</label>
              <textarea id="comments" rows="4" class="textarea-dark" placeholder="Escribe tus comentarios aquí..."
                required></textarea>
            </div>

            <div>
              <label for="fin" class="block text-sm font-semibold text-slate-200 mb-2">Fin</label>
              <input type="datetime-local" id="fin" class="input-dark">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <button id="backButton" class="btn-base btn-secondary w-full">Volver</button>
              <button id="submitSlider" class="btn-base btn-primary w-full">Enviar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal vacaciones -->
  <div id="vacationModal"
    class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-[2px] flex justify-center items-center px-4">
    <div class="modal-panel w-full max-w-md p-6 relative">
      <div class="flex items-start justify-between gap-4 mb-5">
        <div>
          <p class="text-orange-300 font-semibold tracking-wide">Información</p>
          <h2 class="text-2xl font-extrabold text-white">Detalles de vacaciones</h2>
        </div>
        <button id="closeVacationModal" class="w-11 h-11 rounded-xl btn-secondary flex items-center justify-center">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="section-head rounded-2xl p-4">
        <p id="vacationTitle" class="text-base text-slate-100 font-bold mb-2"></p>
        <p id="vacationDate" class="text-sm text-slate-300"></p>
      </div>

      <div class="flex justify-end mt-5">
        <button class="btn-base btn-primary px-6" id="closeVacationModalSecondary">Cerrar</button>
      </div>
    </div>
  </div>

  <!-- FAB -->
  <button id="openDayTasks"
    class="fixed right-6 bottom-6 z-50 w-16 h-16 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 hover:brightness-110 text-white shadow-2xl flex items-center justify-center transition transform hover:scale-105 active:scale-95 border border-cyan-300/30"
    title="Tareas del día">
    <i class="bi bi-list-task text-2xl"></i>
  </button>

  <!-- Modal tareas del día -->
  <div id="dayTasksModal" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/75 backdrop-blur-[2px]"></div>

    <div class="relative mx-auto mt-20 w-[95%] max-w-4xl">
      <div class="modal-panel overflow-hidden">
        <div class="flex items-center justify-between gap-4 p-5 border-b border-cyan-500/10">
          <div>
            <p class="text-cyan-300 font-semibold tracking-[0.16em] uppercase text-xs">Consulta rápida</p>
            <h3 class="text-2xl font-extrabold text-white">Tareas del día</h3>
            <p class="text-sm text-slate-300 mt-1" id="dayTasksSubtitle">Selecciona una fecha para ver la lista</p>
          </div>

          <button id="closeDayTasks" class="w-11 h-11 rounded-xl btn-secondary flex items-center justify-center">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="p-5 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-3 items-end">
            <div>
              <label class="block text-sm font-semibold text-slate-200 mb-2">Día</label>
              <input id="dayTasksDate" type="date" class="input-dark" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-200 mb-2">Buscar</label>
              <input id="dayTasksSearch" type="text" placeholder="Ej: reunión, llamada, Uriangato..."
                class="input-dark" />
            </div>

            <div class="flex gap-2">
              <button id="refreshDayTasks" class="btn-base btn-primary px-4 h-[54px] flex items-center justify-center">
                <i class="bi bi-arrow-repeat"></i>
              </button>
            </div>
          </div>

          <div id="dayTasksList" class="max-h-[60vh] overflow-auto pr-1 space-y-3 scroll-dark">
            <!-- aquí se inyecta la lista -->
          </div>
        </div>

        <div class="p-5 border-t border-cyan-500/10 flex justify-end gap-2">
          <button id="closeDayTasksFooter" class="btn-base btn-secondary px-6">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="js/index.js"></script>
  <script src="https://momentjs.com/downloads/moment.min.js"></script>
  <script src="js/sidebar.js"></script>

  <script>
    // segundo botón para cerrar vacaciones sin romper tu JS actual
    document.addEventListener("DOMContentLoaded", () => {
      const secondary = document.getElementById("closeVacationModalSecondary");
      const primary = document.getElementById("closeVacationModal");
      if (secondary && primary) {
        secondary.addEventListener("click", () => primary.click());
      }
    });
  </script>
</body>

</html>