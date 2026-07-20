<?php

namespace App\Controllers;

class OperateurAuthController extends BaseController
{
    /**
     * Formulaire de connexion. Si la session est deja ouverte, inutile de
     * redemander le mot de passe.
     */
    public function connexion()
    {
        if (session()->get('estOperateur')) {
            return redirect()->to('operateur');
        }

        return view('operateur/login', ['titre' => 'Connexion opérateur']);
    }

    public function traiter()
    {
        $saisi   = (string) $this->request->getPost('motDePasse');
        $attendu = (string) env('operateur.motDePasse');

        // hash_equals compare en temps constant : la duree de la comparaison
        // ne laisse pas deviner la longueur du mot de passe attendu.
        if ($attendu === '' || ! hash_equals($attendu, $saisi)) {
            return redirect()->back()->with('erreur', 'Mot de passe incorrect.');
        }

        // Nouvel identifiant de session apres authentification : evite qu'un
        // identifiant connu d'avance serve a se glisser dans la session.
        session()->regenerate();
        session()->set('estOperateur', true);

        return redirect()->to('operateur');
    }

    public function deconnexion()
    {
        session()->remove('estOperateur');

        return redirect()->to('operateur/connexion')->with('succes', 'Vous êtes déconnecté.');
    }
}
