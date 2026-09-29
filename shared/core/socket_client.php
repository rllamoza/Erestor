<?php
/**
 * Socket Client Helper
 * Allows PHP to trigger real-time events via the Node.js companion service.
 */

function broadcast_event($event, $data) {
    // Configuration - Match with modules/realtime/.env
    $node_url = 'http://localhost:3000/emit';
    $auth_secret = 'servidores_v3_secret_123';

    $payload = json_encode([
        'event' => $event,
        'data' => $data
    ]);

    $ch = curl_init($node_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Auth-Secret: ' . $auth_secret
    ]);
    
    // Low timeout to not block the main request
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code === 200);
}
