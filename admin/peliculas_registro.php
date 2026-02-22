<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$adminId = (int)$_SESSION["admin_id"];
$editId = isset($_GET["edit"]) ? (int)$_GET["edit"] : 0;

$nombre = $genero = $descripcion = $url_trailer = $ruta_imagen = "";
$msg = "";

// Cargar si es edición
if ($editId > 0) {
  $stmt = $pdo->prepare("SELECT * FROM peliculas WHERE id = ?");
  $stmt->execute([$editId]);
  $p = $stmt->fetch();
  if ($p) {
    $nombre = $p["nombre"];
    $genero = $p["genero"];
    $descripcion = $p["descripcion"];
    $url_trailer = $p["url_trailer"];
    $ruta_imagen = $p["ruta_imagen"];
  } else {
    $editId = 0;
  }
}

// Guardar (crear/actualizar)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre = trim($_POST["nombre"] ?? "");
  $genero = trim($_POST["genero"] ?? "");
  $descripcion = trim($_POST["descripcion"] ?? "");
  $url_trailer = trim($_POST["url_trailer"] ?? "");

  if ($nombre === "" || $genero === "" || $descripcion === "" || $url_trailer === "") {
    $msg = "Completa todos los campos.";
  } else {
    // Manejo de imagen (archivo)
    $nuevaRuta = $ruta_imagen; // si no sube nueva, conserva
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES["imagen"]["error"] !== UPLOAD_ERR_OK) {
        $msg = "Error al subir la imagen.";
      } else {
        $tmp = $_FILES["imagen"]["tmp_name"];
        $mime = mime_content_type($tmp);

        if (!in_array($mime, ["image/jpeg", "image/png", "image/webp"])) {
          $msg = "Formato de imagen no permitido (usa JPG, PNG o WEBP).";
        } else {
          $ext = match($mime) {
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
            default => "jpg"
          };

          $dir = __DIR__ . "/../uploads/posters";
          if (!is_dir($dir)) mkdir($dir, 0777, true);

          $filename = "poster_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
          $dest = $dir . "/" . $filename;

          if (!move_uploaded_file($tmp, $dest)) {
            $msg = "No se pudo guardar la imagen.";
          } else {
            // Guardamos ruta RELATIVA para usarla en web/hosting
            $nuevaRuta = "uploads/posters/" . $filename;
          }
        }
      }
    }

    // Si es NUEVA película, imagen es obligatoria
    if ($msg === "" && $editId === 0 && $nuevaRuta === "") {
      $msg = "Debes cargar una imagen (archivo).";
    }

    if ($msg === "") {
      if ($editId > 0) {
        $sql = "UPDATE peliculas
                SET nombre=?, genero=?, descripcion=?, ruta_imagen=?, url_trailer=?
                WHERE id=?";
        $pdo->prepare($sql)->execute([$nombre, $genero, $descripcion, $nuevaRuta, $url_trailer, $editId]);
        $msg = "Película actualizada ✅";
      } else {
        $sql = "INSERT INTO peliculas (nombre, genero, descripcion, ruta_imagen, url_trailer, activa, creado_por)
                VALUES (?, ?, ?, ?, ?, 1, ?)";
        $pdo->prepare($sql)->execute([$nombre, $genero, $descripcion, $nuevaRuta, $url_trailer, $adminId]);
        $msg = "Película registrada ✅";
        // limpiar
        $nombre = $genero = $descripcion = $url_trailer = $ruta_imagen = "";
      }
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | Registrar Película</title>
  <link rel="stylesheet" href="../css/admin.css"/>
</head>
<body class="light">
<header class="topbar">
  <div class="brand">Plataforma de Streaming</div>
  <div class="top-actions">
    <div class="muted">Sesión: <?= htmlspecialchars($_SESSION["admin_nombre"] ?? "Admin") ?></div>
    <a class="logout" href="logout.php">Cerrar sesión</a>
  </div>
</header>

<main class="container">
  <nav class="tabs">
    <a class="tab active" href="peliculas_registro.php">Registrar nueva película</a>
    <a class="tab" href="peliculas_consulta.php">Consultar Películas</a>
    <a class="tab" href="clientes_consulta.php">Consultar clientes</a>
    <a class="tab" href="usuarios_registro.php">Registro de usuarios</a>
  </nav>

  <section class="panel">
    <h2><?= $editId > 0 ? "Modificar película" : "Registrar nueva película" ?></h2>
    <?php if ($msg): ?>
      <p class="muted"><b><?= htmlspecialchars($msg) ?></b></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-grid">
      <div class="field">
        <label>Nombre de la película</label>
        <input name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
      </div>

      <div class="field">
        <label>Seleccionar género</label>
        <input name="genero" value="<?= htmlspecialchars($genero) ?>" required>
      </div>

      <div class="field" style="grid-column:1/-1;">
        <label>Cargar imagen (archivo)</label>
        <input type="file" name="imagen" accept="image/*" <?= $editId>0 ? "" : "required" ?>>
        <?php if ($ruta_imagen): ?>
          <p class="muted">Actual: <img src="../<?= htmlspecialchars($ruta_imagen) ?>" style="height:60px;vertical-align:middle;border:1px solid #222"></p>
        <?php endif; ?>
      </div>

      <div class="field" style="grid-column:1/-1;">
        <label>Descripción</label>
        <textarea name="descripcion" rows="5" required><?= htmlspecialchars($descripcion) ?></textarea>
      </div>

      <div class="field" style="grid-column:1/-1;">
        <label>URL del tráiler</label>
        <input type="url" name="url_trailer" value="<?= htmlspecialchars($url_trailer) ?>" required>
      </div>

      <div class="field" style="grid-column:1/-1;">
        <button class="btn-yellow" type="submit"><?= $editId>0 ? "Guardar cambios" : "Guardar película" ?></button>
        <a class="tab" style="margin-left:10px" href="peliculas_consulta.php">Ir a consulta</a>
      </div>
    </form>
  </section>
</main>
</body>
</html>
