<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-1">Situation des comptes clients</h1>
<p class="text-muted mb-4">
    <?= count($comptes) ?> compte<?= count($comptes) > 1 ? 's' : '' ?> —
    masse monétaire totale : <strong><?= number_format($masseMonetaire, 0, ',', ' ') ?> Ar</strong>
</p>

<?php if ($comptes === []) : ?>
    <p class="text-muted">Aucun compte client.</p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Titulaire</th>
                    <th class="mm-col-montant">Solde</th>
                    <th class="mm-col-montant">Opérations</th>
                    <th class="mm-col-montant">Frais générés</th>
                    <th>Ouverture</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($comptes as $compte) : ?>
                <tr>
                    <td class="fw-semibold" style="font-variant-numeric: tabular-nums;">
                        <?= esc($compte['numeroTel']) ?>
                    </td>
                    <td><?= esc($compte['prenom']) ?> <?= esc($compte['nom']) ?></td>
                    <td class="mm-col-montant"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</td>
                    <td class="mm-col-montant"><?= (int) $compte['nbOperations'] ?></td>
                    <td class="mm-col-montant mm-credit"><?= number_format($compte['fraisGeneres'], 0, ',', ' ') ?> Ar</td>
                    <td class="text-muted"><?= esc($compte['dateCreation']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?= $this->include('layout/footer') ?>
