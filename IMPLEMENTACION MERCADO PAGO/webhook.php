<?php
// Recibimos la notificación de Mercado Pago
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(isset($data["type"]) && $data["type"] == "payment"){
    $id_pago = $data["data"]["id"];
    
    // Aquí consultarías a la API de MP si el estado es 'approved'
    // Y luego: UPDATE tabla_qrs SET estado='pagado' WHERE id_usuario=...
    
    http_response_code(200); // Le decimos a MP que recibimos el aviso
}
?>