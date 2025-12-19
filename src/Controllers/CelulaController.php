<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Core\Logger;
use Src\Models\Celula;
use Src\Models\Congregacao;

class CelulaController extends Controller
{
    public function index(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);
        $celulaModel = new Celula($db);
        $celulas = $celulaModel->allWithDetails();

        $this->view('admin/celulas/index', [
            'title' => 'Gerenciar Células',
            'user' => $auth->user(),
            'celulas' => $celulas,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Células', 'url' => '/admin/celulas']
            ],
        ]);
    }

    public function create(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);
        $congregacaoModel = new Congregacao($db);
        $congregacoes = $congregacaoModel->all();

        // Buscar líderes disponíveis da tabela lideres
        try {
            $stmt = $db->query('SELECT l.*, u.nome as usuario_nome FROM lideres l INNER JOIN usuarios u ON u.id = l.usuario_id WHERE u.ativo = 1 ORDER BY l.nome');
            $lideres = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $lideres = [];
        }
        
        // Se não houver líderes na tabela lideres, verificar se há usuários com perfil 'lider' e criar automaticamente
        if (empty($lideres)) {
            try {
                $stmt = $db->query("SELECT u.id, u.nome FROM usuarios u WHERE u.perfil = 'lider' AND u.ativo = 1 ORDER BY u.nome");
                $usuariosLider = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Criar líderes automaticamente para todos os usuários com perfil 'lider'
                foreach ($usuariosLider as $usuario) {
                    try {
                        $stmt = $db->prepare('INSERT INTO lideres (nome, usuario_id) VALUES (:nome, :usuario_id)');
                        $stmt->execute([
                            'nome' => $usuario['nome'],
                            'usuario_id' => $usuario['id']
                        ]);
                    } catch (\PDOException $e) {
                        // Ignorar se já existir (duplicidade)
                    }
                }
                
                // Buscar novamente após criar
                $stmt = $db->query('SELECT l.*, u.nome as usuario_nome FROM lideres l INNER JOIN usuarios u ON u.id = l.usuario_id WHERE u.ativo = 1 ORDER BY l.nome');
                $lideres = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $lideres = [];
            }
        }
        
        // Garantir que $lideres é sempre um array
        if (!is_array($lideres)) {
            $lideres = [];
        }

        $this->view('admin/celulas/form', [
            'title' => 'Nova Célula',
            'user' => $auth->user(),
            'celula' => null,
            'congregacoes' => $congregacoes,
            'lideres' => $lideres,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Células', 'url' => '/admin/celulas'],
                ['label' => 'Nova', 'url' => '/admin/celulas/novo']
            ],
        ]);
    }

    public function store(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $logger = new Logger(__DIR__ . '/../../storage/logs');
        $db = Database::getConnection($this->config['db']);
        $celulaModel = new Celula($db);

        $nome = trim($_POST['nome'] ?? '');
        $congregacao_id = !empty($_POST['congregacao_id']) ? (int)$_POST['congregacao_id'] : null;
        $lider_id = !empty($_POST['lider_id']) ? (int)$_POST['lider_id'] : null;
        $cidade = trim($_POST['cidade'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $zona = trim($_POST['zona'] ?? '');
        $ponto_referencia = trim($_POST['ponto_referencia'] ?? '');
        $ativa = isset($_POST['ativa']) ? 1 : 0;

        if (empty($nome)) {
            $logger->warning('Tentativa de criar célula sem nome', ['user_id' => $auth->user()['id']]);
            $this->redirect('/admin/celulas/novo?error=' . urlencode('O nome é obrigatório'));
        }

        // Validar foreign keys se fornecidos
        if ($congregacao_id) {
            $stmt = $db->prepare('SELECT id FROM congregacoes WHERE id = :id');
            $stmt->execute(['id' => $congregacao_id]);
            if (!$stmt->fetch()) {
                $this->redirect('/admin/celulas/novo?error=' . urlencode('Congregação inválida'));
            }
        }

        if ($lider_id) {
            // Verificar se é um líder válido na tabela lideres
            $stmt = $db->prepare('SELECT id FROM lideres WHERE id = :id');
            $stmt->execute(['id' => $lider_id]);
            $liderExiste = $stmt->fetch();
            
            // Se não existir na tabela lideres, pode ser que esteja usando ID de usuário
            // Nesse caso, criar o líder automaticamente
            if (!$liderExiste) {
                // Verificar se é um usuário com perfil lider
                $stmt = $db->prepare("SELECT id, nome FROM usuarios WHERE id = :id AND perfil = 'lider' AND ativo = 1");
                $stmt->execute(['id' => $lider_id]);
                $usuario = $stmt->fetch();
                
                if ($usuario) {
                    // Criar líder automaticamente
                    $stmt = $db->prepare('INSERT INTO lideres (nome, usuario_id) VALUES (:nome, :usuario_id)');
                    $stmt->execute([
                        'nome' => $usuario['nome'],
                        'usuario_id' => $usuario['id']
                    ]);
                    $lider_id = (int)$db->lastInsertId();
                    $logger->info('Líder criado automaticamente', ['usuario_id' => $usuario['id'], 'lider_id' => $lider_id]);
                } else {
                    $this->redirect('/admin/celulas/novo?error=' . urlencode('Líder inválido ou não encontrado'));
                }
            }
        }

        try {
            $celulaModel->create([
                'nome' => $nome,
                'congregacao_id' => $congregacao_id,
                'lider_id' => $lider_id,
                'cidade' => $cidade,
                'bairro' => $bairro,
                'zona' => $zona,
                'ponto_referencia' => $ponto_referencia,
                'ativa' => $ativa,
            ]);

            $logger->info('Célula criada', ['nome' => $nome, 'criado_por' => $auth->user()['id']]);
            $this->redirect('/admin/celulas?success=' . urlencode('Célula criada com sucesso'));
        } catch (\PDOException $e) {
            $errorMsg = $e->getMessage();
            // Mensagens mais amigáveis
            if (strpos($errorMsg, 'foreign key constraint') !== false) {
                if (strpos($errorMsg, 'congregacao_id') !== false) {
                    $errorMsg = 'Congregação inválida';
                } elseif (strpos($errorMsg, 'lider_id') !== false) {
                    $errorMsg = 'Líder inválido. Certifique-se de que o líder está cadastrado na tabela lideres.';
                } else {
                    $errorMsg = 'Erro de referência: ' . $errorMsg;
                }
            }
            $logger->error('Erro ao criar célula', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->redirect('/admin/celulas/novo?error=' . urlencode($errorMsg));
        } catch (\Exception $e) {
            $logger->error('Erro ao criar célula', ['error' => $e->getMessage()]);
            $this->redirect('/admin/celulas/novo?error=' . urlencode('Erro ao criar célula: ' . $e->getMessage()));
        }
    }

    public function edit(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getConnection($this->config['db']);
        $stmt = $db->prepare('SELECT * FROM celulas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $celula = $stmt->fetch();

        if (!$celula) {
            $this->redirect('/admin/celulas?error=' . urlencode('Célula não encontrada'));
        }

        $congregacaoModel = new Congregacao($db);
        $congregacoes = $congregacaoModel->all();

        // Buscar líderes disponíveis da tabela lideres
        try {
            $stmt = $db->query('SELECT l.*, u.nome as usuario_nome FROM lideres l INNER JOIN usuarios u ON u.id = l.usuario_id WHERE u.ativo = 1 ORDER BY l.nome');
            $lideres = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $lideres = [];
        }
        
        // Se não houver líderes na tabela lideres, verificar se há usuários com perfil 'lider' e criar automaticamente
        if (empty($lideres)) {
            try {
                $stmt = $db->query("SELECT u.id, u.nome FROM usuarios u WHERE u.perfil = 'lider' AND u.ativo = 1 ORDER BY u.nome");
                $usuariosLider = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Criar líderes automaticamente para todos os usuários com perfil 'lider'
                foreach ($usuariosLider as $usuario) {
                    try {
                        $stmt = $db->prepare('INSERT INTO lideres (nome, usuario_id) VALUES (:nome, :usuario_id)');
                        $stmt->execute([
                            'nome' => $usuario['nome'],
                            'usuario_id' => $usuario['id']
                        ]);
                    } catch (\PDOException $e) {
                        // Ignorar se já existir (duplicidade)
                    }
                }
                
                // Buscar novamente após criar
                $stmt = $db->query('SELECT l.*, u.nome as usuario_nome FROM lideres l INNER JOIN usuarios u ON u.id = l.usuario_id WHERE u.ativo = 1 ORDER BY l.nome');
                $lideres = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $lideres = [];
            }
        }
        
        // Garantir que $lideres é sempre um array
        if (!is_array($lideres)) {
            $lideres = [];
        }

        $this->view('admin/celulas/form', [
            'title' => 'Editar Célula',
            'user' => $auth->user(),
            'celula' => $celula,
            'congregacoes' => $congregacoes,
            'lideres' => $lideres,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Células', 'url' => '/admin/celulas'],
                ['label' => 'Editar', 'url' => '/admin/celulas/edit']
            ],
        ]);
    }

    public function update(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $logger = new Logger(__DIR__ . '/../../storage/logs');
        $db = Database::getConnection($this->config['db']);
        $id = (int)($_POST['id'] ?? 0);

        $nome = trim($_POST['nome'] ?? '');
        $congregacao_id = !empty($_POST['congregacao_id']) ? (int)$_POST['congregacao_id'] : null;
        $lider_id = !empty($_POST['lider_id']) ? (int)$_POST['lider_id'] : null;
        $cidade = trim($_POST['cidade'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $zona = trim($_POST['zona'] ?? '');
        $ponto_referencia = trim($_POST['ponto_referencia'] ?? '');
        $ativa = isset($_POST['ativa']) ? 1 : 0;

        if (empty($nome)) {
            $this->redirect('/admin/celulas/edit?id=' . $id . '&error=' . urlencode('O nome é obrigatório'));
        }

        try {
            $stmt = $db->prepare(
                'UPDATE celulas SET nome = :nome, congregacao_id = :congregacao_id, lider_id = :lider_id,
                 cidade = :cidade, bairro = :bairro, zona = :zona, ponto_referencia = :ponto_referencia, ativa = :ativa
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $id,
                'nome' => $nome,
                'congregacao_id' => $congregacao_id,
                'lider_id' => $lider_id,
                'cidade' => $cidade,
                'bairro' => $bairro,
                'zona' => $zona,
                'ponto_referencia' => $ponto_referencia,
                'ativa' => $ativa,
            ]);

            $logger->info('Célula atualizada', ['id' => $id, 'atualizado_por' => $auth->user()['id']]);
            $this->redirect('/admin/celulas?success=' . urlencode('Célula atualizada com sucesso'));
        } catch (\Exception $e) {
            $logger->error('Erro ao atualizar célula', ['error' => $e->getMessage()]);
            $this->redirect('/admin/celulas/edit?id=' . $id . '&error=' . urlencode('Erro ao atualizar célula'));
        }
    }
}

