<?php

namespace Src\Models;

use Src\Core\Model;

class Congregacao extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM congregacoes ORDER BY nome')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO congregacoes (nome, ativa) VALUES (:nome, :ativa)'
        );
        $stmt->execute([
            'nome' => $data['nome'],
            'ativa' => $data['ativa'] ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }
}

