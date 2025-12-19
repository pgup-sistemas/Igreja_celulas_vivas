<?php
/**
 * Teste de redirect
 */
require __DIR__ . '/../config/config.php';

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

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Teste Redirect</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
    <h1>Teste de Redirect</h1>";

$controller = new class($config) extends \Src\Core\Controller {
    public function testRedirect() {
        $basePath = $this->getBasePath();
        echo "<div class='info'><h2>Caminho Base Detectado:</h2>";
        echo "<pre>" . htmlspecialchars($basePath) . "</pre></div>";
        
        echo "<div class='info'><h2>Teste de Redirects:</h2>";
        echo "<pre>";
        echo "Redirect para '/admin' resultaria em: " . htmlspecialchars($basePath . '/admin') . "\n";
        echo "Redirect para '/home' resultaria em: " . htmlspecialchars($basePath . '/home') . "\n";
        echo "Redirect para '/login' resultaria em: " . htmlspecialchars($basePath . '/login') . "\n";
        echo "</pre></div>";
        
        echo "<div class='info'><h2>URLs Corretas:</h2>";
        echo "<ul>";
        echo "<li><a href='" . htmlspecialchars($basePath . '/login') . "'>Login</a></li>";
        echo "<li><a href='" . htmlspecialchars($basePath . '/admin') . "'>Admin</a></li>";
        echo "<li><a href='" . htmlspecialchars($basePath . '/home') . "'>Home</a></li>";
        echo "</ul></div>";
        
        echo "<div class='info'><h2>Informações do Servidor:</h2>";
        echo "<pre>";
        echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'não definido') . "\n";
        echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'não definido') . "\n";
        echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "\n";
        echo "</pre></div>";
    }
};

$controller->testRedirect();

echo "</div></body></html>";
?>

