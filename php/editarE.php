<?php
include('conexion.php');
header('Content-Type: application/json; charset=utf-8');

if ($conexion->connect_error) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Conexión fallida: ' . $conexion->connect_error]);
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'ID inválido']);
  exit;
}

$stmt = $conexion->prepare("SELECT * FROM eventos WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
  $row = $res->fetch_assoc();
  echo json_encode(['success' => true, 'data' => $row]);
} else {
  echo json_encode(['success' => false, 'error' => 'No se encontró el registro.']);
}

$stmt->close();
$conexion->close();

