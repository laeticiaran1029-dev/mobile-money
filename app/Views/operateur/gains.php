<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-1">Situation des gains</h1>
<p class="text-muted mb-4">
    Les gains proviennent des frais encaissés sur les retraits et les transferts.
</p>

<div class="mm-stat mm-stat-principal mb-4" style="max-width: 24rem;">
    <div class="mm-stat-label">Total encaissé</div>
    <div class="mm-stat-valeur"><?= number_format($gainsTotal, 0, ',', ' ') ?> Ar</div>
</div>

<?php if ($parType === []) : ?>
    <p class="text-muted">Aucune opération enregistrée pour le moment.</p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Opération</th>
                    <th class="mm-col-montant">Nombre</th>
                    <th class="mm-col-montant">Volume traité</th>
                    <th class="mm-col-montant">Frais encaissés</th>
                    <th class="mm-col-montant">Part</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($parType as $ligne) : ?>
                <?php $part = $gainsTotal > 0 ? ($ligne['gains'] / $gainsTotal) * 100 : 0; ?>
                <tr>
                    <td>
                        <span class="mm-badge mm-badge-<?= esc($ligne['type']) ?>">
                            <?= esc(\App\Models\OperationModel::libelle($ligne['type'])) ?>
                        </span>
                    </td>
                    <td class="mm-col-montant"><?= (int) $ligne['nbOperations'] ?></td>
                    <td class="mm-col-montant"><?= number_format($ligne['volume'], 0, ',', ' ') ?> Ar</td>
                    <td class="mm-col-montant mm-credit"><?= number_format($ligne['gains'], 0, ',', ' ') ?> Ar</td>
                    <td class="mm-col-montant"><?= number_format($part, 1, ',', ' ') ?> %</td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?= $this->include('layout/footer') ?>
