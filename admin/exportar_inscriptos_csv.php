<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

$torneo_id = (int)($_GET['id'] ?? 0);
if ($torneo_id <= 0) die("Torneo inválido");

// Torneo
$stmt = $pdo->prepare("SELECT * FROM torneos WHERE id=?");
$stmt->execute([$torneo_id]);
$torneo = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$torneo) die("Torneo no encontrado");

// Detectar columna de fecha en inscripciones: created_at o fecha
$colFecha = null;
$cols = $pdo->query("SHOW COLUMNS FROM inscripciones")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if ($c['Field'] === 'created_at') $colFecha = 'created_at';
    if ($c['Field'] === 'fecha') $colFecha = 'fecha';
}
if (!$colFecha) $colFecha = 'id';

$sql = "
  SELECT i.$colFecha AS fecha_inscripcion,
         j.id AS jugador_id, j.apellido, j.nombre, j.email, j.dni, j.telefono, j.categoria
  FROM inscripciones i
  JOIN jugadores j ON j.id = i.jugador_id
  WHERE i.torneo_id = ?
  ORDER BY i.$colFecha DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$torneo_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Descargar CSV
$filename = "inscriptos_torneo_" . $torneo_id . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$filename);

$out = fopen('php://output', 'w');
fputcsv($out, ['fecha_inscripcion','jugador_id','apellido','nombre','email','dni','telefono','categoria']);

foreach($rows as $r){
  fputcsv($out, [
    $r['fecha_inscripcion'] ?? '',
    $r['jugador_id'] ?? '',
    $r['apellido'] ?? '',
    $r['nombre'] ?? '',
    $r['email'] ?? '',
    $r['dni'] ?? '',
    $r['telefono'] ?? '',
    $r['categoria'] ?? '',
  ]);
}

fclose($out);
exit;
<p>
  <a href="index.php">⬅ Volver al panel</a> |
  <a href="/apiba-padel/index.php">Ir al sitio</a>
</p>
