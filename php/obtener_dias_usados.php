<?php
require 'conexion.php';
header('Content-Type: application/json');

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

$fechaIngresoStr = $row['ingreso'];
if (!$fechaIngresoStr) {
    echo json_encode(["success" => false, "error" => "El usuario no tiene fecha de ingreso"]);
    exit;
}

$fechaIngreso = new DateTime($fechaIngresoStr);
$fechaIngreso->setTime(0,0,0);

$fechaActual = new DateTime(date('Y-m-d'));
$fechaActual->setTime(0,0,0);

// Años cumplidos (solo años COMPLETOS)
$aniosCumplidos = max(0, $fechaIngreso->diff($fechaActual)->y);

// ✅ Regla: vacaciones hasta cumplir 1 año
$esElegibleVacaciones = ($fechaActual >= (clone $fechaIngreso)->modify('+1 year'));

// ✅ Periodo por aniversario (si ya cumplió al menos 1 año)
// periodo_inicio = último aniversario
// periodo_fin    = siguiente aniversario - 1 día
if ($esElegibleVacaciones) {
    $periodoInicio = (clone $fechaIngreso)->modify("+{$aniosCumplidos} years");
    if ($periodoInicio > $fechaActual) {
        $aniosCumplidos--;
        $periodoInicio = (clone $fechaIngreso)->modify("+{$aniosCumplidos} years");
    }
    $periodoFin = (clone $periodoInicio)->modify('+1 year')->modify('-1 day');
} else {
    // Aún no cumple 1 año: igual definimos el periodo actual para otros tipos, si quieres.
    // Aquí lo dejamos como "año calendario" para permisos/boda/enfermedad SOLO si tú lo deseas.
    // Pero como tu problema es el reinicio, lo mejor es unificar TODO al aniversario,
    // aunque no sea elegible para vacaciones.
    $periodoInicio = (clone $fechaIngreso); // desde ingreso hasta antes del 1er aniversario
    $periodoFin = (clone $fechaIngreso)->modify('+1 year')->modify('-1 day');
}

// --- Días disponibles por tipo ---
/*
  ✅ Ajuste importante:
  Tu código decía: 12 + (años * 2) pero comentabas “14 base”.
  Para que el primer año de derecho sean 12 días (al cumplir 1 año) y luego +2 por año adicional:
  - 1 año cumplido => 12
  - 2 años => 14
  - 3 años => 16
*/
$diasVacaciones = 0;
if ($esElegibleVacaciones) {
    $diasVacaciones = 12 + (max(0, $aniosCumplidos - 1) * 2);
}

$diasDisponibles = [
    "vacaciones" => $diasVacaciones,
    "permiso" => 60,
    "boda" => 1,
    "mayor" => "Indefinido",
    "enfermedad" => 60
];

// --- Rango del periodo (strings Y-m-d) ---
$periodoInicioStr = $periodoInicio->format('Y-m-d');
$periodoFinStr = $periodoFin->format('Y-m-d');

// ✅ Tabla de números 0..399 (para contar días sin límite de 30)
$nums = "
    (SELECT (u.n + (d.n*10) + (c.n*100)) AS n
     FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
           UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) u
     CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
           UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d
     CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3) c
    )
";

// ✅ Función SQL “días válidos” recortando al periodo y excluyendo domingos
// startClamp = GREATEST(v.inicio, :periodoInicio)
// endClamp   = LEAST(v.fin,    :periodoFin)
$calcDiasValidos = function($tipo) use ($nums) {
    return "
    COALESCE(SUM(
        CASE WHEN v.tipo = '$tipo' THEN
            (
                SELECT COUNT(*)
                FROM $nums t
                WHERE t.n <= DATEDIFF(
                        LEAST(v.fin, ?),
                        GREATEST(v.inicio, ?)
                    )
                  AND WEEKDAY(ADDDATE(GREATEST(v.inicio, ?), INTERVAL t.n DAY)) != 6
            )
        END
    ), 0) AS $tipo
    ";
};

// ✅ Importante: aquí filtramos solicitudes que SE TRASLAPAN con el periodo aniversario
$query = "
SELECT
    {$calcDiasValidos('vacaciones')},
    {$calcDiasValidos('permiso')},
    {$calcDiasValidos('boda')},
    {$calcDiasValidos('enfermedad')}
FROM vacaciones v
WHERE v.iduser = ?
  AND v.inicio <= ?
  AND v.fin >= ?
";

// Si quieres excluir canceladas/rechazadas, aquí es donde va:
// AND v.estado IN (1,2,3)  (según tu app) o solo aprobadas.
// (No lo pongo porque no me pasaste tu lógica de estados.)

$stmt = $conexion->prepare($query);

// Bind params:
// Por cada tipo usamos 3 placeholders (fin, inicio, inicio) = 12 placeholders (4 tipos)
// + iduser, periodoFin, periodoInicio = 3
// Total 15
$stmt->bind_param(
    str_repeat("s", 12) . "iss",
    // vacaciones
    $periodoFinStr, $periodoInicioStr, $periodoInicioStr,
    // permiso
    $periodoFinStr, $periodoInicioStr, $periodoInicioStr,
    // boda
    $periodoFinStr, $periodoInicioStr, $periodoInicioStr,
    // enfermedad
    $periodoFinStr, $periodoInicioStr, $periodoInicioStr,
    // where
    $idUser, $periodoFinStr, $periodoInicioStr
);

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$diasUsados = $row ?: [
    "vacaciones" => 0,
    "permiso" => 0,
    "boda" => 0,
    "enfermedad" => 0
];

// Calcular días restantes
$diasRestantes = [];
foreach ($diasDisponibles as $tipo => $dias) {
    if ($tipo !== "mayor") {
        $usados = isset($diasUsados[$tipo]) ? (int)$diasUsados[$tipo] : 0;
        $diasRestantes[$tipo] = max(0, (int)$dias - $usados);
    } else {
        $diasRestantes[$tipo] = "Indefinido";
    }
}

// Devolver datos en JSON (incluyo el periodo para que lo veas en debug)
echo json_encode([
    "success" => true,
    "periodo" => [
        "inicio" => $periodoInicioStr,
        "fin" => $periodoFinStr,
        "anios_cumplidos" => $aniosCumplidos,
        "elegible_vacaciones" => $esElegibleVacaciones
    ],
    "dias_disponibles" => $diasDisponibles,
    "dias_usados" => $diasUsados,
    "dias_restantes" => $diasRestantes
]);
