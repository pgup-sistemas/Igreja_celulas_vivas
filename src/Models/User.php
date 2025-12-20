<?php

namespace Src\Models;

use Src\Core\Model;
use PDO;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\Exception $e) {
            error_log('Erro ao buscar usuário por email: ' . $e->getMessage());
            return null;
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\Exception $e) {
            error_log('Erro ao buscar usuário por ID: ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO usuarios (nome, email, senha, perfil, ativo, onboarding_completo, data_criacao)
                 VALUES (:nome, :email, :senha, :perfil, :ativo, :onboarding_completo, NOW())'
            );
            
            $result = $stmt->execute([
                'nome' => $data['nome'],
                'email' => $data['email'],
                'senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
                'perfil' => $data['perfil'],
                'ativo' => $data['ativo'] ?? 1,
                'onboarding_completo' => $data['onboarding_completo'] ?? 0,
            ]);
            
            if (!$result) {
                throw new \Exception('Falha ao executar INSERT');
            }
            
            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('Erro ao criar usuário: ' . $e->getMessage());
            throw $e;
        }
    }

    public function all(): array
    {
        try {
            return $this->db->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();
        } catch (\Exception $e) {
            error_log('Erro ao buscar todos os usuários: ' . $e->getMessage());
            return [];
        }
    }

    public function markOnboardingComplete(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE usuarios SET onboarding_completo = 1 WHERE id = :id');
            $result = $stmt->execute(['id' => $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return true;
            }
            
            // Check if user exists
            $user = $this->findById($userId);
            if (!$user) {
                error_log("Usuário ID $userId não encontrado para marcar onboarding como completo");
                return false;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('Erro ao marcar onboarding como completo: ' . $e->getMessage());
            return false;
        }
    }

    public function updateShowOnboarding(int $userId, bool $show): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE usuarios SET mostrar_onboarding = :mostrar WHERE id = :id');
            $result = $stmt->execute(['mostrar' => $show ? 1 : 0, 'id' => $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return true;
            }
            
            // Check if user exists
            $user = $this->findById($userId);
            if (!$user) {
                error_log("Usuário ID $userId não encontrado para atualizar mostrar_onboarding");
                return false;
            }
            
            // If rowCount is 0, it might mean the value is already the same
            // Let's check the current value
            $currentValue = (bool)$user['mostrar_onboarding'];
            if ($currentValue === $show) {
                // Value is already correct, consider it a success
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('Erro ao atualizar mostrar_onboarding: ' . $e->getMessage());
            return false;
        }
    }

    public function getOnboardingStatus(int $userId): array
    {
        try {
            $user = $this->findById($userId);
            if (!$user) {
                return [
                    'onboarding_completo' => false,
                    'mostrar_onboarding' => true
                ];
            }

            return [
                'onboarding_completo' => (bool)($user['onboarding_completo'] ?? 0),
                'mostrar_onboarding' => (bool)($user['mostrar_onboarding'] ?? 1)
            ];
        } catch (\Exception $e) {
            error_log('Erro ao buscar status do onboarding: ' . $e->getMessage());
            return [
                'onboarding_completo' => false,
                'mostrar_onboarding' => true
            ];
        }
    }
}
