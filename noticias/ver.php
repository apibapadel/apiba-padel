<?php
require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$n = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$n || (int)($n['activa'] ?? 0) !== 1) {
    die("Noticia no encontrada o inactiva");
}
?>

<h1><?= htmlspecialchars($n['titulo'] ?? '') ?></h1>
<p class="muted">📅 <?= htmlspecialchars($n['fecha_publicacion'] ?? '') ?></p>

<?php if (!empty($n['imagen'])): ?>
  <img src="/apiba-padel/uploads/noticias/<?= htmlspecialchars($n['imagen']) ?>"
       style="max-width:900px;width:100%;border-radius:12px;border:1px solid #ddd;margin:10px 0">
<?php endif; ?>

<div style="line-height:1.5">
  <?= nl2br(htmlspecialchars($n['contenido'] ?? '')) ?>
</div>

<br>
<a href="/apiba-padel/index.php">⬅ Volver al inicio</a>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
