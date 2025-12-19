<?php
/**
 * Script para criar o banco de dados e tabelas
 * Execute via linha de comando: php database/create_database.php
 * Ou acesse via navegador: http://localhost/igreja/database/create_database.php
 */

// Configurações do banco
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'igreja';
$charset = 'utf8mb4';

echo "=== Criando Banco de Dados ===\n\n";

try {
    // Conectar sem especificar o banco
    $pdo = new PDO(
        "mysql:host=$host;charset=$charset",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "✓ Conectado ao MySQL com sucesso!\n";
    
    // Criar banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Banco de dados '$database' criado/verificado!\n\n";
    
    // Selecionar o banco
    $pdo->exec("USE `$database`");
    
    // Ler e executar schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        echo "=== Executando schema.sql ===\n";
        $schema = file_get_contents($schemaFile);
        
        // Remover comentários e dividir em comandos
        $schema = preg_replace('/--.*$/m', '', $schema);
        $commands = array_filter(
            array_map('trim', explode(';', $schema)),
            function($cmd) {
                return !empty($cmd) && !preg_match('/^\s*$/', $cmd);
            }
        );
        
        foreach ($commands as $command) {
            if (!empty(trim($command))) {
                try {
                    // Ignorar INSERT de usuário admin do schema.sql (vamos criar depois com hash correto)
                    if (stripos($command, 'INSERT INTO usuarios') !== false && stripos($command, 'admin@igreja.com') !== false) {
                        echo "  (Ignorando INSERT de admin do schema.sql - será criado depois)\n";
                        continue;
                    }
                    $pdo->exec($command);
                } catch (PDOException $e) {
                    // Ignorar erros de tabela já existente
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "⚠ Aviso: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        echo "✓ Schema executado!\n\n";
    }
    
    // Ler e executar migrations.sql se existir
    $migrationsFile = __DIR__ . '/migrations.sql';
    if (file_exists($migrationsFile)) {
        echo "=== Executando migrations.sql ===\n";
        $migrations = file_get_contents($migrationsFile);
        
        // Remover comentários e dividir em comandos
        $migrations = preg_replace('/--.*$/m', '', $migrations);
        $commands = array_filter(
            array_map('trim', explode(';', $migrations)),
            function($cmd) {
                return !empty($cmd) && !preg_match('/^\s*$/', $cmd);
            }
        );
        
        foreach ($commands as $command) {
            if (!empty(trim($command))) {
                try {
                    $pdo->exec($command);
                } catch (PDOException $e) {
                    // Ignorar erros de tabela já existente
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "⚠ Aviso: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        echo "✓ Migrations executadas!\n\n";
    }
    
    // Verificar tabelas criadas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "=== Tabelas criadas ===\n";
    foreach ($tables as $table) {
        echo "✓ $table\n";
    }
    
    // Verificar/criar usuário admin
    $admin = $pdo->query("SELECT * FROM usuarios WHERE email = 'admin@igreja.com'")->fetch();
    if ($admin) {
        echo "\n✓ Usuário admin já existe!\n";
        echo "  Email: admin@igreja.com\n";
        echo "  Senha: admin123\n";
        
        // Verificar se a senha está correta
        $senhaTeste = 'admin123';
        if (!password_verify($senhaTeste, $admin['senha'])) {
            echo "⚠ Senha incorreta! Corrigindo...\n";
            $hashCorreto = password_hash($senhaTeste, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha, ativo = 1, perfil = 'admin' WHERE email = 'admin@igreja.com'");
            $stmt->execute(['senha' => $hashCorreto]);
            echo "✓ Senha corrigida!\n";
        }
    } else {
        // Criar usuário admin com hash correto
        echo "\n⚠ Usuário admin não encontrado! Criando...\n";
        $senhaAdmin = 'admin123';
        $hashAdmin = password_hash($senhaAdmin, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, perfil, ativo, data_criacao)
             VALUES ('Admin', 'admin@igreja.com', :senha, 'admin', 1, NOW())"
        );
        $stmt->execute(['senha' => $hashAdmin]);
        echo "✓ Usuário admin criado!\n";
        echo "  Email: admin@igreja.com\n";
        echo "  Senha: admin123\n";
    }
    
    echo "\n=== Banco de dados configurado com sucesso! ===\n";
    echo "Você pode acessar o sistema em: http://localhost/igreja/public/\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n\n";
    echo "Verifique:\n";
    echo "1. Se o MySQL/MariaDB está rodando no XAMPP\n";
    echo "2. Se as credenciais estão corretas (host: $host, user: $user)\n";
    echo "3. Se a porta 3306 está livre\n";
    echo "4. Tente iniciar o MySQL pelo painel do XAMPP\n";
    exit(1);
}

