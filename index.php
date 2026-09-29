<?php
// El punto de entrada principal redirige al módulo de dashboard.
// La lógica de autenticación (sessionStorage) se maneja en el frontend (common_ui.js).
header("Location: modules/dashboard/index.html");
exit();
?>
