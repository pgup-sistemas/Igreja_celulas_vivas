<?php
/**
 * Script de diagnóstico do MySQL
 * Verifica se o MySQL está acessível e configurado corretamente
 */

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico MySQL - XAMPP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Diagnóstico MySQL - XAMPP</h1>";

$host = '127.0.0.1';
$user = 'root';
$password = '';
$port = 3306;

$issues = [];
$success = [];

// 1. Verificar extensão PDO MySQL
echo "<div class='step'><h2>1. Verificando extensão PHP</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<div class='success'>✓ Extensão PDO MySQL está instalada</div>";
    $success[] = "PDO MySQL";
} else {
    echo "<div class='error'>❌ Extensão PDO MySQL NÃO está instalada</div>";
    $issues[] = "Instale a extensão php_pdo_mysql no php.ini";
}
echo "</div>";

// 2. Testar conexão
echo "<div class='step'><h2>2. Testando conexão MySQL</h2>";
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    echo "<div class='success'>✓ Conectado ao MySQL com sucesso!</div>";
    
    // Informações do servidor
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<div class='info'>Versão MySQL: $version</div>";
    $success[] = "Conexão MySQL";
    
    // Listar bancos de dados
    $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'><strong>Bancos de dados existentes:</strong><br>";
    foreach ($databases as $db) {
        echo "• $db<br>";
    }
    echo "</div>";
    
    // Verificar se o banco 'igreja' existe
    if (in_array('igreja', $databases)) {
        echo "<div class='success'>✓ Banco 'igreja' já existe</div>";
        $pdo->exec('USE `igreja`');
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "<div class='info'><strong>Tabelas no banco 'igreja':</strong><br>";
            foreach ($tables as $table) {
                echo "• $table<br>";
            }
            echo "</div>";
        } else {
            echo "<div class='warning'>⚠ Banco 'igreja' existe mas está vazio. Execute o script create_database.php</div>";
        }
    } else {
        echo "<div class='warning'>⚠ Banco 'igreja' não existe. Execute o script create_database.php para criá-lo</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erro ao conectar: " . htmlspecialchars($e->getMessage()) . "</div>";
    $issues[] = "MySQL não está acessível";
    
    echo "<div class='step'><h3>Possíveis soluções:</h3>";
    echo "<ol>";
    echo "<li><strong>Verifique se o MySQL está rodando:</strong><br>";
    echo "   • Abra o XAMPP Control Panel<br>";
    echo "   • Clique em 'Start' no MySQL<br>";
    echo "   • Aguarde até aparecer 'Running' em verde</li>";
    echo "<li><strong>Verifique a porta:</strong><br>";
    echo "   • Porta padrão: 3306<br>";
    echo "   • Se estiver usando outra porta, ajuste em config/config.php</li>";
    echo "<li><strong>Verifique credenciais:</strong><br>";
    echo "   • Usuário padrão: root<br>";
    echo "   • Senha padrão: (vazia)<br>";
    echo "   • Se alterou a senha, atualize em config/config.php</li>";
    echo "<li><strong>Verifique firewall:</strong><br>";
    echo "   • O firewall pode estar bloqueando a porta 3306</li>";
    echo "</ol></div>";
}
echo "</div>";

// 3. Verificar porta
echo "<div class='step'><h2>3. Verificando porta 3306</h2>";
$connection = @fsockopen($host, $port, $errno, $errstr, 2);
if ($connection) {
    echo "<div class='success'>✓ Porta 3306 está aberta e acessível</div>";
    fclose($connection);
    $success[] = "Porta 3306";
} else {
    echo "<div class='error'>❌ Porta 3306 não está acessível (Erro: $errstr)</div>";
    $issues[] = "Porta 3306 bloqueada ou MySQL não está rodando";
}
echo "</div>";

// Resumo
echo "<div class='step'><h2>📊 Resumo</h2>";
if (count($issues) == 0) {
    echo "<div class='success'><strong>Tudo OK!</strong> O MySQL está funcionando corretamente.</div>";
    echo "<div class='info'>";
    echo "<strong>Próximos passos:</strong><br>";
    echo "1. Se o banco 'igreja' não existe, execute: <a href='create_database.php'>create_database.php</a><br>";
    echo "2. Ou acesse o phpMyAdmin em: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a><br>";
    echo "3. Acesse o sistema em: <a href='../public/'>http://localhost/igreja/public/</a>";
    echo "</div>";
} else {
    echo "<div class='error'><strong>Problemas encontrados:</strong><br>";
    foreach ($issues as $issue) {
        echo "• $issue<br>";
    }
    echo "</div>";
}
echo "</div>";

echo "</div></body></html>";
?>

