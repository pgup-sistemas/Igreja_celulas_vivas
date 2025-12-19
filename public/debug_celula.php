<?php
/**
 * Debug - Testar criação de célula
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
    <title>Debug Célula</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
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
    <h1>🔍 Debug - Criação de Célula</h1>";

try {
    $db = \Src\Core\Database::getConnection($config['db']);
    echo "<div class='success'>✓ Conectado ao banco de dados!</div>";
    
    // Verificar estrutura da tabela celulas
    echo "<div class='info'><h2>Estrutura da tabela celulas:</h2>";
    $columns = $db->query("SHOW COLUMNS FROM celulas")->fetchAll();
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table></div>";
    
    // Verificar foreign keys
    echo "<div class='info'><h2>Foreign Keys:</h2>";
    $fks = $db->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'celulas'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ")->fetchAll();
    
    if (empty($fks)) {
        echo "<p>Nenhuma foreign key encontrada</p>";
    } else {
        echo "<table>";
        echo "<tr><th>Constraint</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
        foreach ($fks as $fk) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fk['CONSTRAINT_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Testar inserção simulada
    echo "<div class='info'><h2>Teste de Validação:</h2>";
    
    // Verificar congregacoes
    $congregacoes = $db->query("SELECT id, nome FROM congregacoes")->fetchAll();
    echo "<p><strong>Congregações disponíveis:</strong> " . count($congregacoes) . "</p>";
    if (empty($congregacoes)) {
        echo "<div class='warning'>⚠ Nenhuma congregação cadastrada. Você pode criar uma célula sem congregação.</div>";
    }
    
    // Verificar líderes
    $lideres = $db->query("SELECT id, nome FROM lideres")->fetchAll();
    echo "<p><strong>Líderes na tabela lideres:</strong> " . count($lideres) . "</p>";
    if (empty($lideres)) {
        echo "<div class='warning'>⚠ Nenhum líder na tabela lideres.</div>";
        
        // Verificar usuários com perfil lider
        $usuariosLider = $db->query("SELECT id, nome FROM usuarios WHERE perfil = 'lider' AND ativo = 1")->fetchAll();
        echo "<p><strong>Usuários com perfil 'lider':</strong> " . count($usuariosLider) . "</p>";
        if (!empty($usuariosLider)) {
            echo "<div class='info'>O sistema criará líderes automaticamente quando você selecionar um usuário.</div>";
        }
    }
    
    echo "</div>";
    
    // Mostrar exemplo de INSERT
    echo "<div class='info'><h2>Exemplo de INSERT que será executado:</h2>";
    echo "<pre>";
    echo "INSERT INTO celulas (nome, congregacao_id, lider_id, cidade, bairro, zona, ponto_referencia, ativa)\n";
    echo "VALUES (\n";
    echo "  'Nome da Célula',\n";
    echo "  NULL,  -- congregacao_id (pode ser NULL)\n";
    echo "  NULL,  -- lider_id (pode ser NULL)\n";
    echo "  'Cidade',\n";
    echo "  'Bairro',\n";
    echo "  'Zona',\n";
    echo "  'Ponto de referência',\n";
    echo "  1      -- ativa\n";
    echo ");\n";
    echo "</pre>";
    echo "<p><strong>Nota:</strong> congregacao_id e lider_id podem ser NULL, mas se fornecidos, devem existir nas tabelas referenciadas.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>

