<?php
$section = 'public';
$active = 'noticias';
$page_title = 'Noticias - APiBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['p'] ?? 1));
$per_page = 9;
$offset = ($page - 1) * $per_page;

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fmt_fecha($dt){
  if (!$dt) return '';
  $ts = strtotime((string)$dt);
  if (!$ts) return (string)$dt;
  return date('d/m/Y H:i', $ts);
}
function img_src($base, $img){
  $img = trim((string)$img);
  if ($img === '') return '';
  return $base . "/uploads/noticias/" . $img;
}
function resumen_auto($text, $max = 160){
  $t = trim(preg_replace('/\s+/', ' ', (string)$text));
  if (mb_strlen($t) <= $max) return $t;
  return mb_substr($t, 0, $max-1) . "…";
}

/* =========================
   DESTACADAS (top)
   ========================= */
$destacadas = [];
try {
  $destacadas = $pdo->query("
    SELECT id, titulo, contenido, imagen, fecha_publicacion
    FROM noticias
    WHERE activa = 1 AND destacada = 1
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 3
  ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $destacadas = []; }

/* =========================
   TOTAL + LISTADO (con búsqueda)
   ========================= */
$total = 0;
$noticias = [];
try {
  $where = "activa = 1";
  $params = [];

  if ($q !== '') {
    $where .= " AND (titulo LIKE ? OR contenido LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
  }

  $st = $pdo->prepare("SELECT COUNT(*) FROM noticias WHERE $where");
  $st->execute($params);
  $total = (int)$st->fetchColumn();

  $st = $pdo->prepare("
    SELECT id, titulo, contenido, imagen, destacada, fecha_publicacion
    FROM noticias
    WHERE $where
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT $per_page OFFSET $offset
  ");
  $st->execute($params);
  $noticias = $st->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
  $total = 0;
  $noticias = [];
}

$pages = max(1, (int)ceil($total / $per_page));
?>

<!-- Encabezado -->
<div class="card" style="margin-bottom:14px;">
  <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <h1 class="h1" style="margin:0;">📰 Noticias</h1>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <a class="btn" href="<?= $base ?>/index.php" style="text-decoration:none;">⬅ Inicio</a>
    </div>
  </div>

  <div class="card__body">
    <form method="get" action="" class="noticias-toolbar">
      <input
        type="text"
        name="q"
        value="<?= h($q) ?>"
        placeholder="Buscar por título o contenido…"
      >
      <button class="btn btn--primary" type="submit">Buscar</button>
      <?php if ($q !== ''): ?>
        <a class="btn" href="<?= $base ?>/public/noticias.php" style="text-decoration:none;">Limpiar</a>
      <?php endif; ?>
    </form>

    <div class="noticias-toolbar__meta">
      <span class="badge"><?= (int)$total ?> publicadas</span>
      <?php if ($pages > 1): ?>
        <span class="badge">Página <?= (int)$page ?> / <?= (int)$pages ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($destacadas) && $q === '' && $page === 1): ?>
  <!-- Destacadas -->
  <div class="card" style="margin-bottom:14px;">
    <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
      <h2 class="h2" style="margin:0;">⭐ Destacadas</h2>
      <span class="badge badge--ok">Top</span>
    </div>

    <div class="card__body">
      <div class="noticias-grid">
        <?php foreach ($destacadas as $n): ?>
          <?php
            $img = img_src($base, $n['imagen'] ?? '');
            $texto = resumen_auto($n['contenido'] ?? '', 190);
          ?>
          <article class="card news-card">
            <?php if ($img !== ''): ?>
              <div class="news-img">
                <img src="<?= h($img) ?>" alt="<?= h($n['titulo'] ?? '') ?>"
                     onerror="this.style.display='none';">
              </div>
            <?php endif; ?>

            <div class="news-meta">
              <span class="badge"><?= h(fmt_fecha($n['fecha_publicacion'] ?? '')) ?></span>
              <span class="badge badge--ok">⭐ Destacada</span>
            </div>

            <h3 class="h2 news-title"><?= h($n['titulo'] ?? '') ?></h3>
            <p class="p news-text"><?= h($texto) ?></p>

            <div class="news-actions">
              <a class="btn btn--primary" href="<?= $base ?>/public/noticia.php?id=<?= (int)$n['id'] ?>" style="text-decoration:none;">Leer</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Listado -->
<div class="card">
  <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <h2 class="h2" style="margin:0;"><?= $q !== '' ? "Resultados" : "Todas" ?></h2>
    <span class="badge">Recientes</span>
  </div>

  <div class="card__body">
    <?php if (empty($noticias)): ?>
      <p class="p">No hay noticias para mostrar.</p>
    <?php else: ?>
      <div class="noticias-grid">
        <?php foreach ($noticias as $n): ?>
          <?php
            $img = img_src($base, $n['imagen'] ?? '');
            $texto = resumen_auto($n['contenido'] ?? '', 160);
          ?>
          <article class="card news-card">
            <?php if ($img !== ''): ?>
              <div class="news-img news-img--small">
                <img src="<?= h($img) ?>" alt="<?= h($n['titulo'] ?? '') ?>"
                     onerror="this.style.display='none';">
              </div>
            <?php endif; ?>

            <div class="news-meta news-meta--left">
              <span class="badge"><?= h(fmt_fecha($n['fecha_publicacion'] ?? '')) ?></span>
              <?php if (!empty($n['destacada'])): ?>
                <span class="badge badge--ok">⭐ Destacada</span>
              <?php endif; ?>
            </div>

            <h3 class="h2 news-title"><?= h($n['titulo'] ?? '') ?></h3>
            <p class="p news-text"><?= h($texto) ?></p>

            <div class="news-actions">
              <a class="btn" href="<?= $base ?>/public/noticia.php?id=<?= (int)$n['id'] ?>" style="text-decoration:none;">Leer</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($pages > 1): ?>
        <div class="noticias-pager">
          <div class="noticias-pager__btns">
            <?php
              $baseUrl = $base . "/public/noticias.php";
              $queryBase = ($q !== '') ? "q=" . urlencode($q) . "&" : "";
            ?>
            <?php if ($page > 1): ?>
              <a class="btn" href="<?= $baseUrl ?>?<?= $queryBase ?>p=<?= $page-1 ?>">⬅ Anterior</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
              <a class="btn btn--primary" href="<?= $baseUrl ?>?<?= $queryBase ?>p=<?= $page+1 ?>">Siguiente ➡</a>
            <?php endif; ?>
          </div>
          <span class="badge"><?= (int)$total ?> noticias</span>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
