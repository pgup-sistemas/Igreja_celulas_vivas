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
                    <i class="bi bi-lock-fill text-warning" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-2">Nova Senha</h5>
                    <p class="text-muted small mb-0">Defina uma nova senha segura</p>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success small"><?= htmlspecialchars($success) ?></div>
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
                <form method="POST" action="<?= htmlspecialchars($basePath . '/forgot-password/reset') ?>" id="resetForm">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">

                    <div class="mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" name="senha" id="senha" class="form-control" required
                               minlength="6" placeholder="Mínimo 6 caracteres">
                        <div class="form-text small">Use letras, números e símbolos para maior segurança</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required
                               minlength="6" placeholder="Digite a senha novamente">
                        <div id="passwordMatch" class="form-text small text-danger" style="display: none;">
                            As senhas não coincidem
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100" id="submitBtn">
                        <i class="bi bi-check-circle me-1"></i>Redefinir Senha
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= htmlspecialchars($basePath . '/login') ?>" class="text-decoration-none small">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Voltar ao login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const senha = document.getElementById('senha');
    const confirmarSenha = document.getElementById('confirmar_senha');
    const passwordMatch = document.getElementById('passwordMatch');
    const submitBtn = document.getElementById('submitBtn');

    function checkPasswords() {
        if (confirmarSenha.value && senha.value !== confirmarSenha.value) {
            passwordMatch.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            passwordMatch.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    senha.addEventListener('input', checkPasswords);
    confirmarSenha.addEventListener('input', checkPasswords);
});
</script>