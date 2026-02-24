<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$j) die("Jugador no encontrado");

// =========================
// LOCALIDADES (ORDENADAS + OTRA AL FINAL)
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

// Detectar si la localidad actual está en la lista
$localidadActual = $j['localidad'] ?? '';
$usaOtra = ($localidadActual !== '' && !in_array($localidadActual, $localidadesLista, true));

include '_header.php';
?>

<h2>Editar jugador #<?= (int)$j['id'] ?></h2>

<form method="post" action="guardar_jugador.php">
  <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">

  <label>Apellido</label><br>
  <input name="apellido" value="<?= htmlspecialchars($j['apellido']) ?>" required><br><br>

  <label>Nombre</label><br>
  <input name="nombre" value="<?= htmlspecialchars($j['nombre']) ?>" required><br><br>

  <label>Email</label><br>
  <input name="email" type="email" value="<?= htmlspecialchars($j['email']) ?>" required><br><br>

  <label>DNI</label><br>
  <input name="dni" value="<?= htmlspecialchars($j['dni']) ?>" required><br><br>

  <label>Teléfono</label><br>
  <input name="telefono" value="<?= htmlspecialchars($j['telefono']) ?>"><br><br>

  <!-- LOCALIDAD -->
  <label>Localidad</label><br>
  <select name="localidad_select" id="localidad_select" required onchange="toggleOtraLocalidad()">
    <option value="">-- Seleccionar localidad --</option>

    <?php foreach ($localidadesLista as $loc): ?>
      <option value="<?= htmlspecialchars($loc) ?>"
        <?= ($loc === $localidadActual) ? 'selected' : '' ?>>
        <?= htmlspecialchars($loc) ?>
      </option>
    <?php endforeach; ?>

    <option value="Otra" <?= $usaOtra ? 'selected' : '' ?>>Otra</option>
  </select><br><br>

  <div id="localidad_otra_wrap" style="display:none">
    <label>Especificar localidad</label><br>
    <input type="text"
           name="localidad_otra"
           id="localidad_otra"
           value="<?= $usaOtra ? htmlspecialchars($localidadActual) : '' ?>"><br><br>
  </div>

  <label>Sexo</label><br>
  <select name="sexo" id="sexo" required onchange="actualizarCategorias()">
    <option value="">-- Seleccionar sexo --</option>
    <option value="M" <?= $j['sexo']==='M'?'selected':'' ?>>Masculino</option>
    <option value="F" <?= $j['sexo']==='F'?'selected':'' ?>>Femenino</option>
  </select><br><br>

  <label>Categoría</label><br>
  <select name="categoria" id="categoria" required>
    <option value="">-- Seleccionar categoría --</option>
  </select><br><br>

  <label>Ranking (puntos)</label><br>
  <input name="ranking" value="<?= (int)($j['ranking'] ?? 0) ?>"><br><br>

  <label>Activo</label><br>
  <select name="activo">
    <option value="1" <?= (int)$j['activo']===1?'selected':'' ?>>Sí</option>
    <option value="0" <?= (int)$j['activo']===0?'selected':'' ?>>No</option>
  </select><br><br>

  <button>Guardar cambios</button>
</form>

<script>
const valorActualCategoria = <?= json_encode($j['categoria'] ?? '') ?>;

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

function actualizarCategorias() {
  const sexo = document.getElementById('sexo').value;
  const categoria = document.getElementById('categoria');

  categoria.innerHTML = '<option value="">-- Seleccionar categoría --</option>';

  let opciones = [];
  if (sexo === 'M') opciones = ['4TA Caballeros','5TA Caballeros','6TA Caballeros','7MA Caballeros'];
  if (sexo === 'F') opciones = ['4TA Damas','5TA Damas','6TA Damas','7MA Damas'];

  opciones.forEach(texto => {
    const opt = document.createElement('option');
    opt.value = texto;
    opt.textContent = texto;
    if (texto === valorActualCategoria) opt.selected = true;
    categoria.appendChild(opt);
  });
}

document.addEventListener("DOMContentLoaded", function(){
  toggleOtraLocalidad();
  actualizarCategorias();
});
</script>

<?php include '_footer.php'; ?>
