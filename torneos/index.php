<?php
$section = 'public';
$active = 'torneos';
$page_title = 'Torneos - APIBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$estado = trim($_GET['estado'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$sede = trim($_GET['sede'] ?? '');

$params = [];
$where = "WHERE 1=1 ";
if ($estado !== '') { $where .= "AND estado = ? "; $params[] = $estado; }
if ($categoria !== '') { $where .= "AND categoria = ? "; $params[] = $categoria; }
if ($sede !== '') { $where .= "AND sede = ? "; $params[] = $sede; }

$estados = [];
$categorias = [];
$sedes = [];
try {
  $estados = $pdo->query("SELECT DISTINCT estado FROM torneos WHERE estado IS NOT NULL AND estado<>'' ORDER BY estado")->fetchAll(PDO::FETCH_COLUMN);
  $categorias = $pdo->query("SELECT DISTINCT categoria FROM torneos WHERE categoria IS NOT NULL AND categoria<>'' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
  $sedes = $pdo->query("SELECT DISTINCT sede FROM torneos WHERE sede IS NOT NULL AND sede<>'' ORDER BY sede")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

$torneos = [];
try {
  $st = $pdo->prepare("SELECT id, nombre, categoria, sede, fecha_inicio, estado FROM torneos $where ORDER BY fecha_inicio DESC, id DESC");
  $st->execute($params);
  $torneos = $st->fetchAll();
} catch (Exception $e) {}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function estado_badge_class($estado) {
  $e = mb_strtolower(trim((string)$estado));
  if ($e === 'abierto') return 'badge--ok';
  if ($e === 'en curso') return 'badge--warn';
  if ($e === 'finalizado') return 'badge';
  if ($e === 'cancelado') return 'badge--danger';
  return 'badge';
}

function slugify_simple(string $s): string {
  $s = trim(mb_strtolower($s, 'UTF-8'));
  $s = preg_replace('/[^\p{L}\p{N}]+/u', '-', $s);
  $s = trim($s, '-');
  return $s ?: 'sede';
}
function sede_img_src(string $sede): string {
  $slug = slugify_simple($sede);
  // Intento principal: uploads/sedes/<slug>.jpg
  return "/apiba-padel/uploads/sedes/{$slug}.jpg";
}
function fecha_corta($dt): string {
  if (!$dt) return '';
  $ts = strtotime((string)$dt);
  if (!$ts) return (string)$dt;
  return date('d/m/Y', $ts);
}

?>
<div class="card" style="margin:10px 0 14px;">
  <div class="card__header">
    <h1 class="h1" style="margin:0;">🎾 Torneos</h1>
    <span class="badge">Listado</span>
  </div>
  <div class="card__body">
    <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
      <div style="min-width:160px;">
        <label class="label">Estado</label>
        <select class="input" name="estado">
          <option value="">Todos</option>
          <?php foreach($estados as $e): ?>
            <option value="<?= h($e) ?>" <?= $estado===$e?'selected':''; ?>><?= h($e) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="min-width:200px;">
        <label class="label">Categoría</label>
        <select class="input" name="categoria">
          <option value="">Todas</option>
          <?php foreach($categorias as $c): ?>
            <option value="<?= h($c) ?>" <?= $categoria===$c?'selected':''; ?>><?= h($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="min-width:200px;">
        <label class="label">Sede</label>
        <select class="input" name="sede">
          <option value="">Todas</option>
          <?php foreach($sedes as $s): ?>
            <option value="<?= h($s) ?>" <?= $sede===$s?'selected':''; ?>><?= h($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex; gap:10px;">
        <button class="btn btn--primary" type="submit">Filtrar</button>
        <a class="btn" href="/apiba-padel/torneos/">Limpiar</a>
      </div>
    </form>
  </div>
</div>


<div class="noticias-grid" style="margin-top:14px;">
  <?php if (empty($torneos)): ?>
    <div class="card"><div class="card__body"><p class="p">No hay torneos para mostrar.</p></div></div>
  <?php else: ?>
    <?php
      $logged = isset($_SESSION['jugador']) && !empty($_SESSION['jugador']);
    ?>
    <?php foreach($torneos as $t): ?>
      <?php
        $sedeNombre = (string)($t['sede'] ?? '');
        $img = $sedeNombre !== '' ? sede_img_src($sedeNombre) : '/apiba-padel/assets/img/hero.jpg';
        $estadoTxt = (string)($t['estado'] ?? '');
        $isAbierto = (mb_strtolower($estadoTxt, 'UTF-8') === 'abierto');
      ?>
      <article class="card news-card">
        <div class="news-img news-img--small">
          <img src="<?= h($img) ?>"
               alt="<?= h($sedeNombre !== '' ? $sedeNombre : 'Sede') ?>"
               onerror="this.onerror=null; this.src='/apiba-padel/assets/img/hero.jpg';">
        </div>

        <div class="news-meta news-meta--left">
          <?php if ($estadoTxt !== ''): ?>
            <span class="badge <?= estado_badge_class($estadoTxt) ?>"><?= h($estadoTxt) ?></span>
          <?php endif; ?>
          <?php if (!empty($t['fecha_inicio'])): ?>
            <span class="badge">📅 <?= h(fecha_corta($t['fecha_inicio'])) ?></span>
          <?php endif; ?>
        </div>

        <h3 class="h2 news-title" style="margin-top:10px;"><?= h($t['nombre'] ?? '') ?></h3>

        <p class="p news-text" style="margin-top:8px;">
          <?php if(!empty($t['categoria'])): ?>
            <span class="badge">🏷️ <?= h($t['categoria']) ?></span>
          <?php endif; ?>
          <?php if($sedeNombre !== ''): ?>
            <span class="badge">📍 <?= h($sedeNombre) ?></span>
          <?php endif; ?>
        </p>

        <div class="news-actions" style="gap:10px;">
          <a class="btn btn--primary" href="/apiba-padel/torneos/ver.php?id=<?= (int)$t['id'] ?>" style="text-decoration:none;">Ver</a>

          <?php if ($logged && $isAbierto): ?>
            <a class="btn" href="/apiba-padel/torneos/inscribirse.php?id=<?= (int)$t['id'] ?>" style="text-decoration:none;">Inscribirse</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
