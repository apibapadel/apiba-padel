<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$err = null;

$categorias = [
  '4TA CATEGORIA CABALLEROS',
  '5TA CATEGORIA CABALLEROS',
  '6TA CATEGORIA CABALLEROS',
  '7MA CATEGORIA CABALLEROS',
  '4TA CATEGORIA DAMAS',
  '5TA CATEGORIA DAMAS',
  '6TA CATEGORIA DAMAS',
  '7MA CATEGORIA DAMAS',
];

$sedes = [
  "90´S PADEL",
  "FRAY PADEL",
  "LA QUINTA PADEL",
  "CASBAS PADEL",
];

// Estados EXACTOS como pediste
$estados = ['Abierto','En Curso','Finalizado','Cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = trim($_POST['categoria'] ?? '');
    $sede = trim($_POST['sede'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    // nombre = categoria
    $nombre = $categoria;

    if ($categoria === '' || $sede === '' || $fecha_inicio === '' || $estado === '') {
        $err = "Completá todos los campos obligatorios.";
    } else {
        if (!in_array($categoria, $categorias, true)) $err = "Categoría inválida.";
        if (!in_array($sede, $sedes, true)) $err = "Sede inválida.";
        if (!in_array($estado, $estados, true)) $err = "Estado inválido.";

        if (!$err) {
            $stmt = $pdo->prepare("
              INSERT INTO torneos (nombre, categoria, sede, fecha_inicio, estado)
              VALUES (?,?,?,?,?)
            ");
            $stmt->execute([$nombre, $categoria, $sede, $fecha_inicio, $estado]);

            header("Location: torneos.php?msg=" . urlencode("Torneo creado correctamente."));
            exit;
        }
    }
}

include '_header.php';
?>

<h2>➕ Nuevo torneo</h2>
<p class="muted">El nombre del torneo se arma automáticamente con la categoría.</p>

<?php if ($err): ?>
  <div class="msg" style="background:#ffe9e9;border-color:#f2a2a2">❌ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" class="grid" autocomplete="off">

    <div class="field">
      <label>Categoría (Nombre del torneo) *</label>
      <?php $vcat = $_POST['categoria'] ?? ''; ?>
      <select name="categoria" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach($categorias as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= $vcat===$c?'selected':'' ?>>
            <?= htmlspecialchars($c) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Sede *</label>
      <?php $vsede = $_POST['sede'] ?? ''; ?>
      <select name="sede" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach($sedes as $s): ?>
          <option value="<?= htmlspecialchars($s) ?>" <?= $vsede===$s?'selected':'' ?>>
            <?= htmlspecialchars($s) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Fecha inicio *</label>
      <input name="fecha_inicio" type="date" required value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? '') ?>">
    </div>

    <div class="field">
      <label>Estado *</label>
      <?php $vest = $_POST['estado'] ?? 'Abierto'; ?>
      <select name="estado" required>
        <?php foreach($estados as $e): ?>
          <option value="<?= htmlspecialchars($e) ?>" <?= $vest===$e?'selected':'' ?>>
            <?= htmlspecialchars($e) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="grid-column:1/-1; display:flex; gap:10px; flex-wrap:wrap">
      <button class="btn btn-ok" type="submit">Guardar torneo</button>
      <a class="btn btn-soft" href="torneos.php">Cancelar</a>
    </div>

  </form>
</div>

<?php include '_footer.php'; ?>
