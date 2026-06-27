<?php
include("conexion.php");

if ($conexion->connect_error) {
  http_response_code(500);
  die("Conexión fallida: " . $conexion->connect_error);
}

$mes  = isset($_POST['mes']) ? (int)$_POST['mes'] : 0;
$year = isset($_POST['year']) ? (int)$_POST['year'] : 0;

function e($v) {
  return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function catChip($cat) {
  $cat = $cat ?: 'Otros';

  $map = [
    'Cobertura' => 'bg-blue-500/20 text-blue-300 ring-1 ring-blue-500/30',
    'Instalación' => 'bg-green-500/20 text-green-300 ring-1 ring-green-500/30',
    'Reporte' => 'bg-orange-500/20 text-orange-300 ring-1 ring-orange-500/30',
    'Sin Servicio' => 'bg-red-500/20 text-red-300 ring-1 ring-red-500/30',
    'Cambio de domicilio' => 'bg-purple-500/20 text-purple-300 ring-1 ring-purple-500/30',
    'Cancelación' => 'bg-red-500/20 text-red-300 ring-1 ring-red-500/30',
    'Servicios' => 'bg-cyan-500/20 text-cyan-300 ring-1 ring-cyan-500/30',
    'Camaras' => 'bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/30',
    'Torniquetes' => 'bg-yellow-500/20 text-yellow-200 ring-1 ring-yellow-500/30',
    'Otros' => 'bg-slate-500/20 text-slate-300 ring-1 ring-slate-500/30',
  ];

  $cls  = $map[$cat] ?? $map['Otros'];
  $safe = e($cat);

  return "
    <span class='inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {$cls} whitespace-nowrap'>
      {$safe}
    </span>
  ";
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
    return "
      <span class='inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-500/20 text-slate-300 ring-1 ring-slate-500/30'>
        —
      </span>
    ";
  }

  [$label, $cls, $icon] = $map[$estado];
  $labelSafe = e($label);

  return "
    <span class='inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {$cls} whitespace-nowrap'>
      <i class='{$icon}'></i> {$labelSafe}
    </span>
  ";
}

if ($mes <= 0 || $year <= 0) {
  echo "
    <div class='p-8 text-center text-red-300'>
      Mes o año inválido.
    </div>
  ";
  $conexion->close();
  exit;
}

$sql = "SELECT 
          e.id,
          e.title,
          e.categoria,
          e.estado,
          e.start,
          e.location,
          e.comentarios,
          e.cliente,
          c.nombre AS cliente_nombre
        FROM eventos e
        LEFT JOIN clientes c 
          ON e.cliente = c.idcliente
        WHERE MONTH(e.start) = ? 
          AND YEAR(e.start) = ?
        ORDER BY e.id DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $mes, $year);
$stmt->execute();

$result = $stmt->get_result();

echo "
<table id='contratos-table' class='w-full min-w-[1150px] text-sm text-left text-slate-200 border-collapse'>
  <thead>
    <tr class='bg-[#081a30] text-slate-200 border-b border-cyan-400/10'>
      <th class='px-4 py-4 w-20'>ID</th>
      <th class='px-4 py-4 w-72'>Título</th>
      <th class='px-4 py-4 w-56'>Cliente</th>
      <th class='px-4 py-4 w-36'>Categoría</th>
      <th class='px-4 py-4 w-32'>Estado</th>
      <th class='px-4 py-4 w-44'>Inicio</th>
      <th class='px-4 py-4 w-72'>Ubicación</th>
      <th class='px-4 py-4 w-64'>Comentarios</th>
      <th class='px-4 py-4 w-36 text-center sticky right-0 bg-[#081a30]' data-no-sort='true'>Acciones</th>
    </tr>
  </thead>
  <tbody>
";

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $id = (int)$row["id"];

    $title = e($row["title"]);
    $location = e($row["location"]);
    $comentarios = e($row["comentarios"]);

    $clienteId = $row["cliente"] ?? null;
    $clienteNombre = trim($row["cliente_nombre"] ?? '');

    if ($clienteId && $clienteNombre !== '') {
      $clienteTexto = e($clienteNombre);
    } elseif ($clienteId) {
      $clienteTexto = "Cliente # " . e($clienteId);
    } else {
      $clienteTexto = "Sin cliente";
    }

    $startRaw = $row["start"] ?? '';
    $start = $startRaw ? e(date("d/m/Y H:i", strtotime($startRaw))) : "Sin fecha";

    $categoria = $row["categoria"] ?? "Otros";
    $estado = $row["estado"] ?? "";

    echo "
      <tr class='bg-[#061426] hover:bg-[#0a213c] transition border-b border-cyan-400/10'>

        <td class='px-4 py-4 font-black text-cyan-200'>
          {$id}
        </td>

        <td class='px-4 py-4'>
          <div class='truncate max-w-[280px] font-bold text-white' title='{$title}'>
            {$title}
          </div>
        </td>

        <td class='px-4 py-4'>
          <div class='truncate max-w-[220px] text-slate-200 font-semibold' title='{$clienteTexto}'>
            {$clienteTexto}
          </div>
        </td>

        <td class='px-4 py-4'>
          " . catChip($categoria) . "
        </td>

        <td class='px-4 py-4'>
          " . estadoChip($estado) . "
        </td>

        <td class='px-4 py-4 font-mono text-xs text-slate-300 whitespace-nowrap'>
          {$start}
        </td>

        <td class='px-4 py-4'>
          <div class='truncate max-w-[300px] text-slate-300' title='{$location}'>
            {$location}
          </div>
        </td>

        <td class='px-4 py-4'>
          <div class='truncate max-w-[260px] text-slate-300' title='{$comentarios}'>
            {$comentarios}
          </div>
        </td>

        <td class='px-4 py-4 text-center sticky right-0 bg-[#061426] group-hover:bg-[#0a213c]'>
          <div class='flex items-center justify-center gap-2'>

            <button
              type='button'
              class='inline-flex items-center justify-center w-10 h-10 rounded-xl bg-yellow-400/15 hover:bg-yellow-400/25 text-yellow-200 border border-yellow-300/30 transition shadow-lg shadow-yellow-950/20'
              onclick='openEditModal({$id})'
              title='Editar'>
              <i class='bi bi-pencil-square'></i>
            </button>

            <button
              type='button'
              class='inline-flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-400/15 hover:bg-cyan-400/25 text-cyan-200 border border-cyan-300/30 transition shadow-lg shadow-cyan-950/20'
              onclick='showEventModal({$id})'
              title='Ver'>
              <i class='bi bi-eye-fill'></i>
            </button>

          </div>
        </td>

      </tr>
    ";
  }
} else {
  echo "
    <tr>
      <td colspan='9' class='px-4 py-10 text-center text-slate-400'>
        <i class='bi bi-inbox text-4xl block mb-3 text-slate-500'></i>
        No se encontraron tareas para este mes.
      </td>
    </tr>
  ";
}

echo "
  </tbody>
</table>
";

$stmt->close();
$conexion->close();
?>