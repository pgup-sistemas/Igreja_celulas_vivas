<?php
// Simplified onboarding completion for debugging
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

// Simple function to log and return error
function logAndReturnError($message) {
    error_log("Onboarding Debug: " . $message);
    echo json_encode([
        'success' => false, 
        'message' => $message,
        'debug_info' => [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'UNKNOWN'
        ]
    ]);
    exit;
}

// Simple function to log and return success
function logAndReturnSuccess($data = []) {
    error_log("Onboarding Debug: SUCCESS");
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

try {
    error_log("Onboarding Debug: Starting completion process");
    
    // Check authentication
    $auth = new \Src\Core\Auth($config);
    if (!$auth->check()) {
        logAndReturnError('User not authenticated');
    }
    
    $user = $auth->user();
    error_log("Onboarding Debug: User authenticated: " . json_encode($user));
    
    // Get database connection
    $db = \Src\Core\Database::getConnection($config['db']);
    error_log("Onboarding Debug: Database connection successful");
    
    // Check if fields exist
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'onboarding_completo'");
    if ($stmt->rowCount() === 0) {
        error_log("Onboarding Debug: Adding onboarding_completo field");
        $db->exec("ALTER TABLE usuarios ADD COLUMN onboarding_completo TINYINT(1) NOT NULL DEFAULT 0");
    }
    
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'mostrar_onboarding'");
    if ($stmt->rowCount() === 0) {
        error_log("Onboarding Debug: Adding mostrar_onboarding field");
        $db->exec("ALTER TABLE usuarios ADD COLUMN mostrar_onboarding TINYINT(1) NOT NULL DEFAULT 1");
    }
    
    // Get input
    $rawInput = file_get_contents('php://input');
    error_log("Onboarding Debug: Raw input: " . $rawInput);
    
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logAndReturnError('Invalid JSON: ' . json_last_error_msg());
    }
    
    $showAgain = $input['show_again'] ?? true;
    error_log("Onboarding Debug: showAgain = " . ($showAgain ? 'true' : 'false'));
    
    // Simple update without transaction
    $userId = $_SESSION['igreja_user']['id'];
    error_log("Onboarding Debug: Updating user ID: " . $userId);
    
    // Update mostrar_onboarding
    $stmt = $db->prepare("UPDATE usuarios SET mostrar_onboarding = :mostrar WHERE id = :id");
    $result1 = $stmt->execute([
        'mostrar' => $showAgain ? 1 : 0,
        'id' => $userId
    ]);
    
    error_log("Onboarding Debug: Update mostrar_onboarding result: " . ($result1 ? 'true' : 'false'));
    
    // If not showing again, mark as complete
    if (!$showAgain) {
        $stmt = $db->prepare("UPDATE usuarios SET onboarding_completo = 1 WHERE id = :id");
        $result2 = $stmt->execute(['id' => $userId]);
        error_log("Onboarding Debug: Update onboarding_completo result: " . ($result2 ? 'true' : 'false'));
        
        // Update session
        $_SESSION['igreja_user']['onboarding_completo'] = 1;
        $_SESSION['igreja_user']['mostrar_onboarding'] = 0;
    } else {
        $_SESSION['igreja_user']['mostrar_onboarding'] = 1;
    }
    
    logAndReturnSuccess([
        'user_id' => $userId,
        'show_again' => $showAgain,
        'updated_fields' => ['mostrar_onboarding']
    ]);
    
} catch (Exception $e) {
    error_log("Onboarding Debug: Exception: " . $e->getMessage());
    logAndReturnError('Server error: ' . $e->getMessage());
}
?>