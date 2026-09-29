<?php
// Configuración centralizada de la base de datos
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'redes_gobernanza');
// Usuario específico con privilegios limitados a esta BD para mayor seguridad
define('DB_USER', 'usr_asi_app'); 
define('DB_PASS', 'Ra020976');
define('DB_CHAR', 'utf8mb4');

// ============================================================================
// CONFIGURACIÓN SMTP (ENVÍO DE CORREOS CON PHPMailer)
// ============================================================================
// Modifica estas variables si cambias de servidor o de proveedor de correo.
// ============================================================================
define('SMTP_HOST',       'mail.ccaguaviva.org'); // Ej: mail.tudominio.com, smtp.gmail.com
define('SMTP_USER',       'mailingasistencia@ccaguaviva.org'); // Tu cuenta de correo
define('SMTP_PASS',       'asisV26*');            // La contraseña de tu cuenta
define('SMTP_PORT',       465);                   // Generalmente 465 para SSL, o 587 para TLS
define('SMTP_SECURE',     'ssl');                 // 'ssl', 'tls', o de lo contrario déjalo vacío ''

// ============================================================================
// DATOS DEL REMITENTE
// ============================================================================
define('MAIL_FROM',       'mailingasistencia@ccaguaviva.org'); // Correo que se mostrará como origen
define('MAIL_FROM_NAME',  'Hamuy Net');      // Nombre a mostrar en el origen
define('MAIL_REPLYTO',       'mailingasistencia@ccaguaviva.org');
define('MAIL_REPLYTO_NAME',  'Atención Hamuy Net');
?>
