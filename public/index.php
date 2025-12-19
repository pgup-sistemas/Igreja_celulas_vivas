<?php

// Bootstrap simples
$config = require __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $prefix = 'Src\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

use Src\Core\Router;
use Src\Controllers\AuthController;
use Src\Controllers\HomeController;
use Src\Controllers\ReuniaoController;
use Src\Controllers\AdminController;
use Src\Controllers\UsuarioController;
use Src\Controllers\CongregacaoController;
use Src\Controllers\CelulaController;
use Src\Controllers\FechamentoController;
use Src\Controllers\RelatorioController;

$router = new Router();

// Rotas públicas
$router->get('/login', fn() => (new AuthController($config))->login());
$router->post('/login', fn() => (new AuthController($config))->authenticate());

// Rotas autenticadas
$router->get('/', function() use ($config) {
    $auth = new \Src\Core\Auth($config);
    if (!$auth->check()) {
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?: '';
        header('Location: ' . $scriptDir . '/login');
        exit;
    }
    $user = $auth->user();
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?: '';
    $redirect = ($user['perfil'] === 'admin') ? '/admin' : '/home';
    header('Location: ' . $scriptDir . $redirect);
    exit;
});
$router->get('/home', fn() => (new HomeController($config))->leaderHome());
$router->get('/reunioes/novo', fn() => (new ReuniaoController($config))->form());
$router->get('/reunioes/lideres', fn() => (new ReuniaoController($config))->getLideresByCelula());
$router->post('/reunioes', fn() => (new ReuniaoController($config))->store());
$router->get('/logout', fn() => (new AuthController($config))->logout());

// Rotas Admin
$router->get('/admin', fn() => (new AdminController($config))->dashboard());

// Usuários
$router->get('/admin/usuarios', fn() => (new UsuarioController($config))->index());
$router->get('/admin/usuarios/novo', fn() => (new UsuarioController($config))->create());
$router->post('/admin/usuarios/store', fn() => (new UsuarioController($config))->store());
$router->get('/admin/usuarios/edit', fn() => (new UsuarioController($config))->edit());
$router->post('/admin/usuarios/update', fn() => (new UsuarioController($config))->update());
$router->post('/admin/usuarios/reset-password', fn() => (new UsuarioController($config))->resetPassword());

// Congregações
$router->get('/admin/congregacoes', fn() => (new CongregacaoController($config))->index());
$router->get('/admin/congregacoes/novo', fn() => (new CongregacaoController($config))->create());
$router->post('/admin/congregacoes/store', fn() => (new CongregacaoController($config))->store());
$router->get('/admin/congregacoes/edit', fn() => (new CongregacaoController($config))->edit());
$router->post('/admin/congregacoes/update', fn() => (new CongregacaoController($config))->update());

// Células
$router->get('/admin/celulas', fn() => (new CelulaController($config))->index());
$router->get('/admin/celulas/novo', fn() => (new CelulaController($config))->create());
$router->post('/admin/celulas/store', fn() => (new CelulaController($config))->store());
$router->get('/admin/celulas/edit', fn() => (new CelulaController($config))->edit());
$router->post('/admin/celulas/update', fn() => (new CelulaController($config))->update());

// Fechamentos
$router->get('/admin/fechamentos', fn() => (new FechamentoController($config))->index());
$router->post('/admin/fechamentos/fechar', fn() => (new FechamentoController($config))->fechar());
$router->post('/admin/fechamentos/reabrir', fn() => (new FechamentoController($config))->reabrir());

// Relatórios
$router->get('/admin/relatorios', fn() => (new RelatorioController($config))->index());
$router->get('/admin/relatorios/exportar-csv', fn() => (new RelatorioController($config))->exportarCsv());
$router->get('/admin/relatorios/exportar-pdf', fn() => (new RelatorioController($config))->exportarPdf());

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

