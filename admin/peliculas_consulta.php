<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$peliculas = $pdo->query("SELECT id, nombre, genero, descripcion, ruta_imagen, activa FROM peliculas ORDER BY id DESC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | Consultar Películas</title>
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
    <a class="tab active" href="peliculas_consulta.php">Consultar Películas</a>
    <a class="tab" href="clientes_consulta.php">Consultar clientes</a>
    <a class="tab" href="usuarios_registro.php">Registro de usuarios</a>
  </nav>

  <section class="panel">
    <table class="table">
      <thead>
        <tr>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Género</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$peliculas): ?>
        <tr><td colspan="5">No hay películas registradas.</td></tr>
      <?php endif; ?>

      <?php foreach ($peliculas as $p): ?>
        <tr>
          <td>
            <?php if (!empty($p["ruta_imagen"])): ?>
              <img class="thumb" src="../<?= htmlspecialchars($p["ruta_imagen"]) ?>" alt="img">
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($p["nombre"]) ?></td>
          <td><?= htmlspecialchars($p["genero"]) ?></td>
          <td><?= htmlspecialchars($p["descripcion"]) ?></td>
          <td>
            <div class="actions-col">
              <a class="btn-blue" href="peliculas_toggle.php?id=<?= (int)$p["id"] ?>&act=1">Activar</a>
              <a class="btn-red" href="peliculas_toggle.php?id=<?= (int)$p["id"] ?>&act=0">Inactivar</a>
              <a class="btn-yellow" href="peliculas_registro.php?edit=<?= (int)$p["id"] ?>">Modificar</a>
              <div class="muted" style="font-size:12px;margin-top:6px;">
                Estado: <b><?= ((int)$p["activa"]===1) ? "Activa" : "Inactiva" ?></b>
              </div>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>
</body>
</html>
