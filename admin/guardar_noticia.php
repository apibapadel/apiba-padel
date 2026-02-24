<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function generar_slug(string $texto): string {
  $texto = trim($texto);
  if ($texto === '') return 'noticia';

  $texto = mb_strtolower($texto, 'UTF-8');

  // Reemplazos básicos de acentos/ñ
  $map = [
    'á'=>'a','à'=>'a','ä'=>'a','â'=>'a',
    'é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
    'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
    'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
    'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u',
    'ñ'=>'n',
  ];
  $texto = strtr($texto, $map);

  // Limpia todo menos letras/números/espacios/guiones
  $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
  // Espacios y guiones múltiples → un guión
  $texto = preg_replace('/[\s-]+/', '-', $texto);

  $texto = trim($texto, '-');
  return $texto !== '' ? $texto : 'noticia';
}

function slug_unico(PDO $pdo, string $baseSlug, int $ignoreId = 0): string {
  $slug = $baseSlug;
  $i = 2;

  while (true) {
    if ($ignoreId > 0) {
      $st = $pdo->prepare("SELECT id FROM noticias WHERE slug = ? AND id <> ? LIMIT 1");
      $st->execute([$slug, $ignoreId]);
    } else {
      $st = $pdo->prepare("SELECT id FROM noticias WHERE slug = ? LIMIT 1");
      $st->execute([$slug]);
    }

    $existe = $st->fetch(PDO::FETCH_ASSOC);
    if (!$existe) return $slug;

    $slug = $baseSlug . "-" . $i;
    $i++;
    if ($i > 2000) return $baseSlug . "-" . time(); // fallback extremo
  }
}

function subir_imagen(?array $file): string {
  if (!$file || empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return '';
  }
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    return '';
  }

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $permitidas = ['jpg','jpeg','png','webp'];

  if (!in_array($ext, $permitidas, true)) {
    return '';
  }

  $dir = $_SERVER['DOCUMENT_ROOT'] . '/apiba-padel/uploads/noticias/';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);

  $nuevo = 'noticia_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = $dir . $nuevo;

  if (move_uploaded_file($file['tmp_name'], $dest)) {
    return $nuevo; // guardamos solo el nombre
  }
  return '';
}

// =========================
// LECTURA POST
// =========================
$id         = (int)($_POST['id'] ?? 0);
$titulo     = trim((string)($_POST['titulo'] ?? ''));
$contenido  = trim((string)($_POST['contenido'] ?? ''));
$destacada  = isset($_POST['destacada']) ? 1 : (int)($_POST['destacada'] ?? 0);
$activa     = isset($_POST['activa']) ? 1 : (int)($_POST['activa'] ?? 0);

// Si viene como select "0/1", lo normalizamos
$destacada = ($destacada ? 1 : 0);
$activa    = ($activa ? 1 : 0);

if ($titulo === '' || $contenido === '') {
  header("Location: /apiba-padel/admin/noticias.php?msg=" . urlencode("⚠️ Falta título o contenido"));
  exit;
}

// =========================
// IMAGEN
// =========================
$nuevaImagen = subir_imagen($_FILES['imagen'] ?? null);

// Si estamos editando y NO subimos imagen nueva, mantener la anterior
$imagenFinal = $nuevaImagen;
if ($id > 0 && $imagenFinal === '') {
  try {
    $st = $pdo->prepare("SELECT imagen FROM noticias WHERE id = ? LIMIT 1");
    $st->execute([$id]);
    $old = $st->fetch(PDO::FETCH_ASSOC);
    if ($old && !empty($old['imagen'])) $imagenFinal = $old['imagen'];
  } catch (Exception $e) {}
}

// =========================
// SLUG
// =========================
$baseSlug = generar_slug($titulo);
$slug = slug_unico($pdo, $baseSlug, $id);

// =========================
// INSERT / UPDATE
// =========================
try {
  if ($id > 0) {
    $st = $pdo->prepare("
      UPDATE noticias
      SET titulo = ?, slug = ?, contenido = ?, imagen = ?, destacada = ?, activa = ?
      WHERE id = ?
      LIMIT 1
    ");
    $st->execute([$titulo, $slug, $contenido, $imagenFinal, $destacada, $activa, $id]);

    header("Location: /apiba-padel/admin/noticias.php?msg=" . urlencode("✅ Noticia actualizada correctamente"));
    exit;

  } else {
    $st = $pdo->prepare("
      INSERT INTO noticias (titulo, slug, contenido, imagen, destacada, fecha_publicacion, activa)
      VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $st->execute([$titulo, $slug, $contenido, $imagenFinal, $destacada, $activa]);

    header("Location: /apiba-padel/admin/noticias.php?msg=" . urlencode("✅ Noticia creada correctamente"));
    exit;
  }

} catch (Exception $e) {
  header("Location: /apiba-padel/admin/noticias.php?msg=" . urlencode("❌ Error al guardar: " . $e->getMessage()));
  exit;
}
