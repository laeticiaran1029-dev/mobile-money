<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-1">Montants dus aux opérateurs</h1>


<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="mm-stat mm-stat-principal">
            <div class="mm-stat-label">Total à reverser</div>
            <div class="mm-stat-valeur"><?= number_format($totalDu, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mm-stat">
            <div class="mm-stat-label">Commissions encaissées au passage</div>
            <div class="mm-stat-valeur"><?= number_format($totalGagne, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<?php if ($lignes === []) : ?>
    <p class="text-muted">Aucun opérateur tiers enregistré.</p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Opérateur</th>
                    <th class="mm-col-montant">Transferts reçus</th>
                    <th class="mm-col-montant">Montant à reverser</th>
                    <th class="mm-col-montant">Commission encaissée</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lignes as $ligne) : ?>
                <tr>
                    <td class="fw-semibold"><?= esc($ligne['nom']) ?></td>
                    <td class="mm-col-montant"><?= (int) $ligne['nbTransferts'] ?></td>
                    <td class="mm-col-montant mm-debit">
                        <?= number_format($ligne['montantDu'], 0, ',', ' ') ?> Ar
                    </td>
                    <td class="mm-col-montant mm-credit">
                        <?= number_format($ligne['commissionsEncaissees'], 0, ',', ' ') ?> Ar
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?= $this->include('layout/footer') ?>
