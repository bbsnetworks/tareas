<?php
require 'conexion.php';

// Verificar que se ha recibido un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["success" => false, "error" => "ID de solicitud inválido"]);
    exit;
}

$id = intval($_GET['id']);

// Preparar la consulta para eliminar la solicitud
$query = "DELETE FROM vacaciones WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Solicitud eliminada correctamente"]);
} else {
    echo json_encode(["success" => false, "error" => "Error al eliminar la solicitud"]);
}
?>