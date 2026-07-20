<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-1">Barèmes de frais</h1>
<p class="text-muted mb-4">
    Les frais s'appliquent par tranche de montant. Un dépôt est gratuit si son barème reste vide.
</p>

<?php foreach ($operations as $operation) : ?>
    <?php
        $id     = (int) $operation['idOperation'];
        $bareme = $baremes[$id] ?? [];
    ?>

    <div class="card mb-4">
        <div class="card-body">

            <h2 class="h5 mb-3">
                <span class="mm-badge mm-badge-<?= esc($operation['type']) ?>">
                    <?= esc(\App\Models\OperationModel::libelle($operation['type'])) ?>
                </span>
            </h2>

            <?php if ($bareme === []) : ?>
                <p class="text-muted">Aucune tranche : cette opération est gratuite.</p>
            <?php else : ?>
                <div class="table-responsive mb-3">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="mm-col-montant">De</th>
                                <th class="mm-col-montant">À</th>
                                <th style="width: 12rem;">Frais</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bareme as $tranche) : ?>
                            <tr>
                                <td class="mm-col-montant"><?= number_format($tranche['montantMin'], 0, ',', ' ') ?> Ar</td>
                                <td class="mm-col-montant"><?= number_format($tranche['montantMax'], 0, ',', ' ') ?> Ar</td>
                                <td>
                                    <form action="<?= site_url('operateur/frais/modifier/' . $tranche['idFrais']) ?>"
                                          method="post" class="d-flex gap-2">
                                        <input class="form-control form-control-sm" type="number" name="frais"
                                               min="0" step="1" value="<?= (int) $tranche['frais'] ?>" required>
                                        <button class="btn btn-sm btn-primary">OK</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form action="<?= site_url('operateur/frais/supprimer/' . $tranche['idFrais']) ?>"
                                          method="post"
                                          onsubmit="return confirm('Supprimer cette tranche ?');">
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>

            <form action="<?= site_url('operateur/frais/ajouter') ?>" method="post" class="row g-2 align-items-end">
                <input type="hidden" name="idOperation" value="<?= $id ?>">
                <div class="col-sm-3">
                    <label class="form-label" for="min<?= $id ?>">Montant min</label>
                    <input class="form-control" type="number" id="min<?= $id ?>" name="montantMin"
                           min="0" step="1" required>
                </div>
                <div class="col-sm-3">
                    <label class="form-label" for="max<?= $id ?>">Montant max</label>
                    <input class="form-control" type="number" id="max<?= $id ?>" name="montantMax"
                           min="0" step="1" required>
                </div>
                <div class="col-sm-3">
                    <label class="form-label" for="frais<?= $id ?>">Frais</label>
                    <input class="form-control" type="number" id="frais<?= $id ?>" name="frais"
                           min="0" step="1" required>
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-outline-primary w-100">Ajouter la tranche</button>
                </div>
            </form>

        </div>
    </div>
<?php endforeach ?>

<?= $this->include('layout/footer') ?>
