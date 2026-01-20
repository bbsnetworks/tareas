<?php
include('conexion.php');
header('Content-Type: application/json; charset=utf-8');

if ($conexion->connect_error) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Conexión fallida: ' . $conexion->connect_error]);
  exit;
}

// Helpers
function post($k, $default = '') {
  return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $default;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'ID inválido']);
  exit;
}

$title      = post('title');
$start      = post('start');           // viene como 2026-01-19T16:41
$end        = post('end');             // puede venir vacío
$location   = post('location');
$estado     = post('estado', 'creado');
$categoria  = post('categoria', 'Otros'); // ✅ NUEVO
$lat        = post('lat', '0');
$lng        = post('lng', '0');
$evidencia  = post('evidencia', '');
$comentarios= post('comentarios', '');

// Normalizar END: si viene vacío -> sentinel
if ($end === '') $end = '2000-01-01T01:01';

// MySQL DATETIME acepta "YYYY-mm-dd HH:ii:ss" o "YYYY-mm-dd HH:ii"
// pero desde inputs viene con "T". Lo convertimos:
$start = str_replace('T', ' ', $start);
$end   = str_replace('T', ' ', $end);

// Debug opcional (si lo quieres)
// error_log("UPDATE id=$id cat=$categoria title=$title start=$start end=$end");

$stmt = $conexion->prepare("
  UPDATE eventos SET
    title = ?,
    start = ?,
    end = ?,
    location = ?,
    estado = ?,
    categoria = ?,       -- ✅ NUEVO
    lat = ?,
    lng = ?,
    evidencia = ?,
    comentarios = ?
  WHERE id = ?
");

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conexion->error]);
  exit;
}

// Ojo: lat/lng en tu tabla son DECIMAL, los mandamos como string está OK
$stmt->bind_param(
  "ssssssssssi",
  $title,
  $start,
  $end,
  $location,
  $estado,
  $categoria,
  $lat,
  $lng,
  $evidencia,
  $comentarios,
  $id
);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conexion->close();
