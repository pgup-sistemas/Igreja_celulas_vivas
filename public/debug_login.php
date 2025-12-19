<?php
/**
 * Debug do processo de login
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
    <title>Debug Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Debug do Login</h1>";

try {
    // Testar conexão
    $db = \Src\Core\Database::getConnection($config['db']);
    echo "<div class='success'>✓ Conexão com banco OK</div>";
    
    // Buscar usuário
    $userModel = new \Src\Models\User($db);
    $user = $userModel->findByEmail('admin@igreja.com');
    
    if (!$user) {
        echo "<div class='error'>❌ Usuário não encontrado!</div>";
        echo "<div class='info'>Execute: <a href='../database/fix_admin_user.php'>fix_admin_user.php</a></div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='success'>✓ Usuário encontrado</div>";
    
    echo "<div class='info'><h2>Dados do Usuário:</h2>";
    echo "<pre>";
    echo "ID: " . $user['id'] . "\n";
    echo "Nome: " . htmlspecialchars($user['nome']) . "\n";
    echo "Email: " . htmlspecialchars($user['email']) . "\n";
    echo "Perfil: " . htmlspecialchars($user['perfil']) . "\n";
    echo "Ativo: " . ($user['ativo'] ? 'Sim (1)' : 'Não (0)') . "\n";
    echo "Hash da senha: " . htmlspecialchars($user['senha']) . "\n";
    echo "</pre></div>";
    
    // Testar senha
    $senhaTeste = 'admin123';
    $senhaValida = password_verify($senhaTeste, $user['senha']);
    
    echo "<div class='info'><h2>Teste de Senha:</h2>";
    echo "<pre>";
    echo "Senha testada: admin123\n";
    echo "Resultado: " . ($senhaValida ? '✓ VÁLIDA' : '✗ INVÁLIDA') . "\n";
    echo "</pre></div>";
    
    if (!$senhaValida) {
        echo "<div class='error'>❌ A senha não está correta!</div>";
        echo "<div class='info'>Execute: <a href='../database/fix_admin_user.php'>fix_admin_user.php</a> para corrigir</div>";
    }
    
    if ($user['ativo'] != 1) {
        echo "<div class='error'>❌ Usuário está INATIVO!</div>";
        echo "<div class='info'>Execute: <a href='../database/fix_admin_user.php'>fix_admin_user.php</a> para ativar</div>";
    }
    
    if ($user['perfil'] != 'admin') {
        echo "<div class='error'>❌ Perfil não é 'admin'!</div>";
        echo "<div class='info'>Execute: <a href='../database/fix_admin_user.php'>fix_admin_user.php</a> para corrigir</div>";
    }
    
    // Testar autenticação completa
    if ($senhaValida && $user['ativo'] == 1 && $user['perfil'] == 'admin') {
        $auth = new \Src\Core\Auth($config);
        $loginOk = $auth->attempt('admin@igreja.com', 'admin123', $userModel);
        
        echo "<div class='info'><h2>Teste de Autenticação Completa:</h2>";
        echo "<pre>";
        echo "Resultado: " . ($loginOk ? '✓ SUCESSO' : '✗ FALHOU') . "\n";
        if ($loginOk) {
            $userSession = $auth->user();
            echo "Dados na sessão:\n";
            print_r($userSession);
        }
        echo "</pre></div>";
        
        if ($loginOk) {
            echo "<div class='success'><h2>✅ Tudo funcionando!</h2>";
            echo "<p>O login deve funcionar corretamente agora.</p>";
            echo "<p><a href='login'>Ir para Login</a></p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Verifique se o banco de dados foi criado.</p>";
    echo "<p><a href='../database/create_database.php'>Criar Banco</a></p>";
    echo "</div>";
}

echo "</div></body></html>";
?>

