<?php
require 'conexion.php'; // Tu archivo de conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y validar los datos
    $id = $_POST['id-ticket'] ?? ''; // ID del ticket
    $titulo = $_POST['titulo'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $productos = $_POST['producto'] ?? [];
    $precios = $_POST['precio'] ?? [];
    $descripcion = '';
    $total = 0;
    $iva = isset($_POST['iva']) ? 1 : 0;
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Construir la descripción y calcular el total
    for ($i = 0; $i < count($productos); $i++) {
        $producto = htmlspecialchars($productos[$i]);
        $precio = floatval($precios[$i]);
        
        // Formatear el número sin comas
        $precio_formateado = number_format($precio, 2, '.', '');
        
        $descripcion .= "Producto: $producto, Precio: $" . $precio_formateado . "\n";
        $total += $precio;
    }
    
    // Validar que todos los campos obligatorios están completos
    if (!$id || !$titulo || !$fecha || empty($productos)) {
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son obligatorios.',
            'datos' => " id= ".$id." titulo= ".$titulo." fecha= ".$fecha." descripcion= ".$descripcion." total= ".$total." IVA= ".$iva." telefono= ".$telefono." email= ".$email
        ]);
        exit;
    }

    // Consulta SQL con valores seguros
    $query = "UPDATE ticket 
              SET titulo = '$titulo', 
                  descripcion = '$descripcion', 
                  fecha = '$fecha', 
                  total = $total, 
                  iva = $iva, 
                  email = '$email', 
                  telefono = '$telefono' 
              WHERE id = $id";

    // Ejecutar la consulta
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        echo json_encode(['success' => true, 'query' => $query]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al actualizar el ticket.',
            'query' => $query,
            'error' => mysqli_error($conexion)
        ]);
    }
}
?>

