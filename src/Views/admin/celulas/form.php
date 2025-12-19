<?php $viewFile = __FILE__; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><?= $celula ? 'Editar' : 'Nova' ?> Célula</h5>
    <a href="<?= url('/admin/celulas') ?>" class="btn btn-secondary btn-sm">Voltar</a>
</div>

<?php if ($_GET['error'] ?? null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url($celula ? '/admin/celulas/update' : '/admin/celulas/store') ?>">
            <?php if ($celula): ?>
                <input type="hidden" name="id" value="<?= $celula['id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($celula['nome'] ?? '') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Congregação</label>
                    <select name="congregacao_id" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($congregacoes as $cong): ?>
                            <option value="<?= $cong['id'] ?>" <?= ($celula['congregacao_id'] ?? null) == $cong['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cong['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Líder</label>
                    <select name="lider_id" class="form-select">
                        <option value="">Selecione...</option>
                        <?php if (empty($lideres ?? [])): ?>
                            <option value="" disabled>Nenhum líder cadastrado. Crie usuários com perfil 'lider' primeiro.</option>
                        <?php else: ?>
                            <?php foreach (($lideres ?? []) as $l): ?>
                                <option value="<?= $l['id'] ?? '' ?>" <?= ($celula['lider_id'] ?? null) == ($l['id'] ?? null) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($l['nome'] ?? 'Sem nome') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (!empty($lideres ?? [])): ?>
                        <small class="text-muted"><?= count($lideres) ?> líder(es) disponível(is)</small>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($celula['cidade'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($celula['bairro'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Zona</label>
                    <input type="text" name="zona" class="form-control" value="<?= htmlspecialchars($celula['zona'] ?? '') ?>">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Ponto de Referência</label>
                    <textarea name="ponto_referencia" class="form-control" rows="2"><?= htmlspecialchars($celula['ponto_referencia'] ?? '') ?></textarea>
                </div>

                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ativa" id="ativa" <?= ($celula['ativa'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ativa">Célula ativa</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="<?= url('/admin/celulas') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

