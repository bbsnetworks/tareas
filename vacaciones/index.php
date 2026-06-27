<?php
include_once("../php/validar_sesion.php");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vacaciones</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
</head>

<body class="bg-[#020b18] text-white min-h-screen">

  <?php include_once("../includes/sidebar.php"); ?>

  <main class="min-h-screen px-4 sm:px-6 lg:px-10 py-8 lg:pl-28">

    <section class="max-w-7xl mx-auto">

      <!-- ENCABEZADO -->
      <div class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
        <div>
          <p class="text-cyan-300 uppercase tracking-[0.35em] text-xs font-black mb-3">
            Gestión de personal
          </p>

          <h1 class="text-4xl md:text-5xl font-black tracking-tight">
            Vacaciones
          </h1>

          <p class="text-slate-300 mt-3 text-base md:text-lg max-w-2xl">
            Consulta días disponibles, registra solicitudes y administra permisos del personal.
          </p>
        </div>

        <a href="../index.php"
          class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400/10 border border-cyan-400/30 text-cyan-200 px-5 py-3 font-bold hover:bg-cyan-400/20 transition">
          <i class="bi bi-calendar-event"></i>
          Volver a tareas
        </a>
      </div>

      <!-- PANEL DÍAS DISPONIBLES -->
      <div class="rounded-[2rem] border border-cyan-400/15 bg-[#061a34]/90 shadow-2xl shadow-cyan-950/30 overflow-hidden mb-8">

        <div class="p-5 md:p-6 border-b border-white/10 bg-[#081a30]/60">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-cyan-400/15 text-cyan-200 border border-cyan-400/20 flex items-center justify-center">
              <i class="bi bi-person-check-fill text-xl"></i>
            </div>

            <div>
              <h2 class="text-xl font-black text-white">
                Días disponibles
              </h2>
              <p class="text-sm text-slate-400">
                Selecciona un usuario para consultar su disponibilidad.
              </p>
            </div>
          </div>

          <div class="max-w-xl">
            <label for="usuario" class="block text-sm font-black text-slate-200 mb-2">
              Usuario
            </label>

            <select
              id="usuario"
              class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
            >
              <?php
              include_once("../php/conexion.php");
              $sql = "SELECT iduser, nombre, ingreso FROM users";
              $result = $conexion->query($sql);
              while ($row = $result->fetch_assoc()) {
                $iduserOption = htmlspecialchars($row['iduser'], ENT_QUOTES, 'UTF-8');
                $nombreOption = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');
                $ingresoOption = htmlspecialchars($row['ingreso'], ENT_QUOTES, 'UTF-8');

                echo "<option value='{$iduserOption}' data-ingreso='{$ingresoOption}'>{$nombreOption}</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <div id="diasDisponibles" class="p-5 md:p-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">

          <div class="rounded-3xl border border-blue-400/20 bg-[#020b18]/60 p-5 hover:bg-blue-400/5 transition">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h3 class="font-black text-blue-300">Vacaciones</h3>
                <p class="text-xs text-slate-500 mt-1">Días ordinarios</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-blue-400/15 text-blue-200 border border-blue-400/20 flex items-center justify-center">
                <i class="bi bi-suitcase-lg-fill"></i>
              </div>
            </div>
            <p id="diasVacaciones" class="text-lg font-black text-white">—</p>
          </div>

          <div class="rounded-3xl border border-yellow-400/20 bg-[#020b18]/60 p-5 hover:bg-yellow-400/5 transition">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h3 class="font-black text-yellow-200">Permiso</h3>
                <p class="text-xs text-slate-500 mt-1">Permisos autorizados</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-yellow-400/15 text-yellow-200 border border-yellow-400/20 flex items-center justify-center">
                <i class="bi bi-calendar-check-fill"></i>
              </div>
            </div>
            <p id="diasPermiso" class="text-lg font-black text-white">—</p>
          </div>

          <div class="rounded-3xl border border-green-400/20 bg-[#020b18]/60 p-5 hover:bg-green-400/5 transition">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h3 class="font-black text-green-300">Boda</h3>
                <p class="text-xs text-slate-500 mt-1">Permiso especial</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-green-400/15 text-green-200 border border-green-400/20 flex items-center justify-center">
                <i class="bi bi-heart-fill"></i>
              </div>
            </div>
            <p id="diasBoda" class="text-lg font-black text-white">—</p>
          </div>

          <div class="rounded-3xl border border-pink-400/20 bg-[#020b18]/60 p-5 hover:bg-pink-400/5 transition">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h3 class="font-black text-pink-300">Embarazo</h3>
                <p class="text-xs text-slate-500 mt-1">Licencia especial</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-pink-400/15 text-pink-200 border border-pink-400/20 flex items-center justify-center">
                <i class="bi bi-person-hearts"></i>
              </div>
            </div>
            <p id="diasEmbarazo" class="text-lg font-black text-white">—</p>
          </div>

          <div class="rounded-3xl border border-slate-400/20 bg-[#020b18]/60 p-5 hover:bg-slate-400/5 transition">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h3 class="font-black text-slate-300">Fuerza Mayor</h3>
                <p class="text-xs text-slate-500 mt-1">Caso extraordinario</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-slate-400/15 text-slate-200 border border-slate-400/20 flex items-center justify-center">
                <i class="bi bi-exclamation-triangle-fill"></i>
              </div>
            </div>
            <p id="diasMayor" class="text-lg font-black text-white">—</p>
          </div>

          <div class="rounded-3xl border border-red-400/20 bg-[#020b18]/60 p-5 hover:bg-red-400/5 transition">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h3 class="font-black text-red-300">Enfermedad</h3>
                <p class="text-xs text-slate-500 mt-1">Días por incapacidad</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-red-400/15 text-red-200 border border-red-400/20 flex items-center justify-center">
                <i class="bi bi-bandaid-fill"></i>
              </div>
            </div>
            <p id="diasEnfermedad" class="text-lg font-black text-white">—</p>
          </div>

        </div>
      </div>

      <?php if ($iduser == 20): ?>
        <!-- FORMULARIO AGREGAR -->
        <div class="rounded-[2rem] border border-cyan-400/15 bg-[#061a34]/90 shadow-2xl shadow-cyan-950/30 overflow-hidden mb-8">

          <div class="p-5 md:p-6 border-b border-white/10 bg-[#081a30]/60">
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-2xl bg-cyan-400/15 text-cyan-200 border border-cyan-400/20 flex items-center justify-center">
                <i class="bi bi-calendar-plus-fill text-xl"></i>
              </div>

              <div>
                <h2 class="text-xl font-black text-white">
                  Agregar vacaciones
                </h2>
                <p class="text-sm text-slate-400">
                  Registra un nuevo periodo o permiso para el usuario seleccionado.
                </p>
              </div>
            </div>
          </div>

          <form id="vacacionesForm" class="p-5 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end">

              <div>
                <label for="fecha_inicio" class="block text-sm font-black text-slate-200 mb-2">
                  Fecha de inicio
                </label>
                <input
                  type="date"
                  id="fecha_inicio"
                  name="fecha_inicio"
                  class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                  required
                >
              </div>

              <div>
                <label for="fecha_fin" class="block text-sm font-black text-slate-200 mb-2">
                  Fecha de fin
                </label>
                <input
                  type="date"
                  id="fecha_fin"
                  name="fecha_fin"
                  class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                  required
                >
              </div>

              <div>
                <label for="tipo" class="block text-sm font-black text-slate-200 mb-2">
                  Tipo
                </label>
                <select
                  id="tipo"
                  name="tipo"
                  class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                  required
                >
                  <option value="vacaciones">Vacaciones</option>
                  <option value="permiso">Permiso</option>
                  <option value="boda">Boda</option>
                  <option value="mayor">Fuerza Mayor</option>
                  <option value="enfermedad">Enfermedad</option>
                </select>
              </div>

              <div>
                <label for="user" class="block text-sm font-black text-slate-200 mb-2">
                  Usuario
                </label>
                <select
                  id="user"
                  name="user"
                  class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
                  required
                >
                  <?php
                  include_once("../php/conexion.php");
                  $sql = "SELECT iduser, nombre FROM users";
                  $result = $conexion->query($sql);
                  while ($row = $result->fetch_assoc()) {
                    $iduserOption = htmlspecialchars($row['iduser'], ENT_QUOTES, 'UTF-8');
                    $nombreOption = htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8');

                    echo "<option value='{$iduserOption}'>{$nombreOption}</option>";
                  }
                  ?>
                </select>
              </div>

              <button
                id="agregar"
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 text-slate-950 px-5 py-3 font-black hover:bg-cyan-300 transition shadow-lg shadow-cyan-950/30"
              >
                <i class="bi bi-plus-circle-fill"></i>
                Agregar
              </button>

            </div>
          </form>
        </div>
      <?php endif; ?>

      <!-- LISTADO -->
      <div class="rounded-[2rem] border border-cyan-400/15 bg-[#061a34]/90 shadow-2xl shadow-cyan-950/30 overflow-hidden">

        <div class="p-5 md:p-6 border-b border-white/10 bg-[#081a30]/60">
          <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

            <div>
              <div class="flex items-center gap-3 mb-2">
                <div class="w-11 h-11 rounded-2xl bg-blue-400/15 text-blue-200 border border-blue-400/20 flex items-center justify-center">
                  <i class="bi bi-list-check text-xl"></i>
                </div>

                <div>
                  <h2 class="text-xl font-black text-white">
                    Días registrados
                  </h2>
                  <p class="text-sm text-slate-400">
                    Historial de vacaciones, permisos y ausencias.
                  </p>
                </div>
              </div>
            </div>

            <div class="w-full lg:w-72">
              <label for="filtroFecha" class="block text-sm font-black text-slate-200 mb-2">
                Filtrar por mes y año
              </label>
              <input
                type="month"
                id="filtroFecha"
                class="w-full rounded-2xl bg-[#0d1525] border border-white/15 text-white px-4 py-3 outline-none focus:border-cyan-300 focus:ring-2 focus:ring-cyan-300/20 transition"
              >
            </div>

          </div>
        </div>

        <div class="p-3 md:p-5">
          <div class="overflow-x-auto rounded-3xl border border-white/10 bg-[#030d1c]">
            <table class="w-full min-w-[1050px] text-sm text-left text-slate-200 border-collapse">
              <thead>
                <tr class="bg-[#081a30] text-slate-200 border-b border-cyan-400/10">
                  <th class="px-4 py-4 w-20">ID</th>
                  <th class="px-4 py-4 w-56">Nombre</th>
                  <th class="px-4 py-4 w-36">Ingreso</th>
                  <th class="px-4 py-4 w-40">Fecha inicio</th>
                  <th class="px-4 py-4 w-40">Fecha fin</th>
                  <th class="px-4 py-4 w-36">Tipo</th>
                  <th class="px-4 py-4 w-36">Estado</th>
                  <th class="px-4 py-4 w-24 text-center">Editar</th>
                  <th class="px-4 py-4 w-24 text-center">Eliminar</th>
                </tr>
              </thead>

              <tbody id="vacacionesTableBody">
                <!-- Datos dinámicos -->
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </section>
  </main>

  <script>
    const usuarioActual = <?php echo json_encode((string)$iduser); ?>;
  </script>

  <script src="../js/vacaciones.js"></script>
  <script src="../js/sidebar.js"></script>
</body>

</html>