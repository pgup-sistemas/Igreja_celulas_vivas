<?php

namespace Src\Core;

use Src\Models\User;

class Auth
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function user(): ?array
    {
        return $_SESSION[$this->config['auth']['session_key']] ?? null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function attempt(string $email, string $password, User $userModel): bool
    {
        $user = $userModel->findByEmail($email);
        if ($user && $user['ativo'] && password_verify($password, $user['senha'])) {
            $_SESSION[$this->config['auth']['session_key']] = [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'perfil' => $user['perfil'],
                'mostrar_onboarding' => $user['mostrar_onboarding'] ?? 1,
                'onboarding_completo' => $user['onboarding_completo'] ?? 0,
            ];
            return true;
        }
        return false;
    }

    public function logout(): void
    {
        unset($_SESSION[$this->config['auth']['session_key']]);
        session_destroy();
    }
}

