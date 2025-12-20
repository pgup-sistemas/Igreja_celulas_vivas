<?php

namespace Src\Models;

use Src\Core\Model;
use PDO;

class Lider extends Model
{
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lideres WHERE usuario_id = :usuario_id LIMIT 1');
        $stmt->execute(['usuario_id' => $userId]);
        $lider = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lider ?: null;
    }

    public function findByTelefone(string $telefone): ?array
    {
        $stmt = $this->db->prepare('SELECT l.*, u.nome as usuario_nome, u.email FROM lideres l JOIN usuarios u ON l.usuario_id = u.id WHERE l.telefone = :telefone LIMIT 1');
        $stmt->execute(['telefone' => $telefone]);
        $lider = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lider ?: null;
    }
}