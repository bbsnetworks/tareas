<?php
require 'conexion.php';

// Verificar si se recibió el ID de la solicitud correctamente
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "ID de solicitud inválido"]);
    exit;
}

// Obtener la solicitud de la base de datos
$query = "SELECT * FROM vacaciones WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$solicitud = $result->fetch_assoc();

if (!$solicitud) {
    echo json_encode(["success" => false, "error" => "Solicitud no encontrada"]);
    exit;
}

// Devolver los datos en formato JSON
echo json_encode(["success" => true, "solicitud" => $solicitud]);
?>
