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

// warehouses
$router->get('/warehouses', 'warehousescontroller@index');
$router->get('/warehouses/create', 'warehousescontroller@create');
$router->post('/warehouses', 'warehousescontroller@store');
$router->get('/warehouses/edit', 'warehousescontroller@edit');
$router->post('/warehouses/update', 'warehousescontroller@update');
$router->post('/warehouses/delete', 'warehousescontroller@destroy');

// products
$router->get('/products', 'productscontroller@index');
$router->get('/products/create', 'productscontroller@create');
$router->post('/products', 'productscontroller@store');
$router->get('/products/edit', 'productscontroller@edit');
$router->post('/products/update', 'productscontroller@update');
$router->post('/products/delete', 'productscontroller@destroy');
$router->get('/products/stock', 'productscontroller@stock');
$router->post('/products/stock', 'productscontroller@savestock');

// customers
$router->get('/customers', 'customerscontroller@index');
$router->get('/customers/create', 'customerscontroller@create');
$router->post('/customers', 'customerscontroller@store');
$router->get('/customers/edit', 'customerscontroller@edit');
$router->post('/customers/update', 'customerscontroller@update');
$router->post('/customers/delete', 'customerscontroller@destroy');

// quotes
$router->get('/quotes', 'quotescontroller@index');
$router->get('/quotes/create', 'quotescontroller@create');
$router->post('/quotes', 'quotescontroller@store');
$router->get('/quotes/show', 'quotescontroller@show');
$router->post('/quotes/cancel', 'quotescontroller@cancel');
$router->post('/quotes/expire', 'quotescontroller@expire');

$router->dispatch();
