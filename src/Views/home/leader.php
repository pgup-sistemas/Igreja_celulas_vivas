<?php 
$viewFile = __FILE__;
$breadcrumb = $breadcrumb ?? [
    ['label' => 'Home', 'url' => '/home']
];
?>
<div class="mb-3">
    <h5 class="fw-bold">
        <i class="bi bi-house me-2"></i>Minhas Células
    </h5>
</div>
<?php if (empty($celulas)): ?>
    <div class="alert alert-info">Nenhuma célula vinculada.</div>
<?php else: ?>
    <div class="row row-cols-1 g-3">
        <?php foreach ($celulas as $celula): ?>
            <div class="col">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($celula['nome']) ?></h6>
                                <p class="text-muted small mb-0">
                                    <?= htmlspecialchars($celula['cidade'] ?? '') ?> - <?= htmlspecialchars($celula['bairro'] ?? '') ?>
                                </p>
                            </div>
                            <a class="btn btn-sm btn-primary" href="<?= url('/reunioes/novo') ?>">
                                <i class="bi bi-plus-circle me-1"></i>Registrar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mt-4">
    <h6 class="fw-bold">Últimas reuniões</h6>
    <?php if (empty($reunioes)): ?>
        <p class="text-muted small">Nenhum registro recente.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($reunioes as $reuniao): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <div>
                        <strong><?= htmlspecialchars($reuniao['nome_celula']) ?></strong>
                        <div class="small text-muted">
                            <?= htmlspecialchars($reuniao['data']) ?> • Presentes: <?= (int)$reuniao['presentes'] ?>
                        </div>
                    </div>
                    <span class="badge bg-secondary">Visitantes <?= (int)$reuniao['visitantes'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

