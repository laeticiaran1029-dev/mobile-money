<?= $this->include('layout/header') ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">

        <h1 class="h4 mb-1">Transfert</h1>
        <p class="text-muted mb-4" style="font-variant-numeric: tabular-nums;">
            Envoyer de l'argent depuis <?= esc($compte['numeroTel']) ?>
            <?php if ($operateur !== null) : ?>
                — <?= esc($operateur['nom']) ?>
            <?php endif ?>
        </p>

        <div class="mm-stat mm-stat-principal mb-4">
            <div class="mm-stat-label"><i class="bi bi-wallet2 me-1"></i> Solde disponible</div>
            <div class="mm-stat-valeur"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= site_url('transfert') ?>" method="post" id="formTransfert">
                    <div class="mb-3">
                        <label for="numeroDestinataire" class="form-label">
                            Numéro(s) du destinataire
                        </label>
                        <textarea name="numeroDestinataire" id="numeroDestinataire"
                                  class="form-control" rows="2" required
                                  placeholder="0340000000, 0320000000"
                                  style="font-variant-numeric: tabular-nums;"></textarea>
                        <div class="form-text">
                            Un seul numéro, ou plusieurs séparés par des virgules : le montant
                            sera alors réparti entre eux. Chaque destinataire doit déjà avoir un compte.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant total à transférer</label>
                        <div class="input-group">
                            <input type="number" name="montant" id="montant"
                                   class="form-control mm-input-montant"
                                   min="1" step="1" required placeholder="0">
                            <span class="input-group-text">Ar</span>
                        </div>
                        <div class="form-text">
                            Les frais sont à votre charge : chaque destinataire reçoit sa part entière.
                        </div>
                    </div>

                    <div class="form-check mb-3 d-none" id="blocFraisRetrait">
                        <input class="form-check-input" type="checkbox"
                               name="inclureFraisRetrait" id="inclureFraisRetrait" value="1">
                        <label class="form-check-label" for="inclureFraisRetrait">
                            Payer d'avance les frais de retrait du destinataire
                        </label>
                    </div>

                    <div class="mm-bareme mb-3 d-none" id="detailCout">
                        <table class="table table-sm mb-0">
                            <tbody id="lignesDetail"></tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Envoyer l'argent</button>
                        <a href="<?= site_url('accueil') ?>" class="btn btn-outline-primary">Retour au tableau de bord</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (! empty($commissions)) : ?>
            <details class="mm-bareme mt-3">
                <summary>
                    <i class="bi bi-percent me-1"></i> Voir les commissions vers les autres opérateurs
                </summary>
                <table class="table table-sm mt-3 mb-0">
                    <thead>
                        <tr>
                            <th>Destinataire</th>
                            <th class="mm-col-montant">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($commissions as $taux) : ?>
                        <tr>
                            <td><?= esc($taux['nom']) ?></td>
                            <td class="mm-col-montant"><?= esc(rtrim(rtrim(number_format($taux['taux'], 2, ',', ' '), '0'), ',')) ?> %</td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <p class="form-text mt-2 mb-0">
                    S'ajoute aux frais du barème ci-dessous.
                </p>
            </details>
        <?php endif ?>

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

<script>
// Le detail affiche ici reproduit le calcul du serveur, qui reste la seule
// autorite : c'est un apercu pour que le client voie ce qu'il paie, pas la
// source de verite.
(function () {
    const prefixes      = <?= json_encode($prefixes, JSON_UNESCAPED_UNICODE) ?>;
    const commissions   = <?= json_encode(array_column($commissions, 'taux', 'idOperateurDestinataire'), JSON_UNESCAPED_UNICODE) ?>;
    const baremeFrais    = <?= json_encode($bareme, JSON_UNESCAPED_UNICODE) ?>;
    const baremesRetrait = <?= json_encode($baremesRetrait, JSON_UNESCAPED_UNICODE) ?>;

    const champNumeros = document.getElementById('numeroDestinataire');
    const champMontant = document.getElementById('montant');
    const blocRetrait  = document.getElementById('blocFraisRetrait');
    const caseRetrait  = document.getElementById('inclureFraisRetrait');
    const blocDetail   = document.getElementById('detailCout');
    const lignesDetail = document.getElementById('lignesDetail');

    const format = (montant) =>
        new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(montant) + ' Ar';

    // Meme regle que FraisModel::calculer : la tranche qui contient le montant,
    // sinon la derniere tranche en dessous, sinon rien.
    function fraisBareme(bareme, montant) {
        let repli = 0;

        for (const tranche of bareme) {
            const min = parseFloat(tranche.montantMin);
            const max = parseFloat(tranche.montantMax);

            if (montant >= min && montant <= max) {
                return parseFloat(tranche.frais);
            }

            if (max < montant) {
                repli = parseFloat(tranche.frais);
            }
        }

        return repli;
    }

    function numerosSaisis() {
        return champNumeros.value
            .split(/[\s,;]+/)
            .map((brut) => brut.replace(/\D/g, ''))
            .filter((numero) => numero !== '');
    }

    function operateurDe(numero) {
        return prefixes[numero.substring(0, 3)] || null;
    }

    function rafraichir() {
        const numeros = numerosSaisis();
        const total   = parseFloat(champMontant.value) || 0;

        // L'option n'a de sens que si au moins un destinataire est chez un
        // operateur qui facture le retrait.
        const retraitFacture = numeros.some((numero) => {
            const operateur = operateurDe(numero);
            return operateur !== null && baremesRetrait[operateur.idOperateur] !== undefined;
        });

        blocRetrait.classList.toggle('d-none', ! retraitFacture);

        if (! retraitFacture) {
            caseRetrait.checked = false;
        }

        if (numeros.length === 0 || total <= 0) {
            blocDetail.classList.add('d-none');
            return;
        }

        const part  = Math.floor(total / numeros.length);
        const reste = total - part * numeros.length;

        if (part < 1) {
            blocDetail.classList.add('d-none');
            return;
        }

        let frais        = 0;
        let commission   = 0;
        let fraisRetrait = 0;

        numeros.forEach((numero, rang) => {
            const operateur = operateurDe(numero);
            const montant   = part + (rang === 0 ? reste : 0);

            frais += fraisBareme(baremeFrais, montant);

            if (operateur === null) {
                return;
            }

            commission += Math.round((montant * parseFloat(commissions[operateur.idOperateur] || 0)) / 100);

            if (caseRetrait.checked && baremesRetrait[operateur.idOperateur]) {
                fraisRetrait += fraisBareme(baremesRetrait[operateur.idOperateur], montant);
            }
        });

        const debit  = total + frais + commission + fraisRetrait;
        const lignes = [
            ['Montant envoyé' + (numeros.length > 1 ? ' (' + numeros.length + ' destinataires)' : ''), total],
            ['Frais de transfert', frais],
        ];

        if (commission > 0) {
            lignes.push(['Commission inter-opérateurs', commission]);
        }

        if (fraisRetrait > 0) {
            lignes.push(["Frais de retrait payés d'avance", fraisRetrait]);
        }

        lignesDetail.innerHTML = lignes
            .map(([libelle, valeur]) =>
                '<tr><td>' + libelle + '</td>'
                + '<td class="mm-col-montant">' + format(valeur) + '</td></tr>')
            .join('')
            + '<tr class="fw-semibold"><td>Total débité</td>'
            + '<td class="mm-col-montant mm-debit">' + format(debit) + '</td></tr>';

        blocDetail.classList.remove('d-none');
    }

    ['input', 'change'].forEach((evenement) => {
        champNumeros.addEventListener(evenement, rafraichir);
        champMontant.addEventListener(evenement, rafraichir);
        caseRetrait.addEventListener(evenement, rafraichir);
    });
})();
</script>

<?= $this->include('layout/footer') ?>
