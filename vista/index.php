<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../menu/login/index.php");
    exit();
}

//echo "Bienvenido, " . $_SESSION['username'];
?>
<body>

<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <!-- Logo -->
        <div class="text-2xl font-bold text-gray-800"><img src="../img/logo-blanco-mo.png" alt="" class="w-44"></div>

        

        <!-- Menú -->
        <div id="menu" class="md:flex md:items-center md:w-auto">
          <ul class="flex md:flex-row md:space-x-6">
            <li>
              <a href="index.php" class="block py-2 px-4 text-gray-600 hover:text-blue-500 transition">Inicio</a>
            </li>
            <li>
              <a href="lista/index.php" class="block py-2 px-4 text-gray-600 hover:text-blue-500 transition">Lista</a>
            </li>
            <li>
              <a href="php/destruir_sesion.php" class="block py-2 px-4 text-gray-600 hover:text-blue-500 transition">Salir</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

<div class="">

</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/index.js"></script>
</body>
</html>