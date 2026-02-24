<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$section = 'public';
$active = 'perfil';
$page_title = 'Editar perfil - APIBA Pádel';

require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['jugador'])) {
  header("Location: /apiba-padel/login.php");
  exit;
}

$pdo = getDB();

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function ensure_csrf() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
$csrf = ensure_csrf();

$jugador_id = (int)($_SESSION['jugador']['id'] ?? 0);
if ($jugador_id <= 0) {
  echo "<div class='card'><div class='card__body'><p class='p'>No se pudo identificar el jugador.</p></div></div>";
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

// Traer jugador actual
$jugador = [];
try {
  $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id = ? LIMIT 1");
  $stmt->execute([$jugador_id]);
  $jugador = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {}

if (!$jugador) {
  echo "<div class='card'><div class='card__body'><p class='p'>Jugador no encontrado.</p></div></div>";
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

$ok = '';
$err = '';

/**
 * Config uploads
 * Guardamos foto como: uploads/jugadores/jugador_<id>.<ext>
 * y en DB guardamos ruta relativa: uploads/jugadores/jugador_<id>.<ext>
 */
$UPLOAD_DIR_FS = __DIR__ . '/../uploads/jugadores';
$UPLOAD_DIR_WEB = 'uploads/jugadores';
$MAX_BYTES = 2 * 1024 * 1024; // 2MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    $err = "Token inválido. Recargá la página e intentá de nuevo.";
  } else {

    // Tomamos datos (NO categoria)
    $apellido  = trim($_POST['apellido'] ?? '');
    $nombre    = trim($_POST['nombre'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $dni       = trim($_POST['dni'] ?? '');
    $sexo      = trim($_POST['sexo'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');

    // Validaciones mínimas
    if ($apellido === '' || $nombre === '') {
      $err = "Nombre y apellido son obligatorios.";
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $err = "Email inválido.";
    } elseif ($dni === '') {
      $err = "DNI es obligatorio.";
    } elseif ($sexo !== '' && !in_array($sexo, ['M','F','X'], true)) {
      $err = "Sexo inválido.";
    } else {

      // Chequear duplicados email/dni (por UNIQUE)
      try {
        $stmt = $pdo->prepare("SELECT id FROM jugadores WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->execute([$email, $jugador_id]);
        if ($stmt->fetch()) {
          $err = "Ese email ya está en uso.";
        }
      } catch (Exception $e) {}

      if (!$err) {
        try {
          $stmt = $pdo->prepare("SELECT id FROM jugadores WHERE dni = ? AND id <> ? LIMIT 1");
          $stmt->execute([$dni, $jugador_id]);
          if ($stmt->fetch()) {
            $err = "Ese DNI ya está en uso.";
          }
        } catch (Exception $e) {}
      }

      // Upload foto (opcional)
      $nueva_foto_rel = null;
      if (!$err && isset($_FILES['foto']) && is_array($_FILES['foto'])) {
        if (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {

          $upErr = $_FILES['foto']['error'] ?? UPLOAD_ERR_OK;
          if ($upErr !== UPLOAD_ERR_OK) {
            $err = "Error subiendo la foto.";
          } else {
            $size = (int)($_FILES['foto']['size'] ?? 0);
            if ($size <= 0 || $size > $MAX_BYTES) {
              $err = "La foto debe pesar hasta 2MB.";
            } else {
              $tmp = $_FILES['foto']['tmp_name'] ?? '';
              $finfo = finfo_open(FILEINFO_MIME_TYPE);
              $mime = $tmp ? finfo_file($finfo, $tmp) : '';
              if ($finfo) finfo_close($finfo);

              $ext = '';
              if ($mime === 'image/jpeg') $ext = 'jpg';
              elseif ($mime === 'image/png') $ext = 'png';
              elseif ($mime === 'image/webp') $ext = 'webp';

              if ($ext === '') {
                $err = "Formato de foto inválido. Usá JPG, PNG o WEBP.";
              } else {
                if (!is_dir($UPLOAD_DIR_FS)) {
                  @mkdir($UPLOAD_DIR_FS, 0775, true);
                }

                if (!is_dir($UPLOAD_DIR_FS) || !is_writable($UPLOAD_DIR_FS)) {
                  $err = "No se puede guardar la foto (carpeta uploads/jugadores sin permisos).";
                } else {
                  $filename = "jugador_" . $jugador_id . "." . $ext;
                  $dest_fs = $UPLOAD_DIR_FS . "/" . $filename;

                  if (!move_uploaded_file($tmp, $dest_fs)) {
                    $err = "No se pudo guardar la foto.";
                  } else {
                    // Guardamos ruta relativa
                    $nueva_foto_rel = $UPLOAD_DIR_WEB . "/" . $filename;
                  }
                }
              }
            }
          }
        }
      }

      // Update
      if (!$err) {
        try {
          if ($nueva_foto_rel !== null) {
            $sql = "UPDATE jugadores
                    SET apellido=?, nombre=?, email=?, dni=?, sexo=?, telefono=?, foto=?
                    WHERE id=? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$apellido, $nombre, $email, $dni, ($sexo ?: null), ($telefono ?: null), $nueva_foto_rel, $jugador_id]);
          } else {
            $sql = "UPDATE jugadores
                    SET apellido=?, nombre=?, email=?, dni=?, sexo=?, telefono=?
                    WHERE id=? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$apellido, $nombre, $email, $dni, ($sexo ?: null), ($telefono ?: null), $jugador_id]);
          }

          // Refrescar datos y sesión
          $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id=? LIMIT 1");
          $stmt->execute([$jugador_id]);
          $jugador = $stmt->fetch(PDO::FETCH_ASSOC) ?: $jugador;

          $_SESSION['jugador'] = $jugador;

          $ok = "Perfil actualizado.";
        } catch (Exception $e) {
          $err = "No se pudo guardar (revisá datos).";
        }
      }
    }
  }
}

// Foto a mostrar
$foto = trim((string)($jugador['foto'] ?? ''));
$foto_src = $foto !== '' ? "/apiba-padel/" . ltrim($foto, "/") : "/apiba-padel/assets/img/user-placeholder.png";
?>

<?php if ($ok): ?>
  <div class="card"><div class="card__body"><span class="badge badge--ok"><?= h($ok) ?></span></div></div>
  <div style="height:10px;"></div>
<?php endif; ?>

<?php if ($err): ?>
  <div class="card"><div class="card__body"><span class="badge badge--danger"><?= h($err) ?></span></div></div>
  <div style="height:10px;"></div>
<?php endif; ?>

<div class="grid grid--2">
  <div class="card">
    <div class="card__header">
      <h1 class="h1">Modificar perfil</h1>
      <span class="badge">Jugador</span>
    </div>

    <div class="card__body">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">

        <div style="display:flex; gap:14px; align-items:center; flex-wrap:wrap;">
          <img src="<?= h($foto_src) ?>" alt="Foto" style="width:110px;height:110px;object-fit:cover;border-radius:16px;border:1px solid var(--border);background:var(--surface);">
          <div style="flex:1; min-width:240px;">
            <label class="p" style="display:block; margin-bottom:6px;"><b>Foto</b> (JPG/PNG/WEBP hasta 2MB)</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
          </div>
        </div>

        <div style="height:12px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>Apellido</b></label>
        <input type="text" name="apellido" value="<?= h($jugador['apellido'] ?? '') ?>" required>

        <div style="height:10px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>Nombre</b></label>
        <input type="text" name="nombre" value="<?= h($jugador['nombre'] ?? '') ?>" required>

        <div style="height:10px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>Email</b></label>
        <input type="email" name="email" value="<?= h($jugador['email'] ?? '') ?>" required>

        <div style="height:10px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>DNI</b></label>
        <input type="text" name="dni" value="<?= h($jugador['dni'] ?? '') ?>" required>

        <div style="height:10px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>Sexo</b></label>
        <select name="sexo">
          <option value="" <?= empty($jugador['sexo']) ? 'selected' : '' ?>>—</option>
          <option value="M" <?= ($jugador['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>M</option>
          <option value="F" <?= ($jugador['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>F</option>
          <option value="X" <?= ($jugador['sexo'] ?? '') === 'X' ? 'selected' : '' ?>>X</option>
        </select>

        <div style="height:10px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>Teléfono</b></label>
        <input type="text" name="telefono" value="<?= h($jugador['telefono'] ?? '') ?>">

        <div style="height:10px;"></div>

        <label class="p" style="display:block; margin-bottom:6px;"><b>Categoría</b> (no editable)</label>
        <input type="text" value="<?= h($jugador['categoria'] ?? '') ?>" disabled>

        <div style="height:12px;"></div>

        <div class="form-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button type="submit" class="btn btn--primary">Guardar cambios</button>
          <a class="btn" href="/apiba-padel/jugador/perfil.php">Volver al perfil</a>
          <a class="btn" href="/apiba-padel/logout_jugador.php">Cerrar sesión</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card__header">
      <h2 class="h2">Notas</h2>
      <span class="badge">Ayuda</span>
    </div>
    <div class="card__body">
      <p class="p" style="margin:0;">
        La categoría no se modifica desde acá. Si necesitás cambiarla, se hace desde administración.
      </p>
      <div style="height:10px;"></div>
      <p class="p" style="margin:0;">
        Si te da error de permisos al subir foto, asegurate de que exista y tenga permisos de escritura:
        <b>/apiba-padel/uploads/jugadores</b>
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/site_footer.php'; ?>
