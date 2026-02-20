<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$msg = "";
$editId = isset($_GET["edit"]) ? (int)$_GET["edit"] : 0;

$nombre = $paterno = $materno = $correo = "";
$claveGenerada = "";

// cargar si es edición
if ($editId > 0) {
  $stmt = $pdo->prepare("SELECT id, nombre, apellido_paterno, apellido_materno, correo
                         FROM usuarios
                         WHERE id=? AND rol='CLIENTE' LIMIT 1");
  $stmt->execute([$editId]);
  $c = $stmt->fetch();
  if ($c) {
    $nombre  = $c["nombre"];
    $paterno = $c["apellido_paterno"];
    $materno = $c["apellido_materno"];
    $correo  = $c["correo"];
  } else {
    $editId = 0;
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = (int)($_POST["id"] ?? 0);

  $nombre  = trim($_POST["nombre"] ?? "");
  $paterno = trim($_POST["paterno"] ?? "");
  $materno = trim($_POST["materno"] ?? "");
  $correo  = trim(strtolower($_POST["correo"] ?? ""));

  if ($nombre==="" || $paterno==="" || $materno==="" || $correo==="") {
    $msg = "Completa todos los campos.";
  } else {
    if ($id > 0) {
      $sql = "UPDATE usuarios
              SET nombre=?, apellido_paterno=?, apellido_materno=?, correo=?
              WHERE id=? AND rol='CLIENTE'";
      $pdo->prepare($sql)->execute([$nombre,$paterno,$materno,$correo,$id]);
      $msg = "Cliente actualizado ✅";
      header("Location: clientes_consulta.php");
      exit;
    } else {
      // crear cliente con clave auto
      $claveGenerada = substr(bin2hex(random_bytes(4)), 0, 8);
      $hash = password_hash($claveGenerada, PASSWORD_DEFAULT);

      $sql = "INSERT INTO usuarios
              (nombre, apellido_paterno, apellido_materno, correo, contrasena_hash, rol, activo)
              VALUES (?,?,?,?,?,'CLIENTE',1)";
      $pdo->prepare($sql)->execute([$nombre,$paterno,$materno,$correo,$hash]);
      $msg = "Cliente creado ✅ (copia la clave generada)";
      // no redirigimos para que vea la clave
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | <?= $editId>0 ? "Actualizar cliente" : "Registrar cliente" ?></title>
  <link rel="stylesheet" href="../css/admin.css"/>
</head>
<body>
<header class="topbar">
  <div class="brand">Plataforma de Streaming - (Nombre)</div>
  <div class="top-actions">
    <div class="muted">Sesión: <?= htmlspecialchars($_SESSION["admin_nombre"] ?? "Admin") ?></div>
    <a class="logout" href="logout.php">Cerrar sesión</a>
  </div>
</header>

<main class="container">
  <nav class="tabs">
    <a class="tab" href="peliculas_registro.php">Registrar nueva película</a>
    <a class="tab" href="peliculas_consulta.php">Consultar Películas</a>
    <a class="tab active" href="clientes_consulta.php">Consultar clientes</a>
    <a class="tab" href="usuarios_registro.php">Registro de usuarios</a>
  </nav>

  <section class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
      <h2 style="margin:0;"><?= $editId>0 ? "Actualizar cliente" : "Registrar cliente" ?></h2>
      <a class="btn-ghost" href="clientes_consulta.php">Volver a consulta</a>
    </div>

    <?php if ($msg): ?>
      <p class="muted"><b><?= htmlspecialchars($msg) ?></b></p>
    <?php endif; ?>

    <?php if ($claveGenerada): ?>
      <p class="muted"><b>Clave generada:</b> <span style="font-size:18px"><?= htmlspecialchars($claveGenerada) ?></span></p>
      <p class="hint">Guárdala: se muestra solo una vez.</p>
      <a class="btn-yellow" href="clientes_consulta.php">Ir a consulta</a>
      <hr style="border:0;border-top:1px solid rgba(255,255,255,.12);margin:16px 0;">
    <?php endif; ?>

    <form method="POST" class="form-grid">
      <input type="hidden" name="id" value="<?= (int)$editId ?>">

      <div class="field">
        <label>Nombre</label>
        <input name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
      </div>

      <div class="field">
        <label>Apellido Paterno</label>
        <input name="paterno" value="<?= htmlspecialchars($paterno) ?>" required>
      </div>

      <div class="field">
        <label>Apellido Materno</label>
        <input name="materno" value="<?= htmlspecialchars($materno) ?>" required>
      </div>

      <div class="field" style="grid-column:1/-1;">
        <label>Correo electrónico</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($correo) ?>" required>
      </div>

      <div class="field" style="grid-column:1/-1;">
        <button class="btn-yellow" type="submit"><?= $editId>0 ? "Actualizar" : "Guardar" ?></button>
      </div>
    </form>
  </section>
</main>
</body>
</html>
