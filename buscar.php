<?php
$section = 'public';
$active = 'buscar';
$page_title = 'Buscar - APIBA Pádel';

require_once __DIR__ . '/includes/site_header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDB();

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$q = trim((string)($_GET['q'] ?? ''));
$like = '%' . $q . '%';

$torneos = [];
$noticias = [];

if ($q !== '') {
  try {
    $st = $pdo->prepare("SELECT id, nombre, categoria, sede, fecha_inicio, estado FROM torneos
                        WHERE nombre LIKE ? OR categoria LIKE ? OR sede LIKE ?
                        ORDER BY (estado='Abierto') DESC, (estado='En Curso') DESC, fecha_inicio ASC");
    $st->execute([$like,$like,$like]);
    $torneos = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) { $torneos = []; }

  try {
    $st = $pdo->prepare("SELECT id, titulo, imagen, fecha_publicacion FROM noticias
                        WHERE activa=1 AND titulo LIKE ?
                        ORDER BY fecha_publicacion DESC, id DESC");
    $st->execute([$like]);
    $noticias = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) { $noticias = []; }
}

function sede_img(string $sede): string {
  $s = mb_strtolower(trim($sede));
  $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
  if (str_contains($s, 'casbas')) return '/apiba-padel/assets/img/sedes/casbas-padel.jpg';
  if (str_contains($s, 'fray')) return '/apiba-padel/assets/img/sedes/fray-padel.jpg';
  if (str_contains($s, 'quinta')) return '/apiba-padel/assets/img/sedes/la-quinta-padel.jpg';
  if (str_contains($s, '90')) return '/apiba-padel/assets/img/sedes/90s-padel.jpg';
  return '/apiba-padel/assets/img/hero.jpg';
}
function estado_badge_class($estado) {
  $e = mb_strtolower(trim((string)$estado));
  if ($e === 'abierto') return 'badge badge--ok';
  if ($e === 'en curso') return 'badge badge--warn';
  if ($e === 'finalizado') return 'badge';
  if ($e === 'cancelado') return 'badge badge--danger';
  return 'badge';
}

?>

<div class="card">
  <div class="card__body">
    <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
      <div>
        <h1 style="margin:0;">🔎 Buscar</h1>
        <div class="muted">Torneos por sede/categoría/nombre · Noticias por título</div>
      </div>
      <span class="badge">Resultados</span>
    </div>

    <form method="get" style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <input class="input" type="text" name="q" value="<?= h($q) ?>" placeholder="Ej: La Quinta, 5TA Caballeros, Finalizado..." style="flex:1; min-width:240px;">
      <button class="btn btn--primary" type="submit">Buscar</button>
      <a class="btn" href="/apiba-padel/buscar.php">Limpiar</a>
    </form>
  </div>
</div>

<div style="height:14px;"></div>

<?php if ($q === ''): ?>
  <div class="card"><div class="card__body"><p class="p">Escribí algo en el buscador para ver resultados.</p></div></div>
<?php else: ?>

  <div class="grid grid--2">

    <div class="card">
      <div class="card__header">
        <h2 class="h2" style="margin:0;">🎾 Torneos</h2>
        <span class="badge"><?= (int)count($torneos) ?></span>
      </div>
      <div class="card__body">
        <?php if (empty($torneos)): ?>
          <p class="p">No se encontraron torneos.</p>
        <?php else: ?>
          <div class="ml-list">
            <?php foreach ($torneos as $t): ?>
              <div class="ml-item">
                <div class="ml-item__img" style="background-image:url('<?= h(sede_img($t['sede'] ?? '')) ?>');"></div>
                <div class="ml-item__body">
                  <p class="ml-item__title"><?= h($t['nombre'] ?? '') ?></p>
                  <div class="ml-item__meta">
                    <?php if (!empty($t['categoria'])): ?><span class="badge">🎾 <?= h($t['categoria']) ?></span><?php endif; ?>
                    <?php if (!empty($t['sede'])): ?><span class="badge">📍 <?= h($t['sede']) ?></span><?php endif; ?>
                    <?php if (!empty($t['fecha_inicio'])): ?><span class="badge">📅 <?= h($t['fecha_inicio']) ?></span><?php endif; ?>
                    <span class="<?= h(estado_badge_class($t['estado'] ?? '')) ?>"><?= h($t['estado'] ?? '') ?></span>
                  </div>
                  <div class="ml-item__cta">
                    <a class="btn btn--primary" href="/apiba-padel/torneos/ver.php?id=<?= (int)$t['id'] ?>">Ver torneo</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card__header">
        <h2 class="h2" style="margin:0;">📰 Noticias</h2>
        <span class="badge"><?= (int)count($noticias) ?></span>
      </div>
      <div class="card__body">
        <?php if (empty($noticias)): ?>
          <p class="p">No se encontraron noticias.</p>
        <?php else: ?>
          <div style="display:grid; gap:12px;">
            <?php foreach ($noticias as $n): ?>
              <div class="card" style="margin:0;">
                <div class="card__body">
                  <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; align-items:center;">
                    <span class="badge"><?= h($n['fecha_publicacion'] ?? '') ?></span>
                    <span class="badge">Noticia</span>
                  </div>
                  <div style="height:10px;"></div>
                  <div style="font-weight:900; font-size:1.05rem;">
                    <?= h($n['titulo'] ?? '') ?>
                  </div>
                  <div style="height:12px;"></div>
                  <a class="btn btn--primary" href="/apiba-padel/public/noticia.php?id=<?= (int)$n['id'] ?>">Leer</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
