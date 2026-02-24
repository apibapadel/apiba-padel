<?php
// Inicia el flujo OAuth con Google
if (session_status() === PHP_SESSION_NONE) session_start();

$cfgFile = __DIR__ . '/config/google_oauth.php';
if (!is_file($cfgFile)) {
  http_response_code(500);
  echo "Falta config/google_oauth.php";
  exit;
}

$cfg = require $cfgFile;

// Para qué flujo se usa (por ahora: registro)
$flow = $_GET['flow'] ?? 'registro';

// Validación mínima
if (empty($cfg['client_id']) || $cfg['client_id'] === 'PONER_CLIENT_ID') {
  http_response_code(500);
  echo "Config Google OAuth incompleta (client_id).";
  exit;
}

// State anti-CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;
$_SESSION['google_oauth_flow']  = $flow;

$params = [
  'client_id' => $cfg['client_id'],
  'redirect_uri' => $cfg['redirect_uri'],
  'response_type' => 'code',
  'scope' => $cfg['scope'] ?? 'openid email profile',
  'state' => $state,
  'access_type' => 'online',
  'prompt' => 'select_account',
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
