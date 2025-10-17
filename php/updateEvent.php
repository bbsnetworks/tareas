<?php
include('conexion.php');

$id = $_POST['id'];
$title = $_POST['title'];
$start = $_POST['start'];
$end = $_POST['end'] ?? '2000-01-01T01:01';
$location = $_POST['location'];
$estado = $_POST['estado'];
$lat = $_POST['lat'];
$lng = $_POST['lng'];
$evidencia = $_POST['evidencia'] ?? "";
$comentarios = $_POST['comentarios'] ?? "";

// Agrega código de depuración
error_log("ID: $id");
error_log("Title: $title");
error_log("Start: $start");
error_log("End: $end");
error_log("Location: $location");
error_log("Estado: $estado");
error_log("Lat: $lat");
error_log("Lng: $lng");
error_log("Evidencia: $evidencia");
error_log("Comentarios: $comentarios");

if($end==''){
    $end='2000-01-01T01:01';
}
$sql = "UPDATE eventos SET 
    title = '$title',
    start = '$start',
    end = '$end',
    location = '$location',
    estado = '$estado',
    lat = '$lat',
    lng = '$lng',
    evidencia = '$evidencia',
    comentarios = '$comentarios'
    WHERE id = $id";
//echo "Este es el script: ".$sql;
if ($conexion->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conexion->error]);
}

$conexion->close();
?>
