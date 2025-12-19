<?php 
$viewFile = __FILE__;
$breadcrumb = $breadcrumb ?? [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Fechamentos', 'url' => '/admin/fechamentos']
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-calendar-check me-2"></i>Fechamento Mensal
    </h5>
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

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title">Fechar Mês</h6>
                <form method="POST" action="<?= url('/admin/fechamentos/fechar') ?>">
                    <div class="row g-2">
                        <div class="col-6">
                            <select name="mes" class="form-select form-select-sm" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                        <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="number" name="ano" class="form-control form-control-sm" value="<?= date('Y') ?>" min="2020" max="2100" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-lock me-1"></i>Fechar Mês
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h6 class="card-title mb-3">Histórico de Fechamentos</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Mês/Ano</th>
                        <th>Status</th>
                        <th>Fechado por</th>
                        <th>Fechado em</th>
                        <th>Reaberto por</th>
                        <th>Reaberto em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fechamentos as $f): ?>
                        <tr>
                            <td><?= str_pad($f['mes'], 2, '0', STR_PAD_LEFT) ?>/<?= $f['ano'] ?></td>
                            <td>
                                <span class="badge bg-<?= $f['fechado'] ? 'danger' : 'success' ?>">
                                    <?= $f['fechado'] ? 'Fechado' : 'Aberto' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($f['fechado_por_nome'] ?? '-') ?></td>
                            <td><?= $f['fechado_em'] ? date('d/m/Y H:i', strtotime($f['fechado_em'])) : '-' ?></td>
                            <td><?= htmlspecialchars($f['reaberto_por_nome'] ?? '-') ?></td>
                            <td><?= $f['reaberto_em'] ? date('d/m/Y H:i', strtotime($f['reaberto_em'])) : '-' ?></td>
                            <td>
                                <?php if ($f['fechado']): ?>
                                    <form method="POST" action="<?= url('/admin/fechamentos/reabrir') ?>" class="d-inline">
                                        <input type="hidden" name="mes" value="<?= $f['mes'] ?>">
                                        <input type="hidden" name="ano" value="<?= $f['ano'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Deseja reabrir este mês?')" title="Reabrir mês">
                                            <i class="bi bi-unlock me-1"></i>Reabrir
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

