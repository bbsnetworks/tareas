<?php
include('conexion.php');

$id = $_POST['id'];

if ($conexion->connect_error) {
    die('Conexión fallida: ' . $conexion->connect_error);
}

$sql = "SELECT * FROM eventos WHERE id = $id";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => $row,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No se encontró el registro.',
    ]);
}

$conexion->close();
?>
