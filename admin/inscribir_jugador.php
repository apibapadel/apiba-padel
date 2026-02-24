<?php
$section = 'admin';
$active  = 'torneos';
$page_title = 'Inscribir jugador';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/_header.php';

$pdo = getDB();

/* Helpers */
function parseNivel($txt) {
  if (preg_match('/(\d+)/', (string)$txt, $m)) return (int)$m[1];
  return null;
}
function parseSexoDesdeTexto($txt) {
  $t = mb_strtolower((string)$txt);
  if (strpos($t, 'caballer') !== false) return 'M';
  if (strpos($t, 'dam') !== false) return 'F';
  return null;
}

/* Torneo */
$torneo_id = isset($_GET['torneo_id']) ? (int)$_GET['torneo_id'] : (int)($_POST['torneo_id'] ?? 0);
if ($torneo_id <= 0) {
  echo "<div class='card'><p>Torneo inválido.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, categoria FROM torneos WHERE id = ?");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$torneo) {
  echo "<div class='card'><p>No se encontró el torneo.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$torneoNivel = parseNivel($torneo['categoria']);
$torneoSexo  = parseSexoDesdeTexto($torneo['categoria']);

if (!$torneoNivel || !$torneoSexo) {
  echo "<div class='card'>
          <p>No pude interpretar la categoría del torneo: <b>".htmlspecialchars($torneo['categoria'])."</b></p>
          <p>Debe decir algo como <b>4TA CATEGORIA CABALLEROS</b> o <b>5TA CATEGORIA DAMAS</b>.</p>
        </div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$err = null;
$okMsg = null;

/* GET: lista de elegibles */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "
  SELECT j.id, j.apellido, j.nombre, j.categoria, j.sexo
  FROM jugadores j
  JOIN carnets c ON c.jugador_id = j.id
  LEFT JOIN inscripciones i
    ON i.jugador_id = j.id AND i.torneo_id = :torneo_id
  WHERE i.id IS NULL
    AND UPPER(TRIM(j.sexo)) = :sexo
";

$params = [
  ':torneo_id' => $torneo_id,
  ':sexo' => $torneoSexo,
];

if ($q !== '') {
  $sql .= " AND (j.apellido LIKE :q OR j.nombre LIKE :q OR j.id = :qid) ";
  $params[':q'] = "%$q%";
  $params[':qid'] = ctype_digit($q) ? (int)$q : 0;
}

$sql .= " ORDER BY j.apellido ASC, j.nombre ASC LIMIT 500";

$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

/* Filtrar por nivel permitido: jugadorNivel >= torneoNivel */
$elegibles = [];
foreach ($rows as $r) {
  $niv = parseNivel($r['categoria']);
  if ($niv && $niv >= $torneoNivel) $elegibles[] = $r;
}

/* POST: inscribir MULTIPLE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? 'guardar';
  // Si quieren “finalizar”, simplemente redirigimos (sin guardar nada extra)
  if ($accion === 'finalizar') {
    header("Location: inscriptos.php?id=".$torneo_id);
    exit;
  }

  $ids = $_POST['jugador_ids'] ?? [];
  if (!is_array($ids) || count($ids) === 0) {
    $err = "Seleccioná al menos un jugador.";
  } else {
    // sanitizar ids
    $ids = array_values(array_unique(array_filter(array_map(function($v){
      return (int)$v;
    }, $ids), function($v){ return $v > 0; })));

    if (count($ids) === 0) {
      $err = "Selección inválida.";
    } else {
      // preparamos statements reutilizables
      $stJugador = $pdo->prepare("SELECT id, categoria, sexo FROM jugadores WHERE id = ? LIMIT 1");
      $stCarnet  = $pdo->prepare("SELECT 1 FROM carnets WHERE jugador_id = ? LIMIT 1");
      $stDup     = $pdo->prepare("SELECT 1 FROM inscripciones WHERE torneo_id = ? AND jugador_id = ? LIMIT 1");
      $stIns     = $pdo->prepare("INSERT INTO inscripciones (torneo_id, jugador_id, fecha) VALUES (?, ?, NOW())");

      $inscriptosOk = 0;
      $saltados = 0;
      $errores = [];

      foreach ($ids as $jugador_id) {
        // Jugador existe
        $stJugador->execute([$jugador_id]);
        $j = $stJugador->fetch(PDO::FETCH_ASSOC);
        if (!$j) { $saltados++; $errores[] = "#$jugador_id: no existe"; continue; }

        $jugNivel = parseNivel($j['categoria']);
        $jugSexo  = strtoupper(trim($j['sexo'] ?? ''));
        if (!$jugSexo) $jugSexo = parseSexoDesdeTexto($j['categoria']);

        // Carnet
        $stCarnet->execute([$jugador_id]);
        if (!(bool)$stCarnet->fetchColumn()) {
          $saltados++; $errores[] = "#$jugador_id: sin carnet"; continue;
        }

        // Sexo
        if ($jugSexo !== $torneoSexo) {
          $saltados++; $errores[] = "#$jugador_id: sexo no coincide"; continue;
        }

        // Nivel
        if (!$jugNivel) {
          $saltados++; $errores[] = "#$jugador_id: categoría inválida"; continue;
        }
        if ($jugNivel < $torneoNivel) {
          $saltados++; $errores[] = "#$jugador_id: es más alta (no puede bajar)"; continue;
        }

        // Duplicado
        $stDup->execute([$torneo_id, $jugador_id]);
        if ($stDup->fetchColumn()) {
          $saltados++; $errores[] = "#$jugador_id: ya inscripto"; continue;
        }

        // Insert
        try {
          $stIns->execute([$torneo_id, $jugador_id]);
          $inscriptosOk++;
        } catch (Exception $e) {
          $saltados++;
          $errores[] = "#$jugador_id: error al guardar";
        }
      }

      // refrescar lista elegibles (para que desaparezcan los inscriptos)
      $st = $pdo->prepare($sql);
      $st->execute($params);
      $rows = $st->fetchAll(PDO::FETCH_ASSOC);
      $elegibles = [];
      foreach ($rows as $r) {
        $niv = parseNivel($r['categoria']);
        if ($niv && $niv >= $torneoNivel) $elegibles[] = $r;
      }

      if ($inscriptosOk > 0) {
        $okMsg = "Listo: $inscriptosOk jugador(es) inscripto(s).";
        if ($saltados > 0) {
          $okMsg .= " Se omitieron $saltados.";
        }
      } else {
        $err = "No se inscribió ninguno. Revisá selección / carnet / categoría.";
      }

      // si hubo errores, los mostramos cortos
      if ($errores) {
        $max = 6;
        $mostrar = array_slice($errores, 0, $max);
        $extra = count($errores) > $max ? (" (+" . (count($errores) - $max) . " más)") : "";
        $okMsg .= " <span style='opacity:.85'>Omitidos: ".htmlspecialchars(implode(' · ', $mostrar)).$extra."</span>";
      }
    }
  }
}
?>

<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Inscribir jugadores</h2>
      <div style="color:var(--muted); margin-top:4px;">
        Torneo: <b><?= htmlspecialchars($torneo['nombre'] ?? ('#'.$torneo['id'])) ?></b> —
        Categoría: <b><?= htmlspecialchars($torneo['categoria']) ?></b>
      </div>
    </div>

    <a class="btn"
      href="inscriptos.php?id=<?= $torneo_id ?>"
      style="background:#93c5fd; border-color:#93c5fd; color:#0b3b73;">
      Finalizar y ver inscriptos
    </a>
  </div>

  <?php if ($err): ?>
    <div class="alert alert-danger" style="margin-top:12px;">
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endif; ?>

  <?php if ($okMsg): ?>
    <div class="alert alert-success" style="margin-top:12px;">
      <?= $okMsg ?>
    </div>
  <?php endif; ?>

  <form method="get" style="margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <input type="hidden" name="torneo_id" value="<?= (int)$torneo_id ?>">
    <input class="input" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por apellido, nombre o ID">
    <button class="btn btn-secondary" type="submit">Buscar</button>
  </form>

  <?php if (count($elegibles) === 0): ?>
    <p style="margin-top:14px; color:var(--muted);">No hay jugadores elegibles disponibles.</p>
  <?php else: ?>

    <form method="post" style="margin-top:14px;">
      <input type="hidden" name="torneo_id" value="<?= (int)$torneo_id ?>">

      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
        <button class="btn" type="submit" name="accion" value="guardar">Agregar seleccionados</button>

        <button class="btn btn-secondary" type="button" onclick="
          const cbs = document.querySelectorAll('.cb-jugador');
          const allChecked = Array.from(cbs).every(cb => cb.checked);
          cbs.forEach(cb => cb.checked = !allChecked);
        ">Marcar / Desmarcar todo</button>

        <a class="btn"
           href="inscriptos.php?id=<?= $torneo_id ?>"
           style="background:#93c5fd; border-color:#93c5fd; color:#0b3b73;">
          Finalizar y ver inscriptos
        </a>
      </div>

      <div style="border:1px solid var(--border); border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="background:rgba(255,255,255,.04);">
              <th style="text-align:left; padding:10px; width:46px;">✔</th>
              <th style="text-align:left; padding:10px; width:90px;">ID</th>
              <th style="text-align:left; padding:10px;">Jugador</th>
              <th style="text-align:left; padding:10px; width:260px;">Categoría</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($elegibles as $j): ?>
              <tr style="border-top:1px solid var(--border);">
                <td style="padding:10px;">
                  <input class="cb-jugador" type="checkbox" name="jugador_ids[]" value="<?= (int)$j['id'] ?>">
                </td>
                <td style="padding:10px;">#<?= (int)$j['id'] ?></td>
                <td style="padding:10px; font-weight:600;">
                  <?= htmlspecialchars($j['apellido'].' '.$j['nombre']) ?>
                </td>
                <td style="padding:10px; color:var(--muted);">
                  <?= htmlspecialchars($j['categoria']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:12px;">
        <button class="btn" type="submit" name="accion" value="guardar">Agregar seleccionados</button>
        <a class="btn"
           href="inscriptos.php?id=<?= $torneo_id ?>"
           style="background:#93c5fd; border-color:#93c5fd; color:#0b3b73;">
          Finalizar y ver inscriptos
        </a>
      </div>
    </form>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
