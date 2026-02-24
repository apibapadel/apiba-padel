<?php
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$torneo_id = (int)($_POST['torneo_id'] ?? 0);
$fasePorJugador = $_POST['fase'] ?? [];

if ($torneo_id <= 0) die("Torneo inválido");

function fase_a_puntos(string $fase): int {
  $map = [
    'Campeon' => 100,
    'Finalista' => 90,
    'Semi (+)' => 80,
    'Semi (-)' => 75,
    'Cuartos' => 60,
    'Octavos' => 50,
    'Dieciseisavos' => 40,
    'Zona' => 35,
  ];
  return $map[$fase] ?? 0;
}

// Validar estado finalizado
$stmt = $pdo->prepare("SELECT estado FROM torneos WHERE id=? LIMIT 1");
$stmt->execute([$torneo_id]);
$tor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tor) die("Torneo no encontrado");

if (mb_strtolower(trim((string)$tor['estado'])) !== 'finalizado') {
  die("Solo se puede guardar puntos cuando el torneo está finalizado.");
}

try {
  /** 1) Guardar puntos por torneo/jugador (CON transacción) **/
  $pdo->beginTransaction();

  $up = $pdo->prepare("
    INSERT INTO puntos_torneo (torneo_id, jugador_id, fase, puntos)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE fase=VALUES(fase), puntos=VALUES(puntos)
  ");
  $del = $pdo->prepare("DELETE FROM puntos_torneo WHERE torneo_id=? AND jugador_id=?");

  foreach ($fasePorJugador as $jugador_id_str => $fase) {
    $jugador_id = (int)$jugador_id_str;
    $fase = trim((string)$fase);

    if ($jugador_id <= 0) continue;

    if ($fase === '') {
      $del->execute([$torneo_id, $jugador_id]);
      continue;
    }

    $puntos = fase_a_puntos($fase);
    $up->execute([$torneo_id, $jugador_id, $fase, $puntos]);
  }

  $pdo->commit();

  /** 2) Recalcular puntos acumulados globales por jugador **/
  $pdo->exec("
    UPDATE jugadores j
    LEFT JOIN (
      SELECT jugador_id, COALESCE(SUM(puntos),0) AS total
      FROM puntos_torneo
      GROUP BY jugador_id
    ) s ON s.jugador_id = j.id
    SET j.puntos = COALESCE(s.total, 0)
  ");

  /** 3) Snapshot para flechas (ranking_prev) **/
  // si la tabla no existe o no tiene la columna, te va a tirar error y lo vas a ver.
  $pdo->exec("DELETE FROM ranking_prev");
  $pdo->exec("
    INSERT INTO ranking_prev (categoria, jugador_id, jugador, puntos, posicion)
    SELECT categoria, jugador_id, jugador, puntos, posicion
    FROM ranking
  ");

  /** 4) Rehacer ranking por categoría del TORNEO **/
  $pdo->exec("DELETE FROM ranking");

  $rows = $pdo->query("
    SELECT
      t.categoria AS categoria_ranking,
      pt.jugador_id AS jugador_id,
      CONCAT(j.apellido,' ',j.nombre) AS jugador,
      COALESCE(SUM(pt.puntos),0) AS puntos
    FROM puntos_torneo pt
    INNER JOIN torneos t ON t.id = pt.torneo_id
    INNER JOIN jugadores j ON j.id = pt.jugador_id
    GROUP BY t.categoria, pt.jugador_id
    ORDER BY t.categoria ASC, puntos DESC, jugador ASC
  ")->fetchAll(PDO::FETCH_ASSOC);

  $ins = $pdo->prepare("
    INSERT INTO ranking (categoria, jugador_id, jugador, puntos, posicion)
    VALUES (?, ?, ?, ?, ?)
  ");

  $catActual = null;
  $pos = 0;

  foreach ($rows as $r) {
    $cat = trim((string)$r['categoria_ranking']);
    if ($cat === '') continue;

    if ($catActual !== $cat) {
      $catActual = $cat;
      $pos = 0;
    }
    $pos++;

    $ins->execute([
      $cat,
      (int)$r['jugador_id'],
      $r['jugador'],
      (int)$r['puntos'],
      $pos
    ]);
  }

  header("Location: /apiba-padel/admin/torneos.php?msg=puntos_ok");
  exit;

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();

  echo "<h2>⚠️ Error al guardar/recalcular ranking.</h2>";
  echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
  echo '<p><a href="/apiba-padel/admin/torneo_puntos.php?id=' . $torneo_id . '">⬅ Volver</a></p>';
  echo '<p><a href="/apiba-padel/admin/torneos.php">⬅ Volver a torneos</a></p>';
}

require_once __DIR__ . '/_footer.php';
