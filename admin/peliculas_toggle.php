<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$act = isset($_GET["act"]) ? (int)$_GET["act"] : 0;

if ($id > 0) {
  $pdo->prepare("UPDATE peliculas SET activa=? WHERE id=?")->execute([$act, $id]);
}

header("Location: peliculas_consulta.php");
exit;
