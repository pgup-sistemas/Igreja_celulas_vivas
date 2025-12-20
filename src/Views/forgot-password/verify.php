<?php $viewFile = __FILE__; ?>
<div class="row justify-content-center">
    <div class="col-11 col-sm-9 col-md-7 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom-0 text-center py-4">
                <i class="bi bi-house-door-fill text-primary" style="font-size: 2.5rem;"></i>
                <h4 class="fw-bold text-primary mb-1">Gestão de Células</h4>
                <p class="text-muted small mb-0">Sistema de acompanhamento de células da igreja</p>
            </div>
            <div class="card-body pt-0">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-2">Verificação de Segurança</h5>
                    <p class="text-muted small mb-0">Confirme sua identidade para redefinir a senha</p>
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
                <form method="POST" action="<?= htmlspecialchars($basePath . '/forgot-password/verify') ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">

                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" required
                               placeholder="Digite seu nome completo">
                        <div class="form-text small">Como cadastrado no sistema</div>
                    </div>

                    <?php if ($user['perfil'] === 'lider'): ?>
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="tel" name="telefone" class="form-control" required
                                   placeholder="(11) 99999-9999">
                            <div class="form-text small">Telefone cadastrado como líder</div>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Importante:</strong> Estes dados são necessários para confirmar que você é o proprietário desta conta.
                    </div>

                    <button class="btn btn-success w-100">Verificar e Continuar</button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= htmlspecialchars($basePath . '/forgot-password') ?>" class="text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i>Usar outro e-mail
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>