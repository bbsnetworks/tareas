<?php 
include 'conexion.php';
header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'success' => false, 
        'error' => 'El ID no se recibió o está vacío.',
    ]);
    exit;
}

$id = (int) $_POST['id'];

$query = "SELECT * FROM eventos WHERE id = $id LIMIT 1";
$result = $conexion->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    echo json_encode([
        'success'     => true,
        'id'          => $row['id'],
        'titulo'      => $row['title'],
        'inicio'      => $row['start'],
        'fin'         => $row['end'],
        'ubicacion'   => $row['location'],
        'estado'      => $row['estado'],
        'categoria'   => $row['categoria'] ?: 'Otros', // ✅ NUEVO
        'lat'         => $row['lat'],
        'lng'         => $row['lng'],
        'evidencia'   => $row['evidencia'],
        'comentarios' => $row['comentarios'],
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'error'   => 'Evento no encontrado.',
    ]);
}

$conexion->close();
