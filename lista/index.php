<!Doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ticket BBS</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css">
  <link rel="stylesheet" href="../css/lista.css">
  <link rel="stylesheet" href="../css/index.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
</head>
<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login/index.php");
    exit();
}
?>
<body class="bg-[#2e3440] text-white">
<!-- <nav class="bg-[#3b4252] shadow-md">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="text-2xl font-bold"><img src="../img/logo-blanco-mo.png" alt="" class="w-44"></div>
        <div id="menu" class="md:flex md:items-center md:w-auto">
          <ul class="flex md:flex-row md:space-x-6">
            <li><a href="../index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Inicio</a></li>
            <li><a href="index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Lista</a></li>
            <li><a href="../vacaciones/index.php" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Vacaciones</a></li>
            <li><a href="http://b88e0bd2df17.sn.mynetname.net/menu/" class="block py-2 px-4 text-gray-300 hover:text-blue-400 transition">Salir</a></li>
          </ul>
        </div>
      </div>
    </div>
</nav> -->
<?php include_once("../includes/sidebar.php");?>
<section class="w-full col-grid-2 mt-16">
  <div class="col-grid-2 fecha p-4">
    <label for="fecha">Fecha:</label>
    <input type="month" name="fecha" id="fecha" class="bg-[#4c566a] border border-gray-600 text-white p-2 rounded">
  </div>
  <div class="tabla p-4 overflow-x-scroll lg:overflow-auto" id="tabla"></div>
  <div class="respuesta" id="respuesta"></div>
</section>
<div id="eventModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-80 flex justify-center items-center">
  <div class="bg-[#3b4252] rounded-lg shadow-lg p-6 w-96 relative overflow-y-scroll modal">
    <div id="eventDetails" class="transition-transform duration-500 transform translate-x-0">
      <div class="flex justify-between items-center">
        <h2 id="idTitle" class="text-xl font-bold"></h2>
        <button id="closeModal" class="text-red-400 hover:text-red-600">&times;</button>
      </div>
      <p id="eventTitle" class="text-md text-gray-300 mt-2 mb-2"></p>
      <p id="eventDate" class="text-md font-bold grid text-gray-300 mt-2 mb-2"></p>
      <h2 id="eventAdress" class="text-md font-bold text-cyan-400 mt-2 mb-2"></h2>
      <div id="eventMap" class="w-full h-64 mt-4"></div>
      <p id="eventStatus" class="text-xl text-gray-300 mt-4"></p>
      <div class="flex justify-between space-x-4 mt-4" id="comentarios"></div>
      <button id="closeModalButton" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">Cerrar <i class="bi bi-box-arrow-left"></i></button>
    </div>
  </div>
</div>
<div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-80 flex justify-center items-center">
  <div class="bg-[#3b4252] rounded-lg shadow-lg p-6 relative overflow-y-scroll modal">
    <div class="flex justify-between items-center">
      <h2 class="text-xl font-bold mb-4 text-gray-100">Editar Evento</h2>
      <button id="closeEdit" class="text-red-400 hover:text-red-600 text-4xl">&times;</button>
    </div>
    <form id="editForm" enctype="multipart/form-data">
      <input type="hidden" id="editId" name="id">
      <div class="mb-3">
        <label for="editTitle" class="block text-gray-300 font-bold">Título</label>
        <input type="text" id="editTitle" name="title" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3">
        <label for="editStart" class="block text-gray-300 font-bold">Fecha de inicio</label>
        <input type="datetime-local" id="editStart" name="start" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3">
        <label for="editEnd" class="block text-gray-300 font-bold">Fecha de fin</label>
        <input type="datetime-local" id="editEnd" name="end" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3">
        <label for="editLocation" class="block text-gray-300 font-bold">Ubicación</label>
        <input type="text" id="editLocation" name="location" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3" id="divEstado">
        <label class="block text-gray-300 font-bold">Estado</label>
        <select id="editEstado" name="estado" class="bg-[#4c566a] border border-gray-600 text-white text-sm rounded-lg block w-full p-2.5">
        </select>
      </div>
      <div class="mb-3">
        <label class="block text-gray-300 font-bold">Mapa de Ubicación</label>
        <div id="editMap" class="w-full h-64 border border-gray-600 rounded"></div>
      </div>
      <div class="mb-3">
        <label for="editLat" class="block text-gray-300 font-bold">Latitud</label>
        <input type="text" id="editLat" name="lat" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3">
        <label for="editLng" class="block text-gray-300 font-bold">Longitud</label>
        <input type="text" id="editLng" name="lng" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3">
        <label for="editEvidencia" class="block text-gray-300 font-bold">Evidencia</label>
        <input type="text" id="editEvidencia" name="evidencia" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2">
      </div>
      <div class="mb-3">
        <label for="editComentarios" class="block text-gray-300 font-bold">Comentarios</label>
        <textarea id="editComentarios" name="comentarios" class="w-full bg-[#4c566a] text-white border border-gray-600 rounded p-2"></textarea>
      </div>
      <div class="flex justify-end space-x-4">
        <button type="button" id="cancelEdit" class="bg-gray-600 text-white px-4 py-2 rounded">Cancelar</button>
        <button type="submit" id="updateEvent" class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar</button>
      </div>
    </form>
  </div>
</div>
<script src="../js/jquery-3.7.1.min.js"></script>
<script src="https://momentjs.com/downloads/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../js/table.js"></script>
<script src="../js/sidebar.js"></script>

</body>
</html>
