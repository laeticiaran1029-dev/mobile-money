<?php

namespace App\Controllers;

use App\Models\CommissionModel;
use App\Models\CompteModel;
use App\Models\FraisModel;
use App\Models\HistoriqueModel;
use App\Models\OperationModel;
use App\Models\PrefixeModel;

class OperationController extends BaseController
{
    public function depot()
    {
        return $this->afficher('depot', 'Dépôt');
    }

    public function transfertMultiple()
    {
        return $this->afficher('transfertMultiple', 'Transfert Multiple');
    }

    public function retrait()
    {
        return $this->afficher('retrait', 'Retrait', [
            'bareme' => (new FraisModel())->baremeDe(OperationModel::RETRAIT),
        ]);
    }

    public function transfert()
    {
        return $this->afficher('transfert', 'Transfert', [
            'bareme' => (new FraisModel())->baremeDe(OperationModel::TRANSFERT),
        ]);
    }
        public function promotion()
    {
        return $this->afficher('promotion', 'Promotion', [
            'bareme' => (new PromotionModel())->baremeDe(OperationModel::PROMOTION),
        ]);
    }

    public function historique()
    {
        $idCompte = session()->get('idCompte');

        if (! $idCompte) {
            return redirect()->to('/');
        }

        return $this->afficher('historique', 'Historique', [
            'transactions' => (new HistoriqueModel())->duCompte((int) $idCompte),
        ]);
    }

    public function effectuerDepot()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du dépôt doit être supérieur à 0.');
        }

        $this->enregistrer(
            OperationModel::DEPOT,
            (int) $compte['idCompte'],
            null,
            $montant,
            0.0,
            (float) $compte['solde'] + $montant,
            null
        );

        return redirect()->to('depot')->with('succes',
            'Dépôt de ' . $this->formater($montant) . ' Ar effectué.');
    }

    public function effectuerRetrait()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du retrait doit être supérieur à 0.');
        }

        $frais = (new FraisModel())->calculer(OperationModel::RETRAIT, $montant);
        $solde = (float) $compte['solde'];

        if ($montant + $frais > $solde) {
            return redirect()->back()->with('erreur',
                'Solde insuffisant : ' . $this->formater($montant + $frais)
                . ' Ar nécessaires (frais compris), ' . $this->formater($solde) . ' Ar disponibles.');
        }

        $this->enregistrer(
            OperationModel::RETRAIT,
            (int) $compte['idCompte'],
            null,
            $montant,
            $frais,
            $solde - $montant - $frais,
            null
        );

        return redirect()->to('retrait')->with('succes',
            'Retrait de ' . $this->formater($montant) . ' Ar effectué'
            . ($frais > 0 ? ' (frais : ' . $this->formater($frais) . ' Ar).' : '.'));
    }

    public function effectuerTransfert()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant   = (float) $this->request->getPost('montant');
        $numeroTel = preg_replace('/\D/', '', (string) $this->request->getPost('numeroDestinataire'));
        $inclureFrais = $this->request->getPost('inclure_frais') === '1';

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du transfert doit être supérieur à 0.');
        }

        if ($numeroTel === $compte['numeroTel']) {
            return redirect()->back()->with('erreur', 'Un transfert vers son propre numéro est impossible.');
        }

        $db = \Config\Database::connect();
        $prefixeModel = new PrefixeModel();

        $opDestinataire = $prefixeModel->operateurDe($numeroTel);
        if ($opDestinataire === null) {
            $valeurPrefixe = substr($numeroTel, 0, 3);
            return redirect()->back()->with('erreur', "L'opérateur du numéro {$valeurPrefixe} n'est pas pris en charge.");
        }

        // Transfert interne = émetteur et destinataire sur le même opérateur.
        $opEmetteur = $prefixeModel->operateurDe($compte['numeroTel']);
        $idOperateurEmetteur = $opEmetteur !== null ? (int) $opEmetteur['idOperateur'] : 0;
        $estInterne = $idOperateurEmetteur === (int) $opDestinataire['idOperateur'];

        $fraisModel = new FraisModel();
        $frais = $fraisModel->calculer(OperationModel::TRANSFERT, $montant);

        $fraisRetraitInclus = 0;
        $commissionExterne = 0;

        if ($estInterne) {
            
            if ($inclureFrais) {
                $fraisRetraitInclus = $fraisModel->calculer(OperationModel::RETRAIT, $montant);
                $frais += $fraisRetraitInclus;
            }

            $destinataire = (new CompteModel())->parNumero($numeroTel);
            if ($destinataire === null) {
                return redirect()->back()->with('erreur', "Le numéro interne {$numeroTel} n'a pas de compte.");
            }
            $idDestinataire = (int)$destinataire['idCompte'];
            $soldeDestinataireApres = (float)$destinataire['solde'] + $montant;

        } else {
            $taux = (new CommissionModel())->taux($idOperateurEmetteur, (int) $opDestinataire['idOperateur']);
            $commissionExterne = $montant * ($taux / 100);
            $frais += $commissionExterne;

            $idDestinataire = null;
            $soldeDestinataireApres = null;
        }

        $solde = (float) $compte['solde'];
        if ($montant + $frais > $solde) {
            return redirect()->back()->with('erreur',
                'Solde insuffisant : ' . $this->formater($montant + $frais)
                . ' Ar nécessaires (frais compris), ' . $this->formater($solde) . ' Ar disponibles.');
        }

        $db->transStart();
        
        (new CompteModel())->update($compte['idCompte'], ['solde' => $solde - $montant - $frais]);

        if ($idDestinataire !== null) {
            (new CompteModel())->update($idDestinataire, ['solde' => $soldeDestinataireApres]);
        }

        $db->table('historique_operation')->insert([
            'idCompte'               => (int)$compte['idCompte'],
            'idCompteDestinataire'   => $idDestinataire,
            'idOperation'            => OperationModel::TRANSFERT,
            'montant'                => $montant,
            'fraisTotal'             => $frais,
            'numeroDestinataire'     => $numeroTel,
            'idOperateurDestinataire'=> (int)$opDestinataire['idOperateur'],
            'commission'             => $commissionExterne,
            'fraisRetraitInclus'     => $fraisRetraitInclus,
        ]);

        $db->transComplete();

        return redirect()->to('transfert')->with('succes',
            'Transfert de ' . $this->formater($montant) . ' Ar effectué vers le réseau ' . $opDestinataire['nom'] . '.');
    }

    public function effectuerTransfertMultiple()
    {
        $compte = $this->compteConnecte();
        if (! is_array($compte)) {
            return $compte;
        }

        $montantGlobal = (float) $this->request->getPost('montant_global');
        $listeNumerosRaw = $this->request->getPost('numeros');

        if ($montantGlobal <= 0) {
            return redirect()->back()->with('erreur', 'Le montant global doit être supérieur à 0.');
        }

        if (empty($listeNumerosRaw) || !is_array($listeNumerosRaw)) {
            return redirect()->back()->with('erreur', 'Veuillez saisir au moins un numéro de téléphone.');
        }

        $numerosDestinataires = [];
        foreach ($listeNumerosRaw as $num) {
            $nettoye = preg_replace('/\D/', '', (string)$num);
            if (!empty($nettoye)) {
                $numerosDestinataires[] = $nettoye;
            }
        }
        $numerosDestinataires = array_unique($numerosDestinataires);
        $nombreDestinataires = count($numerosDestinataires);

        if ($nombreDestinataires === 0) {
            return redirect()->back()->with('erreur', 'Aucun numéro de téléphone valide n\'a été fourni.');
        }

        $montantParPersonne = $montantGlobal / $nombreDestinataires;

        $db = \Config\Database::connect();
        $compteModel = new CompteModel();
        $fraisModel = new FraisModel();
        $prefixeModel = new PrefixeModel();
        $commissionModel = new CommissionModel();

        $opEmetteur = $prefixeModel->operateurDe($compte['numeroTel']);
        $idOperateurEmetteur = $opEmetteur !== null ? (int) $opEmetteur['idOperateur'] : 0;

        $comptesDestinatairesValides = [];
        $fraisTotalCumule = 0;

        foreach ($numerosDestinataires as $numTel) {
            if ($numTel === $compte['numeroTel']) {
                return redirect()->back()->with('erreur', 'Vous ne pouvez pas vous inclure dans la liste.');
            }

            $opDestinataire = $prefixeModel->operateurDe($numTel);
            if ($opDestinataire === null) {
                $valeurPrefixe = substr($numTel, 0, 3);
                return redirect()->back()->with('erreur', "L'opérateur du numéro {$valeurPrefixe} n'est pas pris en charge.");
            }
            $idOperateurDestinataire = (int) $opDestinataire['idOperateur'];

            $fraisUnitaire = $fraisModel->calculer(OperationModel::TRANSFERT, $montantParPersonne);

            $idDestinataire = null;
            $soldeDestinataireApres = null;
            $commissionExterne = 0;

            if ($idOperateurDestinataire === $idOperateurEmetteur) {
                $dest = $compteModel->parNumero($numTel);
                if ($dest === null) {
                    return redirect()->back()->with('erreur', "Le compte correspondant au numéro {$numTel} n'existe pas.");
                }
                $idDestinataire = (int)$dest['idCompte'];
                $soldeDestinataireApres = (float)$dest['solde'] + $montantParPersonne;
            }
            else {
                $taux = $commissionModel->taux($idOperateurEmetteur, $idOperateurDestinataire);
                $commissionExterne = $montantParPersonne * ($taux / 100);
                $fraisUnitaire += $commissionExterne;
            }

            $fraisTotalCumule += $fraisUnitaire;

            $comptesDestinatairesValides[] = [
                'idCompteDestinataire'   => $idDestinataire,
                'soldeDestinataireApres' => $soldeDestinataireApres,
                'numero'                 => $numTel,
                'frais'                  => $fraisUnitaire,
                'idOperateur'            => $idOperateurDestinataire,
                'commission'             => $commissionExterne
            ];
        }

        $soldeEmetteur = (float) $compte['solde'];
        $coutTotalOperation = $montantGlobal + $fraisTotalCumule;

        if ($coutTotalOperation > $soldeEmetteur) {
            return redirect()->back()->with('erreur',
                'Solde insuffisant : ' . $this->formater($coutTotalOperation)
                . ' Ar requis (frais et commissions inclus), ' . $this->formater($soldeEmetteur) . ' Ar disponibles.');
        }

        $db->transStart();

        $compteModel->update($compte['idCompte'], ['solde' => $soldeEmetteur - $coutTotalOperation]);

        foreach ($comptesDestinatairesValides as $item) {
            if ($item['idCompteDestinataire'] !== null) {
                $compteModel->update($item['idCompteDestinataire'], ['solde' => $item['soldeDestinataireApres']]);
            }

            $db->table('historique_operation')->insert([
                'idCompte'               => (int)$compte['idCompte'],
                'idCompteDestinataire'   => $item['idCompteDestinataire'],
                'idOperation'            => OperationModel::TRANSFERT,
                'montant'                => $montantParPersonne,
                'fraisTotal'             => $item['frais'],
                'numeroDestinataire'     => $item['numero'],
                'idOperateurDestinataire'=> $item['idOperateur'],
                'commission'             => $item['commission'],
                'fraisRetraitInclus'     => 0,
            ]);
        }

        $db->transComplete();

        return redirect()->to('transfertMultiple')->with('succes',
            'Envoi multiple réussi ! Le montant global de ' . $this->formater($montantGlobal) . ' Ar a été partagé équitablement entre les ' . $nombreDestinataires . ' bénéficiaires.');
    }


    
    private function enregistrer(
        int $idOperation,
        int $idEmetteur,
        ?int $idDestinataire,
        float $montant,
        float $frais,
        float $soldeEmetteur,
        ?float $soldeDestinataire
    ): void {
        $db = \Config\Database::connect();
        $db->transStart();

        $compteModel = new CompteModel();
        $compteModel->update($idEmetteur, ['solde' => $soldeEmetteur]);

        if ($idDestinataire !== null && $soldeDestinataire !== null) {
            $compteModel->update($idDestinataire, ['solde' => $soldeDestinataire]);
        }

        (new HistoriqueModel())->insert([
            'idCompte'             => $idEmetteur,
            'idCompteDestinataire' => $idDestinataire,
            'idOperation'          => $idOperation,
            'montant'              => $montant,
            'fraisTotal'           => $frais,
        ]);

        $db->transComplete();
    }

    private function afficher(string $vue, string $titre, array $donnees = [])
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        return view('client/' . $vue, array_merge([
            'compte' => $compte,
            'titre'  => $titre,
        ], $donnees));
    }

    private function formater(float $montant): string
    {
        return number_format($montant, 0, ',', ' ');
    }

    private function compteConnecte()
    {
        $idCompte = session()->get('idCompte');

        if (! $idCompte) {
            return redirect()->to('/');
        }

        $compte = (new CompteModel())->find($idCompte);

        if (! $compte) {
            session()->destroy();

            return redirect()->to('/');
        }

        return $compte;
    }

        public function PromotionAjouter()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant   = (float) $this->request->getPost('montant');
        $numeroTel = preg_replace('/\D/', '', (string) $this->request->getPost('numeroDestinataire'));
        $inclureFrais = $this->request->getPost('inclure_frais') === '1';
        $ligne=$this->request->getPost('fraisPromo') === '1';

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du transfert doit être supérieur à 0.');
        }

        if ($numeroTel === $compte['numeroTel']) {
            return redirect()->back()->with('erreur', 'Un transfert vers son propre numéro est impossible.');
        }

        $db = \Config\Database::connect();
        $prefixeModel = new PrefixeModel();

        $opDestinataire = $prefixeModel->operateurDe($numeroTel);
        if ($opDestinataire === null) {
            $valeurPrefixe = substr($numeroTel, 0, 3);
            return redirect()->back()->with('erreur', "L'opérateur du numéro {$valeurPrefixe} n'est pas pris en charge.");
        }

        // Transfert interne = émetteur et destinataire sur le même opérateur.
        $opEmetteur = $prefixeModel->operateurDe($compte['numeroTel']);
        $idOperateurEmetteur = $opEmetteur !== null ? (int) $opEmetteur['idOperateur'] : 0;
        $estInterne = $idOperateurEmetteur === (int) $opDestinataire['idOperateur'];

        $fraisModel = new FraisModel();
        $frais = $fraisModel->calculer(OperationModel::TRANSFERT, $montant);

        $fraisRetraitInclus = 0;
        $commissionExterne = 0;

        if ($estInterne) {
            
            if ($inclureFrais) {
                $fraisRetraitInclus = $fraisModel->calculer(OperationModel::RETRAIT, $montant);
                $frais += $fraisRetraitInclus;
            }

            $destinataire = (new CompteModel())->parNumero($numeroTel);
            if ($destinataire === null) {
                return redirect()->back()->with('erreur', "Le numéro interne {$numeroTel} n'a pas de compte.");
            }
            $idDestinataire = (int)$destinataire['idCompte'];
            $soldeDestinataireApres = (float)$destinataire['solde'] + $montant;

        } else {
            $taux = (new CommissionModel())->taux($idOperateurEmetteur, (int) $opDestinataire['idOperateur']);
            $commissionExterne = $montant * ($taux / 100);
            $frais += $commissionExterne;

            $idDestinataire = null;
            $soldeDestinataireApres = null;
        }

        $solde = (float) $compte['solde'];
        if ($montant + $frais > $solde) {
            return redirect()->back()->with('erreur',
                'Solde insuffisant : ' . $this->formater($montant + $frais)
                . ' Ar nécessaires (frais compris), ' . $this->formater($solde) . ' Ar disponibles.');
        }

        $db->transStart();
        
        (new CompteModel())->update($compte['idCompte'], ['solde' => $solde - $montant - $frais]);

        if ($idDestinataire !== null) {
            (new CompteModel())->update($idDestinataire, ['solde' => $soldeDestinataireApres]);
        }

        $db->table('historique_operation')->insert([
            'idCompte'               => (int)$compte['idCompte'],
            'idCompteDestinataire'   => $idDestinataire,
            'idOperation'            => OperationModel::TRANSFERT,
            'montant'                => $montant,
            'fraisTotal'             => $frais,
            'numeroDestinataire'     => $numeroTel,
            'idOperateurDestinataire'=> (int)$opDestinataire['idOperateur'],
            'commission'             => $commissionExterne,
            'fraisRetraitInclus'     => $fraisRetraitInclus,
        ]);

        $db->transComplete();

        return redirect()->to('transfert')->with('succes',
            'Transfert de ' . $this->formater($montant) . ' Ar effectué vers le réseau ' . $opDestinataire['nom'] . '.');
}