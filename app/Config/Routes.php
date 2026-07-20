<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('accueil', 'Accueil::index');

$routes->get('faketest', static function () {
    session()->set('idCompte', 1);
    return redirect()->to('accueil');
});



