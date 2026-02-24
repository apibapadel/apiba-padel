<?php
// Callback OAuth de Google: intercambia code por token y guarda datos en sesión.
if (session_status() === PHP_SESSION_NONE) session_start();

$cfg = require __DIR__ . '/config/google_oauth.php';

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';
$err   = $_GET['error'] ?? '';

if ($err) {
  header('Location: /apiba-padel/registro.php?oauth=error');
  exit;
}

if ($code === '' || $state === '') {
  header('Location: /apiba-padel/registro.php?oauth=bad');
  exit;
}

if (!isset($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
  header('Location: /apiba-padel/registro.php?oauth=state');
  exit;
}

// Limpiar state (single-use)
unset($_SESSION['google_oauth_state']);

// Intercambio code -> token
$post = [
  'code' => $code,
  'client_id' => $cfg['client_id'],
  'client_secret' => $cfg['client_secret'],
  'redirect_uri' => $cfg['redirect_uri'],
  'grant_type' => 'authorization_code',
];

$opts = [
  'http' => [
    'method' => 'POST',
    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
    'content' => http_build_query($post),
    'timeout' => 12,
  ]
];

$tokenResp = @file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create($opts));
if ($tokenResp === false) {
  header('Location: /apiba-padel/registro.php?oauth=token');
  exit;
}

$token = json_decode($tokenResp, true);
$idToken = $token['id_token'] ?? '';

if ($idToken === '') {
  header('Location: /apiba-padel/registro.php?oauth=noid');
  exit;
}

// Validar / leer claims del id_token con tokeninfo (simple y práctico)
$info = @file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
if ($info === false) {
  header('Location: /apiba-padel/registro.php?oauth=tokeninfo');
  exit;
}

$claims = json_decode($info, true);
if (!is_array($claims) || empty($claims['email']) || empty($claims['sub'])) {
  header('Location: /apiba-padel/registro.php?oauth=claims');
  exit;
}

// Guardar en sesión para completar registro
$_SESSION['google_oauth'] = [
  'sub'   => (string)$claims['sub'],
  'email' => strtolower(trim((string)$claims['email'])),
  'name'  => trim((string)($claims['name'] ?? '')),
  'given_name' => trim((string)($claims['given_name'] ?? '')),
  'family_name' => trim((string)($claims['family_name'] ?? '')),
  'picture' => trim((string)($claims['picture'] ?? '')),
];

$flow = $_SESSION['google_oauth_flow'] ?? 'registro';
unset($_SESSION['google_oauth_flow']);

if ($flow === 'registro') {
  header('Location: /apiba-padel/registro.php?oauth=ok');
  exit;
}

header('Location: /apiba-padel/index.php');
exit;
