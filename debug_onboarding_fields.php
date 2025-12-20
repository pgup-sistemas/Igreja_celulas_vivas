<?php
// Script para verificar a estrutura da tabela usuarios e os campos de onboarding

require_once 'config/config.php';
require_once 'src/Core/Database.php';

$config = require 'config/config.php';
$db = \Src\Core\Database::getConnection($config['db']);

try {
    // Verificar a estrutura da tabela usuarios
    $result = $db->query("DESCRIBE usuarios");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estrutura da tabela 'usuarios':\n";
    echo "================================\n";
    
    foreach ($columns as $column) {
        echo $column['Field'] . " (" . $column['Type'] . ") " . 
             ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " " .
             ($column['Default'] !== null ? 'DEFAULT ' . $column['Default'] : '') . "\n";
    }
    
    echo "\n";
    
    // Verificar se os campos de onboarding existem
    $onboardingFields = ['onboarding_completo', 'mostrar_onboarding'];
    
    foreach ($onboardingFields as $field) {
        $result = $db->query("SHOW COLUMNS FROM usuarios LIKE '$field'");
        if ($result->rowCount() > 0) {
            echo "Campo '$field' existe na tabela.\n";
        } else {
            echo "Campo '$field' NÃO existe na tabela.\n";
        }
    }
    
    // Verificar alguns registros para ver os valores dos campos
    echo "\nVerificando valores dos campos de onboarding em alguns registros:\n";
    echo "================================================================\n";
    
    $result = $db->query("SELECT id, nome, email, onboarding_completo, mostrar_onboarding FROM usuarios LIMIT 5");
    $users = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . ", Nome: " . $user['nome'] . ", Email: " . $user['email'] . "\n";
        echo "  onboarding_completo: " . (isset($user['onboarding_completo']) ? $user['onboarding_completo'] : 'N/A') . "\n";
        echo "  mostrar_onboarding: " . (isset($user['mostrar_onboarding']) ? $user['mostrar_onboarding'] : 'N/A') . "\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Erro ao verificar a tabela: " . $e->getMessage() . "\n";
}