<?php
ob_start();
$section = 'admin';
$active  = 'torneos';
$page_title = 'Equipos unidos';

require_once __DIR__ . '/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
require_once __DIR__ . '/_header.php';

$pdo = getDB();

/* =========================
   HELPERS
   ========================= */
function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { return false; }
}
function normalize_estado($s): string {
  $e = mb_strtolower(trim((string)$s));
  $e = str_replace(['_', '-'], ' ', $e);
  $e = preg_replace('/\s+/', ' ', $e);
  return $e;
}
function get_enum_values(PDO $pdo, string $table, string $col): array {
  try {
    $st = $pdo->query("SHOW COLUMNS FROM `$table` LIKE ".$pdo->quote($col));
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [];
    $type = (string)($row['Type'] ?? '');
    if (!str_starts_with(strtolower($type), 'enum(')) return [];
    $inside = substr($type, 5, -1);
    $vals = [];
    $cur = '';
    $inQ = false;
    for ($i=0; $i<strlen($inside); $i++){
      $ch = $inside[$i];
      if ($ch === "'" && ($i===0 || $inside[$i-1] !== '\\')) { $inQ = !$inQ; continue; }
      if ($ch === ',' && !$inQ) { $vals[] = $cur; $cur=''; continue; }
      $cur .= $ch;
    }
    if ($cur !== '') $vals[] = $cur;
    $vals = array_map(fn($v)=>str_replace("\\'", "'", trim($v)), $vals);
    return array_values(array_filter($vals, fn($v)=>$v!=='')); 
  } catch (Exception $e) { return []; }
}
function pick_save_value(string $target_norm, array $enum_vals): string {
  if (!$enum_vals) return ($target_norm === 'en curso') ? 'en curso' : 'abierto';
  $map = [];
  foreach ($enum_vals as $v) $map[normalize_estado($v)] = $v;

  if (isset($map[$target_norm])) return $map[$target_norm];

  if ($target_norm === 'en curso') {
    foreach (['encurso','en curso','en_curso','en-curso'] as $k) {
      $k2 = normalize_estado($k);
      if (isset($map[$k2])) return $map[$k2];
    }
  } else {
    foreach (['abierto','open'] as $k) {
      $k2 = normalize_estado($k);
      if (isset($map[$k2])) return $map[$k2];
    }
  }
  return $enum_vals[0];
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

/* =========================
   TORNEO
   ========================= */
$torneo_id = (int)($_GET['id'] ?? 0);
if ($torneo_id <= 0) {
  echo "<div class='card'><p>Torneo inválido.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

$cols = ['id','nombre','categoria'];
$hasEstado = column_exists($pdo,'torneos','estado');
if ($hasEstado) $cols[] = 'estado';

$stmt = $pdo->prepare("SELECT ".implode(',', $cols)." FROM torneos WHERE id = ? LIMIT 1");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$torneo) {
  echo "<div class='card'><p>No se encontró el torneo.</p></div>";
  require_once __DIR__ . '/_footer.php';
  exit;
}

/* Estado actual */
$estado_raw  = trim((string)($torneo['estado'] ?? ''));
$estado_norm = normalize_estado($estado_raw);
$is_finalizado = ($estado_norm === 'finalizado');

if (!$is_finalizado && ($estado_norm === '' || ($estado_norm !== 'abierto' && $estado_norm !== 'en curso'))) {
  $estado_raw  = 'abierto';
  $estado_norm = 'abierto';
}

/* =========================
   CAMBIAR ESTADO (toggle)
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'set_estado') {
  if ($is_finalizado) {
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Torneo finalizado: no se puede cambiar el estado.")."&keep=1");
    exit;
  }
  if (!$hasEstado) {
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("No existe columna torneos.estado.")."&keep=1");
    exit;
  }

  $nuevo = normalize_estado($_POST['estado'] ?? '');
  if ($nuevo !== 'abierto' && $nuevo !== 'en curso') {
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Estado inválido.")."&keep=1");
    exit;
  }

  $enum_vals = get_enum_values($pdo, 'torneos', 'estado');
  $saveValue = pick_save_value($nuevo, $enum_vals);

  try {
    $up = $pdo->prepare("UPDATE torneos SET estado = ? WHERE id = ? LIMIT 1");
    $up->execute([$saveValue, $torneo_id]);
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Estado actualizado a: ".($nuevo==='en curso'?'En curso':'Abierto'))."&keep=1");
    exit;
  } catch (Exception $e) {
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Error al guardar estado: ".$e->getMessage())."&keep=1");
    exit;
  }
}

/* Releer estado real */
if ($hasEstado) {
  try {
    $re = $pdo->prepare("SELECT estado FROM torneos WHERE id = ? LIMIT 1");
    $re->execute([$torneo_id]);
    $estado_raw  = trim((string)$re->fetchColumn());
    $estado_norm = normalize_estado($estado_raw);
  } catch (Exception $e) {}
}
$is_finalizado = ($estado_norm === 'finalizado');
$estado_es_abierto  = ($estado_norm === 'abierto');
$estado_es_en_curso = ($estado_norm === 'en curso');

$cat_torneo = trim((string)($torneo['categoria'] ?? ''));
$hasFoto = column_exists($pdo, 'jugadores', 'foto');

/* Zonas generadas */
$zonasGeneradas = false;
try {
  $stZG = $pdo->prepare("
    SELECT COUNT(*)
    FROM torneo_zona_equipos tze
    JOIN torneo_zonas tz ON tz.id = tze.zona_id
    WHERE tz.torneo_id = ?
      AND tz.codigo <> 'LIBRE'
  ");
  $stZG->execute([$torneo_id]);
  $zonasGeneradas = ((int)$stZG->fetchColumn() > 0);
} catch (Exception $e) { $zonasGeneradas = false; }

/* Total inscriptos */
$insc_has_estado = column_exists($pdo, 'inscripciones', 'estado');
$total_inscriptos = 0;
try {
  if ($insc_has_estado) {
    $stT = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE torneo_id = ? AND estado = 'activo'");
    $stT->execute([$torneo_id]);
  } else {
    $stT = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE torneo_id = ?");
    $stT->execute([$torneo_id]);
  }
  $total_inscriptos = (int)$stT->fetchColumn();
} catch (Exception $e) { $total_inscriptos = 0; }

/* Equipos */
$equipos = [];
$st = $pdo->prepare("
  SELECT
    e.id AS equipo_id,
    e.jugador1_id, e.jugador2_id,
    e.categoria_j1, e.categoria_j2,
    j1.apellido AS j1_apellido, j1.nombre AS j1_nombre, ".($hasFoto ? "j1.foto" : "''")." AS j1_foto,
    j2.apellido AS j2_apellido, j2.nombre AS j2_nombre, ".($hasFoto ? "j2.foto" : "''")." AS j2_foto
  FROM torneo_equipos e
  JOIN jugadores j1 ON j1.id = e.jugador1_id
  JOIN jugadores j2 ON j2.id = e.jugador2_id
  WHERE e.torneo_id = ?
  ORDER BY e.id ASC
");
$st->execute([$torneo_id]);
$equipos = $st->fetchAll(PDO::FETCH_ASSOC);
$totalEquipos = count($equipos);

/* Restantes sin unir */
$restantes = 0;
try {
  if ($insc_has_estado) {
    $st2 = $pdo->prepare("
      SELECT COUNT(*)
      FROM inscripciones i
      WHERE i.torneo_id = ?
        AND i.estado = 'activo'
        AND i.jugador_id NOT IN (
          SELECT jugador1_id FROM torneo_equipos WHERE torneo_id = ?
          UNION
          SELECT jugador2_id FROM torneo_equipos WHERE torneo_id = ?
        )
    ");
    $st2->execute([$torneo_id, $torneo_id, $torneo_id]);
  } else {
    $st2 = $pdo->prepare("
      SELECT COUNT(*)
      FROM inscripciones i
      WHERE i.torneo_id = ?
        AND i.jugador_id NOT IN (
          SELECT jugador1_id FROM torneo_equipos WHERE torneo_id = ?
          UNION
          SELECT jugador2_id FROM torneo_equipos WHERE torneo_id = ?
        )
    ");
    $st2->execute([$torneo_id, $torneo_id, $torneo_id]);
  }
  $restantes = (int)$st2->fetchColumn();
} catch (Exception $e) { $restantes = 0; }

/* Regla mínima */
$minEquipos = 6;
$puedeGenerarPorRegla = ($restantes === 0 && $totalEquipos >= $minEquipos);

/* ✅ CLAVE: ABIERTO => SIEMPRE bloqueado. EN CURSO => habilita si cumple reglas */
$disabledGen = ($is_finalizado || !$estado_es_en_curso || !$puedeGenerarPorRegla);

/* POST: DESUNIR / GENERAR */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] !== 'set_estado') {
  if ($is_finalizado) {
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Torneo finalizado: acciones bloqueadas.")."&keep=1");
    exit;
  }

  $accion = $_POST['accion'] ?? '';

  if ($accion === 'desunir') {
    $equipo_id = (int)($_POST['equipo_id'] ?? 0);
    if ($equipo_id > 0) {
      $del = $pdo->prepare("DELETE FROM torneo_equipos WHERE id = ? AND torneo_id = ? LIMIT 1");
      $del->execute([$equipo_id, $torneo_id]);
      header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Equipo desarmado.")."&keep=1");
      exit;
    }
    header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("Equipo inválido.")."&keep=1");
    exit;
  }

  if ($accion === 'generar_torneo') {
    if ($disabledGen) {
      header("Location: equipos_unidos.php?id=".$torneo_id."&msg=".urlencode("No se puede generar (estado/requisitos).")."&keep=1");
      exit;
    }
    header("Location: generar_torneo.php?id=".$torneo_id);
    exit;
  }
}
?>

<style>
/* =========================
   GRID EQUIPOS
   ========================= */
.team-wrap{
  display:grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top:12px;
}
@media (max-width:1100px){ .team-wrap{ grid-template-columns: repeat(2,1fr); } }
@media (max-width:720px){ .team-wrap{ grid-template-columns: 1fr; } }

.team-card{
  background: linear-gradient(180deg, rgba(80,130,255,.08), rgba(80,130,255,.03));
  border: 1px solid rgba(80,130,255,.18);
  padding: 10px;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(0,0,0,.08);
}

.team-num{ display:flex; align-items:center; gap:8px; margin-bottom:6px; }
.team-num .n{ font-size:20px; font-weight:900; }
.team-num .lbl{
  font-size:11px;
  padding:3px 8px;
  border-radius:999px;
  background: rgba(80,130,255,.12);
  border:1px solid rgba(80,130,255,.25);
}

.team-row{ display:grid; grid-template-columns: 1fr 1fr; gap:8px; }

.pbox{
  display:flex;
  gap:6px;
  align-items:center;
  padding:6px;
  border-radius:10px;
  background: rgba(0,0,0,.12);
  border: 1px solid rgba(255,255,255,.05);
}
.pimg{
  width:32px;height:32px;border-radius:999px;overflow:hidden;
  background:rgba(255,255,255,.1); flex:0 0 auto;
}
.pimg img{ width:100%;height:100%;object-fit:cover; }

.pname{ font-size:12px; font-weight:700; line-height:1.2; }
.ppoints{ font-size:11px; opacity:.8; }

.ptotal{
  margin-top:8px;
  font-size:12px;
  font-weight:800;
  padding:6px 8px;
  border-radius:10px;
  background: rgba(80,130,255,.10);
  border:1px dashed rgba(80,130,255,.25);
}

.team-actions{
  margin-top:8px;
  display:flex;
  justify-content:space-between;
  gap:8px;
  align-items:center;
  flex-wrap:wrap;
}

.mini-muted{ font-size:11px; opacity:.75; }

/* =========================
   TOP BAR
   ========================= */
.top-actions{
  display:flex;
  gap:14px;
  align-items:center;
  flex-wrap:wrap;
  justify-content:flex-end;
}

/* Botones arriba (+35% aprox) */
.btn-mini{
  padding: 10px 16px !important;
  font-size: 16px !important;
  border-radius: 14px !important;
  line-height: 1.1 !important;
}

/* =========================
   TOGGLE (+35% aprox)
   ========================= */
.toggle-mini{
  display:inline-flex;
  align-items:center;
  gap:10px;
  padding:8px 12px;
  border-radius:999px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.16);
}

.toggle-mini .lab{
  font-size:14px;
  font-weight:900;
}

.switch-mini{
  position:relative;
  width:50px;
  height:26px;
  border-radius:999px;
  background: rgba(80,130,255,.25);
  border: 1px solid rgba(80,130,255,.55);
  cursor:pointer;
  flex:0 0 auto;
}

.switch-mini input{
  position:absolute;
  inset:0;
  opacity:0;
  cursor:pointer;
}

.knob-mini{
  position:absolute;
  top:3px;
  left:3px;
  width:20px;
  height:20px;
  border-radius:999px;
  background:#fff;
  box-shadow: 0 2px 6px rgba(0,0,0,.22);
  transition: transform .18s ease;
}

.switch-mini input:checked + .knob-mini{ transform: translateX(22px); }

.toggle-mini .txt{
  font-size:14px;
  font-weight:900;
  padding:4px 10px;
  border-radius:999px;
  background: rgba(80,130,255,.14);
  border: 1px solid rgba(80,130,255,.30);
  white-space:nowrap;
}

/* =========================
   BLOQUEO BOTÓN
   ========================= */
.btn-disabled,
button:disabled{
  opacity:.45 !important;
  filter: grayscale(1) !important;
  cursor:not-allowed !important;
  pointer-events:none !important;
}
</style>

<script>
(function(){
  const key = "apiba_eq_scroll_keep_<?= (int)$torneo_id ?>";
  function restoreScroll(){
    try{
      const y = sessionStorage.getItem(key);
      if (y === null) return;
      window.scrollTo(0, parseInt(y, 10) || 0);
    }catch(e){}
  }

  try{
    const url = new URL(window.location.href);
    if (url.searchParams.get("keep") === "1") {
      document.addEventListener("DOMContentLoaded", function(){
        restoreScroll(); requestAnimationFrame(restoreScroll);
        setTimeout(restoreScroll, 0); setTimeout(restoreScroll, 60); setTimeout(restoreScroll, 160);
      });
      window.addEventListener("load", function(){
        restoreScroll(); requestAnimationFrame(restoreScroll);
        try{ sessionStorage.removeItem(key); }catch(e){}
      });
      url.searchParams.delete("keep");
      window.history.replaceState({}, "", url.toString());
    }
  }catch(e){}

  window.APIBA_saveScroll = function(){
    try{ sessionStorage.setItem(key, String(window.scrollY || 0)); }catch(e){}
    return true;
  };
})();
</script>

<div class="card">
  <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start; flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Equipos unidos</h2>
      <div style="color:var(--muted); margin-top:4px;">
        Torneo: <b><?= htmlspecialchars($torneo['nombre'] ?? ('#'.$torneo_id)) ?></b> —
        Categoría: <b><?= htmlspecialchars($torneo['categoria'] ?? '') ?></b>
        <?php if ($hasEstado): ?> — Estado: <b><?= htmlspecialchars($estado_raw) ?></b><?php endif; ?>
      </div>
      <div style="margin-top:6px; display:flex; gap:10px; flex-wrap:wrap; color:var(--muted); font-size:12px;">
        <span>Inscriptos: <b><?= (int)$total_inscriptos ?></b></span>
        <span>Equipos: <b><?= (int)$totalEquipos ?></b> (mín: <b><?= (int)$minEquipos ?></b>)</span>
        <span>Restantes sin unir: <b><?= (int)$restantes ?></b></span>
      </div>
    </div>

    <div class="top-actions">
      <a class="btn btn-secondary btn-mini" href="inscriptos.php?id=<?= (int)$torneo_id ?>" onclick="return APIBA_saveScroll()">⬅ Inscriptos</a>

      <?php if (!$is_finalizado): ?>
        <a class="btn btn-inscribir btn-mini" href="inscribir_jugador.php?torneo_id=<?= (int)$torneo_id ?>" onclick="return APIBA_saveScroll()">+ Inscribir</a>
      <?php else: ?>
        <button class="btn btn-inscribir btn-mini" type="button" disabled>+ Inscribir</button>
      <?php endif; ?>

      <?php if ($hasEstado): ?>
        <form method="post" style="margin:0;" onsubmit="return APIBA_saveScroll()">
          <input type="hidden" name="accion" value="set_estado">
          <input type="hidden" name="estado" id="apiba_estado_value" value="<?= htmlspecialchars($estado_es_en_curso ? 'en curso' : 'abierto') ?>">
          <div class="toggle-mini" title="Cambiar estado (Abierto / En curso)">
            <span class="lab">Estado</span>
            <label class="switch-mini">
              <input type="checkbox" <?= $estado_es_en_curso ? 'checked' : '' ?> onchange="
                APIBA_saveScroll();
                const v = this.checked ? 'en curso' : 'abierto';
                document.getElementById('apiba_estado_value').value = v;
                this.form.submit();
              ">
              <span class="knob-mini"></span>
            </label>
            <span class="txt"><?= $estado_es_en_curso ? 'EN CURSO' : 'ABIERTO' ?></span>
          </div>
        </form>
      <?php endif; ?>

      <form method="post" style="margin:0;" onsubmit="return APIBA_saveScroll()">
        <input type="hidden" name="accion" value="generar_torneo">
        <?php
          if ($is_finalizado) $title = "Torneo finalizado";
          elseif (!$estado_es_en_curso) $title = "Bloqueado: debe estar EN CURSO";
          elseif (!$puedeGenerarPorRegla) $title = "No cumple requisitos (equipos/restantes)";
          else $title = "Listo para generar";
        ?>
        <button
          class="btn btn-ok btn-mini <?= $disabledGen ? 'btn-disabled' : '' ?>"
          type="submit"
          <?= $disabledGen ? 'disabled' : '' ?>
          title="<?= htmlspecialchars($title) ?>"
        >
          GENERAR TORNEO
        </button>
      </form>

      <?php if ($zonasGeneradas): ?>
        <a class="btn btn-ok btn-mini" href="generar_torneo.php?id=<?= (int)$torneo_id ?>&print=1" onclick="return APIBA_saveScroll()">ZONAS</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success" style="margin-top:12px;"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if (count($equipos) === 0): ?>
    <p style="color:var(--muted); margin-top:12px;">No hay equipos armados.</p>
  <?php else: ?>
    <div class="team-wrap">
      <?php $i=0; foreach($equipos as $eq): $i++; ?>
        <?php
          $j1 = (int)$eq['jugador1_id'];
          $j2 = (int)$eq['jugador2_id'];

          $cat1 = trim((string)($eq['categoria_j1'] ?? '')); if ($cat1==='') $cat1 = $cat_torneo;
          $cat2 = trim((string)($eq['categoria_j2'] ?? '')); if ($cat2==='') $cat2 = $cat_torneo;

          $rk1 = puntos_ranking($pdo,$j1,$cat1);
          $rk2 = puntos_ranking($pdo,$j2,$cat2);

          $src1 = foto_src($eq['j1_foto'] ?? '');
          $src2 = foto_src($eq['j2_foto'] ?? '');
        ?>
        <div class="team-card">
          <div class="team-num">
            <div class="n">#<?= $i ?></div>
            <div class="lbl">Equipo</div>
          </div>

          <div class="team-row">
            <div class="pbox">
              <div class="pimg"><?php if($src1): ?><img src="<?= htmlspecialchars($src1) ?>" alt=""><?php endif; ?></div>
              <div>
                <div class="pname"><?= htmlspecialchars($eq['j1_nombre'].' '.$eq['j1_apellido']) ?></div>
                <div class="ppoints"><?= (int)$rk1 ?> pts</div>
              </div>
            </div>

            <div class="pbox">
              <div class="pimg"><?php if($src2): ?><img src="<?= htmlspecialchars($src2) ?>" alt=""><?php endif; ?></div>
              <div>
                <div class="pname"><?= htmlspecialchars($eq['j2_nombre'].' '.$eq['j2_apellido']) ?></div>
                <div class="ppoints"><?= (int)$rk2 ?> pts</div>
              </div>
            </div>
          </div>

          <div class="ptotal">Total: <?= (int)($rk1+$rk2) ?> pts</div>

          <div class="team-actions">
            <div class="mini-muted">Categoría: torneo / anotada.</div>

            <form method="post" style="margin:0;" onsubmit="return APIBA_saveScroll()">
              <input type="hidden" name="accion" value="desunir">
              <input type="hidden" name="equipo_id" value="<?= (int)$eq['equipo_id'] ?>">
              <button class="btn btn-danger" type="submit"
                      <?= $is_finalizado ? 'disabled' : '' ?>
                      onclick="return <?= $is_finalizado ? 'false' : "confirm('¿Deshacer esta unión?');" ?>">
                DESUNIR
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
<?php ob_end_flush(); ?>
