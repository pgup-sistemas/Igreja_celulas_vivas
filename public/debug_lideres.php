<?php
/**
 * Debug - Verificar líderes
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
    <title>Debug Líderes</title>
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
    <h1>🔍 Debug - Líderes</h1>";

try {
    $db = \Src\Core\Database::getConnection($config['db']);
    echo "<div class='success'>✓ Conectado ao banco de dados!</div>";
    
    // Verificar se a tabela lideres existe
    $tables = $db->query("SHOW TABLES LIKE 'lideres'")->fetchAll();
    if (empty($tables)) {
        echo "<div class='error'>❌ Tabela 'lideres' não existe!</div>";
    } else {
        echo "<div class='success'>✓ Tabela 'lideres' existe</div>";
    }
    
    // Verificar usuários com perfil 'lider'
    echo "<div class='info'><h2>Usuários com perfil 'lider':</h2>";
    $usuariosLider = $db->query("SELECT * FROM usuarios WHERE perfil = 'lider' AND ativo = 1")->fetchAll();
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th></tr>";
    foreach ($usuariosLider as $u) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . htmlspecialchars($u['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($u['email']) . "</td>";
        echo "<td>" . ($u['ativo'] ? 'Sim' : 'Não') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total:</strong> " . count($usuariosLider) . " usuário(s) com perfil 'lider'</p>";
    echo "</div>";
    
    // Verificar líderes na tabela lideres
    echo "<div class='info'><h2>Líderes cadastrados na tabela 'lideres':</h2>";
    $lideres = $db->query("SELECT l.*, u.nome as usuario_nome, u.email as usuario_email 
                           FROM lideres l 
                           INNER JOIN usuarios u ON u.id = l.usuario_id 
                           WHERE u.ativo = 1")->fetchAll();
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome</th><th>Telefone</th><th>Usuário ID</th><th>Usuário Nome</th><th>Usuário Email</th></tr>";
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
    
    if (empty($lideres)) {
        echo "<div class='warning'><h2>⚠ Problema Identificado:</h2>";
        echo "<p>A tabela 'lideres' está vazia ou não há líderes cadastrados.</p>";
        echo "<p><strong>Solução:</strong> Você precisa cadastrar líderes na tabela 'lideres' vinculados aos usuários com perfil 'lider'.</p>";
        echo "</div>";
        
        // Mostrar query que está sendo usada
        echo "<div class='info'><h2>Query atual no código:</h2>";
        echo "<pre>SELECT l.*, u.nome as usuario_nome 
FROM lideres l 
INNER JOIN usuarios u ON u.id = l.usuario_id 
WHERE u.ativo = 1</pre>";
        echo "</div>";
        
        // Sugerir solução alternativa
        echo "<div class='info'><h2>💡 Solução Alternativa:</h2>";
        echo "<p>Podemos modificar o código para mostrar usuários com perfil 'lider' diretamente, sem precisar da tabela 'lideres'.</p>";
        echo "<p>Isso seria útil se você não quiser usar a tabela 'lideres' ou se ela ainda não foi populada.</p>";
        echo "</div>";
    } else {
        echo "<div class='success'><h2>✅ Líderes encontrados!</h2>";
        echo "<p>Os líderes devem aparecer no formulário de células.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ ERRO</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>

