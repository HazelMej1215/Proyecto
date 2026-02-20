<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../app/config/db.php";

$rows = $pdo->query("SELECT id, nombre, genero, descripcion, ruta_imagen, url_trailer
                     FROM peliculas
                     WHERE activa=1
                     ORDER BY id DESC")->fetchAll();

echo json_encode(["ok"=>true, "peliculas"=>$rows]);