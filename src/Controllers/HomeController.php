<?php

namespace Src\Controllers;

use Src\Core\Auth;
use Src\Core\Controller;
use Src\Core\Database;
use Src\Models\Celula;
use Src\Models\Reuniao;

class HomeController extends Controller
{
    public function leaderHome(): void
    {
        $auth = new Auth($this->config);
        if (!$auth->check()) {
            $this->redirect('/login');
        }

        $user = $auth->user();
        if ($user['perfil'] !== 'lider') {
            $this->redirect('/admin');
        }

        $db = Database::getConnection($this->config['db']);
        $celulaModel = new Celula($db);
        $reuniaoModel = new Reuniao($db);

        $celulas = $celulaModel->findByUser($user['id']);
        $reunioes = $reuniaoModel->latestByUser($user['id']);

        $this->view('home/leader', [
            'title' => 'Minhas Células',
            'user' => $user,
            'celulas' => $celulas,
            'reunioes' => $reunioes,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => '/home']
            ],
        ]);
    }
}

