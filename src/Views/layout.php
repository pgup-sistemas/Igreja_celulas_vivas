<?php
/** @var string $viewFile */

// Função helper para gerar URLs com caminho base
function url($path = '') {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = dirname($scriptName);
    $basePath = rtrim($basePath, '/');
    if ($basePath === '' || $basePath === '.' || $basePath === '\\') {
        $basePath = '';
    } else {
        $basePath = '/' . ltrim($basePath, '/');
    }
    $path = '/' . ltrim($path, '/');
    return $basePath . $path;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Gestão de Células') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        @media (max-width: 991.98px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= url('/') ?>">
            <i class="bi bi-house-door-fill me-2"></i>
            <span class="d-none d-sm-inline fw-semibold">Gestão de Células</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (!empty($_SESSION['igreja_user'])): ?>
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($_SESSION['igreja_user']['perfil'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/admin') ?>">
                                <i class="bi bi-speedometer2 me-1"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/admin/usuarios') ?>">
                                <i class="bi bi-people me-1"></i>
                                <span>Usuários</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/admin/congregacoes') ?>">
                                <i class="bi bi-building me-1"></i>
                                <span>Congregações</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/admin/celulas') ?>">
                                <i class="bi bi-diagram-3 me-1"></i>
                                <span>Células</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/admin/relatorios') ?>">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                <span>Relatórios</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/admin/fechamentos') ?>">
                                <i class="bi bi-calendar-check me-1"></i>
                                <span>Fechamentos</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="<?= url('/home') ?>">
                                <i class="bi bi-house me-1"></i>
                                <span>Home</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="<?= url('/reunioes/novo') ?>">
                            <i class="bi bi-plus-circle me-1"></i>
                            <span>Nova Reunião</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center fw-semibold" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <span><?= htmlspecialchars($_SESSION['igreja_user']['nome']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item fw-semibold" href="<?= url('/logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="py-4">
    <div class="container">
        <?php 
        // Helper para breadcrumb
        if (!function_exists('breadcrumb')) {
            function breadcrumb(array $items = []) {
                if (empty($items)) return '';
                $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">';
                foreach ($items as $index => $item) {
                    $isLast = ($index === count($items) - 1);
                    if ($isLast) {
                        $html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['label']) . '</li>';
                    } else {
                        $html .= '<li class="breadcrumb-item"><a href="' . url($item['url']) . '">' . htmlspecialchars($item['label']) . '</a></li>';
                    }
                }
                $html .= '</ol></nav>';
                return $html;
            }
        }
        // Renderizar breadcrumb se definido
        if (isset($breadcrumb) && is_array($breadcrumb)) {
            echo breadcrumb($breadcrumb);
        }
        ?>
        <?php include $viewFile; ?>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<!-- Footer discreto PgUp Sistemas -->
<footer class="text-center py-2" style="font-size: 0.75rem; color: #6c757d;">
    <a href="https://github.com/pgup-sistemas" target="_blank" class="text-decoration-none" style="color: #6c757d;">
        <i class="bi bi-github me-1"></i>By PgUp Sistemas
    </a>
</footer>

