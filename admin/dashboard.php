<?php
require_once __DIR__ . "/_auth.php";

$nombre = $_SESSION["admin_nombre"] ?? "Administrador";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin | Dashboard</title>
  <link rel="stylesheet" href="../css/admin.css"/>
</head>

<body class="light">
  <header class="topbar">
    <div class="brand">Plataforma de Streaming</div>

    <div class="top-actions">
      <div class="muted">Sesión: <?= htmlspecialchars($nombre) ?></div>
      <a class="logout" href="logout.php">Cerrar sesión</a>
    </div>
  </header>

  <main class="container">

    <!-- Bienvenida simple -->
   <section class="hero" style="text-align:center">
  <h2> Bienvenido, <?= htmlspecialchars($nombre) ?></h2>
  <p class="muted">Selecciona una sección.</p>
</section>
    <!-- SOLO 4 SECCIONES -->
    <nav class="tabs" style="margin-top:30px; justify-content:center;">
      <a class="tab" href="peliculas_registro.php">Registrar nueva película</a>
      <a class="tab" href="peliculas_consulta.php">Consultar Películas</a>
      <a class="tab" href="clientes_consulta.php">Consultar clientes</a>
      <a class="tab" href="usuarios_registro.php">Registro de usuarios</a>
    </nav>

  </main>
</body>
</html>