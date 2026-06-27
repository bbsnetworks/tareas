<!-- sidebar.php -->
<?php
$base = '/tareas'; // Cambia esto si tu carpeta tiene otro nombre

$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function activeLink($path, $currentPath) {
  return strpos($currentPath, $path) !== false;
}
?>

<!-- BOTÓN ABRIR MENÚ -->
<button
  id="btn-sidebar"
  type="button"
  onclick="toggleSidebar()"
  class="fixed top-5 left-5 z-[60] inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-cyan-400/15 hover:bg-cyan-400/25 text-cyan-200 border border-cyan-400/30 shadow-lg shadow-cyan-950/30 backdrop-blur transition"
  title="Abrir menú"
>
  <i class="fas fa-bars text-lg"></i>
</button>

<!-- BACKDROP -->
<div
  id="sidebar-backdrop"
  class="fixed inset-0 bg-[#020b18]/75 backdrop-blur-sm z-40 hidden"
  onclick="closeSidebar()"
></div>

<!-- SIDEBAR -->
<aside
  id="sidebar"
  class="fixed top-0 left-0 w-[310px] max-w-[86vw] h-full z-50 transform -translate-x-full transition-transform duration-300 ease-out"
>
  <div class="h-full p-4">
    <div class="relative h-full overflow-hidden rounded-r-[2rem] rounded-l-2xl border border-cyan-400/20 bg-[#061a34]/95 shadow-2xl shadow-cyan-950/40">

      <!-- Glow decorativo -->
      <div class="absolute -top-24 -left-24 w-56 h-56 bg-cyan-400/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-24 -right-24 w-56 h-56 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative flex flex-col h-full">

        <!-- HEADER -->
        <div class="px-5 pt-6 pb-5 border-b border-white/10 bg-[#081a30]/60">
          <div class="flex items-start justify-between gap-3">

            <a href="<?= $base ?>/index.php" class="block">
              <img
                src="<?= $base ?>/img/logo.png"
                class="w-48 max-w-full drop-shadow-lg"
                alt="BBS Networks"
              >
            </a>

            <button
              type="button"
              onclick="closeSidebar()"
              class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-white/10 hover:bg-red-500/20 text-slate-200 hover:text-red-200 border border-white/10 hover:border-red-400/30 transition"
              title="Cerrar menú"
            >
              <i class="bi bi-x-lg"></i>
            </button>

          </div>

          <div class="mt-5">
            <p class="text-cyan-300 uppercase tracking-[0.28em] text-[11px] font-black">
              Gestión de tareas
            </p>
            <p class="text-slate-400 text-sm mt-1">
              Eventos, lista y vacaciones
            </p>
          </div>
        </div>

        <!-- NAV -->
        <nav class="relative flex-1 px-4 py-5 space-y-2">

          <a
            href="<?= $base ?>/index.php"
            onclick="closeSidebar()"
            class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 border transition
              <?= activeLink('/tareas/index.php', $currentPath) || $currentPath === '/tareas/' 
                ? 'bg-cyan-400/15 border-cyan-400/30 text-cyan-100' 
                : 'bg-white/5 border-white/10 text-slate-200 hover:bg-cyan-400/10 hover:border-cyan-400/25 hover:text-cyan-100' ?>"
          >
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-400/15 text-cyan-200 border border-cyan-400/20 group-hover:bg-cyan-400/20 transition">
              <i class="fa-solid fa-plus"></i>
            </span>

            <span>
              <span class="block font-black">Agregar evento</span>
              <span class="block text-xs text-slate-400">Crear nueva tarea</span>
            </span>
          </a>

          <a
            href="<?= $base ?>/lista/index.php"
            onclick="closeSidebar()"
            class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 border transition
              <?= activeLink('/lista/', $currentPath) 
                ? 'bg-cyan-400/15 border-cyan-400/30 text-cyan-100' 
                : 'bg-white/5 border-white/10 text-slate-200 hover:bg-cyan-400/10 hover:border-cyan-400/25 hover:text-cyan-100' ?>"
          >
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-400/15 text-blue-200 border border-blue-400/20 group-hover:bg-blue-400/20 transition">
              <i class="fa-solid fa-list-check"></i>
            </span>

            <span>
              <span class="block font-black">Lista de eventos</span>
              <span class="block text-xs text-slate-400">Buscar y administrar</span>
            </span>
          </a>

          <a
            href="<?= $base ?>/vacaciones/index.php"
            onclick="closeSidebar()"
            class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 border transition
              <?= activeLink('/vacaciones/', $currentPath) 
                ? 'bg-cyan-400/15 border-cyan-400/30 text-cyan-100' 
                : 'bg-white/5 border-white/10 text-slate-200 hover:bg-cyan-400/10 hover:border-cyan-400/25 hover:text-cyan-100' ?>"
          >
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-green-400/15 text-green-200 border border-green-400/20 group-hover:bg-green-400/20 transition">
              <i class="fa-solid fa-umbrella-beach"></i>
            </span>

            <span>
              <span class="block font-black">Vacaciones</span>
              <span class="block text-xs text-slate-400">Permisos y días disponibles</span>
            </span>
          </a>

        </nav>

        <!-- FOOTER -->
        <div class="relative px-4 py-5 border-t border-white/10 bg-[#081a30]/60">

          <a
            href="<?= $base ?>/../menu/index.php"
            class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 border bg-red-500/10 border-red-400/20 text-red-200 hover:bg-red-500/20 hover:border-red-400/30 transition"
          >
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-400/15 text-red-200 border border-red-400/20 group-hover:bg-red-400/20 transition">
              <i class="fas fa-sign-out-alt"></i>
            </span>

            <span>
              <span class="block font-black">Salir a menú</span>
              <span class="block text-xs text-red-200/70">Volver al panel principal</span>
            </span>
          </a>

        </div>

      </div>
    </div>
  </div>
</aside>