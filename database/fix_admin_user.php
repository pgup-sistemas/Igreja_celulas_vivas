<?php
/**
 * Script para verificar/criar/corrigir usuário admin
 * Execute via navegador: http://localhost/igreja/database/fix_admin_user.php
 */

// Configurações do banco
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'igreja';
$charset = 'utf8mb4';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Corrigir Usuário Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Corrigir Usuário Admin</h1>";

try {
    // Conectar ao banco
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=$charset",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "<div class='success'>✓ Conectado ao banco de dados!</div>";
    
    // Verificar se a tabela existe
    $tables = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    if (empty($tables)) {
        echo "<div class='error'>❌ Tabela 'usuarios' não existe! Execute primeiro o script create_database.php</div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='info'>✓ Tabela 'usuarios' existe</div>";
    
    // Verificar se o usuário admin existe
    $admin = $pdo->query("SELECT * FROM usuarios WHERE email = 'admin@igreja.com'")->fetch();
    
    // Gerar hash correto para a senha "admin123"
    $senhaCorreta = 'admin123';
    $hashCorreto = password_hash($senhaCorreta, PASSWORD_DEFAULT);
    
    echo "<div class='info'><h2>Informações:</h2>";
    echo "<pre>";
    echo "Email: admin@igreja.com\n";
    echo "Senha: admin123\n";
    echo "Hash gerado: " . $hashCorreto . "\n";
    echo "</pre></div>";
    
    if ($admin) {
        echo "<div class='warning'>⚠ Usuário admin encontrado!</div>";
        echo "<div class='info'><h3>Dados atuais:</h3>";
        echo "<pre>";
        echo "ID: " . $admin['id'] . "\n";
        echo "Nome: " . htmlspecialchars($admin['nome']) . "\n";
        echo "Email: " . htmlspecialchars($admin['email']) . "\n";
        echo "Perfil: " . htmlspecialchars($admin['perfil']) . "\n";
        echo "Ativo: " . ($admin['ativo'] ? 'Sim' : 'Não') . "\n";
        echo "Hash atual: " . htmlspecialchars($admin['senha']) . "\n";
        echo "</pre></div>";
        
        // Testar se a senha atual funciona
        $senhaValida = password_verify($senhaCorreta, $admin['senha']);
        if ($senhaValida) {
            echo "<div class='success'>✓ A senha atual está CORRETA! O problema pode ser outro.</div>";
        } else {
            echo "<div class='error'>❌ A senha atual está INCORRETA! Atualizando...</div>";
            
            // Atualizar senha
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = 'admin@igreja.com'");
            $stmt->execute(['senha' => $hashCorreto]);
            
            echo "<div class='success'>✓ Senha atualizada com sucesso!</div>";
        }
        
        // Garantir que está ativo e é admin
        if ($admin['ativo'] != 1 || $admin['perfil'] != 'admin') {
            echo "<div class='warning'>⚠ Corrigindo perfil e status...</div>";
            $stmt = $pdo->prepare("UPDATE usuarios SET perfil = 'admin', ativo = 1 WHERE email = 'admin@igreja.com'");
            $stmt->execute();
            echo "<div class='success'>✓ Perfil e status corrigidos!</div>";
        }
        
    } else {
        echo "<div class='warning'>⚠ Usuário admin NÃO encontrado! Criando...</div>";
        
        // Criar usuário admin
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, perfil, ativo, data_criacao)
             VALUES (:nome, :email, :senha, 'admin', 1, NOW())"
        );
        $stmt->execute([
            'nome' => 'Admin',
            'email' => 'admin@igreja.com',
            'senha' => $hashCorreto,
        ]);
        
        echo "<div class='success'>✓ Usuário admin criado com sucesso!</div>";
    }
    
    // Verificar novamente após as alterações
    $adminFinal = $pdo->query("SELECT * FROM usuarios WHERE email = 'admin@igreja.com'")->fetch();
    $senhaValidaFinal = password_verify($senhaCorreta, $adminFinal['senha']);
    
    echo "<div class='info'><h2>Status Final:</h2>";
    echo "<pre>";
    echo "Email: " . htmlspecialchars($adminFinal['email']) . "\n";
    echo "Nome: " . htmlspecialchars($adminFinal['nome']) . "\n";
    echo "Perfil: " . htmlspecialchars($adminFinal['perfil']) . "\n";
    echo "Ativo: " . ($adminFinal['ativo'] ? 'Sim' : 'Não') . "\n";
    echo "Senha válida: " . ($senhaValidaFinal ? 'SIM ✓' : 'NÃO ✗') . "\n";
    echo "</pre></div>";
    
    if ($senhaValidaFinal && $adminFinal['ativo'] == 1 && $adminFinal['perfil'] == 'admin') {
        echo "<div class='success'><h2>✅ Tudo OK!</h2>";
        echo "<p>O usuário admin está configurado corretamente.</p>";
        echo "<p><strong>Credenciais:</strong></p>";
        echo "<ul>";
        echo "<li>Email: <code>admin@igreja.com</code></li>";
        echo "<li>Senha: <code>admin123</code></li>";
        echo "</ul>";
        echo "<p><a href='../public/login'>Ir para Login</a></p>";
        echo "</div>";
    } else {
        echo "<div class='error'><h2>❌ Ainda há problemas</h2>";
        echo "<p>Verifique os dados acima e tente novamente.</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Verifique:</p>";
    echo "<ul>";
    echo "<li>Se o MySQL está rodando</li>";
    echo "<li>Se o banco 'igreja' existe</li>";
    echo "<li>Se as credenciais estão corretas</li>";
    echo "</ul>";
    echo "<p><a href='check_mysql.php'>Verificar MySQL</a></p>";
    echo "<p><a href='create_database.php'>Criar Banco de Dados</a></p>";
    echo "</div>";
}

echo "</div></body></html>";
?>

