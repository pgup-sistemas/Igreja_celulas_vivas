<?php
/**
 * Arquivo de teste para verificar configuração do servidor
 */
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Teste - Sistema Igreja</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Teste de Configuração</h1>";

echo "<div class='success'>✓ PHP está funcionando!</div>";

echo "<div class='info'><h2>Informações do Servidor:</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'não definido') . "\n";
echo "</pre></div>";

// Testar autoload
echo "<div class='info'><h2>Teste de Autoload:</h2>";
try {
    $config = require __DIR__ . '/../config/config.php';
    echo "<div class='success'>✓ Config carregado com sucesso!</div>";
    
    spl_autoload_register(function ($class) {
        $prefix = 'Src\\';
        $baseDir = __DIR__ . '/../src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
    
    $router = new \Src\Core\Router();
    echo "<div class='success'>✓ Router carregado com sucesso!</div>";
    
    // Testar caminho
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    echo "<pre>Caminho base detectado: " . $scriptDir . "</pre>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

echo "<div class='info'><h2>Links de Teste:</h2>";
$baseUrl = dirname($_SERVER['SCRIPT_NAME']);
echo "<ul>";
echo "<li><a href='{$baseUrl}/test.php'>Teste (este arquivo)</a></li>";
echo "<li><a href='{$baseUrl}/index.php'>Index.php</a></li>";
echo "<li><a href='{$baseUrl}/login'>Login (via Router)</a></li>";
echo "<li><a href='{$baseUrl}/'>Raiz (via Router)</a></li>";
echo "</ul></div>";

echo "</div></body></html>";
?>

