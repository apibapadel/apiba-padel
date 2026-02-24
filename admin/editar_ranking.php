<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

// Traer datos del jugador (nombre + ranking)
$stmt = $pdo->prepare("
  SELECT id, apellido, nombre, sexo, categoria, ranking
  FROM jugadores
  WHERE id=? LIMIT 1
");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$j) die("Jugador no encontrado");

include '_header.php';
?>

<h2>
  Editar ranking – <?= htmlspecialchars($j['apellido']) ?> <?= htmlspecialchars($j['nombre']) ?>
  <small style="color:#666">(ID #<?= (int)$j['id'] ?>)</small>
</h2>

<form method="post" action="guardar_ranking.php">
  <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">

  <label>Sexo</label><br>
  <select name="sexo" id="sexo" required onchange="actualizarCategorias()">
    <option value="">-- Seleccionar sexo --</option>
    <option value="M" <?= ($j['sexo']==='M')?'selected':'' ?>>Masculino</option>
    <option value="F" <?= ($j['sexo']==='F')?'selected':'' ?>>Femenino</option>
  </select><br><br>

  <label>Categoría</label><br>
  <select name="categoria" id="categoria" required>
    <option value="">-- Seleccionar categoría --</option>
  </select><br><br>

  <label>Puntos (Ranking)</label><br>
  <input name="ranking" type="number" value="<?= (int)($j['ranking'] ?? 0) ?>"><br><br>

  <button>Guardar</button>
</form>

<script>
const valorActualCategoria = <?= json_encode($j['categoria'] ?? '') ?>;

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

actualizarCategorias();
</script>

<?php include '_footer.php'; ?>
