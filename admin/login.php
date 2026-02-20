<?php
session_start();
require_once __DIR__ . "/../app/config/db.php";

$mensaje = "";

// Si ya hay sesión, manda al dashboard
if (isset($_SESSION["admin_id"])) {
  header("Location: dashboard.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $usuario = trim(strtolower($_POST["usuario"] ?? ""));
  $pass    = trim($_POST["contrasena"] ?? "");

  $sql = "SELECT id, nombre, apellido_paterno, apellido_materno, correo, contrasena_hash, rol, activo
          FROM usuarios
          WHERE correo = ? AND rol = 'ADMIN'
          LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$usuario]);
  $u = $stmt->fetch();

  if (!$u) {
    $mensaje = "Usuario o contraseña incorrectos.";
  } elseif ((int)$u["activo"] !== 1) {
    $mensaje = "Tu cuenta está desactivada.";
  } elseif (!password_verify($pass, $u["contrasena_hash"])) {
    $mensaje = "Usuario o contraseña incorrectos.";
  } else {
    // ✅ Login correcto
    session_regenerate_id(true);
    $_SESSION["admin_id"] = (int)$u["id"];
    $_SESSION["admin_nombre"] = trim($u["nombre"] . " " . $u["apellido_paterno"]);
    header("Location: dashboard.php");
    exit;
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | Inicio de sesión</title>
  <link rel="stylesheet" href="../css/admin.css"/>
</head>

<body>
  <header class="topbar">
    <div class="brand">Plataforma de Streaming</div>
    <div class="muted">Panel de administración</div>
  </header>

  <main class="auth">
    <form class="card" method="POST" autocomplete="off" novalidate>
      <h1>Inicio de sesión (Admin)</h1>

      <div class="form">
        <label for="usuario">
          Usuario (correo)
          <input id="usuario" name="usuario" type="email" placeholder="correo@ejemplo.com" required
                 value="<?= htmlspecialchars($_POST["usuario"] ?? "") ?>">
        </label>

        <label for="contrasena">
          Contraseña
          <div style="display:flex; gap:10px; align-items:center;">
            <input id="contrasena" name="contrasena" type="password" placeholder="********" required style="flex:1;">
            <button type="button" class="btn-ghost" id="btnTogglePass" style="white-space:nowrap;">
              Ver
            </button>
          </div>
          <div class="hint">Solo administradores pueden acceder aquí.</div>
        </label>

        <button class="btn" type="submit">Ingresar</button>

        <p class="msg"><?= htmlspecialchars($mensaje) ?></p>
      </div>
    </form>
  </main>

  <script>
    const btn = document.getElementById("btnTogglePass");
    const input = document.getElementById("contrasena");

    btn?.addEventListener("click", () => {
      const isPass = input.type === "password";
      input.type = isPass ? "text" : "password";
      btn.textContent = isPass ? "Ocultar" : "Ver";
    });
  </script>
</body>
</html>