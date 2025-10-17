<?php
include 'conexion.php';

// Configuración para mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que la respuesta siempre sea JSON
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET': // Obtener eventos y vacaciones
            $events = [];
        
            // Obtener eventos
            $result = $conexion->query("SELECT * FROM eventos");
            if (!$result) {
                throw new Exception("Error al obtener los eventos: " . $conexion->error);
            }
            
            while ($row = $result->fetch_assoc()) {
                // Asignar color según el estado
                switch ($row['estado']) {
                    case 'creado':
                        $row['color'] = '#3B82F6'; // Azul
                        break;
                    case 'proceso':
                        $row['color'] = '#FACC15'; // Amarillo
                        break;
                    case 'terminado':
                        $row['color'] = '#22C55E'; // Verde
                        break;
                    case 'cancelado':
                        $row['color'] = '#EF4444'; // Rojo
                        break;
                    default:
                        $row['color'] = '#6B7280'; // Gris (Por si hay un estado desconocido)
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
                    'color' => '#F97316', // Naranja (Vacaciones)
                    'estado' => 'vacaciones',
                    'tipo' => 'vacaciones',
                    'nombre' => $row['nombre'],
                ];
            }
        
            echo json_encode($events);
            break;
        

        case 'POST': // Agregar un evento
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['title']) || !isset($data['start'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Faltan datos obligatorios']);
                exit;
            }

            // Sanitizar los datos
            $title = $conexion->real_escape_string($data['title']);
            $start = $conexion->real_escape_string($data['start']);
            $end = isset($data['end']) ? $conexion->real_escape_string($data['end']) : null;
            $color = isset($data['color']) ? $conexion->real_escape_string($data['color']) : '#38bdf8';
            $location = isset($data['location']) ? $conexion->real_escape_string($data['location']) : null;
            $lat = isset($data['lat']) ? $conexion->real_escape_string($data['lat']) : 0.0;
            $lng = isset($data['lng']) ? $conexion->real_escape_string($data['lng']) : 0.0;

            // Construir consulta SQL
            $sql = $end ? 
                "INSERT INTO eventos (title, start, end, color, location, lat, lng, estado) 
                VALUES ('$title', '$start', '$end', '$color', '$location', '$lat', '$lng', 'creado')" :
                "INSERT INTO eventos (title, start, color, location, lat, lng, estado) 
                VALUES ('$title', '$start', '$color', '$location', '$lat', '$lng', 'creado')";

            // Ejecutar la consulta
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

            $id = $conexion->real_escape_string($_GET['id']);
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

            // Sanitizar los datos
            $id = $conexion->real_escape_string($data['id']);
            $title = $conexion->real_escape_string($data['title']);
            $start = $conexion->real_escape_string($data['start']);
            $end = isset($data['end']) ? $conexion->real_escape_string($data['end']) : null;
            $color = isset($data['color']) ? $conexion->real_escape_string($data['color']) : '#38bdf8';
            $location = isset($data['location']) ? $conexion->real_escape_string($data['location']) : null;
            $lat = isset($data['lat']) ? $conexion->real_escape_string($data['lat']) : 0.0;
            $lng = isset($data['lng']) ? $conexion->real_escape_string($data['lng']) : 0.0;
            $estado = isset($data['estado']) ? $conexion->real_escape_string($data['estado']) : 'creado';

            // Construir consulta SQL
            $sql = "UPDATE eventos SET 
                        title = '$title', 
                        start = '$start', 
                        end = '$end', 
                        color = '$color', 
                        location = '$location', 
                        lat = '$lat', 
                        lng = '$lng', 
                        estado = '$estado' 
                    WHERE id = $id";

            // Ejecutar la consulta
            if (!$conexion->query($sql)) {
                throw new Exception("Error al actualizar el evento: " . $conexion->error);
            }

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conexion->close();
}
?>





