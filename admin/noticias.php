<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

include '_header.php';

$msg = $_GET['msg'] ?? '';

function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    return false;
  }
}

$hasSlug = column_exists($pdo, 'noticias', 'slug');

$noticias = [];
try {
  if ($hasSlug) {
    $noticias = $pdo->query("
      SELECT id, titulo, slug, fecha_publicacion, activa, destacada
      FROM noticias
      ORDER BY fecha_publicacion DESC, id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $noticias = $pdo->query("
      SELECT id, titulo, fecha_publicacion, activa, destacada
      FROM noticias
      ORDER BY fecha_publicacion DESC, id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Exception $e) {
  $noticias = [];
}

function url_publica_noticia(array $n): string {
  $id = (int)($n['id'] ?? 0);
  $slug = trim((string)($n['slug'] ?? ''));

  if ($slug !== '') {
    // URL limpia (con htaccess)
    return "/apiba-padel/noticia/" . rawurlencode($slug);
  }
  // fallback seguro
  return "/apiba-padel/public/noticia.php?id=" . $id;
}
?>

<h2>Noticias</h2>
<p class="muted">Administrá noticias del sitio (activar, destacar y editar).</p>

<?php if ($msg): ?>
  <div class="msg">✅ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card">
  <div class="toolbar">
    <a class="btn btn-ok btn-sm" href="nueva_noticia.php">➕ Nueva noticia</a>
    <a class="btn btn-soft btn-sm" href="/apiba-padel/index.php" target="_blank">Ver sitio</a>
    <a class="btn btn-sm" href="/apiba-padel/public/noticias.php" target="_blank">📰 Ver noticias (público)</a>
  </div>
</div>

<br>

<?php if (empty($noticias)): ?>
  <div class="card">
    ⚠️ No hay noticias cargadas o la tabla <b>noticias</b> no existe.
  </div>
<?php else: ?>
  <table>
    <tr>
      <th style="width:80px">ID</th>
      <th>Título</th>
      <th style="width:170px">Fecha</th>
      <th style="width:120px">Activa</th>
      <th style="width:130px">Destacada</th>
      <th style="width:260px">Acciones</th>
    </tr>

    <?php foreach($noticias as $n): ?>
      <tr>
        <td><b><?= (int)$n['id'] ?></b></td>

        <td>
          <b><?= htmlspecialchars($n['titulo'] ?? '') ?></b>
          <?php if (!empty($n['slug'])): ?>
            <div class="muted" style="font-size:12px;margin-top:4px;">
              slug: <code><?= htmlspecialchars($n['slug']) ?></code>
            </div>
          <?php endif; ?>
        </td>

        <td><?= htmlspecialchars($n['fecha_publicacion'] ?? '') ?></td>

        <td>
          <?php if ((int)($n['activa'] ?? 0) === 1): ?>
            <span class="badge badge-ok">Sí</span>
          <?php else: ?>
            <span class="badge badge-no">No</span>
          <?php endif; ?>
        </td>

        <td>
          <?php if ((int)($n['destacada'] ?? 0) === 1): ?>
            <span class="badge badge-ok">Sí</span>
          <?php else: ?>
            <span class="badge">No</span>
          <?php endif; ?>
        </td>

        <td style="white-space:nowrap">
          <a class="btn btn-soft btn-sm" href="editar_noticia.php?id=<?= (int)$n['id'] ?>">Editar</a>

          <!-- ✅ Ver público (URL limpia si hay slug, si no fallback por id) -->
          <a class="btn btn-sm" href="<?= htmlspecialchars(url_publica_noticia($n)) ?>" target="_blank">Ver público</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p class="muted" style="margin-top:10px">
    * “Ver público” usa la URL limpia <b>/noticia/slug</b> si existe, y si no usa <b>/public/noticia.php?id=</b>.
  </p>
<?php endif; ?>

<?php include '_footer.php'; ?>
