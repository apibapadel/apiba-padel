<?php
// Descarga JPG carnet
// ✅ Jugador: descarga el suyo
// ✅ Admin: descarga cualquiera con ?id=ID_JUGADOR (tiene prioridad si viene ?id)

if (session_status() === PHP_SESSION_NONE) session_start();

$base = $_SERVER['DOCUMENT_ROOT'] . '/apiba-padel';
require_once $base . '/config/database.php';

function redirect_login() {
    if (!empty($_SESSION['admin'])) {
        header("Location: /apiba-padel/admin/login.php");
    } else {
        header("Location: /apiba-padel/login.php");
    }
    exit;
}

// ✅ Resolver jugador_id (PRIORIDAD ADMIN si viene ?id)
$jugador_id = 0;

if (!empty($_SESSION['admin']) && isset($_GET['id'])) {
    $jugador_id = (int)$_GET['id'];
} elseif (!empty($_SESSION['jugador'])) {
    $jugador_id = (int)($_SESSION['jugador']['id'] ?? 0);
} else {
    redirect_login();
}

if ($jugador_id <= 0) die("ID inválido");

$pdo = getDB();

// Jugador
$stmt = $pdo->prepare("SELECT id, apellido, nombre, dni, sexo, categoria, foto FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$jugador_id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$j) die("Jugador no encontrado");

// Carnet
$stmt = $pdo->prepare("SELECT id, numero_carnet, qr_code FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$jugador_id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) die("Este jugador aún no tiene carnet generado");

$fotoPath = $base . '/uploads/jugadores/' . ($j['foto'] ?? '');
$qrPath   = $base . '/uploads/qr/' . ($c['qr_code'] ?? '');

$W = 900; $H = 520;
$img = imagecreatetruecolor($W, $H);

$white = imagecolorallocate($img, 255,255,255);
$black = imagecolorallocate($img, 15,15,15);
$gray  = imagecolorallocate($img, 120,120,120);
$soft  = imagecolorallocate($img, 245,245,245);

imagefilledrectangle($img, 0, 0, $W, $H, $white);

$pad = 40;
imagefilledrectangle($img, $pad, $pad+70, $W-$pad, $H-$pad, $soft);
imagerectangle($img, $pad, $pad+70, $W-$pad, $H-$pad, $black);

// Texto con TTF (tildes OK)
$ttf = $base . '/assets/fonts/DejaVuSans.ttf';
$hasTtf = file_exists($ttf);

function drawText($img, $hasTtf, $ttf, $size, $x, $y, $color, $text) {
    if ($hasTtf) {
        imagettftext($img, $size, 0, $x, $y, $color, $ttf, $text);
    } else {
        imagestring($img, 5, $x, $y-15, $text, $color);
    }
}

drawText($img, $hasTtf, $ttf, 22, $pad, $pad+45, $black, "Carnet de Jugador APiBA");

// Foto
$fotoX = $pad + 20; $fotoY = $pad + 110; $fotoW = 170; $fotoH = 210;
imagerectangle($img, $fotoX, $fotoY, $fotoX+$fotoW, $fotoY+$fotoH, $black);

if (!empty($j['foto']) && file_exists($fotoPath)) {
    $ext = strtolower(pathinfo($fotoPath, PATHINFO_EXTENSION));
    $src = null;
    if ($ext === 'jpg' || $ext === 'jpeg') $src = @imagecreatefromjpeg($fotoPath);
    if ($ext === 'png') $src = @imagecreatefrompng($fotoPath);
    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($fotoPath);

    if ($src) {
        $sw = imagesx($src); $sh = imagesy($src);
        imagecopyresampled($img, $src, $fotoX, $fotoY, 0, 0, $fotoW, $fotoH, $sw, $sh);
        imagedestroy($src);
    } else {
        drawText($img, $hasTtf, $ttf, 14, $fotoX+20, $fotoY+110, $gray, "Sin foto");
    }
} else {
    drawText($img, $hasTtf, $ttf, 14, $fotoX+20, $fotoY+110, $gray, "Sin foto");
}

// Datos
$tx = $pad + 220; $ty = $pad + 140;
drawText($img, $hasTtf, $ttf, 24, $tx, $ty, $black, ($j['apellido'] ?? '') . " " . ($j['nombre'] ?? ''));
drawText($img, $hasTtf, $ttf, 18, $tx, $ty+45, $black, "DNI: " . ($j['dni'] ?? ''));
drawText($img, $hasTtf, $ttf, 18, $tx, $ty+80, $black, "Sexo: " . ($j['sexo'] ?? ''));
drawText($img, $hasTtf, $ttf, 18, $tx, $ty+115, $black, "Categoría: " . ($j['categoria'] ?? ''));
drawText($img, $hasTtf, $ttf, 18, $tx, $ty+150, $black, "Carnet Nº: " . ($c['numero_carnet'] ?? ''));

// QR
$qrX = $W - $pad - 210; $qrY = $pad + 120; $qrS = 170;
imagerectangle($img, $qrX, $qrY, $qrX+$qrS, $qrY+$qrS, $black);

if (!empty($c['qr_code']) && file_exists($qrPath)) {
    $ext = strtolower(pathinfo($qrPath, PATHINFO_EXTENSION));
    $src = null;
    if ($ext === 'jpg' || $ext === 'jpeg') $src = @imagecreatefromjpeg($qrPath);
    if ($ext === 'png') $src = @imagecreatefrompng($qrPath);
    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($qrPath);

    if ($src) {
        $sw = imagesx($src); $sh = imagesy($src);
        imagecopyresampled($img, $src, $qrX, $qrY, 0, 0, $qrS, $qrS, $sw, $sh);
        imagedestroy($src);
    } else {
        drawText($img, $hasTtf, $ttf, 14, $qrX+35, $qrY+90, $gray, "Sin QR");
    }
} else {
    drawText($img, $hasTtf, $ttf, 14, $qrX+35, $qrY+90, $gray, "Sin QR");
}

$filename = 'carnet_' . $jugador_id . '.jpg';

header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

imagejpeg($img, null, 92);
imagedestroy($img);
exit;
