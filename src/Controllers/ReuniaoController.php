<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Core\Logger;
use Src\Controllers\FechamentoController;
use Src\Models\Celula;
use Src\Models\Reuniao;

class ReuniaoController extends Controller
{
    public function form(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            $this->redirect('/login');
        }

        $user = $auth->user();
        $db = Database::getConnection($this->config['db']);
        $celulaModel = new Celula($db);
        $celulas = ($user['perfil'] === 'lider')
            ? $celulaModel->findByUser($user['id'])
            : $celulaModel->all();

        // Recuperar dados do formulário da sessão (se houver erro)
        $formData = [];
        if (isset($_SESSION['reuniao_form_data'])) {
            $formData = $_SESSION['reuniao_form_data'];
            unset($_SESSION['reuniao_form_data']); // Limpar após usar
        }

        $this->view('reunioes/form', [
            'title' => 'Registrar reunião',
            'celulas' => $celulas,
            'user' => $user,
            'error' => $_GET['error'] ?? null,
            'success' => $_GET['success'] ?? null,
            'formData' => $formData,
            'breadcrumb' => [
                ['label' => $user['perfil'] === 'admin' ? 'Dashboard' : 'Home', 'url' => $user['perfil'] === 'admin' ? '/admin' : '/home'],
                ['label' => 'Nova Reunião', 'url' => '/reunioes/novo']
            ],
        ]);
    }

    public function store(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            $this->redirect('/login');
        }

        $logger = new Logger(__DIR__ . '/../../storage/logs');
        $user = $auth->user();
        $db = Database::getConnection($this->config['db']);
        $reuniaoModel = new Reuniao($db);

        $data = $this->sanitizeInput($_POST, $user);

        // Validar se o mês está fechado
        $dataReuniao = \DateTime::createFromFormat('Y-m-d', $data['data']);
        if ($dataReuniao) {
            $mes = (int)$dataReuniao->format('n');
            $ano = (int)$dataReuniao->format('Y');
            if (FechamentoController::isMesFechado($db, $mes, $ano)) {
                $logger->warning('Tentativa de registrar reunião em mês fechado', [
                    'user_id' => $user['id'],
                    'data' => $data['data'],
                    'mes' => $mes,
                    'ano' => $ano,
                ]);
                // Salvar dados do formulário na sessão
                $_SESSION['reuniao_form_data'] = $data;
                $this->redirect('/reunioes/novo?error=' . urlencode("O mês {$mes}/{$ano} está fechado. Entre em contato com o administrador."));
            }
        }

        // Validações detalhadas
        $erros = [];
        if ($data['presentes'] > $data['cadastrados']) {
            $erros[] = 'O número de presentes não pode ser maior que o de cadastrados';
        }
        if ($data['aceitacao'] > $data['visitantes']) {
            $erros[] = 'O número de aceitações não pode ser maior que o de visitantes';
        }
        if ($data['oferta'] < 0) {
            $erros[] = 'A oferta não pode ser negativa';
        }
        if ($data['cadastrados'] < 0 || $data['presentes'] < 0 || $data['visitantes'] < 0
            || $data['mda'] < 0 || $data['visitas'] < 0 || $data['culto_celebracao'] < 0 || $data['aceitacao'] < 0) {
            $erros[] = 'Nenhum campo numérico pode ser negativo';
        }
        if (empty($data['celula_id'])) {
            $erros[] = 'Selecione uma célula';
        }
        if (empty($data['data'])) {
            $erros[] = 'Informe a data da reunião';
        }
        if (empty($data['horario'])) {
            $erros[] = 'Informe o horário da reunião';
        }

        if (!empty($erros)) {
            $logger->warning('Validações falharam ao registrar reunião', [
                'user_id' => $user['id'],
                'erros' => $erros,
            ]);
            // Salvar dados do formulário na sessão para preservar após redirect
            $_SESSION['reuniao_form_data'] = $data;
            $this->redirect('/reunioes/novo?error=' . urlencode(implode('. ', $erros)));
        }

        $data['dia_semana'] = $this->dayOfWeek($data['data']);

        try {
            $created = $reuniaoModel->create($data);

            if (!$created) {
                $logger->warning('Tentativa de criar reunião duplicada', [
                    'user_id' => $user['id'],
                    'celula_id' => $data['celula_id'],
                    'data' => $data['data'],
                    'horario' => $data['horario'],
                ]);
                // Salvar dados do formulário na sessão
                $_SESSION['reuniao_form_data'] = $data;
                $this->redirect('/reunioes/novo?error=' . urlencode('Já existe uma reunião registrada para esta célula, data e horário. Verifique os dados e tente novamente.'));
            }

            $logger->info('Reunião registrada com sucesso', [
                'user_id' => $user['id'],
                'celula_id' => $data['celula_id'],
                'data' => $data['data'],
            ]);

            $this->redirect('/reunioes/novo?success=' . urlencode('Reunião registrada com sucesso!'));
        } catch (\Exception $e) {
            $logger->error('Erro ao registrar reunião', [
                'user_id' => $user['id'],
                'error' => $e->getMessage(),
            ]);
            // Salvar dados do formulário na sessão
            $_SESSION['reuniao_form_data'] = $data;
            $this->redirect('/reunioes/novo?error=' . urlencode('Erro ao registrar reunião. Tente novamente.'));
        }
    }

    private function sanitizeInput(array $input, array $user): array
    {
        $fields = [
            'celula_id', 'lider_nome', 'anfitriao_nome', 'telefone_lider',
            'data', 'horario', 'cadastrados', 'presentes', 'visitantes', 'mda',
            'visitas', 'culto_celebracao', 'aceitacao', 'oferta', 'observacoes',
        ];

        $data = [];
        foreach ($fields as $field) {
            $value = $input[$field] ?? null;
            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        // Buscar nome da célula
        if (!empty($data['celula_id'])) {
            $db = Database::getConnection($this->config['db']);
            $stmt = $db->prepare('SELECT nome FROM celulas WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $data['celula_id']]);
            $celula = $stmt->fetch();
            $data['nome_celula'] = $celula ? $celula['nome'] : '';
        } else {
            $data['nome_celula'] = '';
        }

        // Garantir numéricos com padrão 0
        foreach (['cadastrados', 'presentes', 'visitantes', 'mda', 'visitas', 'culto_celebracao', 'aceitacao'] as $f) {
            $data[$f] = max(0, (int)($data[$f] ?? 0));
        }
        $data['oferta'] = max(0, (float)($data['oferta'] ?? 0));

        $data['dia_semana'] = '';
        $data['criado_por'] = $user['id'];

        return $data;
    }

    private function dayOfWeek(string $date): string
    {
        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        $timestamp = strtotime($date);
        return $dias[(int)date('w', $timestamp)] ?? '';
    }

    public function getLideresByCelula(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado']);
            exit;
        }

        $celulaId = (int)($_GET['celula_id'] ?? 0);
        
        if (!$celulaId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID da célula não fornecido']);
            exit;
        }

        try {
            $db = Database::getConnection($this->config['db']);
            
            // Buscar o líder da célula
            $stmt = $db->prepare(
                'SELECT l.id, l.nome, l.telefone, u.nome as usuario_nome
                 FROM celulas c
                 INNER JOIN lideres l ON l.id = c.lider_id
                 INNER JOIN usuarios u ON u.id = l.usuario_id
                 WHERE c.id = :celula_id AND c.ativa = 1 AND u.ativo = 1
                 LIMIT 1'
            );
            $stmt->execute(['celula_id' => $celulaId]);
            $lider = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($lider) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'lider' => [
                        'id' => (int)$lider['id'],
                        'nome' => $lider['nome'],
                        'telefone' => $lider['telefone'] ?? '',
                        'usuario_nome' => $lider['usuario_nome'] ?? ''
                    ]
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Célula não encontrada ou sem líder vinculado'
                ]);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao buscar líder: ' . $e->getMessage()]);
        }
        exit;
    }
}

