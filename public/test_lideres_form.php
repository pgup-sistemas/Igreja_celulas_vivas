<?php
/**
 * Teste - Verificar o que está sendo passado para o formulário
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
    <title>Teste Líderes no Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Teste - Líderes no Formulário</h1>";

try {
    $db = \Src\Core\Database::getConnection($config['db']);
    echo "<div class='success'>✓ Conectado ao banco de dados!</div>";
    
    // Simular o que o controller faz
    echo "<div class='info'><h2>1. Buscando da tabela lideres:</h2>";
    $stmt = $db->query('SELECT l.*, u.nome as usuario_nome FROM lideres l INNER JOIN usuarios u ON u.id = l.usuario_id WHERE u.ativo = 1');
    $lideres = $stmt->fetchAll();
    echo "<p><strong>Resultado:</strong> " . count($lideres) . " líder(es) encontrado(s)</p>";
    if (!empty($lideres)) {
        echo "<pre>";
        print_r($lideres);
        echo "</pre>";
    } else {
        echo "<p class='warning'>Nenhum líder na tabela lideres</p>";
    }
    echo "</div>";
    
    // Se não houver, buscar usuários
    if (empty($lideres)) {
        echo "<div class='info'><h2>2. Buscando usuários com perfil 'lider':</h2>";
        $stmt = $db->query("SELECT u.id, u.nome as nome, u.nome as usuario_nome, u.email 
                            FROM usuarios u 
                            WHERE u.perfil = 'lider' AND u.ativo = 1 
                            ORDER BY u.nome");
        $lideres = $stmt->fetchAll();
        echo "<p><strong>Resultado:</strong> " . count($lideres) . " usuário(s) com perfil 'lider' encontrado(s)</p>";
        if (!empty($lideres)) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>usuario_nome</th></tr>";
            foreach ($lideres as $l) {
                echo "<tr>";
                echo "<td>" . $l['id'] . "</td>";
                echo "<td>" . htmlspecialchars($l['nome']) . "</td>";
                echo "<td>" . htmlspecialchars($l['email'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($l['usuario_nome']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<pre>";
            print_r($lideres);
            echo "</pre>";
        } else {
            echo "<div class='error'>❌ Nenhum usuário com perfil 'lider' encontrado!</div>";
        }
        echo "</div>";
    }
    
    // Verificar todos os usuários
    echo "<div class='info'><h2>3. Todos os usuários cadastrados:</h2>";
    $todosUsuarios = $db->query("SELECT id, nome, email, perfil, ativo FROM usuarios ORDER BY nome")->fetchAll();
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil</th><th>Ativo</th></tr>";
    foreach ($todosUsuarios as $u) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . htmlspecialchars($u['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($u['email']) . "</td>";
        echo "<td>" . htmlspecialchars($u['perfil']) . "</td>";
        echo "<td>" . ($u['ativo'] ? 'Sim' : 'Não') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Simular o que a view recebe
    echo "<div class='info'><h2>4. Dados que seriam passados para a view:</h2>";
    echo "<pre>";
    echo "Variável \$lideres:\n";
    var_dump($lideres);
    echo "</pre>";
    echo "</div>";
    
    // Simular o que a view renderiza
    echo "<div class='info'><h2>5. Simulação do select que seria renderizado:</h2>";
    echo "<select style='width: 100%; padding: 8px;'>";
    echo "<option value=''>Selecione...</option>";
    if (empty($lideres)) {
        echo "<option value='' disabled>Nenhum líder cadastrado. Crie usuários com perfil 'lider' primeiro.</option>";
    } else {
        foreach ($lideres as $l) {
            $display = htmlspecialchars($l['nome']);
            if (isset($l['usuario_nome']) && $l['nome'] !== $l['usuario_nome']) {
                $display .= ' (' . htmlspecialchars($l['usuario_nome']) . ')';
            }
            echo "<option value='" . $l['id'] . "'>" . $display . "</option>";
        }
    }
    echo "</select>";
    echo "<p><strong>Total de opções:</strong> " . (count($lideres) + 1) . "</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>

