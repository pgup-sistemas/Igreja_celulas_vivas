<?php $viewFile = __FILE__; ?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-center mb-3">
                    <h5 class="fw-bold">Login</h5>
                    <p class="text-muted small mb-0">Acesse com seu usuário</p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php
                // Detectar caminho base
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
                $basePath = dirname($scriptName);
                $basePath = rtrim($basePath, '/');
                if ($basePath === '' || $basePath === '.' || $basePath === '\\') {
                    $basePath = '';
                } else {
                    $basePath = '/' . ltrim($basePath, '/');
                }
                $loginPath = $basePath . '/login';
                ?>
                <form method="POST" action="<?= htmlspecialchars($loginPath) ?>">
                    <div class="mb-3">
                        <label class="form-label">Usuário (e-mail)</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

