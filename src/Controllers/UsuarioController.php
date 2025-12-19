<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Core\Logger;
use Src\Models\User;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $db = Database::getConnection($this->config['db']);
        $userModel = new User($db);
        $usuarios = $userModel->all();

        $this->view('admin/usuarios/index', [
            'title' => 'Gerenciar Usuários',
            'user' => $auth->user(),
            'usuarios' => $usuarios,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Usuários', 'url' => '/admin/usuarios']
            ],
        ]);
    }

    public function create(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $this->view('admin/usuarios/form', [
            'title' => 'Novo Usuário',
            'user' => $auth->user(),
            'usuario' => null,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Usuários', 'url' => '/admin/usuarios'],
                ['label' => 'Novo', 'url' => '/admin/usuarios/novo']
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
        $userModel = new User($db);

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $perfil = $_POST['perfil'] ?? 'lider';
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (empty($nome) || empty($email) || empty($senha)) {
            $logger->warning('Tentativa de criar usuário com dados incompletos', ['user_id' => $auth->user()['id']]);
            $this->redirect('/admin/usuarios/novo?error=' . urlencode('Preencha todos os campos obrigatórios'));
        }

        if (strlen($senha) < 6) {
            $logger->warning('Tentativa de criar usuário com senha muito curta', ['user_id' => $auth->user()['id']]);
            $this->redirect('/admin/usuarios/novo?error=' . urlencode('A senha deve ter no mínimo 6 caracteres'));
        }

        try {
            $userId = $userModel->create([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha,
                'perfil' => $perfil,
                'ativo' => $ativo,
            ]);

            // Se o perfil for 'lider', criar automaticamente na tabela lideres
            if ($perfil === 'lider') {
                try {
                    // Verificar se já existe um líder para este usuário
                    $stmt = $db->prepare('SELECT id FROM lideres WHERE usuario_id = :usuario_id');
                    $stmt->execute(['usuario_id' => $userId]);
                    $liderExistente = $stmt->fetch();
                    
                    if (!$liderExistente) {
                        // Criar líder automaticamente
                        $stmt = $db->prepare('INSERT INTO lideres (nome, usuario_id) VALUES (:nome, :usuario_id)');
                        $stmt->execute([
                            'nome' => $nome,
                            'usuario_id' => $userId
                        ]);
                        $logger->info('Líder criado automaticamente', ['usuario_id' => $userId, 'lider_id' => $db->lastInsertId()]);
                    }
                } catch (\Exception $e) {
                    // Log mas não falha a criação do usuário
                    $logger->warning('Erro ao criar líder automaticamente', ['error' => $e->getMessage(), 'usuario_id' => $userId]);
                }
            }

            $logger->info('Usuário criado', ['user_id' => $userId, 'criado_por' => $auth->user()['id']]);
            $this->redirect('/admin/usuarios?success=' . urlencode('Usuário criado com sucesso'));
        } catch (\Exception $e) {
            $logger->error('Erro ao criar usuário', ['error' => $e->getMessage(), 'user_id' => $auth->user()['id']]);
            $this->redirect('/admin/usuarios/novo?error=' . urlencode('Erro ao criar usuário. Email já existe?'));
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
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $this->redirect('/admin/usuarios?error=' . urlencode('Usuário não encontrado'));
        }

        $this->view('admin/usuarios/form', [
            'title' => 'Editar Usuário',
            'user' => $auth->user(),
            'usuario' => $usuario,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/admin'],
                ['label' => 'Usuários', 'url' => '/admin/usuarios'],
                ['label' => 'Editar', 'url' => '/admin/usuarios/edit']
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
        $email = trim($_POST['email'] ?? '');
        $perfil = $_POST['perfil'] ?? 'lider';
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $senha = $_POST['senha'] ?? '';

        if (empty($nome) || empty($email)) {
            $this->redirect('/admin/usuarios/edit?id=' . $id . '&error=' . urlencode('Preencha todos os campos obrigatórios'));
        }

        try {
            // Buscar perfil anterior para verificar mudança
            $stmt = $db->prepare('SELECT perfil FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $usuarioAntigo = $stmt->fetch();
            $perfilAnterior = $usuarioAntigo['perfil'] ?? null;
            
            if (!empty($senha)) {
                if (strlen($senha) < 6) {
                    $this->redirect('/admin/usuarios/edit?id=' . $id . '&error=' . urlencode('A senha deve ter no mínimo 6 caracteres'));
                }
                $stmt = $db->prepare(
                    'UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, ativo = :ativo, senha = :senha WHERE id = :id'
                );
                $stmt->execute([
                    'id' => $id,
                    'nome' => $nome,
                    'email' => $email,
                    'perfil' => $perfil,
                    'ativo' => $ativo,
                    'senha' => password_hash($senha, PASSWORD_DEFAULT),
                ]);
            } else {
                $stmt = $db->prepare(
                    'UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, ativo = :ativo WHERE id = :id'
                );
                $stmt->execute([
                    'id' => $id,
                    'nome' => $nome,
                    'email' => $email,
                    'perfil' => $perfil,
                    'ativo' => $ativo,
                ]);
            }

            // Se o perfil mudou para 'lider', criar líder se não existir
            if ($perfil === 'lider' && $perfilAnterior !== 'lider') {
                try {
                    $stmt = $db->prepare('SELECT id FROM lideres WHERE usuario_id = :usuario_id');
                    $stmt->execute(['usuario_id' => $id]);
                    $liderExistente = $stmt->fetch();
                    
                    if (!$liderExistente) {
                        $stmt = $db->prepare('INSERT INTO lideres (nome, usuario_id) VALUES (:nome, :usuario_id)');
                        $stmt->execute([
                            'nome' => $nome,
                            'usuario_id' => $id
                        ]);
                        $logger->info('Líder criado automaticamente após mudança de perfil', ['usuario_id' => $id, 'lider_id' => $db->lastInsertId()]);
                    }
                } catch (\Exception $e) {
                    $logger->warning('Erro ao criar líder automaticamente', ['error' => $e->getMessage(), 'usuario_id' => $id]);
                }
            }
            
            // Se o nome mudou e o usuário é líder, atualizar o nome na tabela lideres
            if ($perfil === 'lider') {
                try {
                    $stmt = $db->prepare('UPDATE lideres SET nome = :nome WHERE usuario_id = :usuario_id');
                    $stmt->execute([
                        'nome' => $nome,
                        'usuario_id' => $id
                    ]);
                } catch (\Exception $e) {
                    // Ignorar erro se não houver líder ainda
                }
            }

            $logger->info('Usuário atualizado', ['user_id' => $id, 'atualizado_por' => $auth->user()['id']]);
            $this->redirect('/admin/usuarios?success=' . urlencode('Usuário atualizado com sucesso'));
        } catch (\Exception $e) {
            $logger->error('Erro ao atualizar usuário', ['error' => $e->getMessage(), 'user_id' => $id]);
            $this->redirect('/admin/usuarios/edit?id=' . $id . '&error=' . urlencode('Erro ao atualizar usuário'));
        }
    }

    public function resetPassword(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check() || $auth->user()['perfil'] !== 'admin') {
            $this->redirect('/home');
        }

        $logger = new Logger(__DIR__ . '/../../storage/logs');
        $db = Database::getConnection($this->config['db']);
        $id = (int)($_POST['id'] ?? 0);
        $novaSenha = $_POST['nova_senha'] ?? '';

        if (empty($novaSenha) || strlen($novaSenha) < 6) {
            $this->redirect('/admin/usuarios?error=' . urlencode('A senha deve ter no mínimo 6 caracteres'));
        }

        try {
            $stmt = $db->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
            ]);

            $logger->info('Senha redefinida', ['user_id' => $id, 'redefinido_por' => $auth->user()['id']]);
            $this->redirect('/admin/usuarios?success=' . urlencode('Senha redefinida com sucesso'));
        } catch (\Exception $e) {
            $logger->error('Erro ao redefinir senha', ['error' => $e->getMessage()]);
            $this->redirect('/admin/usuarios?error=' . urlencode('Erro ao redefinir senha'));
        }
    }
}

