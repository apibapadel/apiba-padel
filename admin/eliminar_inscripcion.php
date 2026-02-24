<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

// Acepta GET o POST
$jugador_id = (int)($_GET['jugador_id'] ?? $_POST['jugador_id'] ?? 0);
$torneo_id  = (int)($_GET['torneo_id']  ?? $_POST['torneo_id']  ?? 0);

if ($jugador_id <= 0 || $torneo_id <= 0) die("Datos inválidos");

$stmt = $pdo->prepare("DELETE FROM inscripciones WHERE jugador_id = ? AND torneo_id = ? LIMIT 1");
$stmt->execute([$jugador_id, $torneo_id]);

header("Location: inscriptos.php?id=".$torneo_id."&msg=".urlencode("Inscripción eliminada correctamente."));
exit;
