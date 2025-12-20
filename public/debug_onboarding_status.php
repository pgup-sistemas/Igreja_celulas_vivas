<?php
// Debug onboarding status
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
    
    // Check if fields exist
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'onboarding_completo'");
    $hasOnboardingCompleto = $stmt->rowCount() > 0;
    
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'mostrar_onboarding'");
    $hasMostrarOnboarding = $stmt->rowCount() > 0;
    
    // Get current user status
    $stmt = $db->prepare("SELECT onboarding_completo, mostrar_onboarding FROM usuarios WHERE id = ?");
    $stmt->execute([$user['id']]);
    $status = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'database_fields' => [
            'onboarding_completo_exists' => $hasOnboardingCompleto,
            'mostrar_onboarding_exists' => $hasMostrarOnboarding
        ],
        'current_status' => $status,
        'session' => $_SESSION
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>