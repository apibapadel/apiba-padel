<?php
function subirImagen($campo, $carpeta){
    if(empty($_FILES[$campo]['name'])){
        throw new Exception("No se subió imagen");
    }

    $ext = pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION);
    $permitidas = ['jpg','jpeg','png','webp'];

    if(!in_array($ext, $permitidas)){
        throw new Exception("Formato no permitido");
    }

    $nombre = uniqid().".".$ext;
    $ruta = $_SERVER['DOCUMENT_ROOT']."/apiba-padel/uploads/$carpeta/";

    if(!is_dir($ruta)){
        mkdir($ruta, 0777, true);
    }

    move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta.$nombre);
    return $nombre;
}
