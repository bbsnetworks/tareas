<?php
require 'conexion.php';

// Obtener los datos enviados desde el frontend
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;
$inicio = $data['inicio'] ?? '';
$fin = $data['fin'] ?? '';
$tipo = $data['tipo'] ?? '';

// Validar datos
if (!is_numeric($id) || $id <= 0 || empty($inicio) || empty($fin) || empty($tipo)) {
    echo json_encode(["success" => false, "error" => "Datos no válidos"]);
    exit;
}

// Convertir fechas a formato DateTime para validaciones
$fechaInicio = new DateTime($inicio);
$fechaFin = new DateTime($fin);

// Contar días sin incluir domingos
$diasSolicitados = 0;
$interval = new DateInterval('P1D');
$periodo = new DatePeriod($fechaInicio, $interval, $fechaFin->modify('+1 day'));

foreach ($periodo as $fecha) {
    if ($fecha->format('N') != 7) { // Excluir domingos (N = 7 es domingo)
        $diasSolicitados++;
    }
}

// Verificar que la fecha de fin no sea menor que la de inicio
if ($fechaFin < $fechaInicio) {
    echo json_encode(["success" => false, "error" => "La fecha de fin no puede ser anterior a la de inicio"]);
    exit;
}

// Obtener los días disponibles del usuario por tipo de vacaciones
$query = "SELECT iduser FROM vacaciones WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["success" => false, "error" => "Solicitud no encontrada"]);
    exit;
}

$idUser = $row['iduser'];

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
$añosTrabajados = $fechaIngreso->diff($fechaActual)->y;

// Cálculo de días disponibles por tipo
$diasDisponibles = [
    "vacaciones" => 14 + ($añosTrabajados * 2),
    "permiso" => 60,
    "boda" => 1,
    "mayor" => "Indefinido",
    "enfermedad" => 60
];

// Obtener los días ya utilizados en el año actual por tipo
$query = "SELECT COALESCE(SUM(DATEDIFF(fin, inicio) + 1), 0) AS dias_usados 
          FROM vacaciones 
          WHERE iduser = ? AND YEAR(inicio) = YEAR(NOW()) AND tipo = ? AND id != ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("isi", $idUser, $tipo, $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$diasUsados = $row['dias_usados'] ?? 0;

$diasRestantes = ($tipo !== "mayor") ? max(0, $diasDisponibles[$tipo] - $diasUsados) : "Indefinido";

// Verificar si el usuario tiene suficientes días disponibles
if ($tipo !== "mayor" && $diasSolicitados > $diasRestantes) {
    echo json_encode(["success" => false, "error" => "No tienes suficientes días disponibles para este tipo de vacaciones"]);
    exit;
}

// Actualizar la solicitud en la base de datos
$query = "UPDATE vacaciones SET inicio = ?, fin = ?, tipo = ? WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("sssi", $inicio, $fin, $tipo, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Solicitud actualizada correctamente"]);
} else {
    echo json_encode(["success" => false, "error" => "Error al actualizar la solicitud"]);
}
?>