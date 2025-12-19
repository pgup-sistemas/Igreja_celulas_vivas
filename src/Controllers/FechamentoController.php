<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Core\Logger;

class FechamentoController extends Controller
{
    public function index(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);
        $fechamentos = $db->query(
            'SELECT f.*, u1.nome as fechado_por_nome, u2.nome as reaberto_por_nome
             FROM fechamentos_mensais f
             LEFT JOIN usuarios u1 ON u1.id = f.fechado_por
             LEFT JOIN usuarios u2 ON u2.id = f.reaberto_por
             ORDER BY f.ano DESC, f.mes DESC'
        )->fetchAll();

        $this->view('admin/fechamentos/index', [
            'title' => 'Fechamento Mensal',
            'user' => $auth->user(),
            'fechamentos' => $fechamentos,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Fechamentos', 'url' => '/admin/fechamentos']
            ],
        ]);
    }

    public function fechar(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $logger = new Logger(__DIR__ . '/../../storage/logs');
        $db = Database::getConnection($this->config['db']);
        $mes = (int)($_POST['mes'] ?? 0);
        $ano = (int)($_POST['ano'] ?? 0);

        if ($mes < 1 || $mes > 12 || $ano < 2020 || $ano > 2100) {
            $this->redirect('/admin/fechamentos?error=' . urlencode('Mês/ano inválidos'));
        }

        try {
            // Verificar se já existe
            $stmt = $db->prepare('SELECT id, fechado FROM fechamentos_mensais WHERE mes = :mes AND ano = :ano');
            $stmt->execute(['mes' => $mes, 'ano' => $ano]);
            $existente = $stmt->fetch();

            if ($existente) {
                if ($existente['fechado']) {
                    $this->redirect('/admin/fechamentos?error=' . urlencode('Mês já está fechado'));
                } else {
                    // Atualizar para fechado
                    $stmt = $db->prepare(
                        'UPDATE fechamentos_mensais SET fechado = 1, fechado_por = :user_id, fechado_em = NOW()
                         WHERE id = :id'
                    );
                    $stmt->execute(['id' => $existente['id'], 'user_id' => $auth->user()['id']]);
                }
            } else {
                // Criar novo fechamento
                $stmt = $db->prepare(
                    'INSERT INTO fechamentos_mensais (mes, ano, fechado, fechado_por, fechado_em)
                     VALUES (:mes, :ano, 1, :user_id, NOW())'
                );
                $stmt->execute([
                    'mes' => $mes,
                    'ano' => $ano,
                    'user_id' => $auth->user()['id'],
                ]);
            }

            $logger->info('Mês fechado', ['mes' => $mes, 'ano' => $ano, 'user_id' => $auth->user()['id']]);
            $this->redirect('/admin/fechamentos?success=' . urlencode("Mês {$mes}/{$ano} fechado com sucesso"));
        } catch (\Exception $e) {
            $logger->error('Erro ao fechar mês', ['error' => $e->getMessage()]);
            $this->redirect('/admin/fechamentos?error=' . urlencode('Erro ao fechar mês'));
        }
    }

    public function reabrir(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $logger = new Logger(__DIR__ . '/../../storage/logs');
        $db = Database::getConnection($this->config['db']);
        $mes = (int)($_POST['mes'] ?? 0);
        $ano = (int)($_POST['ano'] ?? 0);

        try {
            $stmt = $db->prepare(
                'UPDATE fechamentos_mensais SET fechado = 0, reaberto_por = :user_id, reaberto_em = NOW()
                 WHERE mes = :mes AND ano = :ano'
            );
            $stmt->execute([
                'mes' => $mes,
                'ano' => $ano,
                'user_id' => $auth->user()['id'],
            ]);

            if ($stmt->rowCount() === 0) {
                $this->redirect('/admin/fechamentos?error=' . urlencode('Fechamento não encontrado'));
            }

            $logger->info('Mês reaberto', ['mes' => $mes, 'ano' => $ano, 'user_id' => $auth->user()['id']]);
            $this->redirect('/admin/fechamentos?success=' . urlencode("Mês {$mes}/{$ano} reaberto com sucesso"));
        } catch (\Exception $e) {
            $logger->error('Erro ao reabrir mês', ['error' => $e->getMessage()]);
            $this->redirect('/admin/fechamentos?error=' . urlencode('Erro ao reabrir mês'));
        }
    }

    public static function isMesFechado(\PDO $db, int $mes, int $ano): bool
    {
        $stmt = $db->prepare('SELECT fechado FROM fechamentos_mensais WHERE mes = :mes AND ano = :ano AND fechado = 1 LIMIT 1');
        $stmt->execute(['mes' => $mes, 'ano' => $ano]);
        return (bool)$stmt->fetch();
    }
}

