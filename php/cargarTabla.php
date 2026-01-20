<?php
include("conexion.php");

if ($conexion->connect_error) {
  die("Conexión fallida: " . $conexion->connect_error);
}

$mes  = isset($_POST['mes']) ? (int)$_POST['mes'] : 0;
$year = isset($_POST['year']) ? (int)$_POST['year'] : 0;

$sql = "SELECT * FROM eventos 
        WHERE MONTH(start) = $mes AND YEAR(start) = $year 
        ORDER BY start DESC;";

$result = $conexion->query($sql);

function e($v) {
  return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function catChip($cat) {
  $cat = $cat ?: 'Otros';

  $map = [
    'Cobertura' => 'bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30',
    'Instalación' => 'bg-green-500/20 text-green-300 ring-1 ring-green-500/30',
    'Reporte' => 'bg-orange-500/20 text-orange-300 ring-1 ring-orange-500/30',
    'Cambio de domicilio' => 'bg-purple-500/20 text-purple-300 ring-1 ring-purple-500/30',
    'Cancelación' => 'bg-red-500/20 text-red-300 ring-1 ring-red-500/30',
    'Servicios' => 'bg-cyan-500/20 text-cyan-300 ring-1 ring-cyan-500/30',
    'Camaras' => 'bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/30',
    'Torniquetes' => 'bg-yellow-500/20 text-yellow-200 ring-1 ring-yellow-500/30',
    'Otros' => 'bg-gray-500/20 text-gray-300 ring-1 ring-gray-500/30',
  ];

  $cls  = $map[$cat] ?? $map['Otros'];
  $safe = e($cat);

  // Incluimos el texto dentro del chip para que DataTables lo encuentre
  return "<span class='inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold $cls whitespace-nowrap'>$safe</span>";
}

function estadoChip($estado) {
  $estado = $estado ?: '';

  $map = [
    'creado'    => ['Creado',    'bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30',  'bi bi-plus-circle-fill'],
    'proceso'   => ['Proceso',   'bg-yellow-500/20 text-yellow-200 ring-1 ring-yellow-500/30', 'bi bi-hammer'],
    'terminado' => ['Terminado', 'bg-green-500/20 text-green-300 ring-1 ring-green-500/30', 'bi bi-check-circle-fill'],
    'cancelado' => ['Cancelado', 'bg-red-500/20 text-red-300 ring-1 ring-red-500/30', 'bi bi-x-circle-fill'],
  ];

  if (!isset($map[$estado])) {
    return "<span class='inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-500/20 text-gray-300 ring-1 ring-gray-500/30'>—</span>";
  }

  [$label, $cls, $icon] = $map[$estado];
  $labelSafe = e($label);

  // Importante: incluye el texto para búsqueda DataTables
  return "<span class='inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold $cls whitespace-nowrap'>
            <i class='$icon'></i> $labelSafe
          </span>";
}

if ($result && $result->num_rows > 0) {
  echo "<table id='contratos-table' class='w-full text-sm text-left text-gray-200 border-separate border-spacing-y-2'>";

  echo "<thead class='sticky top-0 z-0'>
          <tr class='text-xs uppercase tracking-wider text-gray-300 bg-[#3b4252]/80 backdrop-blur border border-white/5'>
            <th class='px-4 py-3 rounded-l-xl w-16'>ID</th>
            <th class='px-4 py-3 w-64'>Título</th>
            <th class='px-4 py-3 w-36'>Categoría</th>
            <th class='px-4 py-3 w-28'>Estado</th>
            <th class='px-4 py-3 w-44'>Inicio</th>
            <th class='px-4 py-3 w-44'>Fin</th>
            <th class='px-4 py-3 w-72'>Ubicación</th>
            <th class='px-4 py-3 w-80'>Comentarios</th>
            <th class='px-4 py-3 w-20 text-center'>Editar</th>
            <th class='px-4 py-3 rounded-r-xl w-20 text-center'>Mostrar</th>
          </tr>
        </thead>";

  echo "<tbody>";

  while ($row = $result->fetch_assoc()) {
    $id = (int)$row["id"];

    $title = e($row["title"]);
    $location = e($row["location"]);
    $comentarios = e($row["comentarios"]);

    $start = e($row["start"]);
    $endRaw = $row["end"] ?? '';
    $end = (($endRaw === '2000-01-01 01:01:00') || ($endRaw === '')) ? 'Sin Fecha' : e($endRaw);

    $categoria = $row["categoria"] ?? "Otros";
    $estado = $row["estado"] ?? "";

    echo "<tr class='bg-[#2f3543] hover:bg-[#343b4a] transition border border-white/5 shadow-sm'>";

    echo "<td class='px-4 py-3 rounded-l-xl font-semibold'>$id</td>";

    echo "<td class='px-4 py-3'>
            <div class='truncate max-w-[260px]' title='$title'>$title</div>
          </td>";

    echo "<td class='px-4 py-3'>" . catChip($categoria) . "</td>";

    echo "<td class='px-4 py-3'>" . estadoChip($estado) . "</td>";

    echo "<td class='px-4 py-3 font-mono text-xs text-gray-200 whitespace-nowrap'>$start</td>";
    echo "<td class='px-4 py-3 font-mono text-xs text-gray-200 whitespace-nowrap'>$end</td>";

    echo "<td class='px-4 py-3'>
            <div class='truncate max-w-[320px]' title='$location'>$location</div>
          </td>";

    echo "<td class='px-4 py-3'>
            <div class='truncate max-w-[380px] text-gray-300' title='$comentarios'>$comentarios</div>
          </td>";

    echo "<td class='px-4 py-3 text-center'>
            <button
              class='inline-flex items-center justify-center w-10 h-10 rounded-lg bg-yellow-500/90 hover:bg-yellow-500 text-white shadow-sm'
              onclick=\"openEditModal($id)\"
              title='Editar'>
              <i class='bi bi-pencil-square'></i>
            </button>
          </td>";

    echo "<td class='px-4 py-3 rounded-r-xl text-center'>
            <button
              class='inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-800 hover:bg-gray-700 text-white shadow-sm'
              onclick=\"showEventModal($id)\"
              title='Ver'>
              <i class='bi bi-eye-fill'></i>
            </button>
          </td>";

    echo "</tr>";
  }

  echo "</tbody>";
  echo "</table>";
} else {
  echo "<div class='text-gray-300 p-4'>0 resultados</div>";
}

$conexion->close();

echo("<script>
  $('#contratos-table').DataTable({
    responsive: true,
    pageLength: 25,
    order: [], // respetar el ORDER BY del backend
    language: {
      search: 'Buscar:',
      lengthMenu: '_MENU_ por página',
      info: 'Mostrando _START_ a _END_ de _TOTAL_',
      infoEmpty: 'Sin registros',
      zeroRecords: 'No se encontraron resultados',
      paginate: { previous: '‹', next: '›' }
    }
  });
</script>");
?>
