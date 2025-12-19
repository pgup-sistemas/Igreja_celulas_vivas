<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Models\Congregacao;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            $this->redirect('/login');
        }

        $user = $auth->user();
        if ($user['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);

        // Buscar filtros
        $mes = !empty($_GET['mes']) ? (int)$_GET['mes'] : null;
        $ano = !empty($_GET['ano']) ? (int)$_GET['ano'] : null;
        $congregacao_id = !empty($_GET['congregacao_id']) ? (int)$_GET['congregacao_id'] : null;

        // Se não houver filtro de mês/ano, buscar o último mês com dados
        if (!$mes || !$ano) {
            $stmt = $db->query('SELECT YEAR(data) as ano, MONTH(data) as mes FROM reunioes ORDER BY data DESC LIMIT 1');
            $ultimoMes = $stmt->fetch();
            if ($ultimoMes) {
                $ano = $ano ?: (int)$ultimoMes['ano'];
                $mes = $mes ?: (int)$ultimoMes['mes'];
            } else {
                // Se não houver dados, usar mês/ano atual
                $ano = $ano ?: (int)date('Y');
                $mes = $mes ?: (int)date('n');
            }
        }

        // Construir query com filtros
        $where = ['YEAR(r.data) = :ano', 'MONTH(r.data) = :mes'];
        $params = ['ano' => $ano, 'mes' => $mes];

        if ($congregacao_id) {
            $where[] = 'c.congregacao_id = :congregacao_id';
            $params['congregacao_id'] = $congregacao_id;
        }

        $whereClause = implode(' AND ', $where);

        $stats = $db->prepare(
            "SELECT
                COUNT(*) AS total_reunioes,
                SUM(r.cadastrados) AS total_cadastrados,
                AVG(r.presentes) AS media_presentes,
                SUM(r.visitantes) AS total_visitantes,
                SUM(r.aceitacao) AS total_aceitacoes,
                SUM(r.oferta) AS total_ofertas
             FROM reunioes r
             INNER JOIN celulas c ON c.id = r.celula_id
             WHERE {$whereClause}"
        );
        $stats->execute($params);
        $statsData = $stats->fetch();

        // Buscar congregações para o filtro
        $congregacaoModel = new Congregacao($db);
        $congregacoes = $congregacaoModel->all();

        $this->view('admin/dashboard', [
            'title' => 'Painel Admin',
            'user' => $user,
            'stats' => $statsData,
            'congregacoes' => $congregacoes,
            'filtros' => [
                'mes' => $mes,
                'ano' => $ano,
                'congregacao_id' => $congregacao_id,
            ],
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin']
            ],
        ]);
    }
}

