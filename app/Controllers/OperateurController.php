<?php

namespace App\Controllers;

use App\Models\CommissionModel;
use App\Models\CompteModel;
use App\Models\FraisModel;
use App\Models\HistoriqueModel;
use App\Models\OperateurModel;
use App\Models\OperationModel;
use App\Models\PrefixeModel;

class OperateurController extends BaseController
{
   
    public function index()
    {
        $comptes     = new CompteModel();
        $historique  = new HistoriqueModel();
        $prefixes    = new PrefixeModel();

        return view('operateur/home', [
            'titre'          => 'Espace opérateur',
            'gainsTotal'     => $historique->gainsTotal(),
            'masseMonetaire' => $comptes->masseMonetaire(),
            'nbComptes'      => $comptes->countAllResults(),
            'nbPrefixes'     => $prefixes->where('statut', 1)->countAllResults(),
            'dernieres'      => $historique->dernieres(8),
        ]);
    }

    

    public function prefixes()
    {
        return view('operateur/prefixes', [
            'titre'      => 'Préfixes',
            'prefixes'   => (new PrefixeModel())->tous(),
            'operateurs' => (new OperateurModel())->tous(),
        ]);
    }

    public function ajouterPrefixe()
    {
        $valeur      = trim((string) $this->request->getPost('valeur'));
        $idOperateur = (int) $this->request->getPost('idOperateur');

        if (! preg_match('/^\d{3}$/', $valeur)) {
            return redirect()->back()->with('erreur', 'Le préfixe doit contenir exactement 3 chiffres.');
        }

        if ((new OperateurModel())->find($idOperateur) === null) {
            return redirect()->back()->with('erreur', 'Opérateur inconnu.');
        }

        $modele = new PrefixeModel();

        if ($modele->where('valeur', $valeur)->countAllResults() > 0) {
            return redirect()->back()->with('erreur', "Le préfixe {$valeur} existe déjà.");
        }

        $modele->insert([
            'valeur'      => $valeur,
            'idOperateur' => $idOperateur,
            'statut'      => 1,
        ]);

        return redirect()->back()->with('succes', "Préfixe {$valeur} ajouté.");
    }

    /**
     * Active ou desactive un prefixe. On ne supprime pas : des comptes
     * existants peuvent deja utiliser ce prefixe.
     */
    public function basculerPrefixe(int $idPrefixe)
    {
        $modele  = new PrefixeModel();
        $prefixe = $modele->find($idPrefixe);

        if ($prefixe === null) {
            return redirect()->back()->with('erreur', 'Préfixe introuvable.');
        }

        $nouveau = ((int) $prefixe['statut'] === 1) ? 0 : 1;
        $modele->update($idPrefixe, ['statut' => $nouveau]);

        $etat = $nouveau === 1 ? 'activé' : 'désactivé';

        return redirect()->back()->with('succes', "Préfixe {$prefixe['valeur']} {$etat}.");
    }



    /**
     * Un onglet par operateur. Chacun a son propre bareme par paliers pour ses
     * operations internes, et ses taux sortants vers les autres operateurs.
     */
    public function frais()
    {
        $fraisModele = new FraisModel();
        $operations  = (new OperationModel())->orderBy('idOperation', 'ASC')->findAll();
        $operateurs  = (new OperateurModel())->tous();
        $matrice     = (new CommissionModel())->matrice();


        $baremes = [];

        foreach ($operateurs as $operateur) {
            $idOperateur = (int) $operateur['idOperateur'];

            foreach ($operations as $operation) {
                $baremes[$idOperateur][(int) $operation['idOperation']] = $fraisModele->baremeDe(
                    (int) $operation['idOperation'],
                    $idOperateur
                );
            }
        }

        return view('operateur/frais', [
            'titre'      => 'Barèmes de frais',
            'operations' => $operations,
            'operateurs' => $operateurs,
            'baremes'    => $baremes,
            'matrice'    => $matrice,
        ]);
    }

    public function ajouterFrais()
    {
        $idOperation = (int) $this->request->getPost('idOperation');
        $idOperateur = (int) $this->request->getPost('idOperateur');
        $min         = (float) $this->request->getPost('montantMin');
        $max         = (float) $this->request->getPost('montantMax');
        $montant     = (float) $this->request->getPost('frais');

        if ((new OperateurModel())->find($idOperateur) === null) {
            return $this->echec('Opérateur inconnu.');
        }

        if ($min < 0 || $max < 0 || $montant < 0) {
            return $this->echec('Les montants ne peuvent pas être négatifs.');
        }

        if ($min > $max) {
            return $this->echec('Le montant minimum doit être inférieur au maximum.');
        }

        $modele = new FraisModel();

        if ($modele->chevauche($idOperation, $idOperateur, $min, $max)) {
            return $this->echec(
                'Cette tranche en chevauche une autre. Un même montant aurait deux frais possibles.');
        }

        $idFrais = $modele->insert([
            'idOperation' => $idOperation,
            'idOperateur' => $idOperateur,
            'montantMin'  => $min,
            'montantMax'  => $max,
            'frais'       => $montant,
        ]);

        // La vue insere la ligne elle-meme : elle a besoin de l'id pour
        // cabler les boutons Modifier et Supprimer.
        return $this->succes('Tranche ajoutée.', [
            'tranche' => [
                'idFrais'    => (int) $idFrais,
                'montantMin' => $min,
                'montantMax' => $max,
                'frais'      => $montant,
            ],
        ]);
    }

    public function modifierFrais(int $idFrais)
    {
        $montant = (float) $this->request->getPost('frais');

        if ($montant < 0) {
            return $this->echec('Les frais ne peuvent pas être négatifs.');
        }

        (new FraisModel())->update($idFrais, ['frais' => $montant]);

        return $this->succes('Frais mis à jour.');
    }

    public function supprimerFrais(int $idFrais)
    {
        (new FraisModel())->delete($idFrais);

        return $this->succes('Tranche supprimée.');
    }

    /**
     * Reponse d'echec : JSON quand la page enregistre sans se recharger,
     * redirection classique sinon (formulaire poste sans JavaScript).
     */
    private function echec(string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(422)
                                  ->setJSON(['erreur' => $message]);
        }

        return redirect()->back()->with('erreur', $message);
    }

    private function succes(string $message, array $donnees = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['succes' => $message] + $donnees);
        }

        return redirect()->back()->with('succes', $message);
    }

    // ------------------------------------------------------------------ Gains

    public function gains()
    {
        $historique = new HistoriqueModel();

        return view('operateur/gains', [
            'titre'            => 'Situation des gains',
            'parOperateur'     => $historique->gainsParOperateur(),
            'parType'          => $historique->gainsParOperation(),
            'gainsTotal'       => $historique->gainsTotal(),
            'commissionsTotal' => $historique->commissionsTotal(),
        ]);
    }

    /**
     * Ce qu'on doit reverser a chaque operateur tiers : les fonds transferes
     * vers ses abonnes. On les a credites nous-memes, l'argent n'est pas sorti.
     */
    public function montantsDus()
    {
        $historique = new HistoriqueModel();
        $lignes     = $historique->montantsDusParOperateur();

        return view('operateur/dus', [
            'titre'       => 'Montants dus aux opérateurs',
            'lignes'      => $lignes,
            'totalDu'     => array_sum(array_column($lignes, 'montantDu')),
            'totalGagne'  => array_sum(array_column($lignes, 'commissionsEncaissees')),
        ]);
    }

    // ------------------------------------------------------------ Commissions

    /**
     * Matrice des taux croises : une ligne par operateur source, une colonne
     * par destinataire.
     */
    public function commissions()
    {
        $operateurs = (new OperateurModel())->tous();

        return view('operateur/commissions', [
            'titre'      => 'Commissions inter-opérateurs',
            'operateurs' => $operateurs,
            'matrice'    => (new CommissionModel())->matrice(),
        ]);
    }

    public function modifierCommission()
    {
        $source = (int) $this->request->getPost('idOperateurSource');
        $dest   = (int) $this->request->getPost('idOperateurDestinataire');
        $taux   = (float) $this->request->getPost('taux');

        if ($taux < 0 || $taux > 100) {
            return $this->echec('Le taux doit être compris entre 0 et 100 %.');
        }

        $operateurs = new OperateurModel();

        if ($operateurs->find($source) === null || $operateurs->find($dest) === null) {
            return $this->echec('Opérateur inconnu.');
        }

        (new CommissionModel())->definir($source, $dest, $taux);

        return $this->succes('Taux mis à jour.');
    }

   
    public function comptes()
    {
        $comptes = new CompteModel();

        return view('operateur/comptes', [
            'titre'          => 'Situation des comptes',
            'comptes'        => $comptes->situation(),
            'masseMonetaire' => $comptes->masseMonetaire(),
        ]);
    }
}
