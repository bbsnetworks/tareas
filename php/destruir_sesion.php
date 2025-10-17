<?php
/* Destruir la sesion */
session_start();
session_destroy();
/* Redirigir */
header('Location: ../../menu/login/index.php');
?>