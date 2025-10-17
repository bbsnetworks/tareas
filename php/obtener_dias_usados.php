<?php
require 'conexion.php';

// Verificar si se recibió el ID del usuario
if (!isset($_GET['idUser']) || !is_numeric($_GET['idUser'])) {
    echo json_encode(["success" => false, "error" => "ID de usuario inválido"]);
    exit;
}

$idUser = intval($_GET['idUser']);

// Obtener la fecha de ingreso del usuario
$query = "SELECT ingreso FROM users WHERE iduser = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $idUser);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
    exit;
}

$fechaIngreso = new DateTime($row['ingreso']);
$fechaActual = new DateTime();
$anioActual = $fechaActual->format('Y');

// Calcular años trabajados
$añosTrabajados = max(0, $fechaIngreso->diff($fechaActual)->y);

// Calcular los días de vacaciones anuales
$diasVacaciones = 12 + ($añosTrabajados * 2); // 14 días base + 2 por cada año cumplido

// Definir días disponibles por tipo
$diasDisponibles = [
    "vacaciones" => $diasVacaciones,
    "permiso" => 60,
    "boda" => 1,
    "mayor" => "Indefinido",
    "enfermedad" => 60
];

// **Consulta optimizada para obtener los días usados excluyendo domingos**
$query = "SELECT 
    iduser,
    COALESCE(SUM(CASE WHEN tipo = 'vacaciones' THEN 
        (SELECT COUNT(*) FROM (
            SELECT ADDDATE(v.inicio, INTERVAL t.n DAY) AS dia
            FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 
                  UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 
                  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                  UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
                  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
                  UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
                  UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21
                  UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                  UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
                  UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30) t
            WHERE t.n <= DATEDIFF(v.fin, v.inicio) 
                  AND WEEKDAY(ADDDATE(v.inicio, INTERVAL t.n DAY)) != 6
        ) AS dias_validos)
    END), 0) AS vacaciones,

    COALESCE(SUM(CASE WHEN tipo = 'permiso' THEN 
        (SELECT COUNT(*) FROM (
            SELECT ADDDATE(v.inicio, INTERVAL t.n DAY) AS dia
            FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 
                  UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 
                  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                  UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
                  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
                  UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
                  UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21
                  UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                  UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
                  UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30) t
            WHERE t.n <= DATEDIFF(v.fin, v.inicio) 
                  AND WEEKDAY(ADDDATE(v.inicio, INTERVAL t.n DAY)) != 6
        ) AS dias_validos)
    END), 0) AS permiso,

    COALESCE(SUM(CASE WHEN tipo = 'boda' THEN 
        (SELECT COUNT(*) FROM (
            SELECT ADDDATE(v.inicio, INTERVAL t.n DAY) AS dia
            FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 
                  UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 
                  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                  UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
                  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
                  UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
                  UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21
                  UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                  UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
                  UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30) t
            WHERE t.n <= DATEDIFF(v.fin, v.inicio) 
                  AND WEEKDAY(ADDDATE(v.inicio, INTERVAL t.n DAY)) != 6
        ) AS dias_validos)
    END), 0) AS boda,

    COALESCE(SUM(CASE WHEN tipo = 'enfermedad' THEN 
        (SELECT COUNT(*) FROM (
            SELECT ADDDATE(v.inicio, INTERVAL t.n DAY) AS dia
            FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 
                  UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 
                  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                  UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
                  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
                  UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
                  UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21
                  UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                  UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
                  UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30) t
            WHERE t.n <= DATEDIFF(v.fin, v.inicio) 
                  AND WEEKDAY(ADDDATE(v.inicio, INTERVAL t.n DAY)) != 6
        ) AS dias_validos)
    END), 0) AS enfermedad

FROM vacaciones v
WHERE iduser = ? AND YEAR(inicio) = ?
GROUP BY iduser;";

$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $idUser, $anioActual);
$stmt->execute();
$result = $stmt->get_result();

// Obtener resultados de días usados
$row = $result->fetch_assoc();
$diasUsados = $row ?? [
    "vacaciones" => 0,
    "permiso" => 0,
    "boda" => 0,
    "enfermedad" => 0
];

// Calcular días restantes
$diasRestantes = [];
foreach ($diasDisponibles as $tipo => $dias) {
    if ($tipo !== "mayor") {
        $diasRestantes[$tipo] = max(0, $dias - $diasUsados[$tipo]);
    } else {
        $diasRestantes[$tipo] = "Indefinido";
    }
}

// Devolver datos en JSON
echo json_encode([
    "success" => true,
    "dias_disponibles" => $diasDisponibles,
    "dias_usados" => $diasUsados,
    "dias_restantes" => $diasRestantes
]);

?>


