<?php
/**
 * Debug temporário para verificar redirects
 */
echo "<pre>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'não definido') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'não definido') . "\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'não definido') . "\n";
echo "HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'não definido') . "\n";
echo "\n";

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = dirname($scriptName);
echo "dirname(SCRIPT_NAME): " . $scriptDir . "\n";

$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '' || $scriptDir === '.' || $scriptDir === '\\') {
    $scriptDir = '';
} else {
    $scriptDir = '/' . ltrim($scriptDir, '/');
}
echo "Base Path calculado: '" . $scriptDir . "'\n";

echo "\nTeste de redirects:\n";
echo "  /admin -> " . $scriptDir . "/admin\n";
echo "  /home -> " . $scriptDir . "/home\n";
echo "  /login -> " . $scriptDir . "/login\n";
echo "</pre>";

echo "<p><a href='" . $scriptDir . "/login'>Ir para Login</a></p>";
echo "<p><a href='" . $scriptDir . "/test.php'>Ir para Test</a></p>";
?>

