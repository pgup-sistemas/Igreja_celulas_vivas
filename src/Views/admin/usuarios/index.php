<?php 
$viewFile = __FILE__;
$breadcrumb = $breadcrumb ?? [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Usuários', 'url' => '/admin/usuarios']
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-people me-2"></i>Gerenciar Usuários
    </h5>
    <a href="<?= url('/admin/usuarios/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>
        <span class="d-none d-sm-inline">Novo Usuário</span>
        <span class="d-sm-none">Novo</span>
    </a>
</div>

<?php if ($success ?? null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error ?? null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $u['perfil'] === 'admin' ? 'danger' : 'primary' ?>">
                                    <?= htmlspecialchars($u['perfil']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $u['ativo'] ? 'success' : 'secondary' ?>">
                                    <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/admin/usuarios/edit?id=' . $u['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                    <span class="d-none d-md-inline ms-1">Editar</span>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal<?= $u['id'] ?>" title="Redefinir Senha">
                                    <i class="bi bi-key"></i>
                                    <span class="d-none d-md-inline ms-1">Redefinir</span>
                                </button>
                            </td>
                        </tr>
                        <!-- Modal Reset Senha -->
                        <div class="modal fade" id="resetModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?= url('/admin/usuarios/reset-password') ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Redefinir Senha</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Nova Senha</label>
                                                <input type="password" name="nova_senha" class="form-control" required minlength="6">
                                                <small class="text-muted">Mínimo 6 caracteres</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-warning">Redefinir</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

