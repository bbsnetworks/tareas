<!Doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lista Tareas BBS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/lista.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
</head>
<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: ../login/index.php");
  exit();
}
?>

<body class="bg-[#2e3440] text-white">
  <!-- <nav class="bg-[#3b4252] shadow-md">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="text-2xl font-bold"><img src="../img/logo-blanco-mo.png" alt="" class="w-44"></div>
        <div id="menu" class="md:flex md:items-center md:w-auto">
          <ul class="flex md:flex-row md:space-x-6">
            <li><a href="../index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Inicio</a></li>
            <li><a href="index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Lista</a></li>
            <li><a href="../vacaciones/index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Vacaciones</a></li>
            <li><a href="http://b88e0bd2df17.sn.mynetname.net/menu/" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Salir</a></li>
          </ul>
        </div>
      </div>
    </div>
</nav> -->
  <?php include_once("../includes/sidebar.php"); ?>
  <main class="min-h-screen bg-[#020b18] text-white px-4 sm:px-6 py-8 ">
  <section class="mx-auto">

    <!-- ENCABEZADO -->
    <div class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
      <div>
        <p class="text-cyan-300 uppercase tracking-[0.35em] text-xs font-bold mb-3">
          Gestión de tareas
        </p>

        <h1 class="text-4xl md:text-5xl font-black tracking-tight">
          Lista de tareas
        </h1>

        <p class="text-slate-300 mt-3 text-base md:text-lg max-w-2xl">
          Consulta, busca, ordena y administra las actividades registradas.
        </p>
      </div>

      <a href="../index.php"
        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400/10 border border-cyan-400/30 text-cyan-200 px-5 py-3 font-bold hover:bg-cyan-400/20 transition">
        <i class="bi bi-calendar-plus"></i>
        Nueva tarea
      </a>
    </div>

    <!-- CARD PRINCIPAL -->
    <div class="rounded-[2rem] border border-cyan-400/15 bg-[#061a34]/90 shadow-2xl shadow-cyan-950/30 overflow-hidden">

      <!-- PANEL DE FILTROS -->
      <div class="p-5 md:p-6 border-b border-white/10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

          <div class="lg:col-span-3">
            <label for="fecha" class="block text-sm font-bold text-slate-200 mb-2">
              Mes
            </label>
            <input
              type="month"
              name="fecha"
              id="fecha"
              class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
            >
          </div>

          <div class="lg:col-span-5">
            <label for="buscarTabla" class="block text-sm font-bold text-slate-200 mb-2">
              Buscar
            </label>
            <div class="relative">
              <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input
                type="text"
                id="buscarTabla"
                placeholder="Buscar por título, cliente, estado, categoría, ubicación..."
                class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white pl-11 pr-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
              >
            </div>
          </div>

          <div class="lg:col-span-2">
            <label for="registrosPorPagina" class="block text-sm font-bold text-slate-200 mb-2">
              Mostrar
            </label>
            <select
              id="registrosPorPagina"
              class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
            >
              <option value="10">10 registros</option>
              <option value="20">20 registros</option>
              <option value="50">50 registros</option>
              <option value="100">100 registros</option>
            </select>
          </div>

          <div class="lg:col-span-2">
            <button
              type="button"
              id="btnLimpiarBusqueda"
              class="w-full rounded-2xl bg-white/10 border border-white/15 text-slate-200 px-4 py-3 font-bold hover:bg-white/15 transition"
            >
              Limpiar
            </button>
          </div>

        </div>
      </div>

      <!-- INFO -->
      <div class="px-5 md:px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-white/10">
        <div id="tablaResumen" class="text-sm text-slate-300">
          Cargando registros...
        </div>

        <div class="text-xs text-slate-400">
          Da clic en un encabezado para ordenar
        </div>
      </div>

      <!-- TABLA -->
      <div class="p-3 md:p-5">
        <div class="overflow-x-auto rounded-3xl border border-white/10 bg-[#030d1c]">
          <div class="tabla min-w-full" id="tabla"></div>
        </div>

        <!-- PAGINACIÓN -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-5">
          <div id="paginacionInfo" class="text-sm text-slate-400"></div>

          <div id="paginacionTabla" class="flex flex-wrap gap-2"></div>
        </div>
      </div>

    </div>

    <div class="respuesta mt-4" id="respuesta"></div>

  </section>
</main>
<!-- MODAL VER TAREA COMPACTO -->
<div
  id="eventModal"
  class="hidden fixed inset-0 z-[9999] bg-[#020b18]/85 backdrop-blur-sm px-4 py-5 overflow-y-auto"
>
  <div class="min-h-full flex items-center justify-center">

    <div
      class="relative w-full max-w-3xl rounded-[1.7rem] border border-cyan-400/20 bg-[#061a34] shadow-2xl shadow-cyan-950/40 overflow-hidden"
    >

      <!-- Glow decorativo -->
      <div class="absolute -top-24 -right-24 w-56 h-56 bg-cyan-400/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-24 -left-24 w-56 h-56 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div id="eventDetails" class="relative">

        <!-- HEADER COMPACTO -->
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-white/10 bg-[#081a30]/80">

          <div class="min-w-0">
            <p class="text-cyan-300 uppercase tracking-[0.28em] text-[11px] font-black mb-2">
              Detalle de tarea
            </p>

            <div class="flex flex-wrap items-center gap-3 mb-3">
              <h2 id="idTitle" class="text-2xl md:text-3xl font-black text-white"></h2>
              <div id="eventStatus" class="text-sm"></div>
            </div>

            <div id="eventTitle" class="text-slate-200 text-base font-bold leading-snug"></div>
          </div>

          <button
            id="closeModal"
            type="button"
            class="shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-white/10 hover:bg-red-500/20 text-slate-200 hover:text-red-200 border border-white/10 hover:border-red-400/30 transition"
            title="Cerrar"
          >
            <i class="bi bi-x-lg"></i>
          </button>

        </div>

        <!-- BODY COMPACTO -->
        <div class="px-6 py-5 space-y-4">

          <!-- INFORMACIÓN -->
          <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

            <div class="flex items-center gap-3 mb-4">
              <div class="w-9 h-9 rounded-2xl bg-cyan-400/15 text-cyan-200 border border-cyan-400/20 flex items-center justify-center">
                <i class="bi bi-info-circle-fill"></i>
              </div>
              <h3 class="font-black text-white text-lg">Información</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

              <div>
                <p class="text-xs uppercase tracking-widest text-slate-500 font-black mb-2">
                  Categoría
                </p>
                <div id="eventCategoria" class="text-slate-200"></div>
              </div>

              <div>
                <p class="text-xs uppercase tracking-widest text-slate-500 font-black mb-2">
                  Fecha
                </p>
                <div
                  id="eventDate"
                  class="text-slate-200 font-semibold bg-white/5 border border-white/10 rounded-2xl px-4 py-3 text-sm"
                ></div>
              </div>

            </div>

            <div class="mt-4">
              <p class="text-xs uppercase tracking-widest text-slate-500 font-black mb-2">
                Ubicación
              </p>
              <h2
                id="eventAdress"
                class="text-cyan-100 font-bold bg-cyan-400/10 border border-cyan-400/20 rounded-2xl px-4 py-3 break-words text-sm"
              ></h2>
            </div>

          </div>

          <!-- MAPA -->
          <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

            <div class="flex items-center gap-3 mb-4">
              <div class="w-9 h-9 rounded-2xl bg-green-400/15 text-green-200 border border-green-400/20 flex items-center justify-center">
                <i class="bi bi-geo-alt-fill"></i>
              </div>
              <h3 class="font-black text-white text-lg">Mapa de ubicación</h3>
            </div>

            <div
              id="eventMap"
              class="w-full h-[260px] rounded-3xl overflow-hidden border border-cyan-400/20 bg-[#020b18]"
            ></div>

          </div>

          <!-- COMENTARIOS -->
          <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

            <div class="flex items-center gap-3 mb-4">
              <div class="w-9 h-9 rounded-2xl bg-blue-400/15 text-blue-200 border border-blue-400/20 flex items-center justify-center">
                <i class="bi bi-chat-left-text-fill"></i>
              </div>
              <h3 class="font-black text-white text-lg">Comentarios</h3>
            </div>

            <div
              id="comentarios"
              class="text-slate-300 leading-relaxed bg-white/5 border border-white/10 rounded-2xl p-4 min-h-[70px] text-sm"
            ></div>

          </div>

        </div>

        <!-- FOOTER COMPACTO -->
        <div class="px-6 py-4 border-t border-white/10 bg-[#081a30]/60 flex justify-end">
          <button
            id="closeModalButton"
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 text-slate-950 px-5 py-2.5 font-black hover:bg-cyan-300 transition shadow-lg shadow-cyan-950/30"
          >
            Cerrar
            <i class="bi bi-box-arrow-left"></i>
          </button>
        </div>

      </div>
    </div>
  </div>
</div>
  <!-- MODAL EDITAR TAREA -->
<div
  id="editModal"
  class="hidden fixed inset-0 z-[9999] bg-[#020b18]/90 backdrop-blur-sm px-4 py-6 overflow-y-auto"
>
  <div class="min-h-full flex items-center justify-center">

    <div
      class="relative w-full max-w-6xl rounded-[2rem] border border-cyan-400/20 bg-[#061a34] shadow-2xl shadow-cyan-950/40 overflow-hidden"
    >

      <!-- Glow decorativo -->
      <div class="absolute -top-32 -right-32 w-72 h-72 bg-cyan-400/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-32 -left-32 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <!-- HEADER -->
      <div class="relative flex items-start justify-between gap-4 p-6 md:p-8 border-b border-white/10 bg-[#081a30]/80">

        <div>
          <p class="text-cyan-300 uppercase tracking-[0.3em] text-xs font-black mb-2">
            Gestión de tareas
          </p>

          <h2 class="text-2xl md:text-3xl font-black text-white">
            Editar tarea
          </h2>

          <p class="text-slate-300 mt-2">
            Actualiza la información del evento, cliente, estado y ubicación.
          </p>
        </div>

        <button
          id="closeEdit"
          type="button"
          class="shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-white/10 hover:bg-red-500/20 text-slate-200 hover:text-red-200 border border-white/10 hover:border-red-400/30 transition"
          title="Cerrar"
        >
          <i class="bi bi-x-lg"></i>
        </button>

      </div>

      <!-- FORM -->
      <form id="editForm" enctype="multipart/form-data" class="relative">
        <input type="hidden" id="editId" name="id">

        <div class="p-6 md:p-8 grid grid-cols-1 xl:grid-cols-12 gap-6">

          <!-- COLUMNA IZQUIERDA -->
          <div class="xl:col-span-5 space-y-5">

            <!-- CARD DATOS -->
            <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-2xl bg-cyan-400/15 text-cyan-200 border border-cyan-400/20 flex items-center justify-center">
                  <i class="bi bi-pencil-square"></i>
                </div>
                <h3 class="font-black text-white text-lg">Datos principales</h3>
              </div>

              <div class="space-y-4">

                <div>
                  <label for="editTitle" class="block text-sm text-slate-200 font-black mb-2">
                    Título
                  </label>
                  <input
                    type="text"
                    id="editTitle"
                    name="title"
                    class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    placeholder="Título del evento"
                  >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="editStart" class="block text-sm text-slate-200 font-black mb-2">
                      Fecha de inicio
                    </label>
                    <input
                      type="datetime-local"
                      id="editStart"
                      name="start"
                      class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    >
                  </div>

                  <div>
                    <label for="editEnd" class="block text-sm text-slate-200 font-black mb-2">
                      Fecha de fin
                    </label>
                    <input
                      type="datetime-local"
                      id="editEnd"
                      name="end"
                      class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    >
                  </div>
                </div>

                <div class="relative">
                  <label for="editClienteSearch" class="block text-sm text-slate-200 font-black mb-2">
                    Cliente
                  </label>

                  <div class="relative">
                    <i class="bi bi-person-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input
                      type="text"
                      id="editClienteSearch"
                      class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white pl-11 pr-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                      placeholder="Buscar por nombre o número de cliente"
                      autocomplete="off"
                    >
                  </div>

                  <input type="hidden" id="editCliente" name="cliente">

                  <div
                    id="editClienteResults"
                    class="hidden absolute z-50 mt-2 w-full rounded-2xl border border-cyan-400/20 bg-[#061a34] shadow-2xl shadow-cyan-950/40 max-h-64 overflow-y-auto"
                  ></div>

                  <button
                    type="button"
                    id="clearEditCliente"
                    class="mt-3 inline-flex items-center gap-2 text-sm text-red-300 hover:text-red-200 font-bold"
                  >
                    <i class="bi bi-x-circle"></i>
                    Quitar cliente
                  </button>
                </div>

              </div>
            </div>

            <!-- CARD CLASIFICACIÓN -->
            <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-2xl bg-blue-400/15 text-blue-200 border border-blue-400/20 flex items-center justify-center">
                  <i class="bi bi-tags-fill"></i>
                </div>
                <h3 class="font-black text-white text-lg">Clasificación</h3>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                  <label for="editCategoria" class="block text-sm text-slate-200 font-black mb-2">
                    Categoría
                  </label>
                  <select
                    id="editCategoria"
                    name="categoria"
                    class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                  >
                    <option value="Cobertura">Cobertura</option>
                    <option value="Instalación">Instalación</option>
                    <option value="Reporte">Reporte</option>
                    <option value="Sin Servicio">Sin Servicio</option>
                    <option value="Cambio de domicilio">Cambio de domicilio</option>
                    <option value="Cancelación">Cancelación</option>
                    <option value="Servicios">Servicios</option>
                    <option value="Camaras">Camaras</option>
                    <option value="Torniquetes">Torniquetes</option>
                    <option value="Otros">Otros</option>
                  </select>
                </div>

                <div id="divEstado">
                  <label for="editEstado" class="block text-sm text-slate-200 font-black mb-2">
                    Estado
                  </label>
                  <select
                    id="editEstado"
                    name="estado"
                    class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                  >
                  </select>
                </div>

              </div>
            </div>

            <!-- CARD OBSERVACIONES -->
            <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-2xl bg-purple-400/15 text-purple-200 border border-purple-400/20 flex items-center justify-center">
                  <i class="bi bi-chat-left-text-fill"></i>
                </div>
                <h3 class="font-black text-white text-lg">Comentarios y evidencia</h3>
              </div>

              <div class="space-y-4">

                <div>
                  <label for="editEvidencia" class="block text-sm text-slate-200 font-black mb-2">
                    Evidencia
                  </label>
                  <input
                    type="text"
                    id="editEvidencia"
                    name="evidencia"
                    class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    placeholder="URL, folio o referencia de evidencia"
                  >
                </div>

                <div>
                  <label for="editComentarios" class="block text-sm text-slate-200 font-black mb-2">
                    Comentarios
                  </label>
                  <textarea
                    id="editComentarios"
                    name="comentarios"
                    rows="5"
                    class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition resize-none"
                    placeholder="Comentarios de la tarea"
                  ></textarea>
                </div>

              </div>
            </div>

          </div>

          <!-- COLUMNA DERECHA -->
          <div class="xl:col-span-7 space-y-5">

            <!-- CARD UBICACIÓN -->
            <div class="rounded-3xl border border-white/10 bg-[#020b18]/55 p-5">

              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-2xl bg-green-400/15 text-green-200 border border-green-400/20 flex items-center justify-center">
                  <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h3 class="font-black text-white text-lg">Ubicación</h3>
              </div>

              <div class="space-y-4">

                <div>
                  <label for="editLocation" class="block text-sm text-slate-200 font-black mb-2">
                    Dirección
                  </label>
                  <input
                    type="text"
                    id="editLocation"
                    name="location"
                    class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    placeholder="Dirección o referencia de ubicación"
                  >
                </div>

                <div>
                  <label class="block text-sm text-slate-200 font-black mb-2">
                    Mapa de ubicación
                  </label>
                  <div
                    id="editMap"
                    class="w-full h-[430px] border border-cyan-400/20 rounded-3xl overflow-hidden bg-[#020b18]"
                  ></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="editLat" class="block text-sm text-slate-200 font-black mb-2">
                      Latitud
                    </label>
                    <input
                      type="text"
                      id="editLat"
                      name="lat"
                      class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    >
                  </div>

                  <div>
                    <label for="editLng" class="block text-sm text-slate-200 font-black mb-2">
                      Longitud
                    </label>
                    <input
                      type="text"
                      id="editLng"
                      name="lng"
                      class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                    >
                  </div>
                </div>

              </div>
            </div>

          </div>

        </div>

        <!-- FOOTER -->
        <div class="relative px-6 md:px-8 py-5 border-t border-white/10 bg-[#081a30]/60 flex flex-col sm:flex-row justify-end gap-3">

          <button
            type="button"
            id="cancelEdit"
            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 border border-white/10 text-slate-200 px-6 py-3 font-black hover:bg-white/15 transition"
          >
            <i class="bi bi-x-circle"></i>
            Cancelar
          </button>

          <button
            type="submit"
            id="updateEvent"
            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 text-slate-950 px-6 py-3 font-black hover:bg-cyan-300 transition shadow-lg shadow-cyan-950/30"
          >
            <i class="bi bi-check-circle-fill"></i>
            Actualizar tarea
          </button>

        </div>

      </form>
    </div>
  </div>
</div>
  <script src="https://momentjs.com/downloads/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="../js/table.js"></script>
  <script src="../js/sidebar.js"></script>

</body>

</html>