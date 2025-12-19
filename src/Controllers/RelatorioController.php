<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Core\PdfGenerator;

class RelatorioController extends Controller
{
    public function index(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);

        // Buscar filtros
        $mes = (int)($_GET['mes'] ?? date('n'));
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $congregacao_id = !empty($_GET['congregacao_id']) ? (int)$_GET['congregacao_id'] : null;
        $cidade = trim($_GET['cidade'] ?? '');
        $bairro = trim($_GET['bairro'] ?? '');
        $celula_id = !empty($_GET['celula_id']) ? (int)$_GET['celula_id'] : null;

        // Construir query com filtros
        $where = ['YEAR(r.data) = :ano', 'MONTH(r.data) = :mes'];
        $params = ['ano' => $ano, 'mes' => $mes];

        if ($congregacao_id) {
            $where[] = 'c.congregacao_id = :congregacao_id';
            $params['congregacao_id'] = $congregacao_id;
        }
        if ($cidade) {
            $where[] = 'c.cidade = :cidade';
            $params['cidade'] = $cidade;
        }
        if ($bairro) {
            $where[] = 'c.bairro = :bairro';
            $params['bairro'] = $bairro;
        }
        if ($celula_id) {
            $where[] = 'r.celula_id = :celula_id';
            $params['celula_id'] = $celula_id;
        }

        $whereClause = implode(' AND ', $where);

        // Estatísticas gerais
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

        // Por cidade
        $porCidade = $db->prepare(
            "SELECT c.cidade,
                COUNT(*) AS total_reunioes,
                SUM(r.cadastrados) AS total_cadastrados,
                AVG(r.presentes) AS media_presentes,
                SUM(r.visitantes) AS total_visitantes,
                SUM(r.aceitacao) AS total_aceitacoes
             FROM reunioes r
             INNER JOIN celulas c ON c.id = r.celula_id
             WHERE {$whereClause} AND c.cidade IS NOT NULL AND c.cidade != ''
             GROUP BY c.cidade
             ORDER BY total_reunioes DESC"
        );
        $porCidade->execute($params);

        // Por bairro
        $porBairro = $db->prepare(
            "SELECT c.bairro,
                COUNT(*) AS total_reunioes,
                SUM(r.cadastrados) AS total_cadastrados,
                AVG(r.presentes) AS media_presentes,
                SUM(r.visitantes) AS total_visitantes,
                SUM(r.aceitacao) AS total_aceitacoes
             FROM reunioes r
             INNER JOIN celulas c ON c.id = r.celula_id
             WHERE {$whereClause} AND c.bairro IS NOT NULL AND c.bairro != ''
             GROUP BY c.bairro
             ORDER BY total_reunioes DESC"
        );
        $porBairro->execute($params);

        // Por célula
        $porCelula = $db->prepare(
            "SELECT r.nome_celula,
                COUNT(*) AS total_reunioes,
                SUM(r.cadastrados) AS total_cadastrados,
                AVG(r.presentes) AS media_presentes,
                SUM(r.visitantes) AS total_visitantes,
                SUM(r.aceitacao) AS total_aceitacoes
             FROM reunioes r
             WHERE {$whereClause}
             GROUP BY r.nome_celula
             ORDER BY total_reunioes DESC"
        );
        $porCelula->execute($params);

        // Buscar opções para filtros
        $congregacoes = $db->query('SELECT * FROM congregacoes ORDER BY nome')->fetchAll();
        $cidades = $db->query('SELECT DISTINCT cidade FROM celulas WHERE cidade IS NOT NULL AND cidade != "" ORDER BY cidade')->fetchAll();
        $bairros = $db->query('SELECT DISTINCT bairro FROM celulas WHERE bairro IS NOT NULL AND bairro != "" ORDER BY bairro')->fetchAll();
        $celulas = $db->query('SELECT * FROM celulas WHERE ativa = 1 ORDER BY nome')->fetchAll();

        $this->view('admin/relatorios/index', [
            'title' => 'Relatórios',
            'user' => $auth->user(),
            'stats' => $statsData,
            'porCidade' => $porCidade->fetchAll(),
            'porBairro' => $porBairro->fetchAll(),
            'porCelula' => $porCelula->fetchAll(),
            'filtros' => [
                'mes' => $mes,
                'ano' => $ano,
                'congregacao_id' => $congregacao_id,
                'cidade' => $cidade,
                'bairro' => $bairro,
                'celula_id' => $celula_id,
            ],
            'opcoes' => [
                'congregacoes' => $congregacoes,
                'cidades' => $cidades,
                'bairros' => $bairros,
                'celulas' => $celulas,
            ],
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Relatórios', 'url' => '/admin/relatorios']
            ],
        ]);
    }

    public function exportarCsv(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            http_response_code(403);
            exit;
        }

        $db = Database::getConnection($this->config['db']);

        $mes = (int)($_GET['mes'] ?? date('n'));
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $congregacao_id = !empty($_GET['congregacao_id']) ? (int)$_GET['congregacao_id'] : null;
        $cidade = trim($_GET['cidade'] ?? '');
        $bairro = trim($_GET['bairro'] ?? '');
        $celula_id = !empty($_GET['celula_id']) ? (int)$_GET['celula_id'] : null;

        $where = ['YEAR(r.data) = :ano', 'MONTH(r.data) = :mes'];
        $params = ['ano' => $ano, 'mes' => $mes];

        if ($congregacao_id) {
            $where[] = 'c.congregacao_id = :congregacao_id';
            $params['congregacao_id'] = $congregacao_id;
        }
        if ($cidade) {
            $where[] = 'c.cidade = :cidade';
            $params['cidade'] = $cidade;
        }
        if ($bairro) {
            $where[] = 'c.bairro = :bairro';
            $params['bairro'] = $bairro;
        }
        if ($celula_id) {
            $where[] = 'r.celula_id = :celula_id';
            $params['celula_id'] = $celula_id;
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $db->prepare(
            "SELECT r.*, c.cidade, c.bairro
             FROM reunioes r
             INNER JOIN celulas c ON c.id = r.celula_id
             WHERE {$whereClause}
             ORDER BY r.data, r.horario"
        );
        $stmt->execute($params);
        $reunioes = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_' . $mes . '_' . $ano . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

        // Cabeçalho
        fputcsv($output, [
            'Data', 'Dia', 'Horário', 'Célula', 'Líder', 'Anfitrião', 'Telefone Líder',
            'Cadastrados', 'Presentes', 'Visitantes', 'MDA', 'Visitas', 'Culto Celebração',
            'Aceitação', 'Oferta', 'Cidade', 'Bairro', 'Observações'
        ], ';');

        // Dados
        foreach ($reunioes as $r) {
            fputcsv($output, [
                $r['data'],
                $r['dia_semana'],
                $r['horario'],
                $r['nome_celula'],
                $r['lider_nome'],
                $r['anfitriao_nome'],
                $r['telefone_lider'],
                $r['cadastrados'],
                $r['presentes'],
                $r['visitantes'],
                $r['mda'],
                $r['visitas'],
                $r['culto_celebracao'],
                $r['aceitacao'],
                number_format($r['oferta'], 2, ',', '.'),
                $r['cidade'] ?? '',
                $r['bairro'] ?? '',
                $r['observacoes'] ?? '',
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function exportarPdf(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            http_response_code(403);
            exit;
        }

        try {
            $pdf = new PdfGenerator();
            
            if (!$pdf->isAvailable()) {
                // Se TCPDF não estiver disponível, redirecionar com erro
                $this->redirect('/admin/relatorios?error=' . urlencode('Biblioteca PDF não disponível. Instale TCPDF em vendor/tecnickcom/tcpdf/'));
                return;
            }

            $db = Database::getConnection($this->config['db']);

            // Buscar filtros (mesmo processo do CSV)
            $mes = (int)($_GET['mes'] ?? date('n'));
            $ano = (int)($_GET['ano'] ?? date('Y'));
            $congregacao_id = !empty($_GET['congregacao_id']) ? (int)$_GET['congregacao_id'] : null;
            $cidade = trim($_GET['cidade'] ?? '');
            $bairro = trim($_GET['bairro'] ?? '');
            $celula_id = !empty($_GET['celula_id']) ? (int)$_GET['celula_id'] : null;

            $where = ['YEAR(r.data) = :ano', 'MONTH(r.data) = :mes'];
            $params = ['ano' => $ano, 'mes' => $mes];

            if ($congregacao_id) {
                $where[] = 'c.congregacao_id = :congregacao_id';
                $params['congregacao_id'] = $congregacao_id;
            }
            if ($cidade) {
                $where[] = 'c.cidade = :cidade';
                $params['cidade'] = $cidade;
            }
            if ($bairro) {
                $where[] = 'c.bairro = :bairro';
                $params['bairro'] = $bairro;
            }
            if ($celula_id) {
                $where[] = 'r.celula_id = :celula_id';
                $params['celula_id'] = $celula_id;
            }

            $whereClause = implode(' AND ', $where);

            // Estatísticas gerais
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

            // Buscar dados detalhados
            $stmt = $db->prepare(
                "SELECT r.*, c.cidade, c.bairro
                 FROM reunioes r
                 INNER JOIN celulas c ON c.id = r.celula_id
                 WHERE {$whereClause}
                 ORDER BY r.data, r.horario"
            );
            $stmt->execute($params);
            $reunioes = $stmt->fetchAll();

            // Por cidade
            $porCidade = $db->prepare(
                "SELECT c.cidade,
                    COUNT(*) AS total_reunioes,
                    SUM(r.cadastrados) AS total_cadastrados,
                    AVG(r.presentes) AS media_presentes,
                    SUM(r.visitantes) AS total_visitantes,
                    SUM(r.aceitacao) AS total_aceitacoes
                 FROM reunioes r
                 INNER JOIN celulas c ON c.id = r.celula_id
                 WHERE {$whereClause} AND c.cidade IS NOT NULL AND c.cidade != ''
                 GROUP BY c.cidade
                 ORDER BY total_reunioes DESC"
            );
            $porCidade->execute($params);

            // Por bairro
            $porBairro = $db->prepare(
                "SELECT c.bairro,
                    COUNT(*) AS total_reunioes,
                    SUM(r.cadastrados) AS total_cadastrados,
                    AVG(r.presentes) AS media_presentes,
                    SUM(r.visitantes) AS total_visitantes,
                    SUM(r.aceitacao) AS total_aceitacoes
                 FROM reunioes r
                 INNER JOIN celulas c ON c.id = r.celula_id
                 WHERE {$whereClause} AND c.bairro IS NOT NULL AND c.bairro != ''
                 GROUP BY c.bairro
                 ORDER BY total_reunioes DESC"
            );
            $porBairro->execute($params);

            // Por célula
            $porCelula = $db->prepare(
                "SELECT r.nome_celula,
                    COUNT(*) AS total_reunioes,
                    SUM(r.cadastrados) AS total_cadastrados,
                    AVG(r.presentes) AS media_presentes,
                    SUM(r.visitantes) AS total_visitantes,
                    SUM(r.aceitacao) AS total_aceitacoes
                 FROM reunioes r
                 WHERE {$whereClause}
                 GROUP BY r.nome_celula
                 ORDER BY total_reunioes DESC"
            );
            $porCelula->execute($params);

            // Nome do mês
            $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                     'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            $nomeMes = $meses[$mes] ?? $mes;

            // Gerar PDF
            $pdf->addPage();
            $pdf->setHeader(
                'Relatório Mensal de Células',
                "{$nomeMes} de {$ano}"
            );

            // Estatísticas gerais
            $pdf->addSection('Estatísticas Gerais', 12);
            $pdf->addStats(
                [
                    (int)($statsData['total_reunioes'] ?? 0),
                    (int)($statsData['total_cadastrados'] ?? 0),
                    number_format((float)($statsData['media_presentes'] ?? 0), 1, ',', '.'),
                    (int)($statsData['total_visitantes'] ?? 0),
                    (int)($statsData['total_aceitacoes'] ?? 0),
                    'R$ ' . number_format((float)($statsData['total_ofertas'] ?? 0), 2, ',', '.'),
                ],
                ['Reuniões', 'Cadastrados', 'Média Presentes', 'Visitantes', 'Aceitações', 'Ofertas']
            );

            // Por cidade
            $porCidadeData = $porCidade->fetchAll();
            if (!empty($porCidadeData)) {
                $pdf->addSection('Resultados por Cidade', 11);
                $headers = ['Cidade', 'Reuniões', 'Cadastrados', 'Média Presentes', 'Visitantes', 'Aceitações'];
                $data = [];
                foreach ($porCidadeData as $pc) {
                    $data[] = [
                        $pc['cidade'],
                        (int)$pc['total_reunioes'],
                        (int)$pc['total_cadastrados'],
                        number_format((float)$pc['media_presentes'], 1, ',', '.'),
                        (int)$pc['total_visitantes'],
                        (int)$pc['total_aceitacoes'],
                    ];
                }
                $pdf->addTable($headers, $data);
            }

            // Por bairro
            $porBairroData = $porBairro->fetchAll();
            if (!empty($porBairroData)) {
                $pdf->addSection('Resultados por Bairro', 11);
                $headers = ['Bairro', 'Reuniões', 'Cadastrados', 'Média Presentes', 'Visitantes', 'Aceitações'];
                $data = [];
                foreach ($porBairroData as $pb) {
                    $data[] = [
                        $pb['bairro'],
                        (int)$pb['total_reunioes'],
                        (int)$pb['total_cadastrados'],
                        number_format((float)$pb['media_presentes'], 1, ',', '.'),
                        (int)$pb['total_visitantes'],
                        (int)$pb['total_aceitacoes'],
                    ];
                }
                $pdf->addTable($headers, $data);
            }

            // Por célula
            $porCelulaData = $porCelula->fetchAll();
            if (!empty($porCelulaData)) {
                $pdf->addSection('Resultados por Célula', 11);
                $headers = ['Célula', 'Reuniões', 'Cadastrados', 'Média Presentes', 'Visitantes', 'Aceitações'];
                $data = [];
                foreach ($porCelulaData as $pcel) {
                    $data[] = [
                        $pcel['nome_celula'],
                        (int)$pcel['total_reunioes'],
                        (int)$pcel['total_cadastrados'],
                        number_format((float)$pcel['media_presentes'], 1, ',', '.'),
                        (int)$pcel['total_visitantes'],
                        (int)$pcel['total_aceitacoes'],
                    ];
                }
                $pdf->addTable($headers, $data);
            }

            // Detalhamento de reuniões (se não houver muitos registros)
            if (count($reunioes) <= 50) {
                $pdf->addPage();
                $pdf->addSection('Detalhamento de Reuniões', 11);
                $headers = ['Data', 'Célula', 'Presentes', 'Visitantes', 'Aceitações', 'Oferta'];
                $data = [];
                foreach ($reunioes as $r) {
                    $data[] = [
                        date('d/m/Y', strtotime($r['data'])),
                        substr($r['nome_celula'], 0, 20),
                        (int)$r['presentes'],
                        (int)$r['visitantes'],
                        (int)$r['aceitacao'],
                        'R$ ' . number_format((float)$r['oferta'], 2, ',', '.'),
                    ];
                }
                $pdf->addTable($headers, $data);
            }

            // Rodapé
            $pdf->addText("Relatório gerado em " . date('d/m/Y H:i:s'), 8, 'C');

            $filename = "relatorio_{$mes}_{$ano}.pdf";
            $pdf->output($filename);

        } catch (\Exception $e) {
            $this->redirect('/admin/relatorios?error=' . urlencode('Erro ao gerar PDF: ' . $e->getMessage()));
        }
    }
}

