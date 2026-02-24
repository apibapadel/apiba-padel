<?php
require "../config/db.php";
require "../lib/fpdf/fpdf.php";

$id = $_SESSION['id'];

$q = $conn->query("SELECT apellido,nombre FROM jugadores WHERE usuario_id=$id");
$j = $q->fetch_assoc();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,"CARNET APIBA",0,1,'C');
$pdf->Ln(10);
$pdf->Cell(0,10,"Apellido: ".$j['apellido'],0,1);
$pdf->Cell(0,10,"Nombre: ".$j['nombre'],0,1);

$pdf->Output("D","carnet.pdf");
