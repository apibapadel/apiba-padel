<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = "/apiba-padel";
$esJugador = isset($_SESSION['jugador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>APiBA Pádel</title>
  <style>
    body{font-family:Arial,sans-serif;margin:0;background:#f6f6f6}
    .topbar{background:#111;color:#fff}
    .topbar .wrap{
      max-width:1100px;
      margin:0 auto;
      padding:10px 14px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px
    }
    .brand{
      display:flex;
      align-items:center;
      gap:10px;
      font-weight:bold;
    }
    .brand img{
      height:45px;          /* 👈 TAMAÑO DEL LOGO */
      width:auto;
      display:block;
    }
    .menu a{color:#fff;text-decoration:none;margin-left:14px}
    .menu a:hover{text-decoration:underline}
    .container{
      max-width:1100px;
      margin:16px auto;
      background:#fff;
      border-radius:10px;
      padding:16px;
      box-shadow:0 10px 25px rgba(0,0,0,.06)
    }
    .pill{
      display:inline-block;
      background:#2b2b2b;
      color:#fff;
      padding:4px 8px;
      border-radius:999px;
      font-size:11px;
      margin-left:8px
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="wrap">
    <div class="brand">
      <a href="<?= $base ?>/index.php">
        <img src="<?= $base ?>/assets/img/logo-apiba.png" alt="APiBA">
      </a>

      <?php if($esJugador): ?>
        <span class="pill">
          👤 <?= htmlspecialchars($_SESSION['jugador']['apellido'] ?? '') ?>
          <?= htmlspecialchars($_SESSION['jugador']['nombre'] ?? '') ?>
        </span>
      <?php endif; ?>
    </div>

    <div class="menu">
      <a href="<?= $base ?>/index.php">Inicio</a>
      <a href="<?= $base ?>/ranking/">📊 Ranking</a>
      <a href="<?= $base ?>/torneos/">🎾 Torneos</a>

      <?php if($esJugador): ?>
        <a href="<?= $base ?>/jugador/perfil.php">Mi perfil</a>
        <a href="<?= $base ?>/logout_jugador.php">Cerrar sesión</a>
      <?php else: ?>
        <a href="<?= $base ?>/login.php">🔐 Ingresar</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container">
