<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-1">Préfixes autorisés</h1>
<p class="text-muted mb-4">
    Seuls les numéros commençant par un préfixe actif peuvent se connecter.
</p>

<div class="row g-4">

    <div class="col-lg-7">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Préfixe</th>
                        <th>État</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($prefixes as $prefixe) : ?>
                    <tr>
                        <td class="fw-semibold fs-5"><?= esc($prefixe['valeur']) ?></td>
                        <td>
                            <?php if ((int) $prefixe['statut'] === 1) : ?>
                                <span class="mm-badge mm-badge-depot">Actif</span>
                            <?php else : ?>
                                <span class="mm-badge mm-badge-inactif">Désactivé</span>
                            <?php endif ?>
                        </td>
                        <td class="text-end">
                            <form action="<?= site_url('operateur/prefixes/basculer/' . $prefixe['idPrefixe']) ?>" method="post">
                                <?php if ((int) $prefixe['statut'] === 1) : ?>
                                    <button class="btn btn-sm btn-outline-danger">Désactiver</button>
                                <?php else : ?>
                                    <button class="btn btn-sm btn-outline-primary">Activer</button>
                                <?php endif ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Ajouter un préfixe</h2>
                <form action="<?= site_url('operateur/prefixes/ajouter') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label" for="valeur">Préfixe</label>
                        <input class="form-control" type="text" id="valeur" name="valeur"
                            maxlength="3" pattern="\d{3}" placeholder="033" required>
                        <div class="form-text">Exactement 3 chiffres.</div>
                    </div>
                    <button class="btn btn-primary w-100">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

</div>

<?= $this->include('layout/footer') ?>
