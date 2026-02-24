<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$n = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$n) die("Noticia no encontrada");

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $fecha = trim($_POST['fecha_publicacion'] ?? $n['fecha_publicacion']);
    $activa = (int)($_POST['activa'] ?? 1);
    $destacada = (int)($_POST['destacada'] ?? 0);

    if ($titulo === '' || $contenido === '') {
        $err = "Título y contenido son obligatorios.";
    } else {
        $imagenNombre = $n['imagen'] ?? null;

        // Si sube nueva imagen, reemplaza
        if (!empty($_FILES['imagen']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg','jpeg','png','webp'];

            if (!in_array($ext, $permitidas, true)) {
                $err = "Imagen inválida. Solo: jpg, jpeg, png, webp.";
            } elseif ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $err = "Imagen demasiado grande (máx 2MB).";
            } else {
                $dir = $_SERVER['DOCUMENT_ROOT'] . "/apiba-padel/uploads/noticias";
                if (!is_dir($dir)) mkdir($dir, 0777, true);

                $nuevo = uniqid('not_') . "." . $ext;
                $destino = $dir . "/" . $nuevo;

                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                    $err = "No se pudo guardar la imagen.";
                } else {
                    // borrar vieja si existe
                    if (!empty($imagenNombre)) {
                        $vieja = $dir . "/" . $imagenNombre;
                        if (file_exists($vieja)) @unlink($vieja);
                    }
                    $imagenNombre = $nuevo;
                }
            }
        }

        if (!$err) {
            $stmt = $pdo->prepare("
              UPDATE noticias
              SET titulo=?, contenido=?, fecha_publicacion=?, activa=?, destacada=?, imagen=?
              WHERE id=?
            ");
            $stmt->execute([$titulo, $contenido, $fecha, $activa, $destacada, $imagenNombre, $id]);

            header("Location: noticias.php?msg=" . urlencode("Noticia actualizada correctamente."));
            exit;
        }
    }
}

include '_header.php';
?>

<h2>Editar noticia</h2>
<p class="muted"><b><?= htmlspecialchars($n['titulo']) ?></b> — ID #<?= (int)$n['id'] ?></p>

<?php if ($err): ?>
  <div class="msg" style="background:#ffe9e9;border-color:#f2a2a2">❌ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" enctype="multipart/form-data" autocomplete="off">

    <div class="field">
      <label>Título *</label>
      <input name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? $n['titulo']) ?>">
    </div>

    <div class="field" style="margin-top:12px">
      <label>Contenido *</label>
      <textarea name="contenido" required style="min-height:180px;padding:10px;border-radius:10px;border:1px solid #ddd;width:100%"><?= htmlspecialchars($_POST['contenido'] ?? $n['contenido']) ?></textarea>
    </div>

    <div class="grid" style="margin-top:12px">
      <div class="field">
        <label>Fecha publicación</label>
        <input name="fecha_publicacion" type="date" value="<?= htmlspecialchars($_POST['fecha_publicacion'] ?? $n['fecha_publicacion']) ?>">
      </div>

      <div class="field">
        <label>Activa</label>
        <?php $a = (string)($_POST['activa'] ?? $n['activa']); ?>
        <select name="activa">
          <option value="1" <?= $a==='1'?'selected':'' ?>>Sí</option>
          <option value="0" <?= $a==='0'?'selected':'' ?>>No</option>
        </select>
      </div>

      <div class="field">
        <label>Destacada</label>
        <?php $d = (string)($_POST['destacada'] ?? $n['destacada']); ?>
        <select name="destacada">
          <option value="1" <?= $d==='1'?'selected':'' ?>>Sí</option>
          <option value="0" <?= $d==='0'?'selected':'' ?>>No</option>
        </select>
      </div>

      <div class="field">
        <label>Nueva imagen (opcional)</label>
        <input type="file" name="imagen">
      </div>
    </div>

    <?php if (!empty($n['imagen'])): ?>
      <div style="margin-top:12px">
        <span class="muted">Imagen actual:</span><br>
        <img src="/apiba-padel/uploads/noticias/<?= htmlspecialchars($n['imagen']) ?>" style="max-width:320px;border-radius:12px;border:1px solid #ddd">
      </div>
    <?php endif; ?>

    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap">
      <button class="btn btn-ok" type="submit">Guardar cambios</button>
      <a class="btn btn-soft" href="noticias.php">Volver</a>
      <a class="btn btn-soft" href="/apiba-padel/noticias/ver.php?id=<?= (int)$n['id'] ?>" target="_blank">Ver público</a>
    </div>

  </form>
</div>

<?php include '_footer.php'; ?>
