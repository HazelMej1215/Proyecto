<?php
require_once __DIR__ . "/_auth.php";
require_once __DIR__ . "/../app/config/db.php";

$clientes = $pdo->query("
    SELECT id, nombre, apellido_paterno, apellido_materno, correo, rol, activo, fecha_registro
    FROM usuarios
    ORDER BY id DESC
")->fetchAll();
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | Consultar usuarios</title>
  <link rel="stylesheet" href="../css/admin.css"/>
</head>

<body>

<header class="topbar">
  <div class="brand">Plataforma de Streaming</div>
  <div class="top-actions">
    <div class="muted">
      Sesión: <?= htmlspecialchars($_SESSION["admin_nombre"] ?? "Admin") ?>
    </div>
    <a class="logout" href="logout.php">Cerrar sesión</a>
  </div>
</header>

<main class="container">

  <nav class="tabs">
    <a class="tab" href="peliculas_registro.php">Registrar nueva película</a>
    <a class="tab" href="peliculas_consulta.php">Consultar Películas</a>
    <a class="tab active" href="clientes_consulta.php">Consultar usuarios</a>
    <a class="tab" href="usuarios_registro.php">Registro de usuarios</a>
  </nav>

  <section class="panel">
    <h2>Consultar usuarios</h2>

    <table class="table">
      <thead>
        <tr>
          <th>Nombre completo</th>
          <th>Correo / Usuario</th>
          <th>Tipo de Usuario</th>
          <th>Fecha de Registro</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody>
      <?php if (!$clientes): ?>
        <tr>
          <td colspan="5">No hay usuarios registrados.</td>
        </tr>
      <?php endif; ?>

      <?php foreach ($clientes as $c): ?>
        <?php 
          $nombreCompleto = trim(
            $c["nombre"] . " " . 
            $c["apellido_paterno"] . " " . 
            $c["apellido_materno"]
          ); 
        ?>

        <tr>
          <td><?= htmlspecialchars($nombreCompleto) ?></td>

          <td><?= htmlspecialchars($c["correo"]) ?></td>

          <td>
            <?php if ($c["rol"] === "ADMIN"): ?>
              <span class="badge-admin">ADMIN</span>
            <?php else: ?>
              <span class="badge-cliente">CLIENTE</span>
            <?php endif; ?>
          </td>

          <td>
            <?= htmlspecialchars(date("Y-m-d", strtotime($c["fecha_registro"]))) ?>
          </td>

          <td>
            <div class="actions-col" style="flex-direction:row;gap:10px;align-items:center;">

              <a class="btn-red"
                 href="clientes_delete.php?id=<?= (int)$c["id"] ?>"
                 onclick="return confirm('¿Eliminar usuario?');">
                 Eliminar
              </a>

              <?php if ((int)$c["activo"] === 1): ?>
                <a class="btn-blue"
                   href="clientes_toggle.php?id=<?= (int)$c["id"] ?>&act=0">
                   Activo
                </a>
              <?php else: ?>
                <a class="btn-blue"
                   href="clientes_toggle.php?id=<?= (int)$c["id"] ?>&act=1">
                   Inactivo
                </a>
              <?php endif; ?>

              <a class="btn-yellow"
                 href="usuarios_registro.php?edit=<?= (int)$c["id"] ?>">
                 Actualizar
              </a>

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