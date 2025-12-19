<?php 
$viewFile = __FILE__;
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin']
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0">
            <i class="bi bi-speedometer2 me-2"></i>Painel do Pastor
        </h5>
        <small class="text-muted">Indicadores consolidados</small>
    </div>
    <a href="<?= url('/reunioes/novo') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>
        <span class="d-none d-sm-inline">Registrar reunião</span>
        <span class="d-sm-none">Nova</span>
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse" aria-expanded="true" aria-controls="filtrosCollapse">
            <h6 class="mb-0 d-flex align-items-center">
                <i class="bi bi-funnel me-2"></i>Filtros
                <i class="bi bi-chevron-down ms-auto collapse-icon"></i>
            </h6>
        </button>
    </div>
    <div class="collapse show" id="filtrosCollapse">
        <div class="card-body">
            <form method="GET" action="<?= url('/admin') ?>" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small">
                        <i class="bi bi-calendar3 me-1"></i>Mês
                    </label>
                    <select name="mes" class="form-select form-select-sm">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == ($filtros['mes'] ?? date('n')) ? 'selected' : '' ?>>
                                <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">
                        <i class="bi bi-calendar-year me-1"></i>Ano
                    </label>
                    <input type="number" name="ano" class="form-control form-control-sm" value="<?= $filtros['ano'] ?? date('Y') ?>" min="2020" max="2100">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">
                        <i class="bi bi-building me-1"></i>Congregação
                    </label>
                    <select name="congregacao_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($congregacoes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filtros['congregacao_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a href="<?= url('/admin') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
    <div class="col">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="small mb-1 opacity-75">
                            <i class="bi bi-calendar-event me-1"></i>Reuniões no mês
                        </p>
                        <h3 class="mb-0 fw-bold"><?= (int)($stats['total_reunioes'] ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-calendar-event fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="small mb-1 opacity-75">
                            <i class="bi bi-people me-1"></i>Total cadastrados
                        </p>
                        <h3 class="mb-0 fw-bold"><?= (int)($stats['total_cadastrados'] ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-people fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="small mb-1 opacity-75">
                            <i class="bi bi-graph-up me-1"></i>Média presentes
                        </p>
                        <h3 class="mb-0 fw-bold"><?= number_format((float)($stats['media_presentes'] ?? 0), 1, ',', '.') ?></h3>
                    </div>
                    <i class="bi bi-graph-up fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="small mb-1 opacity-75">
                            <i class="bi bi-person-plus me-1"></i>Total visitantes
                        </p>
                        <h3 class="mb-0 fw-bold"><?= (int)($stats['total_visitantes'] ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-person-plus fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="small mb-1 opacity-75">
                            <i class="bi bi-heart me-1"></i>Total aceitações
                        </p>
                        <h3 class="mb-0 fw-bold"><?= (int)($stats['total_aceitacoes'] ?? 0) ?></h3>
                    </div>
                    <i class="bi bi-heart fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="small mb-1 opacity-75">
                            <i class="bi bi-cash-coin me-1"></i>Total ofertas
                        </p>
                        <h3 class="mb-0 fw-bold">R$ <?= number_format((float)($stats['total_ofertas'] ?? 0), 2, ',', '.') ?></h3>
                    </div>
                    <i class="bi bi-cash-coin fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const collapseElement = document.getElementById('filtrosCollapse');
    const collapseIcon = document.querySelector('.collapse-icon');
    
    if (collapseElement && collapseIcon) {
        collapseElement.addEventListener('show.bs.collapse', function() {
            collapseIcon.classList.remove('bi-chevron-up');
            collapseIcon.classList.add('bi-chevron-down');
        });
        
        collapseElement.addEventListener('hide.bs.collapse', function() {
            collapseIcon.classList.remove('bi-chevron-down');
            collapseIcon.classList.add('bi-chevron-up');
        });
    }
});
</script>

