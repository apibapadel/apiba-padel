<?php
// Descarga PDF carnet
// ✅ Jugador: descarga el suyo
// ✅ Admin: descarga cualquiera con ?id=ID_JUGADOR (tiene prioridad si viene ?id)
// 🔒 Si no hay sesión válida: redirige

if (session_status() === PHP_SESSION_NONE) session_start();

$base = $_SERVER['DOCUMENT_ROOT'] . '/apiba-padel';

require_once $base . '/config/database.php';
require_once $base . '/lib/fpdf/fpdf.php';

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

// Traer jugador
$stmt = $pdo->prepare("SELECT id, apellido, nombre, dni, sexo, categoria, foto FROM jugadores WHERE id=? LIMIT 1");
$stmt->execute([$jugador_id]);
$j = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$j) die("Jugador no encontrado");

// Traer carnet
$stmt = $pdo->prepare("SELECT id, numero_carnet, qr_code FROM carnets WHERE jugador_id=? LIMIT 1");
$stmt->execute([$jugador_id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) die("Este jugador aún no tiene carnet generado");

// Helper FPDF (FPDF no es UTF-8)
function fpdf_text($s) {
    $s = (string)$s;
    $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
    return $out !== false ? $out : $s;
}

$fotoPath = $base . '/uploads/jugadores/' . ($j['foto'] ?? '');
$qrPath   = $base . '/uploads/qr/' . ($c['qr_code'] ?? '');

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, fpdf_text('Carnet de Jugador APiBA'), 0, 1, 'C');
$pdf->Ln(4);

// Marco carnet
$x = 20; $y = 35; $w = 170; $h = 75;
$pdf->Rect($x, $y, $w, $h);

// Foto
if (!empty($j['foto']) && file_exists($fotoPath)) {
    $pdf->Image($fotoPath, $x + 6, $y + 8, 28, 34);
} else {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Text($x + 8, $y + 25, fpdf_text('Sin foto'));
}

// Datos
$pdf->SetFont('Arial', 'B', 13);
$pdf->Text($x + 40, $y + 18, fpdf_text($j['apellido'] . ' ' . $j['nombre']));

$pdf->SetFont('Arial', '', 11);
$pdf->Text($x + 40, $y + 28, fpdf_text('DNI: ') . fpdf_text($j['dni'] ?? ''));
$pdf->Text($x + 40, $y + 36, fpdf_text('Sexo: ') . fpdf_text($j['sexo'] ?? ''));
$pdf->Text($x + 40, $y + 44, fpdf_text('Categoría: ') . fpdf_text($j['categoria'] ?? ''));
$pdf->Text($x + 40, $y + 52, fpdf_text('Carnet Nº: ') . fpdf_text($c['numero_carnet'] ?? ''));

// QR
if (!empty($c['qr_code']) && file_exists($qrPath)) {
    $pdf->Image($qrPath, $x + 140, $y + 12, 24, 24);
} else {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Text($x + 142, $y + 25, fpdf_text('Sin QR'));
}

$filename = 'carnet_' . $jugador_id . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$pdf->Output('D', $filename);
exit;
