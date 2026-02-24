<?php
// torneos/fecha.php
// Muestra los partidos de una fecha específica del fixture
// Si el usuario quiere inscribirse y NO está logueado como jugador → redirige a registro/login

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/site_header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDB();

// Parámetros obligatorios
$torneo_id = (int)($_GET['torneo'] ?? 0);
$fecha = trim($_GET['fecha'] ?? '');

if ($torneo_id <= 0 || $fecha === '') {
    die("Parámetros
