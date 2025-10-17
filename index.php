<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordenes</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
    <script>
      tailwind.config = {
        darkMode: 'class'
      }
    </script>
    <style>
      body {
        background-color: #2e3440; /* gris neutro tipo Nord */
      }
    </style>
</head>
<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../menu/login/index.php");
    exit();
}
?>
<body class="dark text-white">

<!-- <nav class="bg-gray-800 shadow-md">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="text-2xl font-bold text-white">
          <img src="img/logo-blanco-mo.png" alt="" class="w-44">
        </div>
        <div id="menu" class="md:flex md:items-center md:w-auto">
          <ul class="flex md:flex-row md:space-x-6">
            <li>
              <a href="index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Inicio</a>
            </li>
            <li>
              <a href="lista/index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Lista</a>
            </li>
            <li>
              <a href="vacaciones/index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Vacaciones</a>
            </li>
            <li>
              <a href="http://b88e0bd2df17.sn.mynetname.net/menu/" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Salir</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
</nav> -->
<?php include_once ("includes/sidebar.php") ?>
<div class="container mx-auto mt-16 mb-4">
  <h2 class="text-2xl font-bold mb-4 ml-4">Agregar Evento</h2>
  <form id="eventForm" class="space-y-4 p-4 rounded relative z-10">
    <div>
      <label for="title" class="block text-sm font-medium">Título del Evento</label>
      <input type="text" id="title" class="w-full p-2 border border-gray-600 rounded bg-gray-900 text-white" required>
    </div>
    <div>
      <label for="start" class="block text-sm font-medium">Fecha de Inicio</label>
      <input type="datetime-local" id="start" class="w-full p-2 border border-gray-600 rounded bg-gray-900 text-white" required>
    </div>
    <div>
      <label for="end" class="block text-sm font-medium">Fecha de Fin</label>
      <input type="datetime-local" id="end" class="w-full p-2 border border-gray-600 rounded bg-gray-900 text-white">
    </div>
    <div>
      <label for="color" class="block text-sm font-medium">Color del Evento</label>
      <input type="color" id="color" class="w-full p-2 border border-gray-600 rounded bg-gray-900 text-white" value="#38bdf8">
    </div>
    <div>
      <label for="here-autocomplete" class="block text-sm font-medium">Ubicación</label>
      <input type="text" id="here-autocomplete" class="w-full p-2 border border-gray-600 rounded bg-gray-900 text-white" placeholder="Selecciona una ubicación" readonly>
      <div id="map" class="w-full h-64 mt-4 rounded border border-gray-600"></div>
      <input type="hidden" id="lat">
      <input type="hidden" id="lng">
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Agregar Evento</button>
  </form>

  <div id="calendar" class="mt-6"></div>
  <div id="calendar" class="calendar"></div>

  <!-- Modales incluidos completos y sin cambios -->
  <div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center">
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 relative modal">
      <div id="eventDetails" class="transition-transform duration-500 transform translate-x-0">
        <div class="flex justify-between items-center mb-10">
          <h2 id="idTitle" class="text-xl font-bold"></h2>
          <h2 class="text-xl font-bold"></h2>
          <button id="closeModal" class="text-xl hover:text-red-400">&times;</button>
        </div>
        <p id="eventTitle" class="text-md text-gray-300 font-bold grid"></p>
        <p id="eventDate" class="text-md text-gray-300 font-bold grid"></p>
        <h2 id="eventAdress" class="text-md font-bold text-cyan-400 mt-2 mb-2"></h2>
        <div id="eventMap" class="w-full h-64 mt-4"></div>
        <p id="eventStatus" class="text-xl text-gray-300 mt-5 mb-5"></p>
        <div class="grid gap-4 botones" id="botones"></div>
        <div class="mt-6 mb-5 text-center" id="botonCancelar"></div>
        <button id="closeModalButton" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">Cerrar <i class="bi bi-box-arrow-left"></i></button>
        <button id="deleteEventButton" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Eliminar <i class="bi bi-trash"></i></button>
      </div>
      <div id="sliderContainer" class="hidden transition-transform duration-500 transform translate-x-full absolute top-0 left-0 w-full h-full p-6 bg-gray-800">
        <h3 class="text-lg font-bold">Información adicional</h3>
        <div class="mt-2">
          <label for="evidence" class="block text-sm font-medium text-gray-300">Evidencia (opcional)</label>
          <input type="file" id="evidence" capture accept="image/*" multiple class="mt-1 block w-full border border-gray-600 rounded-md shadow-sm bg-gray-900 text-white">
        </div>
        <div class="mt-4">
          <label for="comments" class="block text-sm font-medium text-gray-300">Comentarios (obligatorio)</label>
          <textarea id="comments" rows="3" class="mt-1 block w-full border border-gray-600 rounded-md shadow-sm p-2 bg-gray-900 text-white" placeholder="Escribe tus comentarios aquí..." required></textarea>
        </div>
        <div class="mt-4">
          <label for="fin" class="block text-sm font-medium text-gray-300">Fin</label>
          <input type="datetime-local" id="fin" class="w-full p-2 border border-gray-600 rounded bg-gray-900 text-white">
        </div>
        <div class="flex justify-between space-x-4 mt-4">
          <button id="backButton" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Volver</button>
          <button id="submitSlider" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enviar</button>
        </div>
      </div>
    </div>
  </div>

  <div id="vacationModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center">
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 w-96 relative">
      <h2 class="text-xl font-bold text-orange-400 text-center mb-4">Detalles de Vacaciones</h2>
      <p id="vacationTitle" class="text-md text-gray-300 font-bold mb-2"></p>
      <p id="vacationDate" class="text-md text-gray-400 mb-2"></p>
      <div class="flex justify-end space-x-2">
        <button id="closeVacationModal" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/index.js"></script>
<script src="https://momentjs.com/downloads/moment.min.js"></script>
<script src="js/sidebar.js"></script>
</body>
</html>