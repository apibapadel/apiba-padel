<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Upload</title>
</head>
<body>

<h2>Subir imagen (prueba)</h2>

<form method="post" enctype="multipart/form-data" action="upload_noticia.php">
    <input type="file" name="imagen" required>
    <br><br>
    <button type="submit">Subir</button>
</form>

</body>
<p>
  <a href="index.php">⬅ Volver al panel</a> |
  <a href="/apiba-padel/index.php">Ir al sitio</a>
</p>

</html>
