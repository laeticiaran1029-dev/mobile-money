<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-4">Tableau de bord</h1>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="mm-stat mm-stat-principal h-100">
            <div class="mm-stat-label"><i class="bi bi-graph-up-arrow me-1"></i> Gains cumulés</div>
            <div class="mm-stat-valeur"><?= number_format($gainsTotal, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="mm-stat h-100">
            <div class="mm-stat-label"><i class="bi bi-wallet2 me-1"></i> Masse monétaire clients</div>
            <div class="mm-stat-valeur"><?= number_format($masseMonetaire, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="mm-stat h-100">
            <div class="mm-stat-label"><i class="bi bi-people me-1"></i> Comptes clients</div>
            <div class="mm-stat-valeur"><?= $nbComptes ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="mm-stat h-100">
            <div class="mm-stat-label"><i class="bi bi-hash me-1"></i> Préfixes actifs</div>
            <div class="mm-stat-valeur"><?= $nbPrefixes ?></div>
        </div>
    </div>
</div>

<h2 class="mm-titre-section">Dernières transactions</h2>

<?php if ($dernieres === []) : ?>
    <p class="text-muted">Aucune transaction enregistrée.</p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Émetteur</th>
                    <th>Destinataire</th>
                    <th class="mm-col-montant">Montant</th>
                    <th class="mm-col-montant">Frais</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dernieres as $ligne) : ?>
                <tr>
                    <td><?= esc($ligne['dateTransaction']) ?></td>
                    <td>
                        <span class="mm-badge mm-badge-<?= esc($ligne['type']) ?>">
                            <?= esc(\App\Models\OperationModel::libelle($ligne['type'])) ?>
                        </span>
                    </td>
                    <td><?= esc($ligne['telEmetteur']) ?></td>
                    <td><?= esc($ligne['telDestinataire'] ?? '—') ?></td>
                    <td class="mm-col-montant"><?= number_format($ligne['montant'], 0, ',', ' ') ?> Ar</td>
                    <td class="mm-col-montant mm-credit">+<?= number_format($ligne['fraisTotal'], 0, ',', ' ') ?> Ar</td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?= $this->include('layout/footer') ?>
