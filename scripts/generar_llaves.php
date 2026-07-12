<?php
/**
 * Genera par de llaves RSA 2048 para firma de integridad.
 * Ejecutar UNA SOLA VEZ desde la terminal:
 *   php scripts/generar_llaves.php
 */

$keysDir = __DIR__ . '/../storage/keys';

if (!is_dir($keysDir)) {
    mkdir($keysDir, 0755, true);
}

$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config' => 'C:/wamp64/bin/apache/apache2.4.65/conf/openssl.cnf',
];

$res = openssl_pkey_new($config);

if (!$res) {
    echo "Error al generar la llave privada.\n";
    exit(1);
}

// Exportar llave privada
openssl_pkey_export($res, $privateKey);
file_put_contents($keysDir . '/private_key.pem', $privateKey);
echo "Llave privada guardada en storage/keys/private_key.pem\n";

// Exportar llave pública
$pubKeyDetails = openssl_pkey_get_details($res);
file_put_contents($keysDir . '/public_key.pem', $pubKeyDetails['key']);
echo "Llave pública guardada en storage/keys/public_key.pem\n";

echo "Llaves generadas exitosamente.\n";
