<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $comments = $_POST['comments'] ?? '';
    $fin = $_POST['fin'] ?? '';
    $uploadedFiles = [];

    // Verificar si hay archivos subidos
    if (!empty($_FILES['evidence']['name'][0])) {
        $uploadDir = '../evidencia/'; // Ruta de la carpeta de evidencia
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Crear carpeta si no existe
        }

        // Procesar cada archivo
        foreach ($_FILES['evidence']['tmp_name'] as $index => $tmpName) {
            $fileExtension = pathinfo($_FILES['evidence']['name'][$index], PATHINFO_EXTENSION);
            $fileName = $id . chr(65 + $index) . '.' . $fileExtension; // Generar nombre (ID + A, B, ...)
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                $uploadedFiles[] = $filePath; // Agregar la ruta al arreglo
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al subir los archivos.']);
                exit;
            }
        }
    }

    // Guardar los datos en la base de datos
    $filesString = implode(',', $uploadedFiles); // Convertir a cadena separada por comas
    $query = "UPDATE eventos 
              SET estado = '$estado', comentarios = '$comments', evidencia = '$filesString', end = '$fin'
              WHERE id = $id";

    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar en la base de datos.']);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
}

?>