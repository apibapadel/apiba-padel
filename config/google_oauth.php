<?php
// =====================================================
// Google OAuth (Registro / Login) - APiBA Pádel
//
// 1) Crear credenciales OAuth en Google Cloud Console
//    - Tipo: "Web application"
//    - Authorized redirect URI:
//        http://localhost/apiba-padel/google_oauth_callback.php
//      (si usás ngrok, agregá también tu URL pública)
//
// 2) Completar estas 2 variables:
// =====================================================

return [
  'client_id'     => 'PONER_CLIENT_ID',
  'client_secret' => 'PONER_CLIENT_SECRET',

  // IMPORTANTE: debe coincidir con el redirect configurado en Google.
  'redirect_uri'  => 'http://localhost/apiba-padel/google_oauth_callback.php',

  // scopes mínimos para "Continuar con Google"
  'scope'         => 'openid email profile',
];
