<?php declare(strict_types=1);

require __DIR__ . '/../app/core/bootstrap.php';

use App\Core\Router;

$router = new Router();

// home + health
$router->get('/', 'homecontroller@index');
$router->get('/health', function () {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'OK';
});

// auth
$router->get('/login', 'authcontroller@loginform');
$router->post('/login', 'authcontroller@login');
$router->post('/logout', 'authcontroller@logout');

// user profile
$router->get('/profile', 'usercontroller@profile');
$router->post('/profile/password', 'usercontroller@changepassword');

// categories
$router->get('/categories', 'categoriescontroller@index');
$router->get('/categories/create', 'categoriescontroller@create');
$router->post('/categories', 'categoriescontroller@store');
$router->get('/categories/edit', 'categoriescontroller@edit');
$router->post('/categories/update', 'categoriescontroller@update');
$router->post('/categories/delete', 'categoriescontroller@destroy');

// makes
$router->get('/makes', 'makescontroller@index');
$router->get('/makes/create', 'makescontroller@create');
$router->post('/makes', 'makescontroller@store');
$router->get('/makes/edit', 'makescontroller@edit');
$router->post('/makes/update', 'makescontroller@update');
$router->post('/makes/delete', 'makescontroller@destroy');

// models
$router->get('/models', 'modelscontroller@index');
$router->get('/models/create', 'modelscontroller@create');
$router->post('/models', 'modelscontroller@store');
$router->get('/models/edit', 'modelscontroller@edit');
$router->post('/models/update', 'modelscontroller@update');
$router->post('/models/delete', 'modelscontroller@destroy');

$router->dispatch();
