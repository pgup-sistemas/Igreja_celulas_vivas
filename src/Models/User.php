<?php

namespace Src\Models;

use Src\Core\Model;
use PDO;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, email, senha, perfil, ativo, data_criacao)
             VALUES (:nome, :email, :senha, :perfil, :ativo, NOW())'
        );
        $stmt->execute([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
            'perfil' => $data['perfil'],
            'ativo' => $data['ativo'] ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();
    }
}

