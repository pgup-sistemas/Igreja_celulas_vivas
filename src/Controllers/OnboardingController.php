<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Models\User;

class OnboardingController extends Controller
{
    public function index()
    {
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            $this->redirect('/login');
        }

        $data = [
            'title' => 'Onboarding - Bem-vindo ao Sistema',
            'breadcrumb' => [
                ['label' => 'Home', 'url' => '/home'],
                ['label' => 'Onboarding', 'url' => '/onboarding']
            ]
        ];

        $this->view('onboarding/index', $data);
    }

    public function complete()
    {
        error_log('OnboardingController::complete() - Iniciando processamento');
        
        // Ensure session is started before any session operations
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        error_log('OnboardingController::complete() - Session status: ' . session_status());
        error_log('OnboardingController::complete() - Session data: ' . json_encode($_SESSION));
        
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            error_log('OnboardingController::complete() - Usuário não autenticado');
            error_log('OnboardingController::complete() - Auth user: ' . json_encode($auth->user()));
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            error_log('OnboardingController::complete() - Resposta de erro enviada');
            return;
        }

        error_log('OnboardingController::complete() - Usuário autenticado');

        try {
            // Get database connection
            $db = \Src\Core\Database::getConnection($this->config['db']);
            error_log('OnboardingController::complete() - Conexão com banco estabelecida');
            
            // Ensure onboarding fields exist
            $this->ensureOnboardingFieldsExist($db);
            
            // Get user model and ID
            $userModel = new User($db);
            $userId = $_SESSION['igreja_user']['id'] ?? null;

            error_log('OnboardingController::complete() - User ID: ' . $userId);

            if (!$userId) {
                error_log('OnboardingController::complete() - ID do usuário não encontrado na sessão');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'ID do usuário não encontrado']);
                error_log('OnboardingController::complete() - Resposta de erro enviada');
                return;
            }

            // Parse input
            $rawInput = file_get_contents('php://input');
            error_log('OnboardingController::complete() - Input bruto: ' . $rawInput);
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('OnboardingController::complete() - JSON inválido: ' . json_last_error_msg());
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'JSON inválido']);
                error_log('OnboardingController::complete() - Resposta de erro enviada');
                return;
            }

            $showAgain = $input['show_again'] ?? true;
            error_log('OnboardingController::complete() - showAgain: ' . ($showAgain ? 'true' : 'false'));

            // Simple update without transaction for debugging
            error_log('OnboardingController::complete() - Fazendo update simples');
            
            // Update user's onboarding preference
            $updateResult = $userModel->updateShowOnboarding($userId, $showAgain);
            error_log('OnboardingController::complete() - Resultado updateShowOnboarding: ' . ($updateResult ? 'true' : 'false'));
            
            if (!$updateResult) {
                error_log('OnboardingController::complete() - Falha no updateShowOnboarding, mas continuando...');
            }

            // If user doesn't want to see onboarding again, mark as complete
            if (!$showAgain) {
                $completeResult = $userModel->markOnboardingComplete($userId);
                error_log('OnboardingController::complete() - Resultado markOnboardingComplete: ' . ($completeResult ? 'true' : 'false'));
                
                if (!$completeResult) {
                    error_log('OnboardingController::complete() - Falha no markOnboardingComplete, mas continuando...');
                }

                // Update session
                $_SESSION['igreja_user']['onboarding_completo'] = 1;
                $_SESSION['igreja_user']['mostrar_onboarding'] = 0;
            } else {
                $_SESSION['igreja_user']['mostrar_onboarding'] = 1;
            }

            error_log('OnboardingController::complete() - Processamento concluído com sucesso');

            $response = ['success' => true, 'message' => 'Onboarding atualizado com sucesso'];
            error_log('OnboardingController::complete() - Enviando resposta de sucesso: ' . json_encode($response));
            echo json_encode($response);
            error_log('OnboardingController::complete() - Resposta enviada');
            
        } catch (\Exception $e) {
            error_log('OnboardingController::complete() - Erro geral: ' . $e->getMessage());
            error_log('OnboardingController::complete() - Stack trace: ' . $e->getTraceAsString());
            http_response_code(500);
            $response = [
                'success' => false, 
                'message' => 'Erro interno do servidor: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
            echo json_encode($response);
            error_log('OnboardingController::complete() - Resposta de erro enviada: ' . json_encode($response));
        }
    }

    public function debug()
    {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $debug = [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_data' => $_SESSION,
            'auth_check' => false,
            'auth_user' => null,
        ];
        
        try {
            $auth = new Auth($this->config);
            $debug['auth_check'] = $auth->check();
            $debug['auth_user'] = $auth->user();
        } catch (Exception $e) {
            $debug['auth_error'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($debug, JSON_PRETTY_PRINT);
    }

    private function ensureOnboardingFieldsExist(\PDO $db): void
    {
        // Check and add onboarding_completo field if it doesn't exist
        $result = $db->query("SHOW COLUMNS FROM usuarios LIKE 'onboarding_completo'");
        if ($result->rowCount() === 0) {
            $db->exec("ALTER TABLE usuarios ADD COLUMN onboarding_completo TINYINT(1) NOT NULL DEFAULT 0");
        }

        // Check and add mostrar_onboarding field if it doesn't exist
        $result = $db->query("SHOW COLUMNS FROM usuarios LIKE 'mostrar_onboarding'");
        if ($result->rowCount() === 0) {
            $db->exec("ALTER TABLE usuarios ADD COLUMN mostrar_onboarding TINYINT(1) NOT NULL DEFAULT 1");
        }
    }
}