<?php
/**
 * Script para sincronizar líderes
 * Cria registros na tabela lideres para todos os usuários com perfil 'lider' que ainda não têm líder
 * Execute via navegador: http://localhost/igreja/database/sincronizar_lideres.php
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
    <title>Sincronizar Líderes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔄 Sincronizar Líderes</h1>";

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
    
    // Buscar usuários com perfil 'lider' que não têm líder criado
    echo "<div class='info'><h2>Verificando usuários com perfil 'lider':</h2>";
    $usuariosLider = $pdo->query("SELECT u.id, u.nome, u.email FROM usuarios u WHERE u.perfil = 'lider' AND u.ativo = 1 ORDER BY u.nome")->fetchAll();
    
    echo "<p><strong>Total de usuários com perfil 'lider':</strong> " . count($usuariosLider) . "</p>";
    
    if (empty($usuariosLider)) {
        echo "<div class='warning'>⚠ Nenhum usuário com perfil 'lider' encontrado.</div>";
        echo "</div>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Status</th></tr>";
        foreach ($usuariosLider as $usuario) {
            // Verificar se já existe líder
            $stmt = $pdo->prepare('SELECT id FROM lideres WHERE usuario_id = :usuario_id');
            $stmt->execute(['usuario_id' => $usuario['id']]);
            $liderExistente = $stmt->fetch();
            
            $status = $liderExistente ? '✓ Já tem líder' : '⚠ Sem líder';
            $cor = $liderExistente ? 'color: green;' : 'color: orange;';
            
            echo "<tr>";
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . htmlspecialchars($usuario['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['email']) . "</td>";
            echo "<td style='$cor'>" . $status . "</td>";
            echo "</tr>";
        }
        echo "</table></div>";
        
        // Criar líderes para os que não têm
        echo "<div class='info'><h2>Criando líderes faltantes:</h2>";
        $criados = 0;
        $erros = 0;
        
        foreach ($usuariosLider as $usuario) {
            // Verificar se já existe líder
            $stmt = $pdo->prepare('SELECT id FROM lideres WHERE usuario_id = :usuario_id');
            $stmt->execute(['usuario_id' => $usuario['id']]);
            $liderExistente = $stmt->fetch();
            
            if (!$liderExistente) {
                try {
                    $stmt = $pdo->prepare('INSERT INTO lideres (nome, usuario_id) VALUES (:nome, :usuario_id)');
                    $stmt->execute([
                        'nome' => $usuario['nome'],
                        'usuario_id' => $usuario['id']
                    ]);
                    $criados++;
                    echo "<p>✓ Líder criado para: <strong>" . htmlspecialchars($usuario['nome']) . "</strong> (ID: " . $pdo->lastInsertId() . ")</p>";
                } catch (PDOException $e) {
                    $erros++;
                    echo "<p class='error'>✗ Erro ao criar líder para " . htmlspecialchars($usuario['nome']) . ": " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
        
        echo "<div class='success'><h3>Resumo:</h3>";
        echo "<p>✓ Líderes criados: <strong>$criados</strong></p>";
        if ($erros > 0) {
            echo "<p>✗ Erros: <strong>$erros</strong></p>";
        }
        echo "</div></div>";
        
        // Listar todos os líderes após sincronização
        echo "<div class='info'><h2>Líderes cadastrados após sincronização:</h2>";
        $lideres = $pdo->query("SELECT l.*, u.nome as usuario_nome, u.email as usuario_email 
                                FROM lideres l 
                                INNER JOIN usuarios u ON u.id = l.usuario_id 
                                WHERE u.ativo = 1 
                                ORDER BY l.nome")->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID Líder</th><th>Nome</th><th>Telefone</th><th>ID Usuário</th><th>Usuário</th><th>Email</th></tr>";
        foreach ($lideres as $l) {
            echo "<tr>";
            echo "<td>" . $l['id'] . "</td>";
            echo "<td>" . htmlspecialchars($l['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($l['telefone'] ?? '-') . "</td>";
            echo "<td>" . $l['usuario_id'] . "</td>";
            echo "<td>" . htmlspecialchars($l['usuario_nome']) . "</td>";
            echo "<td>" . htmlspecialchars($l['usuario_email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total:</strong> " . count($lideres) . " líder(es) cadastrado(s)</p>";
        echo "</div>";
        
        if ($criados > 0) {
            echo "<div class='success'><h2>✅ Sincronização concluída!</h2>";
            echo "<p>Os líderes agora devem aparecer no formulário de criação de células.</p>";
            echo "<p><a href='../public/admin/celulas/novo'>Ir para Criar Célula</a></p>";
            echo "</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Verifique se o banco de dados foi criado.</p>";
    echo "<p><a href='create_database.php'>Criar Banco</a></p>";
    echo "</div>";
}

echo "</div></body></html>";
?>

