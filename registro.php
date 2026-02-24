<?php
$section = 'public';
$active = 'registro';
$page_title = 'Registro - APiBA Pádel';

require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$error = null;

// =========================
// LOCALIDADES (ORDENADAS, OTRA AL FINAL)
// =========================
$localidadesLista = [
  '30 de Agosto',
  'Bonifacio',
  'Carhué',
  'Casbas',
  'Catriló',
  'Coronel Suárez',
  'Garré',
  'Guaminí',
  'Huanguelén',
  'Lonquimay',
  'Pellegrini',
  'Pigué',
  'Rivera',
  'Salliqueló',
  'Santa Rosa',
  'Trenque Lauquen',
  'Tres Lomas'
];

// =========================
// VARIABLES FORM
// =========================
$apellido   = '';
$nombre     = '';
$email      = '';
$dni        = '';
$sexo       = '';
$categoria  = '';
$telefono   = '';
$localidad  = '';
$localidad_select = '';
$localidad_otra   = '';

$permitidasM = ['4TA Caballeros','5TA Caballeros','6TA Caballeros','7MA Caballeros'];
$permitidasF = ['4TA Damas','5TA Damas','6TA Damas','7MA Damas'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $apellido  = trim($_POST['apellido'] ?? '');
    $nombre    = trim($_POST['nombre'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $dni       = trim($_POST['dni'] ?? '');
    $sexo      = $_POST['sexo'] ?? '';
    $categoria = trim($_POST['categoria'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');

    // LOCALIDAD (select + otra)
    $localidad_select = trim($_POST['localidad_select'] ?? '');
    $localidad_otra   = trim($_POST['localidad_otra'] ?? '');

    if ($localidad_select === '') {
        $error = "La localidad es obligatoria.";
    } elseif ($localidad_select === 'Otra') {
        if ($localidad_otra === '') {
            $error = "Debés especificar la localidad.";
        } else {
            $localidad = $localidad_otra;
        }
    } else {
        $localidad = $localidad_select;
    }

    if (!$error && (
        $apellido === '' || $nombre === '' || $email === '' || $password === '' ||
        $dni === '' || $sexo === '' || $categoria === ''
    )) {
        $error = "Completá Apellido, Nombre, Email, Clave, DNI, Sexo, Categoría y Localidad.";
    }

    if (!$error) {
        if ($sexo === 'M' && !in_array($categoria, $permitidasM, true)) {
            $error = "Categoría inválida para Masculino.";
        } elseif ($sexo === 'F' && !in_array($categoria, $permitidasF, true)) {
            $error = "Categoría inválida para Femenino.";
        }
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM jugadores WHERE email = ? OR dni = ? LIMIT 1");
            $stmt->execute([$email, $dni]);
            if ($stmt->fetch()) {
                $error = "Ya existe un jugador con ese Email o DNI.";
            } else {

                $fotoNombre = null;

                if (!empty($_FILES['foto']['name'])) {
                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $permitidas = ['jpg','jpeg','png','webp'];

                    if (!in_array($ext, $permitidas, true)) {
                        throw new Exception("Foto: formato no permitido (jpg, jpeg, png, webp).");
                    }
                    if (($_FILES['foto']['size'] ?? 0) > 2 * 1024 * 1024) {
                        throw new Exception("Foto: máximo 2MB.");
                    }

                    $fotoNombre = uniqid('jug_') . "." . $ext;
                    $destinoDir = __DIR__ . "/uploads/jugadores";
                    $destino = $destinoDir . "/" . $fotoNombre;

                    if (!is_dir($destinoDir)) mkdir($destinoDir, 0777, true);
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                        throw new Exception("No se pudo guardar la foto.");
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO jugadores
                    (apellido,nombre,email,password,dni,sexo,categoria,telefono,localidad,foto,activo)
                    VALUES (?,?,?,?,?,?,?,?,?,?,1)
                ");
                $stmt->execute([
                    $apellido,
                    $nombre,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $dni,
                    $sexo,
                    $categoria,
                    $telefono,
                    $localidad,
                    $fotoNombre
                ]);

                header("Location: login.php");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
require_once __DIR__ . '/includes/site_header.php';
?>


<div class="auth-page">
  <div class="auth-wrap">
    <div class="card auth-card">
      <div class="card__body">

        <h1 class="h1" style="margin-bottom:10px;">📝 Registro de jugador</h1>

        <?php if ($error): ?>
          <div style="margin-bottom:12px;">
            <span class="badge badge--danger">⚠ <?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" autocomplete="off" class="auth-form">

          <div class="field">
            <label class="label">Apellido</label>
            <input name="apellido" required value="<?= htmlspecialchars($apellido) ?>">
          </div>

          <div class="field">
            <label class="label">Nombre</label>
            <input name="nombre" required value="<?= htmlspecialchars($nombre) ?>">
          </div>

          <div class="field">
            <label class="label">Email</label>
            <input name="email" type="email" required value="<?= htmlspecialchars($email) ?>">
          </div>

          <div class="field">
            <label class="label">Clave</label>
            <input name="password" type="password" required>
          </div>

          <div class="field">
            <label class="label">DNI</label>
            <input name="dni" required value="<?= htmlspecialchars($dni) ?>">
          </div>

          <div class="field">
            <label class="label">Teléfono</label>
            <input name="telefono" value="<?= htmlspecialchars($telefono) ?>">
          </div>

          <!-- LOCALIDAD -->
          <div class="field">
            <label class="label">Localidad</label>
            <select name="localidad_select" id="localidad_select" required onchange="toggleOtraLocalidad()">
              <option value="">-- Seleccionar localidad --</option>
              <?php foreach ($localidadesLista as $loc): ?>
                <option value="<?= htmlspecialchars($loc) ?>"
                  <?= ($localidad === $loc) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($loc) ?>
                </option>
              <?php endforeach; ?>
              <option value="Otra" <?= ($localidad_select === 'Otra') ? 'selected' : '' ?>>Otra</option>
            </select>
          </div>

          <div class="field" id="localidad_otra_wrap" style="display:none">
            <label class="label">Especificar localidad</label>
            <input type="text" name="localidad_otra" id="localidad_otra"
                   value="<?= htmlspecialchars($localidad_otra) ?>">
          </div>

          <div class="field">
            <label class="label">Sexo</label>
            <select name="sexo" id="sexo" required>
              <option value="">-- Seleccionar sexo --</option>
              <option value="M" <?= ($sexo === 'M') ? 'selected' : '' ?>>Masculino</option>
              <option value="F" <?= ($sexo === 'F') ? 'selected' : '' ?>>Femenino</option>
            </select>
          </div>

          <div class="field">
            <label class="label">Categoría</label>
            <select name="categoria" id="categoria" required>
              <option value="">-- Seleccionar categoría --</option>
            </select>
          </div>

          <div class="field">
            <label class="label">Foto (opcional)</label>
            <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
          </div>

          <button type="submit" class="btn btn--primary auth-submit">Crear cuenta</button>

          <div class="auth-links">
            <span class="muted">¿Ya tenés cuenta?</span>
            <a class="auth-link" href="/apiba-padel/login.php">Ingresar</a>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

<script>
function toggleOtraLocalidad() {
  const sel = document.getElementById('localidad_select');
  const wrap = document.getElementById('localidad_otra_wrap');
  const input = document.getElementById('localidad_otra');

  if (sel.value === 'Otra') {
    wrap.style.display = 'block';
    input.required = true;
  } else {
    wrap.style.display = 'none';
    input.required = false;
    input.value = '';
  }
}

document.addEventListener("DOMContentLoaded", function(){
  toggleOtraLocalidad();

  const sexoSelect = document.getElementById('sexo');
  const categoriaSelect = document.getElementById('categoria');

  const permitidasM = ['4TA Caballeros','5TA Caballeros','6TA Caballeros','7MA Caballeros'];
  const permitidasF = ['4TA Damas','5TA Damas','6TA Damas','7MA Damas'];

  const selectedSexo = <?= json_encode($sexo) ?>;
  const selectedCategoria = <?= json_encode($categoria) ?>;

  function actualizarCategorias() {
    const sexo = sexoSelect.value;
    categoriaSelect.innerHTML = '<option value="">-- Seleccionar categoría --</option>';
    categoriaSelect.disabled = true;

    let opciones = [];
    if (sexo === 'M') opciones = permitidasM;
    if (sexo === 'F') opciones = permitidasF;

    if (opciones.length > 0) {
      opciones.forEach(texto => {
        const opt = document.createElement('option');
        opt.value = texto;
        opt.textContent = texto;
        if (texto === selectedCategoria) opt.selected = true;
        categoriaSelect.appendChild(opt);
      });
      categoriaSelect.disabled = false;
    }
  }

  if (selectedSexo === 'M' || selectedSexo === 'F') {
    sexoSelect.value = selectedSexo;
  }
  actualizarCategorias();
  sexoSelect.addEventListener("change", actualizarCategorias);
});
</script>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
