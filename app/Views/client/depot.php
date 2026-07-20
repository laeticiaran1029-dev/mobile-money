<?= $this->include('layout/header') ?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        <h1 class="h4 mb-1">Dépôt</h1>
        <p class="text-muted mb-4" style="font-variant-numeric: tabular-nums;">
            Alimenter le compte <?= esc($compte['numeroTel']) ?>
        </p>

        <div class="mm-stat mm-stat-principal mb-4">
            <div class="mm-stat-label"><i class="bi bi-wallet2 me-1"></i> Solde disponible</div>
            <div class="mm-stat-valeur"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= site_url('depot') ?>" method="post">
                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant à déposer</label>
                        <div class="input-group">
                            <input type="number" name="montant" id="montant"
                                   class="form-control mm-input-montant"
                                   min="1" step="1" required placeholder="0">
                            <span class="input-group-text">Ar</span>
                        </div>
                        <div class="form-text">Le dépôt est gratuit : aucun frais n'est prélevé.</div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Confirmer le dépôt</button>
                        <a href="<?= site_url('accueil') ?>" class="btn btn-outline-primary">Retour au tableau de bord</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?= $this->include('layout/footer') ?>
