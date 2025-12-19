<?php

namespace Src\Models;

use Src\Core\Model;
use PDO;

class Reuniao extends Model
{
    public function create(array $data): bool
    {
        // Regra de duplicidade
        $exists = $this->findDuplicate($data['celula_id'], $data['data'], $data['horario']);
        if ($exists) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO reunioes (
                celula_id, nome_celula, lider_nome, anfitriao_nome, telefone_lider,
                data, dia_semana, horario, cadastrados, presentes, visitantes, mda,
                visitas, culto_celebracao, aceitacao, oferta, observacoes, criado_por, criado_em
            ) VALUES (
                :celula_id, :nome_celula, :lider_nome, :anfitriao_nome, :telefone_lider,
                :data, :dia_semana, :horario, :cadastrados, :presentes, :visitantes, :mda,
                :visitas, :culto_celebracao, :aceitacao, :oferta, :observacoes, :criado_por, NOW()
            )'
        );

        return $stmt->execute([
            'celula_id' => $data['celula_id'],
            'nome_celula' => $data['nome_celula'],
            'lider_nome' => $data['lider_nome'],
            'anfitriao_nome' => $data['anfitriao_nome'],
            'telefone_lider' => $data['telefone_lider'],
            'data' => $data['data'],
            'dia_semana' => $data['dia_semana'],
            'horario' => $data['horario'],
            'cadastrados' => $data['cadastrados'],
            'presentes' => $data['presentes'],
            'visitantes' => $data['visitantes'],
            'mda' => $data['mda'],
            'visitas' => $data['visitas'],
            'culto_celebracao' => $data['culto_celebracao'],
            'aceitacao' => $data['aceitacao'],
            'oferta' => $data['oferta'],
            'observacoes' => $data['observacoes'],
            'criado_por' => $data['criado_por'],
        ]);
    }

    public function findDuplicate(int $celulaId, string $data, string $horario): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM reunioes WHERE celula_id = :celula_id AND data = :data AND horario = :horario LIMIT 1'
        );
        $stmt->execute([
            'celula_id' => $celulaId,
            'data' => $data,
            'horario' => $horario,
        ]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function latestByUser(int $userId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*
             FROM reunioes r
             INNER JOIN celulas c ON c.id = r.celula_id
             INNER JOIN lideres l ON l.id = c.lider_id
             WHERE l.usuario_id = :userId
             ORDER BY r.data DESC, r.horario DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

