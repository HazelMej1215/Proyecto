<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$msg = "";
$editId = isset($_GET["edit"]) ? (int)$_GET["edit"] : 0;

// valores del form
$nombre = $paterno = $materno = $correo = "";
$rol = "ADMIN";
$claveVisible = ""; // solo para mostrar en pantalla cuando se crea o se regenera

// ===== Cargar usuario para editar =====
if ($editId > 0) {
  $stmt = $pdo->prepare("SELECT id, nombre, apellido_paterno, apellido_materno, correo, rol
                         FROM usuarios
                         WHERE id=? LIMIT 1");
  $stmt->execute([$editId]);
  $u = $stmt->fetch();
  if ($u) {
    $nombre  = $u["nombre"];
    $paterno = $u["apellido_paterno"];
    $materno = $u["apellido_materno"];
    $correo  = $u["correo"];
    $rol     = $u["rol"];
  } else {
    $editId = 0;
  }
}

// ===== Acciones GET (activar/inactivar/eliminar) =====
if (isset($_GET["toggle"])) {
  $id = (int)$_GET["toggle"];
  $act = isset($_GET["act"]) ? (int)$_GET["act"] : 1;
  $pdo->prepare("UPDATE usuarios SET activo=? WHERE id=?")->execute([$act, $id]);
  header("Location: usuarios_registro.php");
  exit;
}

if (isset($_GET["del"])) {
  $id = (int)$_GET["del"];
  // evita borrar tu propio admin logueado
  if (!isset($_SESSION["admin_id"]) || (int)$_SESSION["admin_id"] !== $id) {
    $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id]);
  }
  header("Location: usuarios_registro.php");
  exit;
}

// ===== Guardar (POST) =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id = (int)($_POST["id"] ?? 0);

  $nombre  = trim($_POST["nombre"] ?? "");
  $paterno = trim($_POST["paterno"] ?? "");
  $materno = trim($_POST["materno"] ?? "");
  $correo  = trim(strtolower($_POST["correo"] ?? ""));
  $rol     = strtoupper(trim($_POST["rol"] ?? "ADMIN"));

  if (!in_array($rol, ["ADMIN", "CLIENTE"])) $rol = "ADMIN";

  if ($nombre==="" || $paterno==="" || $materno==="" || $correo==="") {
    $msg = "Completa todos los campos.";
  } else {

    // validar correo duplicado
    if ($id > 0) {
      $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo=? AND id<>?");
      $chk->execute([$correo, $id]);
    } else {
      $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo=?");
      $chk->execute([$correo]);
    }

    if ((int)$chk->fetchColumn() > 0) {
      $msg = "Ese correo ya está registrado.";
    } else {

      // --- EDITAR ---
      if ($id > 0) {

        // actualizar datos (NO cambia contraseña)
        $sql = "UPDATE usuarios
                SET nombre=?, apellido_paterno=?, apellido_materno=?, correo=?, rol=?
                WHERE id=?";
        $pdo->prepare($sql)->execute([$nombre,$paterno,$materno,$correo,$rol,$id]);

        // si presionaron "Regenerar" clave
        if (isset($_POST["reset_clave"]) && $_POST["reset_clave"] == "1") {
          $claveVisible = substr(bin2hex(random_bytes(4)), 0, 8);
          $hash = password_hash($claveVisible, PASSWORD_DEFAULT);
          $pdo->prepare("UPDATE usuarios SET contrasena_hash=? WHERE id=?")->execute([$hash, $id]);
          $msg = "Usuario actualizado ✅ (Clave regenerada: $claveVisible)";
        } else {
          $msg = "Usuario actualizado ✅";
        }

        // salir de modo edición
        $editId = 0;
        $nombre = $paterno = $materno = $correo = "";
        $rol = "ADMIN";

      } else {
        // --- CREAR ---
        // la clave llega del input readonly (generada por JS)
        $claveVisible = trim($_POST["clave"] ?? "");
        if ($claveVisible === "") {
          $claveVisible = substr(bin2hex(random_bytes(4)), 0, 8);
        }

        $hash = password_hash($claveVisible, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios
                (nombre, apellido_paterno, apellido_materno, correo, contrasena_hash, rol, activo)
                VALUES (?,?,?,?,?,?,1)";
        $pdo->prepare($sql)->execute([$nombre,$paterno,$materno,$correo,$hash,$rol]);

        $msg = "Usuario creado ✅ (Clave: $claveVisible)";
        $nombre = $paterno = $materno = $correo = "";
        $rol = "ADMIN";
      }
    }
  }
}

// ===== Listar usuarios =====
$usuarios = $pdo->query("SELECT id, nombre, apellido_paterno, apellido_materno, correo, rol, activo, fecha_registro
                         FROM usuarios
                         ORDER BY id DESC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | Registro de usuarios</title>
  <link rel="stylesheet" href="../css/admin.css"/>
</head>
<body>
<header class="topbar">
  <div class="brand">Plataforma de Streaming</div>
  <div class="top-actions">
    <div class="muted">Sesión: <?= htmlspecialchars($_SESSION["admin_nombre"] ?? "Admin") ?></div>
    <a class="logout" href="logout.php">Cerrar sesión</a>
  </div>
</header>

<main class="container">
  <nav class="tabs">
    <a class="tab" href="peliculas_registro.php">Registrar nueva película</a>
    <a class="tab" href="peliculas_consulta.php">Consultar Películas</a>
    <a class="tab" href="clientes_consulta.php">Consultar clientes</a>
    <a class="tab active" href="usuarios_registro.php">Registro de usuarios</a>
  </nav>

  <section class="panel">
    <h2><?= $editId>0 ? "Actualizar usuario" : "Registrar usuario" ?></h2>

    <?php if ($msg): ?>
      <p class="muted"><b><?= htmlspecialchars($msg) ?></b></p>
      <hr style="border:0;border-top:1px solid rgba(255,255,255,.12);margin:14px 0;">
    <?php endif; ?>

    <!-- FORM estilo como tu diseño (Nombre/Apellidos/Correo + Clave a la derecha) -->
    <form method="POST" class="form-grid" id="formUsuario">
      <input type="hidden" name="id" value="<?= (int)$editId ?>">

      <div class="field">
        <label>Nombre</label>
        <input name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
      </div>

      <div class="field">
        <label>Clave</label>
        <div style="display:flex;gap:10px;align-items:center;">
          <input id="uClave" name="clave"
                 value="<?= $editId>0 ? "" : htmlspecialchars($claveVisible) ?>"
                 readonly>
          <?php if ($editId == 0): ?>
            <button class="btn-ghost" type="button" id="btnGenerarClave">Generar</button>
          <?php else: ?>
            <button class="btn-ghost" type="submit" name="reset_clave" value="1"
              onclick="return confirm('¿Regenerar clave? Se cambiará la contraseña del usuario.')">
              Regenerar
            </button>
          <?php endif; ?>
        </div>
        <div class="hint">
          <?= $editId==0 ? "Se genera automáticamente al crear." : "Por seguridad no se muestra la clave actual." ?>
        </div>
      </div>

      <div class="field">
        <label>Apellido Paterno</label>
        <input name="paterno" value="<?= htmlspecialchars($paterno) ?>" required>
      </div>

      <div class="field">
        <label>Tipo de usuario</label>
        <select name="rol" required>
          <option value="ADMIN" <?= $rol==="ADMIN"?"selected":"" ?>>ADMIN</option>
          <option value="CLIENTE" <?= $rol==="CLIENTE"?"selected":"" ?>>CLIENTE</option>
        </select>
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
        <?php if ($editId>0): ?>
          <a class="btn-ghost" href="usuarios_registro.php" style="margin-left:10px;">Cancelar</a>
        <?php endif; ?>
      </div>
    </form>

   

  </section>
</main>

<script>
function generarClave(longitud = 8) {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
  let clave = "";
  for (let i = 0; i < longitud; i++) {
    clave += chars[Math.floor(Math.random() * chars.length)];
  }
  return clave;
}

document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("uClave");
  const btn = document.getElementById("btnGenerarClave");

  // Solo en modo crear (si hay botón)
  if (input && btn) {
    if (!input.value) input.value = generarClave(8);
    btn.addEventListener("click", () => input.value = generarClave(8));
  }
});
</script>

</body>
</html>
