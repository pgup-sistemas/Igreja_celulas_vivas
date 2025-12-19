<?php $viewFile = __FILE__; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><?= $congregacao ? 'Editar' : 'Nova' ?> Congregação</h5>
    <a href="<?= url('/admin/congregacoes') ?>" class="btn btn-secondary btn-sm">Voltar</a>
</div>

<?php if ($_GET['error'] ?? null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url($congregacao ? '/admin/congregacoes/update' : '/admin/congregacoes/store') ?>">
            <?php if ($congregacao): ?>
                <input type="hidden" name="id" value="<?= $congregacao['id'] ?>">
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($congregacao['nome'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ativa" id="ativa" <?= ($congregacao['ativa'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ativa">Congregação ativa</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="<?= url('/admin/congregacoes') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

