<?php
include 'conexion.php';
header('Content-Type: application/json'); // Asegura que la respuesta sea JSON

// Recibe los datos enviados por POST
$id = isset($_POST['id']) ? intval($_POST['id']) : null; // Asegura que sea un número entero
$estado = isset($_POST['estado']) ? mysqli_real_escape_string($conexion, $_POST['estado']) : null; // Previene inyección SQL

// Validación de datos
if ($id && $estado) {
    // Query SQL segura
    $query = "UPDATE eventos SET estado = '$estado' WHERE id = $id";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        // Envía una respuesta JSON válida
        echo json_encode(['success' => true, 'message' => 'Evento actualizado correctamente.']);
    } else {
        // Error al ejecutar la consulta
        echo json_encode([
            'success' => false, 
            'error' => 'Error al actualizar en la base de datos.',
            'sql_error' => mysqli_error($conexion) // Devuelve el error SQL para depuración
        ]);
    }
} else {
    // Datos incompletos
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}

// Cierra la conexión
mysqli_close($conexion);
?>

