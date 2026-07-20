<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\PrefixeModel;

/**
 * Authentification client : login par numero de telephone, sans inscription
 * prealable. Le compte est cree a la volee la premiere fois.
 */
class ClientAuthController extends BaseController
{
    public function connexion()
    {
        $numeroTel = preg_replace('/\D/', '', (string) $this->request->getPost('numeroTel'));
        $nom       = trim((string) $this->request->getPost('nom'));
        $prenom    = trim((string) $this->request->getPost('prenom'));

        if (strlen($numeroTel) !== 10) {
            return redirect()->back()->with('erreur', 'Le numéro doit contenir exactement 10 chiffres.');
        }

        if (! (new PrefixeModel())->estActif($numeroTel)) {
            return redirect()->back()->with('erreur',
                'Le préfixe ' . substr($numeroTel, 0, 3) . " n'est pas autorisé.");
        }

        $compteModel = new CompteModel();
        $compte      = $compteModel->parNumero($numeroTel);

        // Pas d'inscription : un numero inconnu ouvre un compte a zero.
        if ($compte === null) {
            if ($nom === '' || $prenom === '') {
                return redirect()->back()->with('erreur',
                    'Premiere connexion : renseignez votre nom et votre prénom.');
            }

            $idCompte = $compteModel->insert([
                'numeroTel' => $numeroTel,
                'nom'       => $nom,
                'prenom'    => $prenom,
                'solde'     => 0,
            ]);

            if ($idCompte === false) {
                return redirect()->back()->with('erreur', 'Impossible de créer le compte.');
            }
        } else {
            $idCompte = $compte['idCompte'];
        }

        // Contrat de session : tout le reste de l'application lit idCompte,
        // le solde est toujours relu en base.
        session()->set('idCompte', (int) $idCompte);

        return redirect()->to('accueil');
    }

    public function deconnexion()
    {
        session()->destroy();

        return redirect()->to('/');
    }
}
