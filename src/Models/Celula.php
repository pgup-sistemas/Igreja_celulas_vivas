<?php

namespace Src\Models;

use Src\Core\Model;

class Celula extends Model
{
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.* FROM celulas c
             INNER JOIN lideres l ON l.id = c.lider_id
             WHERE l.usuario_id = :userId AND c.ativa = 1
             ORDER BY c.nome'
        );
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM celulas WHERE ativa = 1 ORDER BY nome')->fetchAll();
    }

    public function allWithDetails(): array
    {
        return $this->db->query(
            'SELECT c.*, cong.nome as congregacao_nome, l.nome as lider_nome
             FROM celulas c
             LEFT JOIN congregacoes cong ON cong.id = c.congregacao_id
             LEFT JOIN lideres l ON l.id = c.lider_id
             ORDER BY c.nome'
        )->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO celulas (nome, congregacao_id, lider_id, cidade, bairro, zona, ponto_referencia, ativa)
             VALUES (:nome, :congregacao_id, :lider_id, :cidade, :bairro, :zona, :ponto_referencia, :ativa)'
        );
        
        // Converter strings vazias para NULL
        $congregacao_id = !empty($data['congregacao_id']) ? (int)$data['congregacao_id'] : null;
        $lider_id = !empty($data['lider_id']) ? (int)$data['lider_id'] : null;
        $cidade = !empty(trim($data['cidade'] ?? '')) ? trim($data['cidade']) : null;
        $bairro = !empty(trim($data['bairro'] ?? '')) ? trim($data['bairro']) : null;
        $zona = !empty(trim($data['zona'] ?? '')) ? trim($data['zona']) : null;
        $ponto_referencia = !empty(trim($data['ponto_referencia'] ?? '')) ? trim($data['ponto_referencia']) : null;
        
        $stmt->execute([
            'nome' => trim($data['nome']),
            'congregacao_id' => $congregacao_id,
            'lider_id' => $lider_id,
            'cidade' => $cidade,
            'bairro' => $bairro,
            'zona' => $zona,
            'ponto_referencia' => $ponto_referencia,
            'ativa' => $data['ativa'] ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }
}

