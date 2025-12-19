<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Core\Logger;
use Src\Models\Congregacao;

class CongregacaoController extends Controller
{
    public function index(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);
        $congregacaoModel = new Congregacao($db);
        $congregacoes = $congregacaoModel->all();

        $this->view('admin/congregacoes/index', [
            'title' => 'Gerenciar Congregações',
            'user' => $auth->user(),
            'congregacoes' => $congregacoes,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Congregações', 'url' => '/admin/congregacoes']
            ],
        ]);
    }

    public function create(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $this->view('admin/congregacoes/form', [
            'title' => 'Nova Congregação',
            'user' => $auth->user(),
            'congregacao' => null,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Congregações', 'url' => '/admin/congregacoes'],
                ['label' => 'Nova', 'url' => '/admin/congregacoes/novo']
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
        $congregacaoModel = new Congregacao($db);

        $nome = trim($_POST['nome'] ?? '');
        $ativa = isset($_POST['ativa']) ? 1 : 0;

        if (empty($nome)) {
            $logger->warning('Tentativa de criar congregação sem nome', ['user_id' => $auth->user()['id']]);
            $this->redirect('/admin/congregacoes/novo?error=' . urlencode('O nome é obrigatório'));
        }

        try {
            $congregacaoModel->create(['nome' => $nome, 'ativa' => $ativa]);
            $logger->info('Congregação criada', ['nome' => $nome, 'criado_por' => $auth->user()['id']]);
            $this->redirect('/admin/congregacoes?success=' . urlencode('Congregação criada com sucesso'));
        } catch (\Exception $e) {
            $logger->error('Erro ao criar congregação', ['error' => $e->getMessage()]);
            $this->redirect('/admin/congregacoes/novo?error=' . urlencode('Erro ao criar congregação'));
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
        $stmt = $db->prepare('SELECT * FROM congregacoes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $congregacao = $stmt->fetch();

        if (!$congregacao) {
            $this->redirect('/admin/congregacoes?error=' . urlencode('Congregação não encontrada'));
        }

        $this->view('admin/congregacoes/form', [
            'title' => 'Editar Congregação',
            'user' => $auth->user(),
            'congregacao' => $congregacao,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Congregações', 'url' => '/admin/congregacoes'],
                ['label' => 'Editar', 'url' => '/admin/congregacoes/edit']
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
        $ativa = isset($_POST['ativa']) ? 1 : 0;

        if (empty($nome)) {
            $this->redirect('/admin/congregacoes/edit?id=' . $id . '&error=' . urlencode('O nome é obrigatório'));
        }

        try {
            $stmt = $db->prepare('UPDATE congregacoes SET nome = :nome, ativa = :ativa WHERE id = :id');
            $stmt->execute(['id' => $id, 'nome' => $nome, 'ativa' => $ativa]);

            $logger->info('Congregação atualizada', ['id' => $id, 'atualizado_por' => $auth->user()['id']]);
            $this->redirect('/admin/congregacoes?success=' . urlencode('Congregação atualizada com sucesso'));
        } catch (\Exception $e) {
            $logger->error('Erro ao atualizar congregação', ['error' => $e->getMessage()]);
            $this->redirect('/admin/congregacoes/edit?id=' . $id . '&error=' . urlencode('Erro ao atualizar congregação'));
        }
    }
}

