<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function limpiar_texto($t){
  $t = strip_tags((string)$t);
  $t = preg_replace('/\s+/', ' ', $t);
  return trim($t);
}

$id   = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

$n = null;

// ===============================
// 1) Buscar por slug
// ===============================
if ($slug !== '') {
  $st = $pdo->prepare("SELECT * FROM noticias WHERE slug = ? AND activa = 1 LIMIT 1");
  $st->execute([$slug]);
  $n = $st->fetch(PDO::FETCH_ASSOC);
}
// ===============================
// 2) Buscar por id (redirigir)
// ===============================
elseif ($id > 0) {
  $st = $pdo->prepare("SELECT * FROM noticias WHERE id = ? AND activa = 1 LIMIT 1");
  $st->execute([$id]);
  $n = $st->fetch(PDO::FETCH_ASSOC);

  if ($n && !empty($n['slug'])) {
    header("Location: /apiba-padel/noticia/" . $n['slug'], true, 301);
    exit;
  }
}

if (!$n) {
  http_response_code(404);
  $section = 'public';
  $active = 'noticias';
  $page_title = 'Noticia no encontrada';
  require_once __DIR__ . '/../includes/site_header.php';
  echo '<div class="card"><div class="card__body"><p class="p">Noticia no encontrada.</p></div></div>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

// ===============================
// SEO dinámico
// ===============================
$titulo = $n['titulo'];
$descripcion = mb_substr(limpiar_texto($n['contenido']), 0, 160);
$imagen = !empty($n['imagen'])
  ? "http://localhost/apiba-padel/uploads/noticias/" . $n['imagen']
  : "http://localhost/apiba-padel/assets/img/logo-apiba.png";

$urlCanonica = !empty($n['slug'])
  ? "http://localhost/apiba-padel/noticia/" . $n['slug']
  : "http://localhost/apiba-padel/public/noticia.php?id=" . $n['id'];

$section = 'public';
$active = 'noticias';
$page_title = $titulo;

// ===============================
// Header
// ===============================
require_once __DIR__ . '/../includes/site_header.php';
?>

<!-- =============================== -->
<!-- SEO / Open Graph -->
<!-- =============================== -->
<link rel="canonical" href="<?= h($urlCanonica) ?>">

<meta name="description" content="<?= h($descripcion) ?>">

<meta property="og:type" content="article">
<meta property="og:title" content="<?= h($titulo) ?>">
<meta property="og:description" content="<?= h($descripcion) ?>">
<meta property="og:image" content="<?= h($imagen) ?>">
<meta property="og:url" content="<?= h($urlCanonica) ?>">
<meta property="og:site_name" content="APiBA Pádel">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($titulo) ?>">
<meta name="twitter:description" content="<?= h($descripcion) ?>">
<meta name="twitter:image" content="<?= h($imagen) ?>">

<div class="card" style="margin-bottom:14px;">
  <div class="card__header">
    <a class="btn" href="/apiba-padel/public/noticias.php">⬅ Volver</a>
  </div>
</div>

<div class="card">
  <div class="card__header">
    <h1 class="h1"><?= h($titulo) ?></h1>
    <span class="badge"><?= date('d/m/Y H:i', strtotime($n['fecha_publicacion'])) ?></span>
  </div>

  <div class="card__body">

    <?php if (!empty($n['imagen'])): ?>
      <div style="margin:-10px -10px 14px -10px;overflow:hidden;border-radius:14px;">
        <img src="/apiba-padel/uploads/noticias/<?= h($n['imagen']) ?>"
             style="width:100%;height:380px;object-fit:cover;"
             alt="<?= h($titulo) ?>">
      </div>
    <?php endif; ?>

    <div class="p" style="line-height:1.7;">
      <?= nl2br(h($n['contenido'])) ?>
    </div>

    <div style="height:14px;"></div>
    <div class="muted" style="font-size:12.5px;">Publicado: <?= !empty($n['fecha_publicacion']) ? date('d/m/Y', strtotime($n['fecha_publicacion'])) : '' ?></div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
