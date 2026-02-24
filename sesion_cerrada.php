<?php
$section = 'public';
$active = '';
$page_title = 'Sesión cerrada - APiBA Pádel';

require_once __DIR__ . '/includes/site_header.php';
?>

<div class="grid">
  <div class="card">
    <div class="card__header">
      <h1 class="h1">Sesión cerrada</h1>
      <span class="badge badge--ok">OK</span>
    </div>

    <div class="card__body">
      <p class="p">Tu sesión se cerró correctamente.</p>

      <div style="height:12px;"></div>

      <div class="form-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn btn--primary" href="/apiba-padel/index.php">Volver al inicio</a>
        <a class="btn" href="/apiba-padel/login.php">Ingresar</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
