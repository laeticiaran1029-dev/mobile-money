<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('accueil', 'Accueil::index');

$routes->get('depot', 'OperationController::depot');
$routes->get('retrait', 'OperationController::retrait');
$routes->get('transfert', 'OperationController::transfert');
$routes->get('historique', 'OperationController::historique');

// ------------------------------------------------------------ Cote operateur
$routes->group('operateur', static function ($routes) {
    $routes->get('/', 'OperateurController::index');

    $routes->get('prefixes', 'OperateurController::prefixes');
    $routes->post('prefixes/ajouter', 'OperateurController::ajouterPrefixe');
    $routes->post('prefixes/basculer/(:num)', 'OperateurController::basculerPrefixe/$1');

    $routes->get('frais', 'OperateurController::frais');
    $routes->post('frais/ajouter', 'OperateurController::ajouterFrais');
    $routes->post('frais/modifier/(:num)', 'OperateurController::modifierFrais/$1');
    $routes->post('frais/supprimer/(:num)', 'OperateurController::supprimerFrais/$1');

    $routes->get('gains', 'OperateurController::gains');
    $routes->get('comptes', 'OperateurController::comptes');
});

$routes->get('faketest', static function () {
    session()->set('idCompte', 1);
    return redirect()->to('accueil');
});



