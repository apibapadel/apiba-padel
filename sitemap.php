<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

header("Content-Type: application/xml; charset=utf-8");

function iso_date($dt): string {
  $dt = trim((string)$dt);
  if ($dt === '') return date('Y-m-d');
  $ts = strtotime($dt);
  if (!$ts || $ts <= 0) return date('Y-m-d');
  return date('Y-m-d', $ts);
}

$base = "http://localhost/apiba-padel";

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <!-- Páginas principales -->
  <url>
    <loc><?= htmlspecialchars($base . "/", ENT_QUOTES, 'UTF-8') ?></loc>
    <lastmod><?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?></lastmod>
  </url>
  <url>
    <loc><?= htmlspecialchars($base . "/public/noticias.php", ENT_QUOTES, 'UTF-8') ?></loc>
    <lastmod><?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?></lastmod>
  </url>

<?php
$stmt = $pdo->query("
  SELECT slug, fecha_publicacion
  FROM noticias
  WHERE activa = 1 AND slug IS NOT NULL AND slug <> ''
  ORDER BY fecha_publicacion DESC, id DESC
");
while ($n = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $slug = trim((string)$n['slug']);
  if ($slug === '') continue;

  $loc = $base . "/noticia/" . $slug;
  $lastmod = iso_date($n['fecha_publicacion'] ?? '');
  ?>
  <url>
    <loc><?= htmlspecialchars($loc, ENT_QUOTES, 'UTF-8') ?></loc>
    <lastmod><?= htmlspecialchars($lastmod, ENT_QUOTES, 'UTF-8') ?></lastmod>
  </url>
  <?php
}
?>

</urlset>
