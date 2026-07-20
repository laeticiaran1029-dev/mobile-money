<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-4">Situation des gains</h1>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="mm-stat mm-stat-principal">
            <div class="mm-stat-label">Total encaissé</div>
            <div class="mm-stat-valeur">
                <?= number_format($gainsTotal + $commissionsTotal, 0, ',', ' ') ?> Ar
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mm-stat">
            <div class="mm-stat-label">Frais de barème</div>
            <div class="mm-stat-valeur"><?= number_format($gainsTotal, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mm-stat">
            <div class="mm-stat-label">Commissions</div>
            <div class="mm-stat-valeur"><?= number_format($commissionsTotal, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">

        <h2 class="h5 mb-3">Par opérateur</h2>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Opérateur</th>
                        <th class="mm-col-montant">Opérations</th>
                        <th class="mm-col-montant">Volume</th>
                        <th class="mm-col-montant">Frais</th>
                        <th class="mm-col-montant">Commissions</th>
                        <th class="mm-col-montant">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($parOperateur as $ligne) : ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($ligne['nom']) ?></td>
                        <td class="mm-col-montant"><?= (int) $ligne['nbOperations'] ?></td>
                        <td class="mm-col-montant"><?= number_format($ligne['volume'], 0, ',', ' ') ?> Ar</td>
                        <td class="mm-col-montant"><?= number_format($ligne['frais'], 0, ',', ' ') ?> Ar</td>
                        <td class="mm-col-montant"><?= number_format($ligne['commissions'], 0, ',', ' ') ?> Ar</td>
                        <td class="mm-col-montant mm-credit">
                            <?= number_format($ligne['frais'] + $ligne['commissions'], 0, ',', ' ') ?> Ar
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="card mb-4">
    <div class="card-body">

        <h2 class="h5 mb-3">Par opération</h2>

        <?php if ($parType === []) : ?>
            <p class="text-muted mb-0">Aucune opération enregistrée.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Opération</th>
                            <th class="mm-col-montant">Nombre</th>
                            <th class="mm-col-montant">Volume</th>
                            <th class="mm-col-montant">Frais</th>
                            <th class="mm-col-montant">Commissions</th>
                            <th class="mm-col-montant">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($parType as $ligne) : ?>
                        <tr>
                            <td>
                                <span class="mm-badge mm-badge-<?= esc($ligne['type']) ?>">
                                    <?= esc(\App\Models\OperationModel::libelle($ligne['type'])) ?>
                                </span>
                            </td>
                            <td class="mm-col-montant"><?= (int) $ligne['nbOperations'] ?></td>
                            <td class="mm-col-montant"><?= number_format($ligne['volume'], 0, ',', ' ') ?> Ar</td>
                            <td class="mm-col-montant"><?= number_format($ligne['gains'], 0, ',', ' ') ?> Ar</td>
                            <td class="mm-col-montant"><?= number_format($ligne['commissions'], 0, ',', ' ') ?> Ar</td>
                            <td class="mm-col-montant mm-credit">
                                <?= number_format($ligne['gains'] + $ligne['commissions'], 0, ',', ' ') ?> Ar
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>

    </div>
</div>

<?= $this->include('layout/footer') ?>
