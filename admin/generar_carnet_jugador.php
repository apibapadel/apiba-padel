<?php
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apiba-padel/config/database.php';
$pdo = getDB();

function generarQRpng($data, $saveFullPath) {
    // usa un generador externo y guarda el PNG local (simple y funciona en hosting común)
    $url = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&format=png&data=" . urlencode($data);

    $img = @file_get_contents($url);
    if ($img === false) {
        throw new Exception("No se pudo generar el QR (bloqueo de internet o allow_url_fopen).");
    }

    if (@file_put_contents($saveFullPath, $img) === false) {
        throw new Exception("No se pudo guardar el QR en uploads/qr/");
    }
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

// Verificar jugador
$stmt = $pdo->prepare("SELECT id, apellido, nombre FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$j) die("Jugador no encontrado");

// Verificar si ya tiene carnet
$stmt = $pdo->prepare("SELECT id FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$id]);
if ($stmt->fetch()) {
    header("Location: jugadores.php?msg=" . urlencode("El jugador ya tenía carnet generado."));
    exit;
}

// Crear numero carnet
$numero = "APIBA-" . str_pad((string)$id, 6, "0", STR_PAD_LEFT);

// URL para el QR (página pública con datos del jugador)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . "/apiba-padel";
$qrData  = $baseUrl . "/jugador/ver_publico.php?id=" . $id;

// Guardar QR en /uploads/qr/
$qrDir = $_SERVER['DOCUMENT_ROOT'] . "/apiba-padel/uploads/qr";
if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

$qrFile = "qr_" . $id . "_" . time() . ".png";
$qrFull = $qrDir . "/" . $qrFile;

// Generar PNG QR
generarQRpng($qrData, $qrFull);

// Insertar carnet con qr_code
$stmt = $pdo->prepare("INSERT INTO carnets (jugador_id, numero_carnet, qr_code) VALUES (?,?,?)");
$stmt->execute([$id, $numero, $qrFile]);

header("Location: jugadores.php?msg=" . urlencode("Carnet + QR generado para {$j['apellido']} {$j['nombre']} ({$numero})."));
exit;
