<?php 
$viewFile = __FILE__;
$breadcrumb = $breadcrumb ?? [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Células', 'url' => '/admin/celulas']
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-diagram-3 me-2"></i>Gerenciar Células
    </h5>
    <a href="<?= url('/admin/celulas/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>
        <span class="d-none d-sm-inline">Nova Célula</span>
        <span class="d-sm-none">Nova</span>
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
                        <th>Congregação</th>
                        <th>Líder</th>
                        <th>Cidade</th>
                        <th>Bairro</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($celulas as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nome']) ?></td>
                            <td><?= htmlspecialchars($c['congregacao_nome'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($c['lider_nome'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($c['cidade'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($c['bairro'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= $c['ativa'] ? 'success' : 'secondary' ?>">
                                    <?= $c['ativa'] ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/admin/celulas/edit?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                    <span class="d-none d-md-inline ms-1">Editar</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

