<?php
require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$torneo_id = (int)($_GET['id'] ?? 0);

if (!isset($_SESSION['jugador'])) {
  echo "<h2>⚠️ Tenés que iniciar sesión para inscribirte.</h2>";
  echo '<p><a href="/apiba-padel/login.php">Ir al login</a></p>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

if ($torneo_id <= 0) {
  echo "<h2>⚠️ Torneo inválido.</h2>";
  echo '<p><a href="/apiba-padel/torneos/index.php">⬅ Volver a torneos</a></p>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

$jugador_id = (int)$_SESSION['jugador']['id'];

/* =========================
   Helper: normalizar categoría
   ========================= */
function normalizar_categoria(string $s): string {
  $s = strtoupper(trim($s));
  $s = preg_replace('/\s+/', ' ', $s); // colapsa espacios múltiples
  return $s;
}

/* =========================
   1) Exigir carnet
   ========================= */
$stmt = $pdo->prepare("SELECT id FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$jugador_id]);
if (!$stmt->fetch()) {
  echo "<h2>⚠️ Tenés que generar tu carnet antes de inscribirte.</h2>";
  echo '<p><a href="/apiba-padel/jugador/generar_carnet.php">Generar carnet</a></p>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

/* =========================
   2) Traer categoría del jugador
   ========================= */
$stmt = $pdo->prepare("SELECT categoria FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$jugador_id]);
$jug = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jug) {
  echo "<h2>⚠️ Jugador no encontrado.</h2>";
  echo '<p><a href="/apiba-padel/torneos/index.php">⬅ Volver a torneos</a></p>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

$categoriaJugadorRaw = (string)$jug['categoria'];
$categoriaJugador    = normalizar_categoria($categoriaJugadorRaw);

/* =========================
   3) Traer categoría del torneo
   ========================= */
$stmt = $pdo->prepare("SELECT categoria, nombre FROM torneos WHERE id=? LIMIT 1");
$stmt->execute([$torneo_id]);
$tor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tor) {
  echo "<h2>⚠️ Torneo no encontrado.</h2>";
  echo '<p><a href="/apiba-padel/torneos/index.php">⬅ Volver a torneos</a></p>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

$categoriaTorneoRaw = (string)$tor['categoria'];
$categoriaTorneo    = normalizar_categoria($categoriaTorneoRaw);
$nombreTorneo       = trim((string)$tor['nombre']);

/* =========================
   4) VALIDACIÓN: categoría debe coincidir (normalizada)
   ========================= */
if ($categoriaJugador !== $categoriaTorneo) {
  echo "
    <h2>❌ NO CORRESPONDE A LA CATEGORÍA</h2>
    <p>
      Tu categoría: <strong>{$categoriaJugadorRaw}</strong><br>
      Torneo: <strong>{$nombreTorneo}</strong><br>
      Categoría del torneo: <strong>{$categoriaTorneoRaw}</strong>
    </p>
  ";
  echo '<p><a href="/apiba-padel/torneos/ver.php?id=' . $torneo_id . '">⬅ Volver al torneo</a></p>';
  require_once __DIR__ . '/../includes/site_footer.php';
  exit;
}

/* =========================
   5) Insertar inscripción (si corresponde)
   ========================= */
try {
  $stmt = $pdo->prepare("
    INSERT INTO inscripciones (torneo_id, jugador_id, categoria_anotada)
    VALUES (?,?,?)
  ");
  $stmt->execute([$torneo_id, $jugador_id, $categoriaTorneoRaw]);

  echo "<h2>✅ Inscripción realizada.</h2>";
} catch (Exception $e) {
  echo "<h2>⚠️ Ya estabas inscripto o hubo un error.</h2>";
}

echo '<p><a href="/apiba-padel/torneos/ver.php?id=' . $torneo_id . '">⬅ Volver al torneo</a></p>';

require_once __DIR__ . '/../includes/site_footer.php';
