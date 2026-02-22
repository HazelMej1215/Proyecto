<?php
// ✅ Ajustado a tu estructura real:
require_once __DIR__ . "/app/config/db.php";

session_start();
$mensaje = "";

/** Genera contraseña segura (sin caracteres confusos) */
function generarPassword($longitud = 8): string {
  $chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
  $pwd = "";
  for ($i=0; $i<$longitud; $i++) {
    $pwd .= $chars[random_int(0, strlen($chars)-1)];
  }
  return $pwd;
}

// ✅ Generar clave en GET (para mostrarla en el input)
if (empty($_SESSION["tmp_cliente_pwd"])) {
  $_SESSION["tmp_cliente_pwd"] = generarPassword(8);
}
$pwdPlano = $_SESSION["tmp_cliente_pwd"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $nombre  = trim($_POST["nombre"] ?? "");
  $paterno = trim($_POST["apellido_paterno"] ?? "");
  $materno = trim($_POST["apellido_materno"] ?? "");
  $correo  = trim(strtolower($_POST["correo"] ?? ""));

  if ($nombre === "" || $paterno === "" || $correo === "") {
    $mensaje = "<div class='msg err'>Completa los campos obligatorios.</div>";
  } else {
    // Verificar correo duplicado
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
    $check->execute([$correo]);

    if ($check->fetch()) {
      $mensaje = "<div class='msg err'>Ese correo ya está registrado.</div>";
    } else {
      // ✅ Encriptar la clave que ya mostramos
      $pwdHash = password_hash($pwdPlano, PASSWORD_DEFAULT);

      $stmt = $pdo->prepare("
        INSERT INTO usuarios
          (nombre, apellido_paterno, apellido_materno, correo, clave, rol, activo, fecha_registro)
        VALUES
          (?, ?, ?, ?, ?, 'CLIENTE', 1, NOW())
      ");
      $stmt->execute([$nombre, $paterno, $materno, $correo, $pwdHash]);

      $mensaje = "
        <div class='msg ok'>
          Registro exitoso ✅<br>
          Tu contraseña generada es: <b>$pwdPlano</b><br>
          (Guárdala, porque no se volverá a mostrar)
        </div>
      ";

      // ✅ Generar otra para el siguiente registro
      $_SESSION["tmp_cliente_pwd"] = generarPassword(8);
      $pwdPlano = $_SESSION["tmp_cliente_pwd"];
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Registro de Cliente</title>

  <!-- ✅ Si ya tienes un CSS bonito global, puedes enlazarlo aquí:
  <link rel="stylesheet" href="css/admin.css"/>
  -->
  <style>
    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial}
    .bg{
      min-height:100vh;
      background:
        radial-gradient(900px 500px at 15% 0%, rgba(255,255,255,.08), transparent 60%),
        #07070a;
      color:#fff;
      display:grid;
      place-items:center;
      padding:24px;
    }
    .card{
      width:min(460px, 100%);
      background:rgba(18,18,24,.78);
      border:1px solid rgba(255,255,255,.10);
      border-radius:18px;
      padding:26px;
      box-shadow:0 18px 60px rgba(0,0,0,.5);
      backdrop-filter: blur(10px);
    }
    h1{margin:0 0 16px;font-size:20px}
    .form{display:grid;gap:14px}
    label{display:grid;gap:8px;font-size:14px;color:rgba(255,255,255,.85)}
    input{
      padding:12px;
      border-radius:12px;
      border:1px solid rgba(255,255,255,.10);
      background:rgba(0,0,0,.35);
      color:#fff;
      outline:none;
    }
    input:focus{border-color:rgba(255,255,255,.35)}
    input[readonly]{opacity:.95}
    .btn{
      width:100%;
      padding:12px 14px;
      border:0;
      border-radius:12px;
      background:linear-gradient(180deg, #2a2a35, #14141c);
      color:#fff;
      font-weight:700;
      cursor:pointer;
      border:1px solid rgba(255,255,255,.10);
    }
    .btn:hover{filter:brightness(1.08)}
    .msg{margin:10px 0 0;font-size:13px;border-radius:12px;padding:10px 12px}
    .msg.ok{background:rgba(40,180,90,.18);border:1px solid rgba(40,180,90,.35)}
    .msg.err{background:rgba(220,60,60,.18);border:1px solid rgba(220,60,60,.35)}
    .hint{font-size:12px;color:rgba(255,255,255,.65)}
  </style>
</head>
<body>
  <div class="bg">
    <div class="card">
      <h1>Registro de Cliente</h1>
      <div class="hint">Solo se crean cuentas tipo <b>CLIENTE</b>.</div>

      <?= $mensaje ?>

      <form class="form" method="POST" autocomplete="off">
        <label>Nombre
          <input name="nombre" required>
        </label>

        <label>Apellido Paterno
          <input name="apellido_paterno" required>
        </label>

        <label>Apellido Materno
          <input name="apellido_materno">
        </label>

        <label>Correo electrónico
          <input type="email" name="correo" required>
        </label>

        <!-- ✅ Clave generada automáticamente -->
        <label>Contraseña (generada automáticamente)
          <input value="<?= htmlspecialchars($pwdPlano) ?>" readonly>
        </label>

        <button class="btn" type="submit">Registrarse</button>
      </form>
    </div>
  </div>
</body>
</html>