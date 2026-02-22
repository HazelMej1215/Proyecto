<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") exit;

require_once __DIR__ . "/../app/config/db.php";

$input = json_decode(file_get_contents("php://input"), true);
$correo = strtolower(trim($input["correo"] ?? ""));
$pass   = trim($input["password"] ?? "");

if ($correo === "" || $pass === "") {
  echo json_encode(["ok"=>false, "msg"=>"Faltan datos"]);
  exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, apellido_paterno, apellido_materno, correo, contrasena_hash, activo, rol
                       FROM usuarios
                       WHERE correo=? AND rol='CLIENTE'
                       LIMIT 1");
$stmt->execute([$correo]);
$u = $stmt->fetch();

if (!$u || !password_verify($pass, $u["contrasena_hash"])) {
  echo json_encode(["ok"=>false, "msg"=>"Credenciales incorrectas"]);
  exit;
}

if ((int)$u["activo"] !== 1) {
  echo json_encode(["ok"=>false, "msg"=>"Lo sentimos, tu cuenta está desactivada"]);
  exit;
}

echo json_encode([
  "ok"=>true,
  "msg"=>"Login correcto",
  "user"=>[
    "id"=>(int)$u["id"],
    "nombre"=>trim($u["nombre"]." ".$u["apellido_paterno"]." ".$u["apellido_materno"]),
    "correo"=>$u["correo"]
  ]
]);