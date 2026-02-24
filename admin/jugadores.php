<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

if (session_status() === PHP_SESSION_NONE) session_start();

include '_header.php';

$msg = $_GET['msg'] ?? '';

// =========================
// FILTROS (persistentes)
// =========================
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
  unset($_SESSION['jugadores_filtros']);
  header("Location: jugadores.php");
  exit;
}

$sexo          = $_GET['sexo'] ?? null;
$categoria     = $_GET['categoria'] ?? null;
$localidad     = isset($_GET['localidad']) ? trim($_GET['localidad']) : null;
$carnet_estado = $_GET['carnet_estado'] ?? null; // '' | 'GEN' | 'NO'

// defaults sesión
$sf = $_SESSION['jugadores_filtros'] ?? [
  'sexo' => '',
  'categoria' => 'ALL',
  'localidad' => '',
  'carnet_estado' => ''
];

// si no vienen por GET, usar sesión
if ($sexo === null)          $sexo = $sf['sexo'];
if ($categoria === null)     $categoria = $sf['categoria'];
if ($localidad === null)     $localidad = $sf['localidad'];
if ($carnet_estado === null) $carnet_estado = $sf['carnet_estado'];

// si el usuario envió el form, guardar en sesión
if (isset($_GET['sexo']) || isset($_GET['categoria']) || isset($_GET['localidad']) || isset($_GET['carnet_estado'])) {
  $_SESSION['jugadores_filtros'] = [
    'sexo' => (string)$sexo,
    'categoria' => (string)$categoria,
    'localidad' => (string)$localidad,
    'carnet_estado' => (string)$carnet_estado
  ];
}

// querystring para mantener filtros en links
$qsFiltros = http_build_query([
  'sexo' => $sexo,
  'categoria' => $categoria,
  'localidad' => $localidad,
  'carnet_estado' => $carnet_estado
]);

// =========================
// LISTAS PARA FILTROS
// =========================
$categorias = $pdo->query("
  SELECT DISTINCT categoria
  FROM jugadores
  WHERE categoria IS NOT NULL AND categoria <> ''
  ORDER BY categoria
")->fetchAll(PDO::FETCH_COLUMN);

// =========================
// CARNET GENERADO (PNG en uploads/qr)
// FIX: evitar falsos positivos (id 12 vs 112) + cache de archivos
// =========================
$qrDir = rtrim($_SERVER['DOCUMENT_ROOT'].'/apiba-padel/uploads/qr', '/\\');

$qrBasenames = [];
if (is_dir($qrDir)) {
  foreach (glob($qrDir . DIRECTORY_SEPARATOR . "*.png") ?: [] as $p) {
    $qrBasenames[] = strtolower(basename($p));
  }
}

function carnet_png_generado(int $id, array $qrBasenames): bool {
  if (!$qrBasenames) return false;

  $idStr = (string)$id;

  // 1) Patrones exactos comunes (lo más seguro)
  $exact = [
    "qr_{$idStr}.png",
    "jugador_{$idStr}.png",
    "{$idStr}.png",
    "carnet_{$idStr}.png",
  ];
  foreach ($exact as $name) {
    if (in_array(strtolower($name), $qrBasenames, true)) return true;
  }

  // 2) Backup robusto: el ID como "token" numérico (no substring)
  // Ej: "qr_12.png" matchea, pero "qr_112.png" NO matchea al buscar 12.
  $re = '/(^|[^0-9])' . preg_quote($idStr, '/') . '([^0-9]|$)/';

  foreach ($qrBasenames as $base) {
    if (preg_match($re, $base)) return true;
  }

  return false;
}

// =========================
// QUERY
// =========================
$sql = "
  SELECT id, apellido, nombre, email, dni, telefono, localidad, sexo, categoria, ranking, activo
  FROM jugadores
  WHERE 1=1
";
$params = [];

if ($sexo === 'M' || $sexo === 'F') {
  $sql .= " AND sexo = ? ";
  $params[] = $sexo;
}

if ($categoria !== '' && $categoria !== 'ALL') {
  $sql .= " AND categoria = ? ";
  $params[] = $categoria;
}

if ($localidad !== '') {
  $sql .= " AND localidad LIKE ? ";
  $params[] = '%' . $localidad . '%';
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jugadores_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// FILTRO CARNET GENERADO
// =========================
$jugadores = [];
foreach ($jugadores_raw as $j) {
  $id = (int)$j['id'];
  $generado = carnet_png_generado($id, $qrBasenames);

  if ($carnet_estado === 'GEN' && !$generado) continue;
  if ($carnet_estado === 'NO'  && $generado) continue;

  $j['_carnet_generado'] = $generado ? 1 : 0;
  $jugadores[] = $j;
}
?>

<h2>Jugadores</h2>
<p class="muted">Administrá jugadores, carnet, inscripciones y contraseña.</p>

<?php if ($msg): ?>
  <div class="msg">✅ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card">
  <form method="get" class="toolbar">

    <div class="field">
      <label>Sexo</label>
      <select name="sexo">
        <option value="" <?= $sexo===''?'selected':'' ?>>Todos</option>
        <option value="M" <?= $sexo==='M'?'selected':'' ?>>Masculino</option>
        <option value="F" <?= $sexo==='F'?'selected':'' ?>>Femenino</option>
      </select>
    </div>

    <div class="field">
      <label>Categoría</label>
      <select name="categoria">
        <option value="ALL" <?= $categoria==='ALL'?'selected':'' ?>>Todas</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria===$cat?'selected':'' ?>>
            <?= htmlspecialchars($cat) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Localidad</label>
      <input
        type="text"
        name="localidad"
        placeholder="Ej: Pehuajó, Trenque, Santa..."
        value="<?= htmlspecialchars($localidad) ?>"
      >
      <div class="muted" style="font-size:12px;margin-top:4px;">
        Busca por coincidencia (contiene).
      </div>
    </div>

    <div class="field">
      <label>Carnet generado</label>
      <select name="carnet_estado">
        <option value="" <?= $carnet_estado===''?'selected':'' ?>>Todos</option>
        <option value="GEN" <?= $carnet_estado==='GEN'?'selected':'' ?>>Generado</option>
        <option value="NO"  <?= $carnet_estado==='NO'?'selected':'' ?>>No generado</option>
      </select>
      <div class="muted" style="font-size:12px;margin-top:4px;">
        Detecta PNG en /uploads/qr.
      </div>
    </div>

    <button class="btn btn-sm" type="submit">Filtrar</button>
    <a class="btn btn-soft btn-sm" href="jugadores.php?clear=1">Limpiar</a>

    <div style="flex:1"></div>

    <a class="btn btn-ok btn-sm" href="nuevo_jugador.php?<?= $qsFiltros ?>">➕ Nuevo jugador</a>
  </form>
</div>

<br>

<table>
  <tr>
    <th>ID</th>
    <th>Jugador</th>
    <th>Contacto</th>
    <th>Sexo / Cat.</th>
    <th>Ranking</th>
    <th>Estado</th>
    <th style="width:420px">Acciones</th>
  </tr>

  <?php if (!$jugadores): ?>
    <tr><td colspan="7">No hay jugadores para los filtros seleccionados.</td></tr>
  <?php endif; ?>

  <?php foreach ($jugadores as $j): ?>
    <tr>
      <td><?= (int)$j['id'] ?></td>
      <td>
        <b><?= htmlspecialchars($j['apellido'].' '.$j['nombre']) ?></b><br>
        <span class="muted">DNI: <?= htmlspecialchars($j['dni']) ?></span>
      </td>
      <td>
        <div><?= htmlspecialchars($j['email']) ?></div>
        <div class="muted"><?= htmlspecialchars($j['telefono'] ?? '') ?></div>
        <div class="muted">📍 <?= htmlspecialchars($j['localidad'] ?? '') ?></div>
      </td>
      <td>
        <span class="badge"><?= htmlspecialchars($j['sexo']) ?></span>
        <span class="badge"><?= htmlspecialchars($j['categoria']) ?></span>
      </td>
      <td><b><?= (int)($j['ranking'] ?? 0) ?></b></td>
      <td>
        <?php if ((int)$j['activo'] === 1): ?>
          <span class="badge badge-ok">Activo</span>
        <?php else: ?>
          <span class="badge badge-no">Inactivo</span>
        <?php endif; ?>
      </td>

      <td>
        <a class="btn btn-soft btn-sm" href="editar_jugador.php?id=<?= (int)$j['id'] ?>&<?= $qsFiltros ?>">Editar</a>
        <a class="btn btn-soft btn-sm" href="ver_carnet_jugador.php?id=<?= (int)$j['id'] ?>&<?= $qsFiltros ?>">Ver carnet</a>
        <a class="btn btn-sm" href="generar_carnet_jugador.php?id=<?= (int)$j['id'] ?>&<?= $qsFiltros ?>">Generar carnet</a>
        <a class="btn btn-danger btn-sm" href="reset_password_jugador.php?id=<?= (int)$j['id'] ?>&<?= $qsFiltros ?>">Reset pass</a>
        <a class="btn btn-soft btn-sm" href="inscripciones_jugador.php?id=<?= (int)$j['id'] ?>&<?= $qsFiltros ?>">Inscripciones</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include '_footer.php'; ?>
