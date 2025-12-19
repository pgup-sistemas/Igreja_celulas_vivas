<?php 
$viewFile = __FILE__;
$breadcrumb = $breadcrumb ?? [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Relatórios', 'url' => '/admin/relatorios']
];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-primary">
            <i class="bi bi-bar-chart-line-fill me-2"></i>Relatórios de Células
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= url('/admin/relatorios/exportar-csv?' . http_build_query($filtros)) ?>" class="btn btn-success btn-sm shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                <span class="d-none d-sm-inline">Exportar CSV</span>
                <span class="d-sm-none">CSV</span>
            </a>
            <a href="<?= url('/admin/relatorios/exportar-pdf?' . http_build_query($filtros)) ?>" class="btn btn-danger btn-sm shadow-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                <span class="d-none d-sm-inline">Exportar PDF</span>
                <span class="d-sm-none">PDF</span>
            </a>
        </div>
    </div>

    <div class="card shadow mb-4 border-0">
        <div class="card-header bg-light">
            <h6 class="mb-0 text-dark">
                <i class="bi bi-funnel-fill me-2"></i>Filtros de Pesquisa
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= url('/admin/relatorios') ?>" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Mês</label>
                    <select name="mes" class="form-select">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == ($filtros['mes'] ?? date('n')) ? 'selected' : '' ?>>
                                <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Ano</label>
                    <input type="number" name="ano" class="form-control" value="<?= $filtros['ano'] ?? date('Y') ?>" min="2020" max="2100">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Congregação</label>
                    <select name="congregacao_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($opcoes['congregacoes'] as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filtros['congregacao_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Cidade</label>
                    <select name="cidade" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($opcoes['cidades'] as $c): ?>
                            <option value="<?= htmlspecialchars($c['cidade']) ?>" <?= ($filtros['cidade'] ?? '') === $c['cidade'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['cidade']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Bairro</label>
                    <select name="bairro" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($opcoes['bairros'] as $b): ?>
                            <option value="<?= htmlspecialchars($b['bairro']) ?>" <?= ($filtros['bairro'] ?? '') === $b['bairro'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['bairro']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Célula</label>
                    <select name="celula_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($opcoes['celulas'] as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filtros['celula_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a href="<?= url('/admin/relatorios') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <div class="col">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-calendar-event-fill fs-1"></i>
                    </div>
                    <p class="text-muted small mb-1 fw-semibold">Total de Reuniões</p>
                    <h3 class="mb-0 text-primary fw-bold"><?= (int)($stats['total_reunioes'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-people-fill fs-1"></i>
                    </div>
                    <p class="text-muted small mb-1 fw-semibold">Total Cadastrados</p>
                    <h3 class="mb-0 text-success fw-bold"><?= (int)($stats['total_cadastrados'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-graph-up fs-1"></i>
                    </div>
                    <p class="text-muted small mb-1 fw-semibold">Média Presentes</p>
                    <h3 class="mb-0 text-info fw-bold"><?= number_format((float)($stats['media_presentes'] ?? 0), 1, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-person-plus-fill fs-1"></i>
                    </div>
                    <p class="text-muted small mb-1 fw-semibold">Total Visitantes</p>
                    <h3 class="mb-0 text-warning fw-bold"><?= (int)($stats['total_visitantes'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-heart-fill fs-1"></i>
                    </div>
                    <p class="text-muted small mb-1 fw-semibold">Total Aceitações</p>
                    <h3 class="mb-0 text-danger fw-bold"><?= (int)($stats['total_aceitacoes'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="bi bi-cash-coin fs-1"></i>
                    </div>
                    <p class="text-muted small mb-1 fw-semibold">Total Ofertas</p>
                    <h3 class="mb-0 text-secondary fw-bold">R$ <?= number_format((float)($stats['total_ofertas'] ?? 0), 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-geo-alt-fill me-2"></i>Relatório por Cidade
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Cidade</th>
                                    <th>Reuniões</th>
                                    <th>Cadastrados</th>
                                    <th>Aceitações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($porCidade as $pc): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($pc['cidade']) ?></td>
                                        <td class="text-center"><?= (int)$pc['total_reunioes'] ?></td>
                                        <td class="text-center"><?= (int)$pc['total_cadastrados'] ?></td>
                                        <td class="text-center text-success fw-bold"><?= (int)$pc['total_aceitacoes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-house-door-fill me-2"></i>Relatório por Bairro
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Bairro</th>
                                    <th>Reuniões</th>
                                    <th>Cadastrados</th>
                                    <th>Aceitações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($porBairro as $pb): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($pb['bairro']) ?></td>
                                        <td class="text-center"><?= (int)$pb['total_reunioes'] ?></td>
                                        <td class="text-center"><?= (int)$pb['total_cadastrados'] ?></td>
                                        <td class="text-center text-success fw-bold"><?= (int)$pb['total_aceitacoes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-diagram-3-fill me-2"></i>Relatório por Célula
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Célula</th>
                                    <th>Reuniões</th>
                                    <th>Cadastrados</th>
                                    <th>Aceitações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($porCelula as $pcel): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($pcel['nome_celula']) ?></td>
                                        <td class="text-center"><?= (int)$pcel['total_reunioes'] ?></td>
                                        <td class="text-center"><?= (int)$pcel['total_cadastrados'] ?></td>
                                        <td class="text-center text-success fw-bold"><?= (int)$pcel['total_aceitacoes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

