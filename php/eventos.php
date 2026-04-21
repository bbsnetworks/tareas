<?php
include 'conexion.php';

// Configuración para mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que la respuesta siempre sea JSON
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Categorías permitidas
$categoriasPermitidas = [
    "Cobertura",
    "Instalación",
    "Reporte",
    "Cambio de domicilio",
    "Cancelación",
    "Servicios",
    "Camaras",
    "Torniquetes",
    "Otros"
];

try {
    switch ($method) {

        case 'GET': // Obtener eventos y vacaciones
            $events = [];

            // Obtener eventos
            $result = $conexion->query("
            SELECT 
            e.*,
            c.nombre AS cliente_nombre
            FROM eventos e
            LEFT JOIN clientes c ON e.cliente = c.idcliente
            ");
            if (!$result) {
                throw new Exception("Error al obtener los eventos: " . $conexion->error);
            }

            while ($row = $result->fetch_assoc()) {
                // Asignar color según el estado (visual)
                switch ($row['estado']) {
                    case 'creado':
                        $row['color'] = '#3B82F6';
                        break; // Azul
                    case 'proceso':
                        $row['color'] = '#FACC15';
                        break; // Amarillo
                    case 'terminado':
                        $row['color'] = '#22C55E';
                        break; // Verde
                    case 'cancelado':
                        $row['color'] = '#EF4444';
                        break; // Rojo
                    default:
                        $row['color'] = '#6B7280';
                        break; // Gris
                }
                if (!isset($row['cliente']) || $row['cliente'] === '') {
                $row['cliente'] = null;
                } else {
                $row['cliente'] = (int)$row['cliente'];
                }

                if (!isset($row['cliente_nombre']) || $row['cliente_nombre'] === '') {
                $row['cliente_nombre'] = null;
}
                // Asegurar categoría en registros viejos
                if (!isset($row['categoria']) || $row['categoria'] === null || $row['categoria'] === '') {
                    $row['categoria'] = 'Otros';
                }

                // Asegurar cliente como null si viene vacío
                if (!isset($row['cliente']) || $row['cliente'] === '') {
                    $row['cliente'] = null;
                } else {
                    $row['cliente'] = (int)$row['cliente'];
                }

                $row['tipo'] = 'evento';
                $events[] = $row;
            }

            // Obtener vacaciones
            $result = $conexion->query("SELECT v.id, v.iduser, v.inicio, v.fin, v.tipo, u.nombre
                                        FROM vacaciones v
                                        JOIN users u ON v.iduser = u.iduser");
            if (!$result) {
                throw new Exception("Error al obtener las vacaciones: " . $conexion->error);
            }

            while ($row = $result->fetch_assoc()) {
                $events[] = [
                    'id' => 'vac_' . $row['id'],
                    'title' => 'Vacaciones de ' . $row['nombre'],
                    'start' => $row['inicio'],
                    'end' => $row['fin'],
                    'color' => '#F97316',
                    'estado' => 'vacaciones',
                    'tipo' => 'vacaciones',
                    'nombre' => $row['nombre'],
                    'categoria' => 'Vacaciones',
                    'cliente' => null
                ];
            }

            echo json_encode($events);
            break;


        case 'POST': // Agregar un evento
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['title']) || !isset($data['start']) || !isset($data['categoria'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Faltan datos obligatorios']);
                exit;
            }

            // Sanitizar datos
            $title = $conexion->real_escape_string($data['title']);
            $start = $conexion->real_escape_string($data['start']);
            $end = isset($data['end']) && $data['end'] !== '' ? $conexion->real_escape_string($data['end']) : null;
            $color = '#38bdf8';

            if (!in_array($data['categoria'], $categoriasPermitidas, true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Categoría inválida']);
                exit;
            }
            $categoria = $conexion->real_escape_string($data['categoria']);

            $location = isset($data['location']) && $data['location'] !== ''
                ? $conexion->real_escape_string($data['location'])
                : null;

            $lat = isset($data['lat']) && $data['lat'] !== '' ? $conexion->real_escape_string($data['lat']) : 0.0;
            $lng = isset($data['lng']) && $data['lng'] !== '' ? $conexion->real_escape_string($data['lng']) : 0.0;

            // Nuevo campo cliente (INT NULL)
            $cliente = (isset($data['cliente']) && $data['cliente'] !== '' && $data['cliente'] !== null)
                ? (int)$data['cliente']
                : null;

            $endValue = $end ? "'$end'" : "NULL";
            $locationValue = $location !== null ? "'$location'" : "NULL";
            $clienteValue = $cliente !== null ? $cliente : "NULL";

            if ($end !== null) {
                $sql = "INSERT INTO eventos (title, start, end, color, location, lat, lng, estado, categoria, cliente)
                        VALUES ('$title', '$start', $endValue, '$color', $locationValue, '$lat', '$lng', 'creado', '$categoria', $clienteValue)";
            } else {
                $sql = "INSERT INTO eventos (title, start, color, location, lat, lng, estado, categoria, cliente)
                        VALUES ('$title', '$start', '$color', $locationValue, '$lat', '$lng', 'creado', '$categoria', $clienteValue)";
            }

            if (!$conexion->query($sql)) {
                throw new Exception("Error al guardar el evento: " . $conexion->error);
            }

            echo json_encode(['success' => true]);
            break;


        case 'DELETE': // Eliminar un evento
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID del evento no proporcionado']);
                exit;
            }

            $id = (int)$_GET['id'];
            $sql = "DELETE FROM eventos WHERE id = $id";

            if (!$conexion->query($sql)) {
                throw new Exception("Error al eliminar el evento: " . $conexion->error);
            }

            echo json_encode(['success' => true]);
            break;


        case 'PUT': // Editar un evento
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['id']) || !isset($data['title']) || !isset($data['start'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Faltan datos obligatorios']);
                exit;
            }

            $id = (int)$data['id'];
            $title = $conexion->real_escape_string($data['title']);
            $start = $conexion->real_escape_string($data['start']);
            $end = isset($data['end']) && $data['end'] !== '' ? $conexion->real_escape_string($data['end']) : null;
            $color = '#38bdf8';

            $location = isset($data['location']) && $data['location'] !== ''
                ? $conexion->real_escape_string($data['location'])
                : null;

            $lat = isset($data['lat']) && $data['lat'] !== '' ? $conexion->real_escape_string($data['lat']) : 0.0;
            $lng = isset($data['lng']) && $data['lng'] !== '' ? $conexion->real_escape_string($data['lng']) : 0.0;
            $estado = isset($data['estado']) ? $conexion->real_escape_string($data['estado']) : 'creado';

            // Nuevo campo cliente (INT NULL)
            $cliente = (isset($data['cliente']) && $data['cliente'] !== '' && $data['cliente'] !== null)
                ? (int)$data['cliente']
                : null;

            // Categoría opcional en update
            $categoriaSql = "";
            if (isset($data['categoria']) && $data['categoria'] !== '') {
                if (!in_array($data['categoria'], $categoriasPermitidas, true)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Categoría inválida']);
                    exit;
                }

                $categoria = $conexion->real_escape_string($data['categoria']);
                $categoriaSql = ", categoria = '$categoria'";
            }

            $endValue = $end ? "'$end'" : "NULL";
            $locationValue = $location !== null ? "'$location'" : "NULL";
            $clienteValue = $cliente !== null ? $cliente : "NULL";

            $sql = "UPDATE eventos SET
                        title = '$title',
                        start = '$start',
                        end = $endValue,
                        color = '$color',
                        location = $locationValue,
                        lat = '$lat',
                        lng = '$lng',
                        estado = '$estado',
                        cliente = $clienteValue
                        $categoriaSql
                    WHERE id = $id";

            if (!$conexion->query($sql)) {
                throw new Exception("Error al actualizar el evento: " . $conexion->error);
            }

            echo json_encode(['success' => true]);
            break;


        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conexion->close();
}
?>
