<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('connexion', 'ClientAuthController::connexion');
$routes->get('logout', 'ClientAuthController::deconnexion');

$routes->get('accueil', 'Accueil::index');

$routes->get('depot', 'OperationController::depot');
$routes->post('depot', 'OperationController::effectuerDepot');
$routes->get('retrait', 'OperationController::retrait');
$routes->post('retrait', 'OperationController::effectuerRetrait');
$routes->get('transfert', 'OperationController::transfert');
$routes->post('transfert', 'OperationController::effectuerTransfert');
$routes->get('historique', 'OperationController::historique');

// Routes pour le Transfert Multiple
$routes->get('transfertMultiple', 'OperationController::transfertMultiple');
$routes->post('transfertMultiple', 'OperationController::effectuerTransfertMultiple');

$routes->get('operateur/connexion', 'OperateurAuthController::connexion');
$routes->post('operateur/connexion', 'OperateurAuthController::traiter');
$routes->get('operateur/deconnexion', 'OperateurAuthController::deconnexion');


$routes->group('operateur', ['filter' => 'operateur'], static function ($routes) {
    $routes->get('/', 'OperateurController::index');

    $routes->get('prefixes', 'OperateurController::prefixes');
    $routes->post('prefixes/ajouter', 'OperateurController::ajouterPrefixe');
    $routes->post('prefixes/basculer/(:num)', 'OperateurController::basculerPrefixe/$1');

    $routes->get('frais', 'OperateurController::frais');
    $routes->post('frais/ajouter', 'OperateurController::ajouterFrais');
    $routes->post('frais/modifier/(:num)', 'OperateurController::modifierFrais/$1');
    $routes->post('frais/supprimer/(:num)', 'OperateurController::supprimerFrais/$1');
    $routes->post('frais/commission', 'OperateurController::modifierCommission');

    $routes->get('commissions', 'OperateurController::commissions');
    $routes->post('commissions/modifier', 'OperateurController::modifierCommission');

    $routes->get('gains', 'OperateurController::gains');
    $routes->get('dus', 'OperateurController::montantsDus');
    $routes->get('comptes', 'OperateurController::comptes');
});



