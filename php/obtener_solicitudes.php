<?php
require 'conexion.php';

// Validar parámetros de entrada
$anio = isset($_GET['anio']) && is_numeric($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$mes = isset($_GET['mes']) && is_numeric($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$actual = date('Y-m-d');

//$query = "SELECT v.id, u.nombre, u.ingreso, v.inicio, v.fin, v.tipo, CASE WHEN $actual < v.inicio AND $actual < v.fin THEN '1' WHEN $actual BETWEEN v.inicio AND v.fin THEN '2' WHEN $actual > v.fin THEN '3' END AS estado FROM vacaciones v JOIN users u ON v.iduser = u.iduser WHERE YEAR(v.inicio) = ? AND MONTH(v.inicio) = ?";
$query = "SELECT v.id, v.inicio,v.fin,v.tipo,u.nombre, u.ingreso,
case
WHEN CURDATE() BETWEEN v.inicio AND v.fin THEN '2'
    WHEN CURDATE() < v.inicio THEN '1'
    WHEN CURDATE() > v.fin THEN '3'
end as estado from vacaciones v
JOIN users u ON v.iduser = u.iduser 
WHERE YEAR(v.inicio) = ? AND MONTH(v.inicio) = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $anio, $mes);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si hay resultados
$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}

// Si no hay registros, devolver un array vacío
if (empty($solicitudes)) {
    echo json_encode([]);
} else {
    echo json_encode($solicitudes);
    //echo json_encode($query);
}
?>

