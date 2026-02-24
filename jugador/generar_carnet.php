<?php
require_once __DIR__ . '/../includes/site_header.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

function generarQRpng($data, $saveFullPath) {
    $url = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&format=png&data=" . urlencode($data);
    $img = @file_get_contents($url);
    if ($img === false) {
        throw new Exception("No se pudo generar el QR (bloqueo de internet o allow_url_fopen).");
    }
    if (@file_put_contents($saveFullPath, $img) === false) {
        throw new Exception("No se pudo guardar el QR en uploads/qr/");
    }
}

$jid = (int)$_SESSION['jugador']['id'];

// Si ya tiene carnet, ir a verlo
$stmt = $pdo->prepare("SELECT id FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$jid]);
if ($stmt->fetch()) {
  header("Location: carnet.php");
  exit;
}

// Crear número de carnet
$numero = "APIBA-" . str_pad((string)$jid, 6, "0", STR_PAD_LEFT);

// URL QR
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . "/apiba-padel";
$qrData  = $baseUrl . "/jugador/ver_publico.php?id=" . $jid;

// Guardar QR en /uploads/qr/
$qrDir = $_SERVER['DOCUMENT_ROOT'] . "/apiba-padel/uploads/qr";
if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

$qrFile = "qr_" . $jid . "_" . time() . ".png";
$qrFull = $qrDir . "/" . $qrFile;

// Generar PNG QR
try {
    generarQRpng($qrData, $qrFull);

    // Insertar carnet
    $stmt = $pdo->prepare("INSERT INTO carnets (jugador_id, numero_carnet, qr_code) VALUES (?,?,?)");
    $stmt->execute([$jid, $numero, $qrFile]);

    echo "<h2>✅ Carnet + QR generado correctamente</h2>";
    echo '<p><a href="carnet.php">Ver carnet</a></p>';
    echo '<p><a href="perfil.php">Volver al perfil</a></p>';

} catch (Exception $e) {
    echo "<h2>❌ " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo '<p class="muted">Si tu hosting bloquea esto, te paso el generador QR offline (sin internet).</p>';
}

require_once __DIR__ . '/../includes/site_footer.php';
