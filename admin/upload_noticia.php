<?php
require_once 'auth.php';

$permitidos = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!isset($_FILES['imagen'])) {
    die("Archivo no enviado");
}

$file = $_FILES['imagen'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir archivo");
}

if (!in_array(mime_content_type($file['tmp_name']), $permitidos)) {
    die("Formato no permitido");
}

if ($file['size'] > $maxSize) {
    die("Archivo demasiado grande");
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$nombreSeguro = uniqid('img_') . '.' . $ext;

$destino = $_SERVER['DOCUMENT_ROOT'] . '/apiba-padel/uploads/noticias/' . $nombreSeguro;

if (!move_uploaded_file($file['tmp_name'], $destino)) {
    die("No se pudo guardar el archivo");
}

echo "✅ Upload OK: " . $nombreSeguro;
<p>
  <a href="index.php">⬅ Volver al panel</a> |
  <a href="/apiba-padel/index.php">Ir al sitio</a>
</p>
