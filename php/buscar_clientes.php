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

/*
  Usamos prepared statement para evitar inyección SQL
  y para que la búsqueda sea más segura.
*/
$like = '%' . $q . '%';

$sql = "
    SELECT 
        c.idcliente,
        c.nombre,

        ct.ubicacion_lat,
        ct.ubicacion_lng,
        ct.ubicacion_precision,
        ct.ubicacion_fuente,
        ct.ubicacion_fecha,

        CONCAT_WS(', ',
            NULLIF(ct.calle, ''),
            NULLIF(ct.numero, ''),
            NULLIF(ct.colonia, ''),
            NULLIF(ct.municipio, ''),
            NULLIF(ct.estado, '')
        ) AS ubicacion_texto

    FROM clientes c

    LEFT JOIN contratos ct 
        ON ct.idcontrato = c.idcliente

    WHERE c.idcliente LIKE ?
       OR c.nombre LIKE ?

    ORDER BY c.nombre ASC
    LIMIT 10
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar la búsqueda de clientes'
    ]);
    exit;
}

$stmt->bind_param('ss', $like, $like);
$stmt->execute();

$result = $stmt->get_result();

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
    $tieneUbicacion = !empty($row['ubicacion_lat']) && !empty($row['ubicacion_lng']);

    $clientes[] = [
        'idcliente' => (int)$row['idcliente'],
        'nombre' => $row['nombre'],

        'ubicacion_lat' => $tieneUbicacion ? $row['ubicacion_lat'] : null,
        'ubicacion_lng' => $tieneUbicacion ? $row['ubicacion_lng'] : null,
        'ubicacion_precision' => $tieneUbicacion ? $row['ubicacion_precision'] : null,
        'ubicacion_fuente' => $tieneUbicacion ? $row['ubicacion_fuente'] : null,
        'ubicacion_fecha' => $tieneUbicacion ? $row['ubicacion_fecha'] : null,

        'ubicacion_texto' => $tieneUbicacion
            ? ($row['ubicacion_texto'] ?: 'Ubicación guardada en contrato')
            : ''
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'clientes' => $clientes
], JSON_UNESCAPED_UNICODE);