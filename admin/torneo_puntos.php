<?php
declare(strict_types=1);

$section = 'admin';
$active  = 'torneos';
$page_title = 'Cargar puntos';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/_header.php';

$pdo = getDB();

/* ========= Helpers ========= */
function es_finalizado($estado): bool {
  return mb_strtolower(trim((string)$estado)) === 'finalizado';
}
function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { return false; }
}
function ensure_zonas_tables(PDO $pdo): void {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS torneo_zonas (
      id INT AUTO_INCREMENT PRIMARY KEY,
      torneo_id INT NOT NULL,
      codigo VARCHAR(10) NOT NULL,
      orden INT NOT NULL,
      UNIQUE KEY uq_torneo_codigo (torneo_id, codigo),
      KEY idx_torneo (torneo_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS torneo_zona_equipos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      zona_id INT NOT NULL,
      equipo_id INT NOT NULL,
      posicion INT NOT NULL,
      UNIQUE KEY uq_zona_pos (zona_id, posicion),
      UNIQUE KEY uq_zona_equipo (zona_id, equipo_id),
      KEY idx_zona (zona_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
}
function foto_src($foto): string {
  $foto = trim((string)$foto);
  if ($foto === '') return '';
  if (str_starts_with($foto, '/') || str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) return $foto;
  return "/apiba-padel/uploads/jugadores/" . $foto;
}
function puntos_ranking(PDO $pdo, int $jugador_id, string $categoria): int {
  $categoria = trim((string)$categoria);
  if ($jugador_id <= 0 || $categoria === '') return 0;
  try {
    $st = $pdo->prepare("SELECT puntos FROM ranking WHERE jugador_id = ? AND categoria = ? LIMIT 1");
    $st->execute([$jugador_id, $categoria]);
    $v = $st->fetchColumn();
    return ($v !== false && $v !== null && $v !== '') ? (int)$v : 0;
  } catch (Exception $e) { return 0; }
}
function upsert_puntos(PDO $pdo, bool $hasUpdatedAt, int $torneo_id, int $jugador_id, string $fase, int $puntos): void {
  $st = $pdo->prepare("SELECT id FROM puntos_torneo WHERE torneo_id = ? AND jugador_id = ? LIMIT 1");
  $st->execute([$torneo_id, $jugador_id]);
  $id = (int)($st->fetchColumn() ?: 0);

  if ($id > 0) {
    if ($hasUpdatedAt) {
      $up = $pdo->prepare("UPDATE puntos_torneo SET fase = ?, puntos = ?, updated_at = NOW() WHERE id = ? LIMIT 1");
      $up->execute([$fase, $puntos, $id]);
    } else {
      $up = $pdo->prepare("UPDATE puntos_torneo SET fase = ?, puntos = ? WHERE id = ? LIMIT 1");
      $up->execute([$fase, $puntos, $id]);
    }
  } else {
    if ($hasUpdatedAt) {
      $ins = $pdo->prepare("INSERT INTO puntos_torneo (torneo_id, jugador_id, fase, puntos, updated_at) VALUES (?,?,?,?,NOW())");
      $ins->execute([$torneo_id, $jugador_id, $fase, $puntos]);
    } else {
      $ins = $pdo->prepare("INSERT INTO puntos_torneo (torneo_id, jugador_id, fase, puntos) VALUES (?,?,?,?)");
      $ins->execute([$torneo_id, $jugador_id, $fase, $puntos]);
    }
  }
}

/* ========= Torneo ========= */
$torneo_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
if ($torneo_id <= 0) {
  echo "<div class='card'><p>Torneo inválido.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$st = $pdo->prepare("SELECT id, nombre, categoria, estado FROM torneos WHERE id = ? LIMIT 1");
$st->execute([$torneo_id]);
$torneo = $st->fetch(PDO::FETCH_ASSOC);

if (!$torneo) {
  echo "<div class='card'><p>No se encontró el torneo.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$finalizado = es_finalizado($torneo['estado'] ?? '');
$cat_torneo = trim((string)($torneo['categoria'] ?? ''));
$disabled = (!$finalizado);

/* ========= Fases -> puntos ========= */
$fases_pts = [
  'Campeon'       => 100,
  'Finalista'     => 90,
  'Semi (+)'      => 80,
  'Semi (-)'      => 75,
  'Cuartos'       => 60,
  'Octavos'       => 50,
  'Dieciseisavos' => 40,
  'Zona'          => 35,
];
$fases = array_keys($fases_pts);

$PT_HAS_UPDATED_AT = column_exists($pdo, 'puntos_torneo', 'updated_at');
$hasFoto = column_exists($pdo, 'jugadores', 'foto');
$sf1 = $hasFoto ? "j1.foto" : "''";
$sf2 = $hasFoto ? "j2.foto" : "''";

/* ========= Asegurar tablas zonas ========= */
ensure_zonas_tables($pdo);

/* ========= Puntos ya cargados ========= */
$puntos_por_jugador = [];
try {
  $stP = $pdo->prepare("SELECT jugador_id, fase, puntos FROM puntos_torneo WHERE torneo_id = ?");
  $stP->execute([$torneo_id]);
  foreach ($stP->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $puntos_por_jugador[(int)$r['jugador_id']] = $r;
  }
} catch (Exception $e) { $puntos_por_jugador = []; }

/* ========= POST guardar ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$finalizado) {
    header("Location: torneo_puntos.php?id=".$torneo_id."&msg=".urlencode("El torneo debe estar finalizado."));
    exit;
  }

  $equipos_post = $_POST['equipos'] ?? [];
  if (!is_array($equipos_post)) $equipos_post = [];

  $unicos = ['Campeon','Finalista','Semi (+)','Semi (-)'];
  $countUnicos = array_fill_keys($unicos, 0);
  $zonaZonaCount = []; // [zona_id] => cant "Zona"
  $faltan = 0;

  foreach ($equipos_post as $eqid => $data) {
    $fase = trim((string)($data['fase'] ?? ''));
    $zona_id = (int)($data['zona_id'] ?? 0);

    if ($fase === '') $faltan++;
    if ($fase !== '' && isset($countUnicos[$fase])) $countUnicos[$fase]++;

    if ($fase === 'Zona' && $zona_id > 0) {
      if (!isset($zonaZonaCount[$zona_id])) $zonaZonaCount[$zona_id] = 0;
      $zonaZonaCount[$zona_id]++;
    }
  }

  if ($faltan > 0) {
    header("Location: torneo_puntos.php?id=".$torneo_id."&msg=".urlencode("Error: hay equipos sin fase. Tenés que seleccionar una fase en todos."));
    exit;
  }

  foreach ($countUnicos as $fase => $c) {
    if ($c > 1) {
      header("Location: torneo_puntos.php?id=".$torneo_id."&msg=".urlencode("Error: Hay {$c} en '{$fase}'. Solo puede haber 1."));
      exit;
    }
  }

  foreach ($zonaZonaCount as $zid => $c) {
    if ($c > 1) {
      header("Location: torneo_puntos.php?id=".$torneo_id."&msg=".urlencode("Error: En una misma zona no puede haber 2 'Zona (35)'. Revisá esa zona."));
      exit;
    }
  }

  try {
    foreach ($equipos_post as $eqid => $data) {
      $j1 = (int)($data['j1'] ?? 0);
      $j2 = (int)($data['j2'] ?? 0);
      $fase = trim((string)($data['fase'] ?? ''));

      if ($j1 <= 0 || $j2 <= 0) continue;
      if ($fase === '' || !isset($fases_pts[$fase])) continue;

      $puntos = (int)$fases_pts[$fase];
      upsert_puntos($pdo, $PT_HAS_UPDATED_AT, $torneo_id, $j1, $fase, $puntos);
      upsert_puntos($pdo, $PT_HAS_UPDATED_AT, $torneo_id, $j2, $fase, $puntos);
    }

    header("Location: torneo_puntos.php?id=".$torneo_id."&msg=".urlencode("Guardado OK."));
    exit;
  } catch (Exception $e) {
    header("Location: torneo_puntos.php?id=".$torneo_id."&msg=".urlencode("Error guardando: ".$e->getMessage()));
    exit;
  }
}

/* ========= Cargar zonas + equipos ========= */
$zonas = [];
try {
  $stZ = $pdo->prepare("SELECT * FROM torneo_zonas WHERE torneo_id=? ORDER BY orden ASC, id ASC");
  $stZ->execute([$torneo_id]);
  $zonas = $stZ->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $zonas = []; }

$zonaEquipos = [];
$hayZonasArmadas = false;

if ($zonas) {
  $ids = array_map(fn($z)=> (int)$z['id'], $zonas);
  $in = implode(',', array_fill(0, count($ids), '?'));

  $stE = $pdo->prepare("
    SELECT
      tze.zona_id, tze.posicion,
      e.id AS equipo_id,
      e.jugador1_id, e.jugador2_id,
      e.categoria_j1, e.categoria_j2,
      j1.apellido AS j1_apellido, j1.nombre AS j1_nombre, $sf1 AS j1_foto,
      j2.apellido AS j2_apellido, j2.nombre AS j2_nombre, $sf2 AS j2_foto
    FROM torneo_zona_equipos tze
    JOIN torneo_equipos e ON e.id = tze.equipo_id
    JOIN jugadores j1 ON j1.id = e.jugador1_id
    JOIN jugadores j2 ON j2.id = e.jugador2_id
    WHERE tze.zona_id IN ($in)
    ORDER BY tze.zona_id ASC, tze.posicion ASC
  ");
  $stE->execute($ids);
  $rows = $stE->fetchAll(PDO::FETCH_ASSOC);

  if ($rows) $hayZonasArmadas = true;

  foreach ($rows as $r) {
    $cat1 = trim((string)($r['categoria_j1'] ?? ''));
    $cat2 = trim((string)($r['categoria_j2'] ?? ''));
    if ($cat1==='') $cat1 = $cat_torneo;
    if ($cat2==='') $cat2 = $cat_torneo;

    $rk1 = puntos_ranking($pdo, (int)$r['jugador1_id'], $cat1);
    $rk2 = puntos_ranking($pdo, (int)$r['jugador2_id'], $cat2);
    $r['_total'] = $rk1 + $rk2;

    $j1 = (int)$r['jugador1_id'];
    $j2 = (int)$r['jugador2_id'];
    $fasePrev = $puntos_por_jugador[$j1]['fase'] ?? '';
    if ($fasePrev === '') $fasePrev = ($puntos_por_jugador[$j2]['fase'] ?? '');
    $r['_fase_prev'] = (string)$fasePrev;
    $r['_pts_prev']  = ($fasePrev !== '' && isset($fases_pts[$fasePrev])) ? (int)$fases_pts[$fasePrev] : '';

    $zona_id = (int)$r['zona_id'];
    if (!isset($zonaEquipos[$zona_id])) $zonaEquipos[$zona_id] = [];
    $zonaEquipos[$zona_id][] = $r;
  }
}
?>

<style>
.zwrap{
  display:grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap:12px;
  margin-top:12px;
  width:100%;
}
@media (max-width: 900px){ .zwrap{ grid-template-columns: 1fr; } }

.zcard{
  background: linear-gradient(180deg, rgba(80,130,255,.16), rgba(80,130,255,.08));
  border: 1px solid rgba(80,130,255,.22);
  border-radius: 14px;
  padding: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,.18);
  min-width:0;
}

.zhead{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:8px; min-width:0; }
.ztitle{ font-size:15px; font-weight:900; }
.zmeta{ font-size:11px; opacity:.85; }
.smallmuted{ font-size:12px; opacity:.8; }

.teamline{
  display:flex;
  gap:8px;
  align-items:flex-start;
  justify-content:space-between;
  padding:8px;
  border-radius:12px;
  background: rgba(0,0,0,.14);
  border: 1px solid rgba(255,255,255,.06);
  margin-bottom:0;
  min-width:0;
}
.tleft{ display:flex; gap:8px; align-items:center; min-width:0; }
.avas{ display:flex; gap:6px; flex:0 0 auto; }
.ava{ width:30px;height:30px;border-radius:999px;overflow:hidden;background:rgba(255,255,255,.10); }
.ava img{ width:100%;height:100%;object-fit:cover; }

.tnames{ min-width:0; max-width: 320px; }
.tnames .n, .tnames .n2{
  font-weight:900;
  font-size:12px;
  line-height:1.15;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.tnames .p{ font-size:11px; opacity:.85; margin-top:2px; }

.fasebox{
  display:flex;
  gap:10px;
  align-items:center;
  flex-wrap:wrap;
  padding:10px;
  border-radius:12px;
  background: rgba(0,0,0,.10);
  border:1px solid rgba(255,255,255,.06);
  margin-top:8px;
}

/* ✅ Separador debajo de Fase/Puntos */
.team-sep{
  height: 8px;
  border-radius: 999px;
  background: rgba(255,255,255,.22);
  border: 1px solid rgba(0,0,0,.06);
  margin: 10px 0 12px 0;
}

/* ✅ blancos */
select.input{
  padding:6px 10px;
  border-radius:10px;
  border:1px solid #d6d6d6;
  background:#ffffff;
  color:#111;
  font-weight:800;
  min-width:190px;
}
input.input{
  padding:6px 10px;
  border-radius:10px;
  border:1px solid #d6d6d6;
  background:#ffffff;
  color:#111;
  font-weight:800;
}
input.input[readonly]{ background:#ffffff; opacity:1; }

/* ✅ puntos mas chico + centrado */
input.input[data-pts='1']{
  width: 64px !important;
  padding: 6px 8px !important;
  text-align: center !important;
}

.warnbox{
  margin-top:12px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.08);
  background: rgba(255, 183, 77, .10);
}
.btn:disabled{ opacity:.45; cursor:not-allowed; }
</style>

<script>
  const FASE_PTS = <?= json_encode($fases_pts, JSON_UNESCAPED_UNICODE) ?>;
  const UNICOS = new Set(['Campeon','Finalista','Semi (+)','Semi (-)']);

  function setPtsFromFase(selectEl){
    const fase = selectEl.value || "";
    const pts = (fase && (fase in FASE_PTS)) ? FASE_PTS[fase] : "";

    const box = selectEl.closest(".fasebox");
    if (box) {
      const input = box.querySelector("input[data-pts='1']");
      if (input) input.value = pts;
    }

    // únicos globales
    if (fase && UNICOS.has(fase)) {
      const all = document.querySelectorAll("select[data-fase-select='1']");
      all.forEach(s => {
        if (s === selectEl) return;
        if (s.value === fase) {
          s.value = "";
          setPtsFromFase(s);
        }
      });
    }

    // no 2 ZONA dentro de la misma zona
    if (fase === "Zona") {
      const zcard = selectEl.closest(".zcard");
      if (!zcard) return;

      const selectsZona = zcard.querySelectorAll("select[data-fase-select='1']");
      let countZona = 0;
      selectsZona.forEach(s => { if (s.value === "Zona") countZona++; });

      if (countZona > 1) {
        selectEl.value = "";
        setPtsFromFase(selectEl);
        alert("En una misma zona NO puede haber 2 equipos con fase 'Zona (35)'.");
      }
    }
  }

  function validarAntesDeGuardar(){
    const all = document.querySelectorAll("select[data-fase-select='1']");
    let faltan = 0;
    all.forEach(s => { if (!s.value) faltan++; });
    if (faltan > 0) {
      alert("Tenés equipos sin Fase. Tenés que elegir una fase en todos antes de guardar.");
      return false;
    }

    const zonas = document.querySelectorAll(".zcard[data-zona='1']");
    for (const z of zonas) {
      const sels = z.querySelectorAll("select[data-fase-select='1']");
      let c = 0;
      sels.forEach(s => { if (s.value === "Zona") c++; });
      if (c > 1) {
        alert("Hay más de un 'Zona (35)' dentro de una misma zona. Corregilo antes de guardar.");
        return false;
      }
    }
    return true;
  }
</script>

<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Cargar puntos</h2>
      <div style="color:var(--muted); margin-top:4px;">
        Torneo: <b><?= htmlspecialchars($torneo['nombre'] ?? ('#'.$torneo['id'])) ?></b> —
        Categoría: <b><?= htmlspecialchars($torneo['categoria'] ?? '') ?></b> —
        Estado: <b><?= htmlspecialchars($torneo['estado'] ?? '') ?></b>
      </div>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <a href="inscriptos.php?id=<?= (int)$torneo_id ?>" class="btn btn-volver">Volver a inscriptos</a>
    </div>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success" style="margin-top:12px;"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if (!$finalizado): ?>
    <div class="alert alert-danger" style="margin-top:12px;">
      Este torneo no está <b>finalizado</b>. No se pueden cargar puntos.
    </div>
  <?php endif; ?>

  <?php if (!$hayZonasArmadas): ?>
    <div class="warnbox">
      No hay zonas armadas en este torneo. Para verlas como en generar_torneo.php, primero armá las zonas.
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top:14px;" onsubmit="return validarAntesDeGuardar();">
    <input type="hidden" name="id" value="<?= (int)$torneo_id ?>">

    <div class="zwrap">
      <?php foreach ($zonas as $z): ?>
        <?php
          $zona_id = (int)$z['id'];
          $codigo  = (string)$z['codigo'];
          if ($codigo === 'LIBRE') continue;

          $lista  = $zonaEquipos[$zona_id] ?? [];
          $actual = count($lista);
        ?>

        <div class="zcard" data-zona="1">
          <div class="zhead">
            <div style="min-width:0;">
              <div class="ztitle"><?= htmlspecialchars('Zona '.$codigo) ?></div>
              <div class="zmeta">Equipos: <b><?= (int)$actual ?></b></div>
            </div>
          </div>

          <?php if (!$lista): ?>
            <div class="smallmuted">Zona vacía.</div>
          <?php else: ?>
            <?php foreach ($lista as $r): ?>
              <?php
                $eqid = (int)$r['equipo_id'];
                $j1 = (int)$r['jugador1_id'];
                $j2 = (int)$r['jugador2_id'];

                $src1 = foto_src($r['j1_foto'] ?? '');
                $src2 = foto_src($r['j2_foto'] ?? '');

                $n1 = trim((string)$r['j1_nombre']).' '.trim((string)$r['j1_apellido']);
                $n2 = trim((string)$r['j2_nombre']).' '.trim((string)$r['j2_apellido']);

                $fasePrev = (string)($r['_fase_prev'] ?? '');
                $ptsPrev  = ($fasePrev !== '' && isset($fases_pts[$fasePrev])) ? (int)$fases_pts[$fasePrev] : '';
              ?>

              <div class="teamline">
                <div class="tleft">
                  <div class="avas">
                    <div class="ava"><?php if($src1): ?><img src="<?= htmlspecialchars($src1) ?>" alt=""><?php endif; ?></div>
                    <div class="ava"><?php if($src2): ?><img src="<?= htmlspecialchars($src2) ?>" alt=""><?php endif; ?></div>
                  </div>

                  <div class="tnames">
                    <div class="n"><?= htmlspecialchars($n1) ?></div>
                    <div class="n2"><?= htmlspecialchars($n2) ?></div>
                    <div class="p"><?= (int)$r['_total'] ?> pts — Pos <?= (int)$r['posicion'] ?></div>
                  </div>
                </div>
              </div>

              <div class="fasebox">
                <input type="hidden" name="equipos[<?= $eqid ?>][j1]" value="<?= (int)$j1 ?>">
                <input type="hidden" name="equipos[<?= $eqid ?>][j2]" value="<?= (int)$j2 ?>">
                <input type="hidden" name="equipos[<?= $eqid ?>][zona_id]" value="<?= (int)$zona_id ?>">

                <label style="min-width:55px;">Fase:</label>
                <select class="input"
                        data-fase-select="1"
                        name="equipos[<?= $eqid ?>][fase]"
                        onchange="setPtsFromFase(this)"
                        <?= $disabled ? 'disabled' : '' ?>>
                  <option value="">—</option>
                  <?php foreach ($fases as $f): ?>
                    <option value="<?= htmlspecialchars($f) ?>" <?= ($fasePrev === $f ? 'selected' : '') ?>>
                      <?= htmlspecialchars($f) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <label style="min-width:60px;">Puntos:</label>
                <input class="input" type="text"
                       data-pts="1"
                       name="equipos[<?= $eqid ?>][puntos_auto]"
                       value="<?= ($ptsPrev !== '' ? (int)$ptsPrev : '') ?>"
                       readonly>
              </div>

              <!-- ✅ separador DEBAJO de fase/puntos -->
              <div class="team-sep"></div>

            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      <?php endforeach; ?>
    </div>

    <div style="margin-top:16px; display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; align-items:center;">
      <button class="btn btn-ok" type="submit" <?= ($disabled ? 'disabled' : '') ?>>
        GUARDAR TODO
      </button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
