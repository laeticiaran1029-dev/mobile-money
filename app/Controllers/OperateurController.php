<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\FraisModel;
use App\Models\HistoriqueModel;
use App\Models\OperationModel;
use App\Models\PrefixeModel;

class OperateurController extends BaseController
{
    // ---------------------------------------------------------------- Accueil

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
            'titre'    => 'Préfixes',
            'prefixes' => (new PrefixeModel())->tous(),
        ]);
    }

    public function ajouterPrefixe()
    {
        $valeur = trim((string) $this->request->getPost('valeur'));

        if (! preg_match('/^\d{3}$/', $valeur)) {
            return redirect()->back()->with('erreur', 'Le préfixe doit contenir exactement 3 chiffres.');
        }

        $modele = new PrefixeModel();

        if ($modele->where('valeur', $valeur)->countAllResults() > 0) {
            return redirect()->back()->with('erreur', "Le préfixe {$valeur} existe déjà.");
        }

        $modele->insert(['valeur' => $valeur, 'statut' => 1]);

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



    public function frais()
    {
        $fraisModele = new FraisModel();
        $operations  = (new OperationModel())->orderBy('idOperation', 'ASC')->findAll();

        $baremes = [];
        foreach ($operations as $operation) {
            $baremes[$operation['idOperation']] = $fraisModele->baremeDe((int) $operation['idOperation']);
        }

        return view('operateur/frais', [
            'titre'      => 'Barèmes de frais',
            'operations' => $operations,
            'baremes'    => $baremes,
        ]);
    }

    public function ajouterFrais()
    {
        $idOperation = (int) $this->request->getPost('idOperation');
        $min         = (float) $this->request->getPost('montantMin');
        $max         = (float) $this->request->getPost('montantMax');
        $montant     = (float) $this->request->getPost('frais');

        if ($min < 0 || $max < 0 || $montant < 0) {
            return redirect()->back()->with('erreur', 'Les montants ne peuvent pas être négatifs.');
        }

        if ($min > $max) {
            return redirect()->back()->with('erreur', 'Le montant minimum doit être inférieur au maximum.');
        }

        $modele = new FraisModel();

        if ($modele->chevauche($idOperation, $min, $max)) {
            return redirect()->back()->with('erreur',
                'Cette tranche en chevauche une autre. Un même montant aurait deux frais possibles.');
        }

        $modele->insert([
            'idOperation' => $idOperation,
            'montantMin'  => $min,
            'montantMax'  => $max,
            'frais'       => $montant,
        ]);

        return redirect()->back()->with('succes', 'Tranche ajoutée.');
    }

    public function modifierFrais(int $idFrais)
    {
        $montant = (float) $this->request->getPost('frais');

        if ($montant < 0) {
            return redirect()->back()->with('erreur', 'Les frais ne peuvent pas être négatifs.');
        }

        (new FraisModel())->update($idFrais, ['frais' => $montant]);

        return redirect()->back()->with('succes', 'Frais mis à jour.');
    }

    public function supprimerFrais(int $idFrais)
    {
        (new FraisModel())->delete($idFrais);

        return redirect()->back()->with('succes', 'Tranche supprimée.');
    }

    // ------------------------------------------------------------------ Gains

    public function gains()
    {
        $historique = new HistoriqueModel();

        return view('operateur/gains', [
            'titre'      => 'Situation des gains',
            'parType'    => $historique->gainsParOperation(),
            'gainsTotal' => $historique->gainsTotal(),
        ]);
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
