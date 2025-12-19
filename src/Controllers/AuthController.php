<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Models\User;

class AuthController extends Controller
{
    public function login(): void
    {
        $error = $_GET['error'] ?? null;
        $this->view('auth/login', ['title' => 'Login', 'error' => $error]);
    }

    public function authenticate(): void
    {
        $db = Database::getConnection($this->config['db']);
        $userModel = new User($db);
        $auth = new Auth($this->config);

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['senha'] ?? '';

        if ($auth->attempt($email, $password, $userModel)) {
            $user = $auth->user();
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
}

