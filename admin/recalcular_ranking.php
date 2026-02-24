<?php
require_once __DIR__ . '/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

try {
  // Snapshot para flechas
  $pdo->exec("DELETE FROM ranking_prev");
  $pdo->exec("
    INSERT INTO ranking_prev (categoria, jugador_id, jugador, puntos, posicion)
    SELECT categoria, jugador_id, jugador, puntos, posicion
    FROM ranking
  ");

  // Recalcular puntos totales (opcional)
  $pdo->exec("
    UPDATE jugadores j
    LEFT JOIN (
      SELECT jugador_id, COALESCE(SUM(puntos),0) AS total
      FROM puntos_torneo
      GROUP BY jugador_id
    ) s ON s.jugador_id = j.id
    SET j.puntos = COALESCE(s.total, 0)
  ");

  // Rehacer ranking
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

  header("Location: /apiba-padel/admin/ranking.php?msg=recalc_ok");
  exit;

} catch (Exception $e) {
  die("Error al recalcular ranking: " . htmlspecialchars($e->getMessage()));
}
