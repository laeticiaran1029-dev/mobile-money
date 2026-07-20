<?= $this->include('layout/header') ?>

<h1 class="h4 mb-1">Bonjour <?= esc($compte['prenom']) ?> <?= esc($compte['nom']) ?></h1>
<p class="text-muted mb-4" style="font-variant-numeric: tabular-nums;">
    <?= esc($compte['numeroTel']) ?>
</p>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="mm-stat mm-stat-principal">
            <div class="mm-stat-label"><i class="bi bi-wallet2 me-1"></i> Solde disponible</div>
            <div class="mm-stat-valeur"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="mm-stat">
            <div class="mm-stat-label"><i class="bi bi-arrow-left-right me-1"></i> Transactions</div>
            <div class="mm-stat-valeur"><?= $nbTransactions ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="mm-stat">
            <div class="mm-stat-label"><i class="bi bi-percent me-1"></i> Frais payés</div>
            <div class="mm-stat-valeur"><?= number_format($fraisPayes, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<h2 class="mm-titre-section">Que voulez-vous faire ?</h2>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <a class="mm-action mm-action-depot" href="<?= site_url('depot') ?>">
            <span class="mm-action-icone"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i></span>
            <span class="mm-action-titre">Dépôt</span>
            <span class="mm-action-aide">Alimenter mon compte</span>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a class="mm-action mm-action-retrait" href="<?= site_url('retrait') ?>">
            <span class="mm-action-icone"><i class="bi bi-box-arrow-up" aria-hidden="true"></i></span>
            <span class="mm-action-titre">Retrait</span>
            <span class="mm-action-aide">Retirer de l'argent</span>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a class="mm-action mm-action-transfert" href="<?= site_url('transfert') ?>">
            <span class="mm-action-icone"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
            <span class="mm-action-titre">Transfert</span>
            <span class="mm-action-aide">Envoyer à un autre numéro</span>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a class="mm-action mm-action-historique" href="<?= site_url('historique') ?>">
            <span class="mm-action-icone"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
            <span class="mm-action-titre">Historique</span>
            <span class="mm-action-aide">Mes transactions</span>
        </a>
    </div>
</div>

<div class="d-flex align-items-baseline justify-content-between">
    <h2 class="mm-titre-section mb-0">Dernières transactions</h2>
    <a class="mm-lien-tout" href="<?= site_url('historique') ?>">Tout voir &rsaquo;</a>
</div>

<?php if ($transactions === []) : ?>
    <p class="text-muted mt-3">Aucune transaction enregistrée.</p>
<?php else : ?>
    <div class="table-responsive mt-3">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Contrepartie</th>
                    <th class="mm-col-montant">Montant</th>
                    <th class="mm-col-montant">Frais</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $ligne) : ?>
                <?php
                    // Credit si le compte a recu un transfert, ou s'il s'agit d'un depot.
                    $estRecu   = (int) ($ligne['idCompteDestinataire'] ?? 0) === (int) $compte['idCompte'];
                    $estCredit = $ligne['type'] === 'depot' || $estRecu;

                    $contrepartie = $estRecu ? $ligne['telEmetteur'] : ($ligne['telDestinataire'] ?? null);
                    $frais        = $estRecu ? 0 : (float) $ligne['fraisTotal'];
                ?>
                <tr>
                    <td><?= esc(date('d/m/Y H:i', strtotime($ligne['dateTransaction']))) ?></td>
                    <td>
                        <span class="mm-badge mm-badge-<?= esc($ligne['type']) ?>">
                            <?= esc(\App\Models\OperationModel::libelle($ligne['type'])) ?>
                        </span>
                    </td>
                    <td style="font-variant-numeric: tabular-nums;">
                        <?= esc($contrepartie ?? '—') ?>
                    </td>
                    <td class="mm-col-montant <?= $estCredit ? 'mm-credit' : 'mm-debit' ?>">
                        <?= $estCredit ? '+' : '−' ?><?= number_format($ligne['montant'], 0, ',', ' ') ?> Ar
                    </td>
                    <td class="mm-col-montant">
                        <?= $frais > 0 ? '−' . number_format($frais, 0, ',', ' ') . ' Ar' : '—' ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<?= $this->include('layout/footer') ?>
