<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([
        'success' => true,
        'clientes' => []
    ]);
    exit;
}

$q = $conexion->real_escape_string($q);

$sql = "
    SELECT idcliente, nombre
    FROM clientes
    WHERE idcliente LIKE '%$q%'
       OR nombre LIKE '%$q%'
    ORDER BY nombre ASC
    LIMIT 10
";

$result = $conexion->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al buscar clientes'
    ]);
    exit;
}

$clientes = [];

while ($row = $result->fetch_assoc()) {
    $clientes[] = [
        'idcliente' => (int)$row['idcliente'],
        'nombre' => $row['nombre']
    ];
}

echo json_encode([
    'success' => true,
    'clientes' => $clientes
]);