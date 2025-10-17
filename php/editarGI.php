<?php
include('conexion.php');

$idticket = $_POST['id'];

if ($conexion->connect_error) {
    die('Conexión fallida: ' . $conexion->connect_error);
}

$sql = 'SELECT * FROM ticket WHERE id=' . $idticket;
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $descripcion = $row['descripcion'];
        $lineas = explode("\n", trim($descripcion)); // Divide las líneas
        echo '<form id="editForm" enctype="multipart/form-data">';
        echo '<div class="row">';
        echo '<div class="col-12 id d-none">
                <label class="form-label" for="id">ID:</label>
                <input class="form-control" type="number" id="id-ticket" name="id-ticket" value=' . htmlspecialchars($row['id']) . ' required>
              </div>';
        // Campo de Título
        echo '<div class="col-12 col-lg-6 titulo">
                <label class="form-label" for="titulo">Título:</label>
                <input class="form-control" type="text" id="titulo" name="titulo" value="' . htmlspecialchars($row['titulo']) . '" required>
              </div>';

        // Campo de Fecha
        echo '<div class="col-12 col-lg-6 fecha">
                <label class="form-label" for="fecha">Fecha:</label>
                <input class="form-control" type="date" id="fecha" name="fecha" value="' . htmlspecialchars($row['fecha']) . '" required>
              </div>';
        echo '<div class="col-12 col-lg-6 email">
                <label class="form-label" for="titulo">Email:</label>
                <input class="form-control" type="mail" id="email" name="email" value="'. htmlspecialchars($row['email']) .'">
             </div>';
        echo '<div class="col-12 col-lg-6 telefono">
                <label class="form-label" for="telefono">teléfono:</label>
                <input class="form-control" type="text" id="telefono" name="telefono" value="'. htmlspecialchars($row['telefono']) .'">
             </div>';  
        if($row['iva']==0){
            echo '<div class="col-12">
                            <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="iva" id="flexCheckDefault">
                            <label class="form-check-label" for="flexCheckDefault">
                                <span>Agregar IVA</span><i class="bi bi-coin"></i>
                            </label>
                        </div>
                    </div>';
        }else if($row['iva']==1){
            echo '<div class="col-12">
                            <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="iva" id="flexCheckDefault" checked>
                            <label class="form-check-label" for="flexCheckDefault">
                                <span>Agregar IVA</span><i class="bi bi-coin"></i>
                            </label>
                        </div>
                    </div>';
        }              
        echo '<div class="col-12 col-lg-6 total">
                <label class="form-label" for="total">Total:</label>
                <input class="form-control" type="number" id="total" name="total" value="' . htmlspecialchars($row['total']) . '" disabled>
              </div>';
            
        // Contenedor de Productos y Precios
        echo '<div class="col-12">
                <div id="compraContainer" class="compraContainer">';
        
        // Generar inputs dinámicos
        foreach ($lineas as $linea) {
            if (trim($linea) === "") continue; // Saltar líneas vacías
        
            // Separar el producto y el precio
            preg_match('/Producto: (.*), Precio: \$([0-9,]+\.\d{2})/', $linea, $matches);
            $producto = isset($matches[1]) ? htmlspecialchars($matches[1]) : '';
            $precio = isset($matches[2]) ? str_replace(',', '', htmlspecialchars($matches[2])) : '';
        
            echo '<div class="compra-item row">
                    <div class="col-12 col-lg-5 centrar input-p">
                        <input class="form-control" type="text" name="producto[]" value="' . $producto . '" placeholder="Producto" required>
                    </div>
                    <div class="col-12 col-lg-5 centrar input-p">
                        <input class="form-control" type="number" name="precio[]" value="' . $precio . '" placeholder="Precio" required step="0.01" onchange="calcularTotal()">
                    </div>
                    <div class="col-12 col-lg-1 centrar trash">
                        <button type="button" class="btn btn-danger" onclick="eliminarCompra(this)">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                  </div>';
        }
        

        echo '</div>
        <div class="col-12 text-center my-3">
                    <button type="button" class="btn btn-primary" onclick="agregarCompra()">Agregar Campo</button>
                  </div>'; // Cierra compraContainer
        echo '</div>'; // Cierra columna de 12

        // Botón de enviar
        echo '<div class="col-12 centrar enviar">
                <button type="button" class="btn" onclick="enviarFormulario()">Actualizar</button>
              </div>';

        echo '</div>'; // Cierra fila
        echo '</form>';
    }
} else {
    echo '<p>No se encontraron datos para el ticket seleccionado.</p>';
}

$conexion->close();

?>