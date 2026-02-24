<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

function generar_slug(string $texto): string {
  $texto = trim($texto);
  if ($texto === '') return 'noticia';

  $texto = mb_strtolower($texto, 'UTF-8');
  $map = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','é'=>'e','è'=>'e','ë'=>'e','ê'=>'e','í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o','ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ñ'=>'n'];
  $texto = strtr($texto, $map);

  $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
  $texto = preg_replace('/[\s-]+/', '-', $texto);
  $texto = trim($texto, '-');

  return $texto !== '' ? $texto : 'noticia';
}

function slug_unico(PDO $pdo, string $baseSlug, int $ignoreId): string {
  $slug = $baseSlug;
  $i = 2;
  while (true) {
    $st = $pdo->prepare("SELECT id FROM noticias WHERE slug = ? AND id <> ? LIMIT 1");
    $st->execute([$slug, $ignoreId]);
    if (!$st->fetch()) return $slug;
    $slug = $baseSlug . "-" . $i;
    $i++;
    if ($i > 2000) return $baseSlug . "-" . time();
  }
}

$rows = $pdo->query("SELECT id, titulo, slug FROM noticias ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$ok = 0;
foreach ($rows as $r) {
  $id = (int)$r['id'];
  $nuevoBase = generar_slug((string)$r['titulo']);
  $nuevo = slug_unico($pdo, $nuevoBase, $id);

  if ((string)$r['slug'] !== $nuevo) {
    $st = $pdo->prepare("UPDATE noticias SET slug = ? WHERE id = ? LIMIT 1");
    $st->execute([$nuevo, $id]);
    $ok++;
  }
}

echo "Slugs normalizados: $ok\n";
