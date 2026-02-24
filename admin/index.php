<?php
$section = 'admin';
$active = 'panel';
$page_title = 'Panel - Admin APiBA';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_header.php';
?>

<div class="card" style="margin-bottom:14px;">
  <div class="card__header">
    <div>
      <h1 class="h1" style="margin:0;">Panel Administrador</h1>
      <div class="muted">Accesos rápidos para gestionar el circuito</div>
    </div>
  </div>
</div>

<div class="admin-dash-grid">
  <a class="admin-dash-card" href="/apiba-padel/admin/jugadores.php">
    <div class="admin-dash-ico">👤</div>
    <div>
      <div class="admin-dash-title">Jugadores</div>
      <div class="muted">Alta, edición, carnets y ranking</div>
    </div>
  </a>

  <a class="admin-dash-card" href="/apiba-padel/admin/torneos.php">
    <div class="admin-dash-ico">🏆</div>
    <div>
      <div class="admin-dash-title">Torneos</div>
      <div class="muted">Crear, editar, estados, imprimir</div>
    </div>
  </a>

  <a class="admin-dash-card" href="/apiba-padel/admin/noticias.php">
    <div class="admin-dash-ico">📰</div>
    <div>
      <div class="admin-dash-title">Noticias</div>
      <div class="muted">Publicar y ordenar contenido</div>
    </div>
  </a>

  <a class="admin-dash-card" href="/apiba-padel/admin/ranking.php">
    <div class="admin-dash-ico">📈</div>
    <div>
      <div class="admin-dash-title">Ranking</div>
      <div class="muted">Puntos, categorías, reportes</div>
    </div>
  </a>
</div>

<div class="card" style="margin-top:14px;">
  <div class="card__body">
    <div class="muted" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
      <div>📱 Optimizado para celular / tablet.</div>
      <div>Sesión: <b><?= htmlspecialchars($_SESSION['admin']['email'] ?? 'admin') ?></b></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
