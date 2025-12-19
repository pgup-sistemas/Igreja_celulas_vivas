<?php $viewFile = __FILE__; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><?= $usuario ? 'Editar' : 'Novo' ?> Usuário</h5>
    <a href="<?= url('/admin/usuarios') ?>" class="btn btn-secondary btn-sm">Voltar</a>
</div>

<?php if ($_GET['error'] ?? null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url($usuario ? '/admin/usuarios/update' : '/admin/usuarios/store') ?>">
            <?php if ($usuario): ?>
                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label"><?= $usuario ? 'Nova ' : '' ?>Senha <?= $usuario ? '(deixe em branco para manter)' : '' ?> <span class="text-danger"><?= $usuario ? '' : '*' ?></span></label>
                <input type="password" name="senha" class="form-control" <?= $usuario ? '' : 'required' ?> minlength="6">
                <small class="text-muted">Mínimo 6 caracteres</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Perfil <span class="text-danger">*</span></label>
                <select name="perfil" class="form-select" required>
                    <option value="lider" <?= ($usuario['perfil'] ?? 'lider') === 'lider' ? 'selected' : '' ?>>Líder</option>
                    <option value="admin" <?= ($usuario['perfil'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= ($usuario['ativo'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ativo">Usuário ativo</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="<?= url('/admin/usuarios') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

