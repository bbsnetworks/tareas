<?php
session_start();
if (!isset($_SESSION['iduser'])) {
    header("Location: http://b88e0bd2df17.sn.mynetname.net/menu/login/index.php");
    exit();
}
$iduser = $_SESSION['iduser'];
?>