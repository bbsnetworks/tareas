<?php
 include_once("../php/validar_sesion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vacaciones</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <style>
    body{
        background-color: #2E3440;
    }
  </style>  
</head>
<body class="text-gray-200">

<!-- <nav class="bg-gray-800 shadow-md">
  <div class="container mx-auto px-4">
    <div class="flex justify-between items-center py-4">
      <div class="text-2xl font-bold"><img src="../img/logo-blanco-mo.png" alt="Logo" class="w-44"></div>
      <ul class="flex space-x-6">
        <li><a href="../index.php" class="hover:text-blue-400 transition">Inicio</a></li>
        <li><a href="../lista/index.php" class="hover:text-blue-400 transition">Lista</a></li>
        <li><a href="../vacaciones/index.php" class="hover:text-blue-400 transition">Vacaciones</a></li>
        <li><a href="http://b88e0bd2df17.sn.mynetname.net/menu/" class="hover:text-blue-400 transition">Salir</a></li>
      </ul>
    </div>
  </div>
</nav> -->
<?php include_once("../includes/sidebar.php");?>
<div class="container mx-auto px-4 mt-16">
  <h1 class="text-2xl font-bold my-6">Días Disponibles de Vacaciones</h1>

  <div class="bg-gray-800 p-6 rounded shadow-md">
    <label for="usuario" class="block font-bold mb-2">Selecciona un usuario:</label>
    <select id="usuario" class="w-full px-3 py-2 rounded bg-gray-700 text-white border-gray-600" onchange="mostrarDiasDisponibles()">
      <?php
        include_once("../php/conexion.php");
        $sql = "SELECT iduser, nombre, ingreso FROM users";
        $result = $conexion->query($sql);
        while ($row = $result->fetch_assoc()) {
          echo "<option value='{$row['iduser']}' data-ingreso='{$row['ingreso']}'>{$row['nombre']}</option>";
        }
      ?>
    </select>

    <div id="diasDisponibles" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
      <div class="p-4 bg-gray-700 rounded shadow"><h3 class="font-bold text-blue-400">Vacaciones</h3><p id="diasVacaciones" class="text-lg font-semibold"></p></div>
      <div class="p-4 bg-gray-700 rounded shadow"><h3 class="font-bold text-yellow-300">Permiso</h3><p id="diasPermiso" class="text-lg font-semibold"></p></div>
      <div class="p-4 bg-gray-700 rounded shadow"><h3 class="font-bold text-green-400">Boda</h3><p id="diasBoda" class="text-lg font-semibold"></p></div>
      <div class="p-4 bg-gray-700 rounded shadow"><h3 class="font-bold text-pink-400">Embarazo</h3><p id="diasEmbarazo" class="text-lg font-semibold"></p></div>
      <div class="p-4 bg-gray-700 rounded shadow"><h3 class="font-bold text-gray-300">Fuerza Mayor</h3><p id="diasMayor" class="text-lg font-semibold"></p></div>
      <div class="p-4 bg-gray-700 rounded shadow"><h3 class="font-bold text-red-400">Enfermedad</h3><p id="diasEnfermedad" class="text-lg font-semibold"></p></div>
    </div>
  </div>
</div>

<?php if ($iduser == 20): ?>
<div class="container mx-auto px-4">
  <h2 class="text-2xl font-bold my-6">Agregar Vacaciones</h2>
  <form id="vacacionesForm" class="bg-gray-800 p-6 rounded shadow-md">
    <label class="block mb-2">Fecha de Inicio:</label>
    <input type="date" id="fecha_inicio" name="fecha_inicio" class="w-full px-3 py-2 mb-4 rounded bg-gray-700 text-white border-gray-600" required>

    <label class="block mb-2">Fecha de Fin:</label>
    <input type="date" id="fecha_fin" name="fecha_fin" class="w-full px-3 py-2 mb-4 rounded bg-gray-700 text-white border-gray-600" required>

    <label class="block mb-2">Tipo:</label>
    <select id="tipo" name="tipo" class="w-full px-3 py-2 mb-4 rounded bg-gray-700 text-white border-gray-600" required>
      <option value="vacaciones">Vacaciones</option>
      <option value="permiso">Permiso</option>
      <option value="boda">Boda</option>
      <option value="mayor">Fuerza Mayor</option>
      <option value="enfermedad">Enfermedad</option>
    </select>

    <label class="block mb-2">Usuario:</label>
    <select id="user" name="user" class="w-full px-3 py-2 mb-4 rounded bg-gray-700 text-white border-gray-600" required>
      <?php
        include_once("../php/conexion.php");
        $sql = "SELECT iduser, nombre FROM users";
        $result = $conexion->query($sql);
        while ($row = $result->fetch_assoc()) {
          echo "<option value='{$row['iduser']}'>{$row['nombre']}</option>";
        }
      ?>
    </select>

    <button id="agregar" type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Agregar</button>
  </form>
</div>
<?php endif; ?>

<div class="container mx-auto px-4 mt-10">
  <label class="block mb-2">Filtrar por Mes y Año</label>
  <input type="month" id="filtroFecha" class="w-48 p-2 rounded bg-gray-700 text-white border-gray-600 mb-4" onchange="cargarSolicitudes()">

  <h2 class="text-xl font-bold mb-4">Días de Vacaciones</h2>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-700 bg-gray-800 rounded-lg shadow-md text-sm text-gray-100">
      <thead class="bg-gray-800">
        <tr>
          <th class="py-2 px-4 border-b border-gray-700">ID</th>
          <th class="py-2 px-4 border-b border-gray-700">Nombre</th>
          <th class="py-2 px-4 border-b border-gray-700">Ingreso</th>
          <th class="py-2 px-4 border-b border-gray-700">Fecha de Inicio</th>
          <th class="py-2 px-4 border-b border-gray-700">Fecha de Fin</th>
          <th class="py-2 px-4 border-b border-gray-700">Tipo</th>
          <th class="py-2 px-4 border-b border-gray-700">Estado</th>
          <th class="py-2 px-4 border-b border-gray-700">Editar</th>
          <th class="py-2 px-4 border-b border-gray-700">Eliminar</th>
        </tr>
      </thead>
      <tbody id="vacacionesTableBody">
        <!-- Datos dinámicos -->
      </tbody>
    </table>
  </div>
</div>

<script>const usuarioActual = <?php echo json_encode($iduser); ?>;</script>
<script src="../js/vacaciones.js"></script>
<script src="../js/sidebar.js"></script>
</body>
</html>

