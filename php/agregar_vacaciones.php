<?php
require 'conexion.php';

// Obtener los datos enviados desde el frontend
$data = json_decode(file_get_contents("php://input"), true);

$idUser = $data['idUser'] ?? 0;
$inicio = $data['inicio'] ?? '';
$fin = $data['fin'] ?? '';
$tipo = $data['tipo'] ?? '';

// Validar datos
if (!is_numeric($idUser) || $idUser <= 0 || empty($inicio) || empty($fin) || empty($tipo)) {
    echo json_encode(["success" => false, "error" => "Datos no válidos"]);
    exit;
}

// Convertir fechas a formato DateTime para validaciones
$fechaInicio = new DateTime($inicio);
$fechaFin = new DateTime($fin);
$anioActual = (int) date('Y');

// Verificar que la fecha de fin no sea menor que la de inicio
if ($fechaFin < $fechaInicio) {
    echo json_encode(["success" => false, "error" => "La fecha de fin no puede ser anterior a la de inicio"]);
    exit;
}

// Contar días sin incluir domingos
$diasSolicitados = 0;
$interval = new DateInterval('P1D');
$periodo = new DatePeriod($fechaInicio, $interval, $fechaFin->modify('+1 day'));

foreach ($periodo as $fecha) {
    if ($fecha->format('N') != 7) { // Excluir domingos (N = 7 es domingo)
        $diasSolicitados++;
    }
}

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
$añosTrabajados = max(0, $fechaIngreso->diff(new DateTime())->y);

// Reiniciar días de vacaciones cada año
$diasBase = 12; // Base inicial
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

// Obtener los días ya utilizados en el año actual por tipo excluyendo domingos
$diasUsados = 0;
$query = "SELECT inicio, fin FROM vacaciones WHERE iduser = ? AND YEAR(inicio) = ? AND tipo = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("iis", $idUser, $anioActual, $tipo);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fechaInicioUsada = new DateTime($row['inicio']);
    $fechaFinUsada = new DateTime($row['fin']);
    $periodoUsado = new DatePeriod($fechaInicioUsada, $interval, $fechaFinUsada->modify('+1 day'));
    
    foreach ($periodoUsado as $fecha) {
        if ($fecha->format('N') != 7) { // Excluir domingos
            $diasUsados++;
        }
    }
}

$diasRestantes = ($tipo !== "mayor") ? max(0, $diasDisponibles[$tipo] - $diasUsados) : "Indefinido";

// Verificar si el usuario tiene suficientes días disponibles
if ($tipo !== "mayor" && $diasSolicitados > $diasRestantes) {
    echo json_encode([
        "success" => false,
        "error" => "No tienes suficientes días disponibles para este tipo de vacaciones",
        "dias" => "Días restantes: $diasRestantes, Días solicitados: $diasSolicitados",
        "debug" => [
            "diasDisponibles" => $diasDisponibles,
            "diasUsados" => $diasUsados,
            "tipo" => $tipo,
            "anioActual" => $anioActual
        ]
    ]);
    exit;
}

// Insertar en la base de datos solo si aún hay días restantes suficientes
$query = "INSERT INTO vacaciones (iduser, inicio, fin, tipo) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($query);
$stmt->bind_param("isss", $idUser, $inicio, $fin, $tipo);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Vacaciones registradas correctamente"]);
} else {
    echo json_encode(["success" => false, "error" => "Error al registrar las vacaciones"]);
}
?>


