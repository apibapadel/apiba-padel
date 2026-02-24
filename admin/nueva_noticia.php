<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$err = null;

function generar_slug(string $texto): string {
    $texto = trim($texto);
    if ($texto === '') return 'noticia';

    $texto = mb_strtolower($texto, 'UTF-8');

    // Reemplazos básicos (acentos/ñ)
    $map = [
        'á'=>'a','à'=>'a','ä'=>'a','â'=>'a',
        'é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
        'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
        'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
        'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u',
        'ñ'=>'n',
    ];
    $texto = strtr($texto, $map);

    // Quitar caracteres raros (dejar letras/números/espacios/guiones)
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    // Espacios y guiones repetidos -> un guión
    $texto = preg_replace('/[\s-]+/', '-', $texto);

    $texto = trim($texto, '-');
    return $texto !== '' ? $texto : 'noticia';
}

function slug_unico(PDO $pdo, string $baseSlug): string {
    $slug = $baseSlug;
    $i = 2;

    while (true) {
        $st = $pdo->prepare("SELECT id FROM noticias WHERE slug = ? LIMIT 1");
        $st->execute([$slug]);
        $existe = $st->fetch(PDO::FETCH_ASSOC);

        if (!$existe) return $slug;

        $slug = $baseSlug . "-" . $i;
        $i++;
        if ($i > 2000) return $baseSlug . "-" . time(); // fallback extremo
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $fecha = trim($_POST['fecha_publicacion'] ?? date('Y-m-d'));
    $activa = (int)($_POST['activa'] ?? 1);
    $destacada = (int)($_POST['destacada'] ?? 0);

    if ($titulo === '' || $contenido === '') {
        $err = "Título y contenido son obligatorios.";
    } else {
        // ✅ SLUG PRO (único)
        $baseSlug = generar_slug($titulo);
        $slug = slug_unico($pdo, $baseSlug);

        // Imagen opcional
        $imagenNombre = null;

        if (!empty($_FILES['imagen']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg','jpeg','png','webp'];

            if (!in_array($ext, $permitidas, true)) {
                $err = "Imagen inválida. Solo: jpg, jpeg, png, webp.";
            } elseif (($_FILES['imagen']['size'] ?? 0) > 2 * 1024 * 1024) {
                $err = "Imagen demasiado grande (máx 2MB).";
            } else {
                $dir = $_SERVER['DOCUMENT_ROOT'] . "/apiba-padel/uploads/noticias";
                if (!is_dir($dir)) mkdir($dir, 0777, true);

                $imagenNombre = uniqid('not_') . "." . $ext;
                $destino = $dir . "/" . $imagenNombre;

                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                    $err = "No se pudo guardar la imagen.";
                }
            }
        }

        if (!$err) {
            $stmt = $pdo->prepare("
              INSERT INTO noticias (titulo, slug, contenido, fecha_publicacion, activa, destacada, imagen)
              VALUES (?,?,?,?,?,?,?)
            ");
            $stmt->execute([$titulo, $slug, $contenido, $fecha, $activa, $destacada, $imagenNombre]);

            header("Location: noticias.php?msg=" . urlencode("Noticia creada correctamente."));
            exit;
        }
    }
}

include '_header.php';
?>

<h2>➕ Nueva noticia</h2>
<p class="muted">Creá una noticia. Podés activarla y marcarla como destacada.</p>

<?php if ($err): ?>
  <div class="msg" style="background:#ffe9e9;border-color:#f2a2a2">❌ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" enctype="multipart/form-data" autocomplete="off">

    <div class="field">
      <label>Título *</label>
      <input name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
    </div>

    <div class="field" style="margin-top:12px">
      <label>Contenido *</label>
      <textarea name="contenido" required style="min-height:180px;padding:10px;border-radius:10px;border:1px solid #ddd;width:100%"><?= htmlspecialchars($_POST['contenido'] ?? '') ?></textarea>
    </div>

    <div class="grid" style="margin-top:12px">
      <div class="field">
        <label>Fecha publicación</label>
        <input name="fecha_publicacion" type="date" value="<?= htmlspecialchars($_POST['fecha_publicacion'] ?? date('Y-m-d')) ?>">
      </div>

      <div class="field">
        <label>Activa</label>
        <?php $a = $_POST['activa'] ?? '1'; ?>
        <select name="activa">
          <option value="1" <?= $a==='1'?'selected':'' ?>>Sí</option>
          <option value="0" <?= $a==='0'?'selected':'' ?>>No</option>
        </select>
      </div>

      <div class="field">
        <label>Destacada</label>
        <?php $d = $_POST['destacada'] ?? '0'; ?>
        <select name="destacada">
          <option value="1" <?= $d==='1'?'selected':'' ?>>Sí</option>
          <option value="0" <?= $d==='0'?'selected':'' ?>>No</option>
        </select>
      </div>

      <div class="field">
        <label>Imagen (opcional, máx 2MB)</label>
        <input type="file" name="imagen">
      </div>
    </div>

    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap">
      <button class="btn btn-ok" type="submit">Guardar noticia</button>
      <a class="btn btn-soft" href="noticias.php">Cancelar</a>
    </div>

  </form>
</div>

<?php include '_footer.php'; ?>
