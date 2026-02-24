<?php
declare(strict_types=1);
ob_start();

$section = 'admin';
$active  = 'torneos';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$printMode = (isset($_GET['print']) && (string)$_GET['print'] === '1');

function fmt_fecha_archivo(?string $s): string {
  $s = trim((string)$s);
  if ($s === '' || strtotime($s) === false) return date('d-m-Y');
  return date('d-m-Y', strtotime($s));
}
function limpiar_cat_archivo(string $cat): string {
  $cat = trim($cat);
  $cat = preg_replace('/[^A-Za-z0-9 ]+/', '', $cat);
  $cat = trim(preg_replace('/\s+/', ' ', $cat));
  return $cat;
}

/* ========= Torneo ID ========= */
$torneo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($torneo_id <= 0) {
  $page_title = 'Generar Torneo';
  require_once __DIR__ . '/_header.php';
  echo "<div class='card'><p>Torneo inválido.</p></div>";
  require_once __DIR__ . '/_footer.php';
  ob_end_flush();
  exit;
}

/* ========= PRE-CARGA (solo para title PDF) ANTES del header ========= */
$page_title = 'Generar Torneo';
if ($printMode) {
  try {
    $stPre = $pdo->prepare("SELECT categoria, fecha_inicio FROM torneos WHERE id=? LIMIT 1");
    $stPre->execute([$torneo_id]);
    $pre = $stPre->fetch(PDO::FETCH_ASSOC);

    $catPre = limpiar_cat_archivo((string)($pre['categoria'] ?? ''));
    $fechaPre = fmt_fecha_archivo($pre['fecha_inicio'] ?? '');

    $page_title = "Torneo-" . ($catPre !== '' ? $catPre : 'SIN-CATEGORIA') . "-" . $fechaPre;
  } catch (Exception $e) {
    $page_title = "Torneo-SIN-CATEGORIA-" . date('d-m-Y');
  }
}

/* Ahora sí imprimimos el HTML del head (title incluido) */
require_once __DIR__ . '/_header.php';

/**
 * print=1 = "MODO IMPRESIÓN (visual)".
 * NO dispara la impresora automáticamente.
 * El botón "IMPRIMIR" ejecuta window.print().
 */
$printMode = (isset($_GET['print']) && (string)$_GET['print'] === '1');

/* ========= Helpers DB ========= */
function column_exists(PDO $pdo, string $table, string $col): bool {
  try {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { return false; }
}
function normalize_estado(string $s): string {
  $e = mb_strtolower(trim($s));
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
  if (!$enum_vals) return $target_norm; // varchar
  $map = [];
  foreach ($enum_vals as $v) $map[normalize_estado($v)] = $v;

  if (isset($map[$target_norm])) return $map[$target_norm];

  if ($target_norm === 'finalizado') {
    foreach ($enum_vals as $v) {
      $nv = normalize_estado($v);
      if ($nv === 'finalizado' || str_contains($nv, 'final')) return $v;
    }
    return $enum_vals[0];
  }
  if ($target_norm === 'en curso') {
    foreach ($enum_vals as $v) {
      $nv = normalize_estado($v);
      if ($nv === 'en curso' || str_contains($nv, 'curso')) return $v;
    }
    return $enum_vals[0];
  }
  foreach ($enum_vals as $v) {
    $nv = normalize_estado($v);
    if ($nv === 'abierto' || str_contains($nv, 'abiert') || $nv === 'open') return $v;
  }
  return $enum_vals[0];
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

  try {
    if (!column_exists($pdo, 'torneo_zonas', 'tamanio_objetivo')) {
      $pdo->exec("ALTER TABLE torneo_zonas ADD COLUMN tamanio_objetivo INT NOT NULL DEFAULT 3");
    }
  } catch (Exception $e) {}
}

ensure_zonas_tables($pdo);
$HAS_TAM_OBJ = column_exists($pdo, 'torneo_zonas', 'tamanio_objetivo');

/* ========= Helpers UI / ranking ========= */
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

/* ========= Lógica Zonas ========= */
function zonaCodigo(int $i): string {
  $s = '';
  $i += 1;
  while ($i > 0) {
    $mod = ($i - 1) % 26;
    $s = chr(65 + $mod) . $s;
    $i = intdiv($i - 1, 26);
  }
  return $s;
}
/** zonas = floor(n/3), resto 0..2 => 0..2 zonas de 4 al final */
function sizes_por_equipos(int $n): array {
  if ($n < 6) return [];
  $z = intdiv($n, 3);
  $r = $n - (3 * $z);
  $cant4 = $r;
  $cant3 = $z - $cant4;
  if ($cant3 < 0) return [];
  $sizes = [];
  for ($i=0; $i<$cant3; $i++) $sizes[] = 3;
  for ($i=0; $i<$cant4; $i++) $sizes[] = 4;
  return $sizes;
}
function compactar_posiciones(PDO $pdo, int $zona_id): void {
  $st = $pdo->prepare("SELECT id FROM torneo_zona_equipos WHERE zona_id=? ORDER BY posicion ASC, id ASC");
  $st->execute([$zona_id]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  $pos = 1;
  $up = $pdo->prepare("UPDATE torneo_zona_equipos SET posicion=? WHERE id=?");
  foreach ($rows as $r) {
    $up->execute([$pos, (int)$r['id']]);
    $pos++;
  }
}
function ensure_zona_libre(PDO $pdo, int $torneo_id, bool $hasTamObj, int $capacidad): int {
  $st = $pdo->prepare("SELECT id FROM torneo_zonas WHERE torneo_id=? AND codigo='LIBRE' LIMIT 1");
  $st->execute([$torneo_id]);
  $id = $st->fetchColumn();
  if ($id) {
    if ($hasTamObj) {
      $up = $pdo->prepare("UPDATE torneo_zonas SET tamanio_objetivo=? WHERE id=? LIMIT 1");
      $up->execute([$capacidad, (int)$id]);
    }
    return (int)$id;
  }

  $stO = $pdo->prepare("SELECT COALESCE(MAX(orden),0) FROM torneo_zonas WHERE torneo_id=?");
  $stO->execute([$torneo_id]);
  $nuevoOrden = (int)$stO->fetchColumn() + 1;

  if ($hasTamObj) {
    $ins = $pdo->prepare("INSERT INTO torneo_zonas (torneo_id, codigo, orden, tamanio_objetivo) VALUES (?,?,?,?)");
    $ins->execute([$torneo_id, 'LIBRE', $nuevoOrden, $capacidad]);
  } else {
    $ins = $pdo->prepare("INSERT INTO torneo_zonas (torneo_id, codigo, orden) VALUES (?,?,?)");
    $ins->execute([$torneo_id, 'LIBRE', $nuevoOrden]);
  }
  return (int)$pdo->lastInsertId();
}
function refrescar_orden(PDO $pdo, int $torneo_id, array $codigosEsperados): void {
  $st = $pdo->prepare("SELECT id, codigo, orden FROM torneo_zonas WHERE torneo_id=? ORDER BY orden ASC, id ASC");
  $st->execute([$torneo_id]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $map = [];
  $otros = [];
  $libre = null;

  foreach ($rows as $r) {
    $cod = (string)$r['codigo'];
    if ($cod === 'LIBRE') { $libre = $r; continue; }
    $map[$cod] = $r;
    $otros[] = $r;
  }

  $otrosNoEsperados = [];
  $setEsperados = array_flip($codigosEsperados);
  foreach ($otros as $r) {
    $cod = (string)$r['codigo'];
    if (!isset($setEsperados[$cod])) $otrosNoEsperados[] = $r;
  }

  $ord = 1;
  $up = $pdo->prepare("UPDATE torneo_zonas SET orden=? WHERE id=?");

  foreach ($codigosEsperados as $cod) {
    if (isset($map[$cod])) {
      $up->execute([$ord, (int)$map[$cod]['id']]);
      $ord++;
    }
  }
  foreach ($otrosNoEsperados as $r) {
    $up->execute([$ord, (int)$r['id']]);
    $ord++;
  }
  if ($libre) $up->execute([$ord, (int)$libre['id']]);
}
function zona_overflow_msg(string $codigo, int $actual, int $objetivo): string {
  if ($codigo === 'LIBRE') return '';
  if ($actual <= $objetivo) return '';
  return "⚠️ Hay ".($actual-$objetivo)." equipo(s) de más (objetivo $objetivo).";
}
function fmt_fecha(string $s): string {
  $s = trim($s);
  if ($s === '') return '';
  $ts = strtotime($s);
  if ($ts === false) return $s;
  return date('d/m/Y', $ts);
}

/* ========= Torneo ========= */
$cols = ['id','nombre','categoria'];
if (column_exists($pdo,'torneos','sede')) $cols[] = 'sede';
if (column_exists($pdo,'torneos','fecha_inicio')) $cols[] = 'fecha_inicio';
if (column_exists($pdo,'torneos','estado')) $cols[] = 'estado';

$stmt = $pdo->prepare("SELECT ".implode(',', $cols)." FROM torneos WHERE id = ? LIMIT 1");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$torneo) {
  echo "<div class='card'><p>No se encontró el torneo.</p></div>";
  require_once __DIR__ . '/_footer.php';
  ob_end_flush();
  exit;
}

$estado = trim((string)($torneo['estado'] ?? ''));
$is_finalizado = (normalize_estado($estado) === 'finalizado');

$cat_torneo = trim((string)($torneo['categoria'] ?? ''));
$nombreComplejo = trim((string)($torneo['sede'] ?? ''));
$fechaTorneo = fmt_fecha((string)($torneo['fecha_inicio'] ?? ''));
$hasFoto = column_exists($pdo, 'jugadores', 'foto');

if ($printMode) {
  $catRaw = trim((string)($torneo['categoria'] ?? $cat_torneo ?? ''));
  $catNorm = $catRaw;
  if (function_exists('iconv')) {
    $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $catNorm);
    if ($tmp !== false && $tmp !== '') $catNorm = $tmp;
  }
  $catNorm = preg_replace('/[^A-Za-z0-9 ]+/', '', $catNorm);
  $catNorm = trim(preg_replace('/\s+/', ' ', $catNorm));

  $fi = trim((string)($torneo['fecha_inicio'] ?? ''));
  $fechaFile = ($fi !== '' && strtotime($fi) !== false) ? date('d-m-Y', strtotime($fi)) : date('d-m-Y');
  $page_title = "Torneo-" . ($catNorm !== '' ? $catNorm : 'SIN-CATEGORIA') . "-" . $fechaFile;
}

/* logo */
$apibaLogo = "/apiba-padel/assets/apiba_logo.png";

/* total equipos */
$stCnt = $pdo->prepare("SELECT COUNT(*) FROM torneo_equipos WHERE torneo_id=?");
$stCnt->execute([$torneo_id]);
$totalEquipos = (int)$stCnt->fetchColumn();

$sizesNow = sizes_por_equipos($totalEquipos);
$codigosEsperados = [];
for ($i=0; $i<count($sizesNow); $i++) $codigosEsperados[] = zonaCodigo($i);

/* asegurar libre y orden (solo si NO print y NO finalizado) */
if (!$printMode && !$is_finalizado) {
  ensure_zona_libre($pdo, $torneo_id, $HAS_TAM_OBJ, max(1, $totalEquipos));
  refrescar_orden($pdo, $torneo_id, $codigosEsperados);
}

/* ========= AUTO-ASIGNACIÓN (solo si no hay equipos asignados aún) ========= */
if (!$printMode && !$is_finalizado && $totalEquipos >= 6 && count($sizesNow) > 0) {
  try {
    $stAsg = $pdo->prepare("
      SELECT COUNT(*)
      FROM torneo_zona_equipos tze
      JOIN torneo_zonas tz ON tz.id = tze.zona_id
      WHERE tz.torneo_id = ?
        AND tz.codigo <> 'LIBRE'
    ");
    $stAsg->execute([$torneo_id]);
    $yaAsignados = ((int)$stAsg->fetchColumn() > 0);

    if (!$yaAsignados) {
      $pdo->beginTransaction();

      for ($i=0; $i<count($sizesNow); $i++) {
        $cod = zonaCodigo($i);
        $orden = $i + 1;

        $stZ = $pdo->prepare("SELECT id FROM torneo_zonas WHERE torneo_id=? AND codigo=? LIMIT 1");
        $stZ->execute([$torneo_id, $cod]);
        $zonaId = (int)$stZ->fetchColumn();

        if ($zonaId <= 0) {
          if ($HAS_TAM_OBJ) {
            $insZ = $pdo->prepare("INSERT INTO torneo_zonas (torneo_id, codigo, orden, tamanio_objetivo) VALUES (?,?,?,?)");
            $insZ->execute([$torneo_id, $cod, $orden, (int)$sizesNow[$i]]);
          } else {
            $insZ = $pdo->prepare("INSERT INTO torneo_zonas (torneo_id, codigo, orden) VALUES (?,?,?)");
            $insZ->execute([$torneo_id, $cod, $orden]);
          }
        } else {
          if ($HAS_TAM_OBJ) {
            $upZ = $pdo->prepare("UPDATE torneo_zonas SET orden=?, tamanio_objetivo=? WHERE id=? LIMIT 1");
            $upZ->execute([$orden, (int)$sizesNow[$i], $zonaId]);
          } else {
            $upZ = $pdo->prepare("UPDATE torneo_zonas SET orden=? WHERE id=? LIMIT 1");
            $upZ->execute([$orden, $zonaId]);
          }
        }
      }

      refrescar_orden($pdo, $torneo_id, $codigosEsperados);

      $zonaIds = [];
      foreach ($codigosEsperados as $cod) {
        $stZ = $pdo->prepare("SELECT id FROM torneo_zonas WHERE torneo_id=? AND codigo=? LIMIT 1");
        $stZ->execute([$torneo_id, $cod]);
        $zonaIds[] = (int)$stZ->fetchColumn();
      }

      $stEq = $pdo->prepare("SELECT id FROM torneo_equipos WHERE torneo_id=? ORDER BY id ASC");
      $stEq->execute([$torneo_id]);
      $equipoIds = array_map('intval', $stEq->fetchAll(PDO::FETCH_COLUMN));

      $insTZE = $pdo->prepare("INSERT INTO torneo_zona_equipos (zona_id, equipo_id, posicion) VALUES (?,?,?)");

      $idx = 0;
      for ($z=0; $z<count($sizesNow); $z++) {
        $zona_id = (int)$zonaIds[$z];
        $cap = (int)$sizesNow[$z];

        for ($pos=1; $pos <= $cap; $pos++) {
          if (!isset($equipoIds[$idx])) break;
          $insTZE->execute([$zona_id, (int)$equipoIds[$idx], $pos]);
          $idx++;
        }
      }

      $pdo->commit();
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
  }
}

/* ========= Acciones editor ========= */
if (!$printMode && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';

  /* ✅ CERRAR TORNEO (reversible desde fixture.php con "ABRIR TORNEO") */
  if ($accion === 'cerrar_torneo') {
    if (!column_exists($pdo, 'torneos', 'estado')) {
      header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("No existe torneos.estado.")."#keep");
      exit;
    }
    $enum_vals = get_enum_values($pdo, 'torneos', 'estado');
    $saveValue = pick_save_value('finalizado', $enum_vals);

    try {
      $up = $pdo->prepare("UPDATE torneos SET estado = ? WHERE id = ? LIMIT 1");
      $up->execute([$saveValue, $torneo_id]);
    } catch (Exception $e) {}

    header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("Torneo cerrado. Ahora podés ARMAR FIXTURE (y reabrir desde FIXTURE si querés).")."#keep");
    exit;
  }

  if ($is_finalizado) {
    header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("Torneo finalizado: edición bloqueada.")."#keep");
    exit;
  }

  if ($accion === 'refrescar') {
    refrescar_orden($pdo, $torneo_id, $codigosEsperados);
    header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("Módulo refrescado (ordenado).")."#keep");
    exit;
  }

  if ($accion === 'crear_zona') {
    try {
      $stO = $pdo->prepare("SELECT COALESCE(MAX(orden),0) FROM torneo_zonas WHERE torneo_id=? AND codigo<>'LIBRE'");
      $stO->execute([$torneo_id]);
      $nuevoOrden = (int)$stO->fetchColumn() + 1;

      $i = 0;
      while (true) {
        $cod = zonaCodigo($i);
        $st = $pdo->prepare("SELECT 1 FROM torneo_zonas WHERE torneo_id=? AND codigo=? LIMIT 1");
        $st->execute([$torneo_id, $cod]);
        if (!$st->fetchColumn()) { $nuevoCod = $cod; break; }
        $i++;
        if ($i > 500) { $nuevoCod = 'ZZ'; break; }
      }

      if ($HAS_TAM_OBJ) {
        $ins = $pdo->prepare("INSERT INTO torneo_zonas (torneo_id, codigo, orden, tamanio_objetivo) VALUES (?,?,?,?)");
        $ins->execute([$torneo_id, $nuevoCod, $nuevoOrden, 3]);
      } else {
        $ins = $pdo->prepare("INSERT INTO torneo_zonas (torneo_id, codigo, orden) VALUES (?,?,?)");
        $ins->execute([$torneo_id, $nuevoCod, $nuevoOrden]);
      }

      refrescar_orden($pdo, $torneo_id, $codigosEsperados);
      header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("Zona $nuevoCod creada.")."#keep");
      exit;
    } catch (Exception $e) {
      header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("Error creando zona: ".$e->getMessage())."#keep");
      exit;
    }
  }

  if ($accion === 'limpiar_libre') {
    $to_zona_id = (int)($_POST['to_zona_id'] ?? 0);
    if ($to_zona_id > 0) {
      $pdo->beginTransaction();
      try {
        $stL = $pdo->prepare("SELECT id FROM torneo_zonas WHERE torneo_id=? AND codigo='LIBRE' LIMIT 1");
        $stL->execute([$torneo_id]);
        $libre_id = (int)$stL->fetchColumn();

        $stD = $pdo->prepare("SELECT id, codigo FROM torneo_zonas WHERE id=? AND torneo_id=? LIMIT 1");
        $stD->execute([$to_zona_id, $torneo_id]);
        $dest = $stD->fetch(PDO::FETCH_ASSOC);

        if ($dest && (string)$dest['codigo'] !== 'LIBRE') {
          $stMax = $pdo->prepare("SELECT COALESCE(MAX(posicion),0) FROM torneo_zona_equipos WHERE zona_id=?");
          $stMax->execute([$to_zona_id]);
          $pos = (int)$stMax->fetchColumn();

          $stEq = $pdo->prepare("SELECT id FROM torneo_zona_equipos WHERE zona_id=? ORDER BY posicion ASC, id ASC");
          $stEq->execute([$libre_id]);
          $rows = $stEq->fetchAll(PDO::FETCH_ASSOC);

          $up = $pdo->prepare("UPDATE torneo_zona_equipos SET zona_id=?, posicion=? WHERE id=?");
          foreach ($rows as $r) {
            $pos++;
            $up->execute([$to_zona_id, $pos, (int)$r['id']]);
          }

          compactar_posiciones($pdo, $to_zona_id);
          compactar_posiciones($pdo, $libre_id);
        }

        $pdo->commit();
      } catch (Exception $e) {
        $pdo->rollBack();
      }
    }
    header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("LIBRE limpiado.")."#keep");
    exit;
  }

  if ($accion === 'quitar_zona') {
    $zona_id = (int)($_POST['zona_id'] ?? 0);
    if ($zona_id > 0) {
      $stChk = $pdo->prepare("SELECT COUNT(*) FROM torneo_zona_equipos WHERE zona_id=?");
      $stChk->execute([$zona_id]);
      $cnt = (int)$stChk->fetchColumn();

      if ($cnt === 0) {
        $st = $pdo->prepare("SELECT codigo FROM torneo_zonas WHERE id=? AND torneo_id=? LIMIT 1");
        $st->execute([$zona_id, $torneo_id]);
        $cod = (string)$st->fetchColumn();

        if ($cod !== '' && $cod !== 'LIBRE') {
          $del = $pdo->prepare("DELETE FROM torneo_zonas WHERE id=? AND torneo_id=? LIMIT 1");
          $del->execute([$zona_id, $torneo_id]);
          refrescar_orden($pdo, $torneo_id, $codigosEsperados);
        }
      }
    }
    header("Location: generar_torneo.php?id=".$torneo_id."&msg=".urlencode("Zona eliminada (solo si estaba vacía).")."#keep");
    exit;
  }

  if ($accion === 'sacar') {
    $equipo_id = (int)($_POST['equipo_id'] ?? 0);
    if ($equipo_id > 0) {
      $pdo->beginTransaction();
      try {
        $stL = $pdo->prepare("SELECT id FROM torneo_zonas WHERE torneo_id=? AND codigo='LIBRE' LIMIT 1");
        $stL->execute([$torneo_id]);
        $libre_id = (int)$stL->fetchColumn();

        $st = $pdo->prepare("
          SELECT tze.id, tze.zona_id
          FROM torneo_zona_equipos tze
          JOIN torneo_zonas tz ON tz.id=tze.zona_id
          WHERE tze.equipo_id=? AND tz.torneo_id=?
          LIMIT 1
        ");
        $st->execute([$equipo_id, $torneo_id]);
        $cur = $st->fetch(PDO::FETCH_ASSOC);

        if ($cur) {
          $from_zona_id = (int)$cur['zona_id'];
          if ($from_zona_id !== $libre_id) {
            $stMax = $pdo->prepare("SELECT COALESCE(MAX(posicion),0) FROM torneo_zona_equipos WHERE zona_id=?");
            $stMax->execute([$libre_id]);
            $newPos = (int)$stMax->fetchColumn() + 1;

            $up = $pdo->prepare("UPDATE torneo_zona_equipos SET zona_id=?, posicion=? WHERE id=? LIMIT 1");
            $up->execute([$libre_id, $newPos, (int)$cur['id']]);

            compactar_posiciones($pdo, $from_zona_id);
            compactar_posiciones($pdo, $libre_id);
          }
        }

        $pdo->commit();
      } catch (Exception $e) {
        $pdo->rollBack();
      }
    }
    header("Location: generar_torneo.php?id=".$torneo_id."#keep");
    exit;
  }

  if ($accion === 'mover') {
    $equipo_id = (int)($_POST['equipo_id'] ?? 0);
    $to_zona_id = (int)($_POST['to_zona_id'] ?? 0);

    if ($equipo_id > 0 && $to_zona_id > 0) {
      $pdo->beginTransaction();
      try {
        $st = $pdo->prepare("
          SELECT tze.id, tze.zona_id
          FROM torneo_zona_equipos tze
          JOIN torneo_zonas tz ON tz.id = tze.zona_id
          WHERE tze.equipo_id = ? AND tz.torneo_id = ?
          LIMIT 1
        ");
        $st->execute([$equipo_id, $torneo_id]);
        $cur = $st->fetch(PDO::FETCH_ASSOC);

        if ($cur) {
          $from_zona_id = (int)$cur['zona_id'];

          $stMax = $pdo->prepare("SELECT COALESCE(MAX(posicion),0) FROM torneo_zona_equipos WHERE zona_id=?");
          $stMax->execute([$to_zona_id]);
          $newPos = (int)$stMax->fetchColumn() + 1;

          $up = $pdo->prepare("UPDATE torneo_zona_equipos SET zona_id=?, posicion=? WHERE id=? LIMIT 1");
          $up->execute([$to_zona_id, $newPos, (int)$cur['id']]);

          compactar_posiciones($pdo, $from_zona_id);
          compactar_posiciones($pdo, $to_zona_id);
        }

        $pdo->commit();
      } catch (Exception $e) {
        $pdo->rollBack();
      }
    }

    header("Location: generar_torneo.php?id=".$torneo_id."#keep");
    exit;
  }

  if ($accion === 'up' || $accion === 'down') {
    $zona_id = (int)($_POST['zona_id'] ?? 0);
    $equipo_id = (int)($_POST['equipo_id'] ?? 0);

    if ($zona_id > 0 && $equipo_id > 0) {
      $pdo->beginTransaction();
      try {
        $st = $pdo->prepare("SELECT id, posicion FROM torneo_zona_equipos WHERE zona_id=? AND equipo_id=? LIMIT 1");
        $st->execute([$zona_id, $equipo_id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
          $idA  = (int)$row['id'];
          $posA = (int)$row['posicion'];

          $swapPos = ($accion === 'up') ? ($posA - 1) : ($posA + 1);

          $stMax = $pdo->prepare("SELECT COALESCE(MAX(posicion),0) FROM torneo_zona_equipos WHERE zona_id=?");
          $stMax->execute([$zona_id]);
          $maxPos = (int)$stMax->fetchColumn();

          if ($swapPos >= 1 && $swapPos <= $maxPos) {
            $st2 = $pdo->prepare("SELECT id FROM torneo_zona_equipos WHERE zona_id=? AND posicion=? LIMIT 1");
            $st2->execute([$zona_id, $swapPos]);
            $idB = (int)$st2->fetchColumn();

            if ($idB > 0) {
              $u = $pdo->prepare("UPDATE torneo_zona_equipos SET posicion=? WHERE id=?");
              $tmp = 0;
              $u->execute([$tmp, $idA]);
              $u->execute([$posA, $idB]);
              $u->execute([$swapPos, $idA]);
            }
          }
        }

        $pdo->commit();
      } catch (Exception $e) {
        $pdo->rollBack();
      }
    }
    header("Location: generar_torneo.php?id=".$torneo_id."#keep");
    exit;
  }
}

/* ========= Cargar zonas + equipos ========= */
$zonas = [];
$stZ = $pdo->prepare("SELECT * FROM torneo_zonas WHERE torneo_id=? ORDER BY orden ASC, id ASC");
$stZ->execute([$torneo_id]);
$zonas = $stZ->fetchAll(PDO::FETCH_ASSOC);

$objetivoPorCodigo = [];
for ($i=0; $i<count($sizesNow); $i++) $objetivoPorCodigo[zonaCodigo($i)] = (int)$sizesNow[$i];

$zonaEquipos = [];
if ($zonas) {
  $ids = array_map(fn($z)=> (int)$z['id'], $zonas);
  $in = implode(',', array_fill(0, count($ids), '?'));

  $stE = $pdo->prepare("
    SELECT
      tze.zona_id, tze.posicion,
      e.id AS equipo_id,
      e.jugador1_id, e.jugador2_id,
      e.categoria_j1, e.categoria_j2,
      j1.apellido AS j1_apellido, j1.nombre AS j1_nombre, ".(column_exists($pdo,'jugadores','foto') ? "j1.foto" : "''")." AS j1_foto,
      j2.apellido AS j2_apellido, j2.nombre AS j2_nombre, ".(column_exists($pdo,'jugadores','foto') ? "j2.foto" : "''")." AS j2_foto
    FROM torneo_zona_equipos tze
    JOIN torneo_equipos e ON e.id = tze.equipo_id
    JOIN jugadores j1 ON j1.id = e.jugador1_id
    JOIN jugadores j2 ON j2.id = e.jugador2_id
    WHERE tze.zona_id IN ($in)
    ORDER BY tze.zona_id ASC, tze.posicion ASC
  ");
  $stE->execute($ids);
  $rows = $stE->fetchAll(PDO::FETCH_ASSOC);

  foreach ($rows as $r) {
    $cat1 = trim((string)($r['categoria_j1'] ?? ''));
    $cat2 = trim((string)($r['categoria_j2'] ?? ''));
    if ($cat1==='') $cat1 = $cat_torneo;
    if ($cat2==='') $cat2 = $cat_torneo;

    $rk1 = puntos_ranking($pdo, (int)$r['jugador1_id'], $cat1);
    $rk2 = puntos_ranking($pdo, (int)$r['jugador2_id'], $cat2);

    $r['_total'] = $rk1 + $rk2;

    $zona_id = (int)$r['zona_id'];
    if (!isset($zonaEquipos[$zona_id])) $zonaEquipos[$zona_id] = [];
    $zonaEquipos[$zona_id][] = $r;
  }
}

$libreId = null;
foreach ($zonas as $z) { if ((string)$z['codigo'] === 'LIBRE') { $libreId = (int)$z['id']; break; } }
$libreCount = ($libreId && isset($zonaEquipos[$libreId])) ? count($zonaEquipos[$libreId]) : 0;

/* ======= Título impresión ======= */
$tituloPrint = "CIRCUITO APIBA 2026";
if ($cat_torneo !== '') $tituloPrint .= " - ".$cat_torneo;
if ($nombreComplejo !== '') $tituloPrint .= " - ".$nombreComplejo;
if ($fechaTorneo !== '') $tituloPrint .= " - ".$fechaTorneo;
?>
<style>
#keep{ position:relative; top:-12px; }
.card{ overflow-x:hidden; }

/* ✅ SIEMPRE 2 columnas */
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
.bad{ color:#ffd2d2; font-size:12px; font-weight:800; }

.teamline{
  display:flex;
  gap:8px;
  align-items:flex-start;
  justify-content:space-between;
  padding:8px;
  border-radius:12px;
  background: rgba(0,0,0,.14);
  border: 1px solid rgba(255,255,255,.06);
  margin-bottom:8px;
  min-width:0;
}
.tleft{ display:flex; gap:8px; align-items:center; min-width:0; }
.avas{ display:flex; gap:6px; flex:0 0 auto; }
.ava{ width:30px;height:30px;border-radius:999px;overflow:hidden;background:rgba(255,255,255,.10); }
.ava img{ width:100%;height:100%;object-fit:cover; }

.tnames{ min-width:0; max-width: 260px; }
.tnames .n, .tnames .n2{
  font-weight:900;
  font-size:12px;
  line-height:1.15;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.tnames .p{ font-size:11px; opacity:.85; }

.actions{
  display:flex;
  align-items:center;
  gap:4px;
  flex-wrap:nowrap;
  flex:0 0 auto;
  justify-content:flex-end;
}
select{
  padding:4px 6px;
  border-radius:9px;
  border:1px solid rgba(255,255,255,.12);
  background: rgba(0,0,0,.25);
  color: inherit;
  font-size:11px;
  width: 74px;
}
.btn-mini{
  padding:5px 7px !important;
  border-radius:10px !important;
  font-size:11px !important;
  line-height:1 !important;
  white-space:nowrap;
}
.btn:disabled{ opacity:.45; cursor:not-allowed; }

/* ======= IMPRESIÓN A4 ======= */
@page { size: A4; margin: 10mm; }

.print-header, .print-footer{ display:none; }
.zone-logo{ display:none; width:18px; height:18px; object-fit:contain; opacity:.95; }

@media print{
  body, html{ background:#fff !important; color:#000 !important; }
  .top, .nav, .actions, .brand, .pill, .wrap > hr{ display:none !important; }
  .wrap{ max-width:none !important; margin:0 !important; padding:0 !important; background:transparent !important; box-shadow:none !important; border-radius:0 !important; }
  .card{ background:transparent !important; border:none !important; box-shadow:none !important; padding:0 !important; }
  .no-print{ display:none !important; }

  .print-header{
    display:block !important;
    position: fixed;
    top:0; left:0; right:0;
    padding-bottom:4mm;
    border-bottom:1px solid rgba(0,0,0,.25);
    background:#fff;
  }
  .print-toprow{ display:flex; justify-content:space-between; align-items:center; }
  .print-title{ font-size:13pt !important; font-weight:900 !important; }
  .print-logo{ width:36px !important; height:36px !important; object-fit:contain; }
  .print-spacer-top{ height:22mm !important; }

  .print-footer{
    display:flex !important;
    position:fixed;
    bottom:0; left:0; right:0;
    padding-top:3mm;
    border-top:1px solid rgba(0,0,0,.25);
    background:#fff;
    font-size:9pt;
    justify-content:space-between;
    align-items:center;
  }
  .print-spacer-bottom{ height:14mm !important; }

  .zwrap{ display:grid !important; grid-template-columns: repeat(2, 1fr) !important; gap:6mm !important; }
  .zcard{
    background: linear-gradient(180deg, rgba(80,130,255,.16), rgba(80,130,255,.08)) !important;
    border: 1px solid rgba(80,130,255,.28) !important;
    border-radius:14px !important;
    padding:10px !important;
    box-shadow:none !important;
    break-inside: avoid;
    page-break-inside: avoid;
  }
  .zmeta, .bad{ display:none !important; }
  .ztitle{ font-size:12pt !important; font-weight:900 !important; }

  .teamline{
    background: rgba(0,0,0,.10) !important;
    border:1px solid rgba(255,255,255,.25) !important;
    border-radius:12px !important;
    padding:6px !important;
    margin-bottom:6px !important;
  }
  .avas{ display:flex !important; }
  .ava{ width:26px !important; height:26px !important; }
  .tnames .n, .tnames .n2{ font-size:9.5pt !important; font-weight:900 !important; }
  .tnames .p{ font-size:8.5pt !important; }
  html, body{ font-size:10pt !important; }

  body > div.wrap > div:last-child{ display:none !important; }
  .wrap a{ display:none !important; }
}

.wa{ display:inline-flex; align-items:center; gap:6px; }
.wa svg{ width:14px; height:14px; }
</style>

<a id="keep"></a>

<?php if ($printMode): ?>
  <div class="print-header">
    <div class="print-toprow">
      <div>
        <div class="print-title"><?= htmlspecialchars($tituloPrint) ?></div>
        <div class="print-sub">Zonas del torneo (listo para imprimir)</div>
      </div>
      <img class="print-logo" src="<?= htmlspecialchars($apibaLogo) ?>" alt="APiBA" onerror="this.style.display='none'">
    </div>
  </div>
  <div class="print-spacer-top"></div>
<?php endif; ?>

<div class="card">
  <div class="no-print" style="display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;"><?= $printMode ? 'Zonas (modo impresión)' : 'Zonas' ?></h2>
      <div style="color:var(--muted); margin-top:4px;">
        Torneo: <b><?= htmlspecialchars($torneo['nombre'] ?? ('#'.$torneo_id)) ?></b> —
        Categoría: <b><?= htmlspecialchars($torneo['categoria'] ?? '') ?></b>
        <?php if ($estado !== ''): ?>
          — Estado: <b><?= htmlspecialchars($estado) ?></b>
        <?php endif; ?>
      </div>
      <div class="smallmuted" style="margin-top:6px;">
        Total equipos: <b><?= (int)$totalEquipos ?></b> — Zonas esperadas: <b><?= (int)count($sizesNow) ?></b>
      </div>

      <?php if ($is_finalizado): ?>
        <div style="margin-top:8px; font-size:12px; font-weight:800; color:#ffd2d2;">
          ⚠️ Torneo finalizado: edición bloqueada. Podés imprimir.
        </div>
      <?php endif; ?>
    </div>

    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <a class="btn btn-secondary" href="equipos_unidos.php?id=<?= (int)$torneo_id ?>">⬅ Volver a equipos</a>

      <?php if ($printMode): ?>
        <button class="btn btn-ok" type="button" onclick="window.print();">🖨️ IMPRIMIR</button>
        <?php if (!$is_finalizado): ?>
          <a class="btn btn-secondary" href="generar_torneo.php?id=<?= (int)$torneo_id ?>">Volver a edición</a>
        <?php endif; ?>
      <?php else: ?>
        <a class="btn btn-secondary" href="generar_torneo.php?id=<?= (int)$torneo_id ?>&print=1">🖨️ MODO IMPRESIÓN</a>

        <?php if (!$is_finalizado): ?>
          <form method="post" style="margin:0;">
            <input type="hidden" name="accion" value="refrescar">
            <button class="btn btn-ok" type="submit">REFRESCAR MÓDULO</button>
          </form>

          <?php if ($libreCount > 0): ?>
            <form method="post" style="margin:0; display:flex; gap:6px; align-items:center;">
              <input type="hidden" name="accion" value="limpiar_libre">
              <span class="smallmuted">LIBRE: <b><?= (int)$libreCount ?></b></span>
              <select name="to_zona_id" required>
                <?php foreach ($zonas as $z): ?>
                  <?php if ((string)$z['codigo'] === 'LIBRE') continue; ?>
                  <option value="<?= (int)$z['id'] ?>">Zona <?= htmlspecialchars((string)$z['codigo']) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-ok" type="submit" onclick="return confirm('Mueve TODOS los equipos de LIBRE a la zona elegida. ¿Seguro?');">LIMPIAR LIBRE</button>
            </form>
          <?php endif; ?>

          <form method="post" style="margin:0;">
            <input type="hidden" name="accion" value="crear_zona">
            <button class="btn btn-ok" type="submit">CREAR ZONA</button>
          </form>

          <form method="post" style="margin:0;">
            <input type="hidden" name="accion" value="cerrar_torneo">
            <button class="btn btn-danger" type="submit"
                    onclick="return confirm('¿Seguro que querés CERRAR el torneo? Luego lo podés reabrir desde el FIXTURE.');">
              CERRAR TORNEO
            </button>
          </form>

        <?php else: ?>
          <a class="btn btn-ok" href="fixture.php?torneo=<?= (int)$torneo_id ?>">ARMAR FIXTURE</a>

          <button class="btn btn-ok" type="button" disabled>REFRESCAR MÓDULO</button>
          <button class="btn btn-ok" type="button" disabled>CREAR ZONA</button>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success no-print" style="margin-top:12px;"><?= htmlspecialchars((string)$_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if (!$zonas): ?>
    <p style="margin-top:12px; color:var(--muted);">Todavía no hay zonas.</p>
  <?php else: ?>

    <div class="zwrap">
      <?php foreach ($zonas as $z): ?>
        <?php
          $zona_id = (int)$z['id'];
          $codigo  = (string)$z['codigo'];
          if ($printMode && $codigo === 'LIBRE') continue;

          $lista  = $zonaEquipos[$zona_id] ?? [];
          $actual = count($lista);

          $obj = 3;
          if ($codigo === 'LIBRE') $obj = max(1, $totalEquipos);
          elseif (isset($objetivoPorCodigo[$codigo])) $obj = (int)$objetivoPorCodigo[$codigo];
          elseif (isset($z['tamanio_objetivo']) && $z['tamanio_objetivo'] !== null && $z['tamanio_objetivo'] !== '') $obj = (int)$z['tamanio_objetivo'];

          $warn = zona_overflow_msg($codigo, $actual, $obj);
          $tituloZona = ($codigo === 'LIBRE') ? 'ZONA LIBRE (manual)' : ('Zona '.$codigo);
          $puedeQuitar = (!$printMode && !$is_finalizado && $codigo !== 'LIBRE' && $actual === 0);
        ?>

        <div class="zcard">
          <div class="zhead">
            <div style="min-width:0;">
              <div class="ztitle"><?= htmlspecialchars($tituloZona) ?></div>
              <div class="zmeta">Objetivo: <b><?= (int)$obj ?></b> — Actual: <b><?= (int)$actual ?></b></div>
              <?php if ($warn !== ''): ?><div class="bad"><?= htmlspecialchars($warn) ?></div><?php endif; ?>
            </div>

            <img class="zone-logo" src="<?= htmlspecialchars($apibaLogo) ?>" alt="APiBA" onerror="this.style.display='none'">

            <?php if (!$printMode && !$is_finalizado && $codigo !== 'LIBRE'): ?>
              <form method="post" class="no-print" style="margin:0;">
                <input type="hidden" name="accion" value="quitar_zona">
                <input type="hidden" name="zona_id" value="<?= $zona_id ?>">
                <button class="btn btn-danger btn-mini" type="submit" <?= $puedeQuitar ? '' : 'disabled' ?>
                        title="<?= $puedeQuitar ? '' : 'Solo se puede quitar si la zona está vacía' ?>">
                  QUITAR
                </button>
              </form>
            <?php endif; ?>
          </div>

          <?php if (!$lista): ?>
            <div class="smallmuted">Zona vacía.</div>
          <?php else: ?>
            <?php foreach ($lista as $r): ?>
              <?php
                $src1 = foto_src($r['j1_foto'] ?? '');
                $src2 = foto_src($r['j2_foto'] ?? '');
                $n1 = trim((string)$r['j1_nombre']).' '.trim((string)$r['j1_apellido']);
                $n2 = trim((string)$r['j2_nombre']).' '.trim((string)$r['j2_apellido']);
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

                <?php if (!$printMode && !$is_finalizado): ?>
                  <div class="actions no-print">
                    <form method="post" style="margin:0;">
                      <input type="hidden" name="accion" value="up">
                      <input type="hidden" name="zona_id" value="<?= $zona_id ?>">
                      <input type="hidden" name="equipo_id" value="<?= (int)$r['equipo_id'] ?>">
                      <button class="btn btn-mini btn-ok" type="submit">↑</button>
                    </form>

                    <form method="post" style="margin:0;">
                      <input type="hidden" name="accion" value="down">
                      <input type="hidden" name="zona_id" value="<?= $zona_id ?>">
                      <input type="hidden" name="equipo_id" value="<?= (int)$r['equipo_id'] ?>">
                      <button class="btn btn-mini btn-ok" type="submit">↓</button>
                    </form>

                    <?php if ($codigo !== 'LIBRE'): ?>
                      <form method="post" style="margin:0;">
                        <input type="hidden" name="accion" value="sacar">
                        <input type="hidden" name="equipo_id" value="<?= (int)$r['equipo_id'] ?>">
                        <button class="btn btn-mini btn-danger" type="submit">SACAR</button>
                      </form>
                    <?php endif; ?>

                    <form method="post" style="margin:0; display:flex; gap:4px; align-items:center;">
                      <input type="hidden" name="accion" value="mover">
                      <input type="hidden" name="equipo_id" value="<?= (int)$r['equipo_id'] ?>">
                      <select name="to_zona_id">
                        <?php foreach ($zonas as $z2): ?>
                          <?php
                            if ($printMode && (string)$z2['codigo']==='LIBRE') continue;
                            $label = ((string)$z2['codigo']==='LIBRE') ? 'LIBRE' : (string)$z2['codigo'];
                          ?>
                          <option value="<?= (int)$z2['id'] ?>" <?= ((int)$z2['id']===$zona_id)?'selected':'' ?>>
                            <?= htmlspecialchars($label) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-mini btn-ok" type="submit">MOVER</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>
</div>

<?php if ($printMode): ?>
  <div class="print-spacer-bottom"></div>

  <div class="print-footer">
    <div>© Deltamax</div>
    <div class="wa">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path fill="currentColor" d="M12 2a9.7 9.7 0 0 0-9.7 9.7c0 1.7.45 3.35 1.3 4.8L2 22l5.7-1.5a9.7 9.7 0 0 0 4.3 1A9.7 9.7 0 0 0 21.7 12 9.7 9.7 0 0 0 12 2zm5.64 14.05c-.24.68-1.2 1.26-1.9 1.41-.48.1-1.1.18-3.18-.67-2.66-1.1-4.38-3.8-4.51-3.97-.13-.18-1.07-1.43-1.07-2.73 0-1.3.67-1.93.91-2.2.24-.28.53-.35.7-.35.18 0 .35 0 .5.01.16.01.38-.06.6.46.24.58.8 2.01.87 2.15.07.13.12.3.02.48-.1.18-.15.3-.3.46-.15.16-.31.36-.44.48-.15.15-.3.31-.13.6.18.28.8 1.32 1.71 2.14 1.18 1.05 2.17 1.38 2.45 1.54.28.15.45.13.62-.08.18-.2.71-.83.9-1.12.18-.28.37-.23.62-.14.24.1 1.55.73 1.82.86.27.14.45.2.52.31.07.12.07.68-.17 1.36z"/>
      </svg>
      <span>2923-647346</span>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>
<?php ob_end_flush(); ?>