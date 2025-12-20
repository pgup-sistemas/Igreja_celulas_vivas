<?php $viewFile = __FILE__; ?>
<div class="row justify-content-center">
    <div class="col-11 col-sm-8 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom-0 text-center py-4">
                <i class="bi bi-house-door-fill text-primary" style="font-size: 2.5rem;"></i>
                <h4 class="fw-bold text-primary mb-1">Gestão de Células</h4>
                <p class="text-muted small mb-0">Sistema de acompanhamento de células da igreja</p>
            </div>
            <div class="card-body pt-0">
                <div class="text-center mb-4">
                    <i class="bi bi-key-fill text-primary" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-2">Esqueci minha senha</h5>
                    <p class="text-muted small mb-0">Digite seu e-mail para redefinir a senha</p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
                $basePath = dirname($scriptName);
                $basePath = rtrim($basePath, '/');
                if ($basePath === '' || $basePath === '.' || $basePath === '\\') {
                    $basePath = '';
                } else {
                    $basePath = '/' . ltrim($basePath, '/');
                }
                ?>
                <form method="POST" action="<?= htmlspecialchars($basePath . '/forgot-password') ?>">
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required
                               placeholder="seu@email.com">
                    </div>
                    <button class="btn btn-primary w-100">Continuar</button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= htmlspecialchars($basePath . '/login') ?>" class="text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i>Voltar ao login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>