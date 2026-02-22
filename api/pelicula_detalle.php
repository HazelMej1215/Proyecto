<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../app/config/db.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$stmt = $pdo->prepare("SELECT id, nombre, genero, descripcion, ruta_imagen, url_trailer
                       FROM peliculas
                       WHERE id=? AND activa=1
                       LIMIT 1");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { echo json_encode(["ok"=>false, "msg"=>"No encontrada"]); exit; }

echo json_encode(["ok"=>true, "pelicula"=>$p]);