<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id > 0) {
  $pdo->prepare("DELETE FROM usuarios WHERE id=? AND rol='CLIENTE'")
      ->execute([$id]);
}

header("Location: clientes_consulta.php");
exit;
