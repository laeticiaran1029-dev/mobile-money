<?= $this->include('layout/header') ?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        <h1 class="h4 mb-1">Retrait</h1>
        <p class="text-muted mb-4" style="font-variant-numeric: tabular-nums;">
            Retirer depuis le compte <?= esc($compte['numeroTel']) ?>
        </p>

        <div class="mm-stat mm-stat-principal mb-4">
            <div class="mm-stat-label"><i class="bi bi-wallet2 me-1"></i> Solde disponible</div>
            <div class="mm-stat-valeur"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= site_url('retrait') ?>" method="post">
                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant à retirer</label>
                        <div class="input-group">
                            <input type="number" name="montant" id="montant"
                                   class="form-control mm-input-montant"
                                   min="1" step="1" required placeholder="0">
                            <span class="input-group-text">Ar</span>
                        </div>
                        <div class="form-text">
                            Les frais du barème sont prélevés en plus du montant retiré.
                        </div>

                        
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Confirmer le retrait</button>
                        <a href="<?= site_url('accueil') ?>" class="btn btn-outline-primary">Retour au tableau de bord</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (! empty($bareme)) : ?>
            <details class="mm-bareme mt-3">
                <summary>
                    <i class="bi bi-info-circle me-1"></i> Voir le barème des frais
                </summary>
                <table class="table table-sm mt-3 mb-0">
                    <thead>
                        <tr>
                            <th>Montant</th>
                            <th class="mm-col-montant">Frais</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bareme as $tranche) : ?>
                        <tr>
                            <td class="mm-col-tranche">
                                <?= number_format($tranche['montantMin'], 0, ',', ' ') ?>
                                à <?= number_format($tranche['montantMax'], 0, ',', ' ') ?> Ar
                            </td>
                            <td class="mm-col-montant">
                                <?= number_format($tranche['frais'], 0, ',', ' ') ?> Ar
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </details>
        <?php endif ?>

    </div>
</div>

<?= $this->include('layout/footer') ?>