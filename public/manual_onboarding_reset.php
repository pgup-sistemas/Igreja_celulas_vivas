<?php
// Manual onboarding reset script
$config = require __DIR__ . '/config/config.php';

spl_autoload_register(function ($class) {
    $prefix = 'Src\\';
    $baseDir = __DIR__ . '/src/';
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

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');

try {
    // Check authentication
    $auth = new \Src\Core\Auth($config);
    if (!$auth->check()) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    
    $user = $auth->user();
    
    // Get database connection
    $db = \Src\Core\Database::getConnection($config['db']);
    
    // Ensure fields exist
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'onboarding_completo'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN onboarding_completo TINYINT(1) NOT NULL DEFAULT 0");
    }
    
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'mostrar_onboarding'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN mostrar_onboarding TINYINT(1) NOT NULL DEFAULT 1");
    }
    
    // Mark onboarding as complete and hide it
    $stmt = $db->prepare("UPDATE usuarios SET onboarding_completo = 1, mostrar_onboarding = 0 WHERE id = ?");
    $result = $stmt->execute([$user['id']]);
    
    // Update session
    $_SESSION['igreja_user']['onboarding_completo'] = 1;
    $_SESSION['igreja_user']['mostrar_onboarding'] = 0;
    
    echo json_encode([
        'success' => true,
        'message' => 'Onboarding reset and marked as complete',
        'user_id' => $user['id']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>