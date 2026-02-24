<?php
$section = 'public';
$active = 'ranking';
$page_title = 'Jugador - APiBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$id = (int)($_GET['id'] ?? 0);

$cat = trim((string)($_GET['cat'] ?? ''));
if ($id <= 0) {
  echo "<div class='card'><div class='card__body'><p class='p'>Jugador inválido.</p></div></div>";
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

$jugador = [];
try {
  $stmt = $pdo->prepare("SELECT id, apellido, nombre, foto, categoria, puntos, ranking, activo FROM jugadores WHERE id=? LIMIT 1");
  $stmt->execute([$id]);
  $jugador = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
  $jugador = [];
}

if (!$jugador || (int)($jugador['activo'] ?? 0) !== 1) {
  echo "<div class='card'><div class='card__body'><p class='p'>Jugador no encontrado.</p></div></div>";
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

// Foto robusta
$foto_raw = trim((string)($jugador['foto'] ?? ''));
$foto_src = "/apiba-padel/assets/img/user-placeholder.png";
if ($foto_raw !== '') {
  if (preg_match('~^https?://~i', $foto_raw)) $foto_src = $foto_raw;
  elseif (strpos($foto_raw, '/apiba-padel/') === 0) $foto_src = $foto_raw;
  elseif ($foto_raw[0] === '/') $foto_src = $foto_raw;
  elseif (strpos($foto_raw, 'uploads/') === 0) $foto_src = "/apiba-padel/" . $foto_raw;
  else $foto_src = "/apiba-padel/uploads/jugadores/" . $foto_raw;
}
?>

<div class="grid grid--2">

  <div class="card">
    <div class="card__header">
      <h1 class="h1">Jugador</h1>
      <span class="badge">Perfil público</span>
    </div>

    <div class="card__body" style="display:flex; gap:14px; align-items:center; flex-wrap:wrap;">
      <img
        src="<?= h($foto_src) ?>"
        alt="Foto"
        style="width:110px;height:110px;object-fit:cover;border-radius:16px;border:1px solid var(--border);"
      >

      <div style="flex:1; min-width:240px;">
        <div class="h2" style="margin:0;">
          <?= h($jugador['apellido'] ?? '') ?> <?= h($jugador['nombre'] ?? '') ?>
        </div>

        <div style="height:10px;"></div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <span class="badge badge--warn">Categoría: <?= h($jugador['categoria'] ?? '—') ?></span>
          <span class="badge">Puntos: <?= (int)($jugador['puntos'] ?? 0) ?></span>
          <?php if ((int)($jugador['ranking'] ?? 0) > 0): ?>
            <span class="badge badge--ok">Puesto: #<?= (int)$jugador['ranking'] ?></span>
          <?php else: ?>
            <span class="badge">Puesto: —</span>
          <?php endif; ?>
        </div>

        <div style="height:12px;"></div>

        <div class="form-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
         <a class="btn" href="/apiba-padel/ranking/<?= $cat !== '' ? '?cat='.urlencode($cat) : '' ?>">Volver al ranking</a>
          <a class="btn" href="/apiba-padel/torneos/">Ver torneos</a>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card__header">
      <h2 class="h2">Info</h2>
      <span class="badge">Ranking</span>
    </div>
    <div class="card__body">
      <p class="p" style="margin:0;">
        Este es un perfil público: no muestra datos sensibles (email, DNI, teléfono).
      </p>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
