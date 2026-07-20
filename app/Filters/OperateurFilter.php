<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Protege l'espace operateur. Applique au groupe de routes 'operateur'
 * dans Config\Routes, il evite de repeter le controle dans chaque methode
 * du controleur.
 */
class OperateurFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('estOperateur')) {
            return redirect()->to('operateur/connexion')
                             ->with('erreur', 'Connexion requise pour accéder à cet espace.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien a faire apres la requete.
    }
}
