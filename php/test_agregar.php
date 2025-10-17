<?php
 require 'conexion.php';

 $idUser = 7;
 $inicio = "2025-02-21";
 $fin = "2025-02-26";
 $tipo = "vacaciones";

 // Convertir fechas a formato DateTime para validaciones
$fechaInicio = new DateTime($inicio);
$fechaFin = new DateTime($fin);
$anioActual = date('Y');
echo "inicio: ".$inicio . " fin: " . $fin . " anio: " . $anioActual."<br>";

// Contar días sin incluir domingos
$diasSolicitados = 0;
$interval = new DateInterval('P1D');
$periodo = new DatePeriod($fechaInicio, $interval, $fechaFin->modify('+1 day'));

foreach ($periodo as $fecha) {
    if ($fecha->format('N') != 7) { // Excluir domingos (N = 7 es domingo)
        $diasSolicitados++;
    }
}
echo "dias solicitados: " . $diasSolicitados."<br>";
// Obtener la fecha de ingreso del usuario
$query = "SELECT ingreso FROM users WHERE iduser = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $idUser);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Usuario no encontrado";
    exit;
}
echo $row['ingreso']."<br>";

$fechaIngreso = new DateTime($row['ingreso']);
$añosTrabajados = max(0, $fechaIngreso->diff(new DateTime())->y);

echo "años trabajados: ".$añosTrabajados."<br>";

// Reiniciar días de vacaciones cada año
$diasBase = 14; // Base inicial
$diasExtra = ($añosTrabajados > 0) ? ($añosTrabajados * 2) : 0;
$diasVacacionesAnuales = $diasBase + $diasExtra;

// Cálculo de días disponibles por tipo
$diasDisponibles = [
    "vacaciones" => $diasVacacionesAnuales,
    "permiso" => 60,
    "boda" => 1,
    "mayor" => "Indefinido",
    "enfermedad" => 60
];
echo "dias disponibles: ".$diasVacacionesAnuales."<br>";

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

    COALESCE(SUM(CASE WHEN tipo = 'mayor' THEN 
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
    END), 0) AS mayor,

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

while ($row = $result->fetch_assoc()) {
    echo "iduser: ".$row['iduser']." vacaciones: ".$row['vacaciones']." permiso: ".$row['permiso']." boda: ".$row['boda']." mayor: ".$row['mayor']." enfermedad: ".$row['enfermedad']."<br>";
    $u_vacaciones = $row['vacaciones'];
    $u_permiso = $row['permiso'];
    $u_boda = $row['boda'];
    $u_mayor = $row['mayor'];
    $u_enfermedad = $row['enfermedad'];
}
$disponibles = $diasVacacionesAnuales - $u_vacaciones;
echo "dias disponibles: ".$disponibles."<br>";
?>