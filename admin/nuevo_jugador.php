<?php
require_once 'auth.php';
include '_header.php';

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
?>

<h2>➕ Nuevo jugador</h2>

<form method="post" action="guardar_nuevo_jugador.php" enctype="multipart/form-data" autocomplete="off">

  <label>Apellido</label><br>
  <input name="apellido" required><br><br>

  <label>Nombre</label><br>
  <input name="nombre" required><br><br>

  <label>Email</label><br>
  <input name="email" type="email" required><br><br>

  <label>Clave (para que el jugador pueda ingresar)</label><br>
  <input name="password" type="text" value="apiba123" required><br><br>

  <label>DNI</label><br>
  <input name="dni" required><br><br>

  <label>Teléfono</label><br>
  <input name="telefono"><br><br>

  <!-- LOCALIDAD -->
  <label>Localidad</label><br>
  <select name="localidad_select" id="localidad_select" required onchange="toggleOtraLocalidad()">
    <option value="">-- Seleccionar localidad --</option>
    <?php foreach ($localidadesLista as $loc): ?>
      <option value="<?= htmlspecialchars($loc) ?>">
        <?= htmlspecialchars($loc) ?>
      </option>
    <?php endforeach; ?>
    <option value="Otra">Otra</option>
  </select><br><br>

  <div id="localidad_otra_wrap" style="display:none">
    <label>Especificar localidad</label><br>
    <input type="text" name="localidad_otra" id="localidad_otra"><br><br>
  </div>

  <label>Sexo</label><br>
  <select name="sexo" id="sexo" required onchange="actualizarCategorias()">
    <option value="">-- Seleccionar sexo --</option>
    <option value="M">Masculino</option>
    <option value="F">Femenino</option>
  </select><br><br>

  <label>Categoría</label><br>
  <select name="categoria" id="categoria" required disabled>
    <option value="">-- Seleccionar categoría --</option>
  </select><br><br>

  <label>Ranking (puntos)</label><br>
  <input name="ranking" type="number" value="0"><br><br>

  <label>Activo</label><br>
  <select name="activo">
    <option value="1" selected>Sí</option>
    <option value="0">No</option>
  </select><br><br>

  <label>Foto (opcional)</label><br>
  <input type="file" name="foto"><br><br>

  <button type="submit">Guardar jugador</button>
</form>

<script>
function toggleOtraLocalidad() {
  const select = document.getElementById('localidad_select');
  const wrap   = document.getElementById('localidad_otra_wrap');
  const input  = document.getElementById('localidad_otra');

  if (select.value === 'Otra') {
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
  categoria.disabled = true;

  let opciones = [];
  if (sexo === 'M') opciones = ['4TA Caballeros','5TA Caballeros','6TA Caballeros','7MA Caballeros'];
  if (sexo === 'F') opciones = ['4TA Damas','5TA Damas','6TA Damas','7MA Damas'];

  if (opciones.length > 0) {
    opciones.forEach(texto => {
      const opt = document.createElement('option');
      opt.value = texto;
      opt.textContent = texto;
      categoria.appendChild(opt);
    });
    categoria.disabled = false;
  }
}
</script>

<?php include '_footer.php'; ?>
