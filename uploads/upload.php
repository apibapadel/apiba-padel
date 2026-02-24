
<?php
$allowed=['image/jpeg','image/png','image/webp'];
if($_FILES['file']['size']>2*1024*1024) die("Archivo grande");
if(!in_array($_FILES['file']['type'],$allowed)) die("Tipo no permitido");
$name=uniqid().basename($_FILES['file']['name']);
move_uploaded_file($_FILES['file']['tmp_name'],$name);
?>
