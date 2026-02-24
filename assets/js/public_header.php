<?php
// includes/public_header.php
// Uso en cada página:
// $active = 'inicio';  // o ranking / torneos / ingresar
// $page_title = '...';
// include __DIR__ . '/public_header.php';

if (!isset($active)) $active = '';
if (!isset($page_title)) $page_title = 'APIBA Pádel';

$BASE = '/apiba-padel';
function is_active($key, $active) { return ($key === $active) ? 'active' : ''; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>

  <link rel="stylesheet" href="<?= $BASE ?>/assets/css/public.css">

  <!-- Evita "parpadeo" aplicando tema antes de render -->
  <script>
    (function(){
      try {
        const k="apiba_theme";
        const s=localStorage.getItem(k);
        if (s==="dark" || s==="light") {
          // Lo aplicamos al body cuando exista
          document.addEventListener("DOMContentLoaded", function(){
            document.body.setAttribute("data-theme", s);
          });
        }
      } catch(e) {}
    })();
  </script>
</head>
<body>

<div class="topbar">
  <div class="container topbar__inner">
    <a class="brand" href="<?= $BASE ?>/">
      <span class="brand__logo"></span>
      <span>APIBA Pádel</span>
    </a>

    <div class="nav">
      <a class="<?= is_active('inicio', $active) ?>" href="<?= $BASE ?>/">Inicio</a>
      <a class="<?= is_active('ranking', $active) ?>" href="<?= $BASE ?>/ranking.php">Ranking</a>
      <a class="<?= is_active('torneos', $active) ?>" href="<?= $BASE ?>/torneos.php">Torneos</a>
      <a class="<?= is_active('ingresar', $active) ?>" href="<?= $BASE ?>/ingresar.php">Ingresar</a>

      <div class="theme-toggle" onclick="APIBA_toggleTheme()" role="button" tabindex="0" aria-label="Cambiar tema">
        <span style="color:var(--muted); font-weight:700; font-size:12px;">Modo</span>
        <span class="switch"></span>
      </div>
    </div>
  </div>
</div>

<div class="page">
  <div class="container">
