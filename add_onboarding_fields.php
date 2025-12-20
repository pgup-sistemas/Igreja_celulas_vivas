<?php
// Script para adicionar os campos de onboarding à tabela usuarios

require_once 'config/config.php';
require_once 'src/Core/Database.php';

$config = require 'config/config.php';
$db = \Src\Core\Database::getConnection($config['db']);

try {
    // Adicionar campo onboarding_completo se não existir
    $result = $db->query("SHOW COLUMNS FROM usuarios LIKE 'onboarding_completo'");
    if ($result->rowCount() === 0) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN onboarding_completo TINYINT(1) NOT NULL DEFAULT 0");
        echo "Campo 'onboarding_completo' adicionado com sucesso.\n";
    } else {
        echo "Campo 'onboarding_completo' já existe.\n";
    }
    
    // Adicionar campo mostrar_onboarding se não existir
    $result = $db->query("SHOW COLUMNS FROM usuarios LIKE 'mostrar_onboarding'");
    if ($result->rowCount() === 0) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN mostrar_onboarding TINYINT(1) NOT NULL DEFAULT 1");
        echo "Campo 'mostrar_onboarding' adicionado com sucesso.\n";
    } else {
        echo "Campo 'mostrar_onboarding' já existe.\n";
    }
    
    echo "\nCampos de onboarding adicionados à tabela 'usuarios'.\n";
    
} catch (Exception $e) {
    echo "Erro ao adicionar campos: " . $e->getMessage() . "\n";
}