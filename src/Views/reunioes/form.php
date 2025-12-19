<?php 
$viewFile = __FILE__;
$breadcrumb = $breadcrumb ?? [
    ['label' => 'Home', 'url' => '/home'],
    ['label' => 'Nova Reunião', 'url' => '/reunioes/novo']
];
?>
<div class="row justify-content-center">
    <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-primary">
                        <i class="bi bi-calendar-plus-fill me-2"></i>Nova Reunião de Célula
                    </h4>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>Preencha os dados da reunião. Valores numéricos começam em 0.
                    </small>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/reunioes') ?>">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>Informações Básicas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-diagram-3 me-1"></i>Célula
                                </label>
                                <select name="celula_id" id="celula_id" class="form-select" required>
                                    <option value="">Selecione uma célula</option>
                                    <?php foreach ($celulas as $celula): ?>
                                        <option value="<?= (int)$celula['id'] ?>" <?= (isset($formData['celula_id']) && (int)$formData['celula_id'] === (int)$celula['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($celula['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-fill me-1"></i>Líder(es)
                                </label>
                                <select name="lider_nome" id="lider_nome" class="form-select" required>
                                    <option value="">Selecione uma célula primeiro</option>
                                    <?php if (isset($formData['lider_nome']) && !empty($formData['lider_nome'])): ?>
                                        <option value="<?= htmlspecialchars($formData['lider_nome']) ?>" selected>
                                            <?= htmlspecialchars($formData['lider_nome']) ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted" id="lider_help">
                                    <i class="bi bi-lightbulb me-1"></i>Selecione uma célula para carregar o líder automaticamente
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-house-door me-1"></i>Anfitrião
                                </label>
                                <input type="text" name="anfitriao_nome" class="form-control" value="<?= htmlspecialchars($formData['anfitriao_nome'] ?? '') ?>" required placeholder="Nome do anfitrião">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-telephone me-1"></i>Telefone do Líder
                                </label>
                                <input type="text" name="telefone_lider" id="telefone_lider" class="form-control" value="<?= htmlspecialchars($formData['telefone_lider'] ?? '') ?>" required placeholder="(11) 99999-9999">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-date me-1"></i>Data
                                </label>
                                <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($formData['data'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Horário
                                </label>
                                <input type="time" name="horario" class="form-control" value="<?= htmlspecialchars($formData['horario'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-people-fill me-2"></i>Participação e Presença
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-check me-1"></i>Cadastrados
                                </label>
                                <input type="number" name="cadastrados" class="form-control" value="<?= htmlspecialchars($formData['cadastrados'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>Presentes
                                </label>
                                <input type="number" name="presentes" class="form-control" value="<?= htmlspecialchars($formData['presentes'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-plus me-1"></i>Visitantes
                                </label>
                                <input type="number" name="visitantes" class="form-control" value="<?= htmlspecialchars($formData['visitantes'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-star me-1"></i>MDA
                                </label>
                                <input type="number" name="mda" class="form-control" value="<?= htmlspecialchars($formData['mda'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-house me-1"></i>Visitas
                                </label>
                                <input type="number" name="visitas" class="form-control" value="<?= htmlspecialchars($formData['visitas'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-music-note me-1"></i>Culto Celebração
                                </label>
                                <input type="number" name="culto_celebracao" class="form-control" value="<?= htmlspecialchars($formData['culto_celebracao'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-heart me-1"></i>Aceitação
                                </label>
                                <input type="number" name="aceitacao" class="form-control" value="<?= htmlspecialchars($formData['aceitacao'] ?? '0') ?>" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-cash-coin me-1"></i>Oferta (R$)
                                </label>
                                <input type="number" step="0.01" name="oferta" class="form-control" value="<?= htmlspecialchars($formData['oferta'] ?? '0') ?>" min="0" placeholder="0,00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-sticky me-2"></i>Observações
                        </h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-pencil me-1"></i>Anotações da Reunião
                        </label>
                        <textarea name="observacoes" class="form-control" rows="4" placeholder="Registre observações importantes sobre a reunião..."><?= htmlspecialchars($formData['observacoes'] ?? '') ?></textarea>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Opcional: anote pontos importantes, decisões tomadas, etc.
                        </small>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i>Salvar Reunião
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const celulaSelect = document.getElementById('celula_id');
    const liderSelect = document.getElementById('lider_nome');
    const telefoneInput = document.getElementById('telefone_lider');
    const liderHelp = document.getElementById('lider_help');
    
    if (!celulaSelect || !liderSelect) return;
    
    // Se já houver uma célula selecionada (dados recuperados de erro), carregar o líder
    // Mas só se o select de líder ainda não tiver um valor (não foi preenchido pelo PHP)
    const celulaIdInicial = celulaSelect.value;
    const liderInicial = liderSelect.value;
    if (celulaIdInicial && !liderInicial) {
        carregarLider(celulaIdInicial);
    } else if (celulaIdInicial && liderInicial) {
        // Se já tem líder, apenas habilitar o select
        liderSelect.disabled = false;
        liderHelp.textContent = 'Líder carregado';
    }
    
    celulaSelect.addEventListener('change', function() {
        const celulaId = this.value;
        
        if (!celulaId) {
            liderSelect.innerHTML = '<option value="">Selecione uma célula primeiro</option>';
            liderSelect.disabled = true;
            liderHelp.textContent = 'Selecione uma célula para carregar o líder';
            telefoneInput.value = '';
            return;
        }
        
        carregarLider(celulaId);
    });
    
    function carregarLider(celulaId) {
        // Não limpar telefone se já tiver valor (pode ter sido preenchido manualmente ou recuperado de erro)
        const telefoneAtual = telefoneInput.value;
        const liderAtual = liderSelect.value;
        
        // Limpar campos apenas se não houver valor atual no select
        if (!liderAtual) {
            liderSelect.innerHTML = '<option value="">Carregando...</option>';
            liderSelect.disabled = true;
        }
        
        // Fazer requisição AJAX
        const baseUrl = '<?= url("/reunioes/lideres") ?>';
        fetch(baseUrl + '?celula_id=' + celulaId)
            .then(response => response.json())
            .then(data => {
                // Se já houver um líder selecionado (dados recuperados), não sobrescrever
                if (liderAtual) {
                    liderSelect.disabled = false;
                    return;
                }
                
                liderSelect.innerHTML = '';
                
                if (data.success && data.lider) {
                    const option = document.createElement('option');
                    option.value = data.lider.nome;
                    option.textContent = data.lider.nome;
                    option.selected = true;
                    liderSelect.appendChild(option);
                    
                    // Preencher telefone se disponível e não houver valor atual
                    if (data.lider.telefone && !telefoneAtual) {
                        telefoneInput.value = data.lider.telefone;
                    }
                    
                    liderHelp.textContent = 'Líder carregado automaticamente';
                    liderSelect.disabled = false;
                } else {
                    liderSelect.innerHTML = '<option value="">Nenhum líder encontrado para esta célula</option>';
                    liderSelect.disabled = true;
                    liderHelp.textContent = data.message || 'Esta célula não possui líder vinculado';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar líder:', error);
                if (!liderAtual) {
                    liderSelect.innerHTML = '<option value="">Erro ao carregar líder</option>';
                    liderSelect.disabled = true;
                    liderHelp.textContent = 'Erro ao buscar líder. Tente novamente.';
                }
            });
    }
});
</script>

