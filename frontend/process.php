<?php

/**
 * Fungsi BFF (Backend For Frontend) untuk membangun payload JSON.
 * Dipisahkan agar bisa diuji secara independen menggunakan PHPUnit.
 */
function buildOrderPayload($type, $qty, $isCustom, $complexity, $notes) {
    return json_encode([
        "type"       => (string) $type,
        "qty"        => (int) $qty,
        "isCustom"   => filter_var($isCustom, FILTER_VALIDATE_BOOLEAN),
        "complexity" => (int) $complexity,
        "notes"      => (string) $notes
    ]);
}

/**
 * Blok ini hanya berjalan jika dipanggil via browser (POST request).
 * Tidak akan dieksekusi saat PHPUnit meng-require file ini.
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $type       = $_POST['type']       ?? '';
    $qty        = $_POST['qty']        ?? 0;
    $isCustom   = $_POST['isCustom']   ?? 'false';
    $complexity = $_POST['complexity'] ?? 1;
    $notes      = $_POST['notes']      ?? '';

    $payload = buildOrderPayload($type, $qty, $isCustom, $complexity, $notes);

    $ch = curl_init('http://localhost:8080/api/order');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<h2>Status HTTP: " . $httpCode . "</h2>";
    echo "<div data-testid='api-response'>" . $response . "</div>";

    echo "<h3>Catatan Anda:</h3>";
    echo "<div data-testid='display-notes'>" . htmlspecialchars($notes) . "</div>";
}
?>