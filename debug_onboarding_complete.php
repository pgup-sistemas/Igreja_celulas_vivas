<?php
// Debug script for onboarding completion
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

use Src\Controllers\OnboardingController;
use Src\Core\Auth;

echo "<h2>Debug Onboarding Complete</h2>";

// Test 1: Check if user is authenticated
echo "<h3>Test 1: Authentication Check</h3>";
$auth = new Auth($config);
$isAuthenticated = $auth->check();
echo "Is authenticated: " . ($isAuthenticated ? 'YES' : 'NO') . "<br>";

if ($isAuthenticated) {
    $user = $auth->user();
    echo "Current user: " . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "<br>";
    
    // Test 2: Check database connection and fields
    echo "<h3>Test 2: Database Connection and Fields</h3>";
    try {
        $db = \Src\Core\Database::getConnection($config['db']);
        echo "Database connection: SUCCESS<br>";
        
        // Check if onboarding fields exist
        $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'onboarding_completo'");
        $hasOnboardingCompleto = $stmt->rowCount() > 0;
        echo "Field 'onboarding_completo' exists: " . ($hasOnboardingCompleto ? 'YES' : 'NO') . "<br>";
        
        $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'mostrar_onboarding'");
        $hasMostrarOnboarding = $stmt->rowCount() > 0;
        echo "Field 'mostrar_onboarding' exists: " . ($hasMostrarOnboarding ? 'YES' : 'NO') . "<br>";
        
        // Check current user status
        $userId = $_SESSION['igreja_user']['id'] ?? null;
        if ($userId) {
            $stmt = $db->prepare("SELECT onboarding_completo, mostrar_onboarding FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            $status = $stmt->fetch(\PDO::FETCH_ASSOC);
            echo "Current user onboarding status: " . json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "<br>";
        }
        
        // Test 3: Test the complete method directly
        echo "<h3>Test 3: Direct Method Test</h3>";
        
        // Simulate the POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        // Capture the output
        ob_start();
        
        $controller = new OnboardingController($config);
        
        // Call the complete method
        $controller->complete();
        
        $output = ob_get_clean();
        echo "Controller output: " . $output . "<br>";
        
        // Check if there are any errors in the log
        echo "<h3>Test 4: Recent Error Log</h3>";
        $logFile = __DIR__ . '/storage/logs/error.log';
        if (file_exists($logFile)) {
            $logLines = file($logFile);
            $recentLines = array_slice($logLines, -20); // Last 20 lines
            echo "Recent error log entries:<br><pre>";
            foreach ($recentLines as $line) {
                if (strpos($line, 'OnboardingController') !== false) {
                    echo htmlspecialchars($line);
                }
            }
            echo "</pre>";
        } else {
            echo "No error log file found.<br>";
        }
        
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "User is not authenticated. Please login first.<br>";
}

echo "<h3>Test 5: Session Debug</h3>";
echo "Session data: <pre>" . json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
?>