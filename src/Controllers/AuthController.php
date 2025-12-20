<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Models\Lider;
use Src\Models\User;

class AuthController extends Controller
{
    private $db;
    private $userModel;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->db = Database::getConnection($this->config['db']);
        $this->userModel = new User($this->db);
    }
    public function login(): void
    {
        $error = $_GET['error'] ?? null;
        $this->view('auth/login', ['title' => 'Login', 'error' => $error]);
    }

    public function authenticate(): void
    {
        $auth = new Auth($this->config);

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['senha'] ?? '';

        if ($auth->attempt($email, $password, $this->userModel)) {
            $user = $auth->user();

            // Verificar se precisa fazer onboarding
            if (!empty($user['mostrar_onboarding']) && empty($user['onboarding_completo'])) {
                $this->redirect('/onboarding');
                return;
            }

            $home = ($user['perfil'] === 'admin') ? '/admin' : '/home';
            $this->redirect($home);
        } else {
            $this->redirect('/login?error=Credenciais%20inv%C3%A1lidas%20ou%20usu%C3%A1rio%20inativo');
        }
    }

    public function logout(): void
    {
        $auth = new Auth($this->config);
        $auth->logout();
        $this->redirect('/login');
    }

    public function forgotPassword(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $error = 'E-mail é obrigatório';
            } else {
                $user = $this->userModel->findByEmail($email);
                if (!$user) {
                    $error = 'E-mail não encontrado no sistema';
                } elseif (!$user['ativo']) {
                    $error = 'Esta conta está inativa';
                } else {
                    // Redirecionar para verificação
                    $_SESSION['forgot_password_user'] = $user;
                    $this->redirect('/forgot-password/verify');
                    return;
                }
            }
        }

        $this->view('forgot-password/request', [
            'title' => 'Esqueci minha senha',
            'error' => $error
        ]);
    }

    public function verifyForgotPassword(): void
    {
        if (!isset($_SESSION['forgot_password_user'])) {
            $this->redirect('/forgot-password');
            return;
        }

        $user = $_SESSION['forgot_password_user'];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');

            // Validar nome
            if (strtolower($nome) !== strtolower($user['nome'])) {
                $error = 'Nome não corresponde ao cadastrado';
            } else {
                // Para líderes, validar telefone
                if ($user['perfil'] === 'lider') {
                    $liderModel = new Lider($this->db);
                    $lider = $liderModel->findByUserId($user['id']);

                    if (!$lider || $telefone !== $lider['telefone']) {
                        $error = 'Telefone não corresponde ao cadastrado';
                    }
                }

                if (!$error) {
                    // Validação passou, redirecionar para redefinição
                    $_SESSION['forgot_password_verified'] = true;
                    $this->redirect('/forgot-password/reset');
                    return;
                }
            }
        }

        $this->view('forgot-password/verify', [
            'title' => 'Verificar identidade',
            'error' => $error,
            'user' => $user
        ]);
    }

    public function resetPassword(): void
    {
        if (!isset($_SESSION['forgot_password_user']) || !isset($_SESSION['forgot_password_verified'])) {
            $this->redirect('/forgot-password');
            return;
        }

        $user = $_SESSION['forgot_password_user'];
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha = $_POST['senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            if (strlen($senha) < 6) {
                $error = 'A senha deve ter pelo menos 6 caracteres';
            } elseif ($senha !== $confirmarSenha) {
                $error = 'As senhas não coincidem';
            } else {
                // Atualizar senha
                $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
                $stmt->execute(['senha' => $hashedPassword, 'id' => $user['id']]);

                // Limpar sessão
                unset($_SESSION['forgot_password_user']);
                unset($_SESSION['forgot_password_verified']);

                $success = 'Senha redefinida com sucesso! Você pode fazer login agora.';
            }
        }

        $this->view('forgot-password/reset', [
            'title' => 'Redefinir senha',
            'error' => $error,
            'success' => $success,
            'userId' => $user['id']
        ]);
    }
}

