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
$router->post('/quotes/convert', 'quotescontroller@convert');

// orders
$router->get('/orders', 'orderscontroller@index');
$router->get('/orders/show', 'orderscontroller@show');

// notes
$router->post('/notes', 'notescontroller@store');
$router->post('/notes/update', 'notescontroller@update');
$router->post('/notes/delete', 'notescontroller@destroy');

// printables
$router->get('/quotes/print', 'quotescontroller@printpage');
$router->get('/orders/print', 'orderscontroller@printpage');

// invoices
$router->get('/invoices', 'invoicescontroller@index');
$router->get('/invoices/show', 'invoicescontroller@show');
$router->get('/invoices/print', 'invoicescontroller@printpage');
$router->post('/invoices/create-from-order', 'invoicescontroller@createfromorder');

// payments (invoice)
$router->get('/payments', 'paymentscontroller@index');
$router->get('/payments/create', 'paymentscontroller@create');
$router->post('/payments', 'paymentscontroller@store');
$router->post('/payments/delete', 'paymentscontroller@destroy');

// suppliers
$router->get('/suppliers', 'supplierscontroller@index');
$router->get('/suppliers/create', 'supplierscontroller@create');
$router->post('/suppliers', 'supplierscontroller@store');
$router->get('/suppliers/edit', 'supplierscontroller@edit');
$router->post('/suppliers/update', 'supplierscontroller@update');
$router->post('/suppliers/delete', 'supplierscontroller@destroy');

// purchase orders
$router->get('/purchaseorders', 'purchaseorderscontroller@index');
$router->get('/purchaseorders/create', 'purchaseorderscontroller@create');
$router->post('/purchaseorders', 'purchaseorderscontroller@store');
$router->get('/purchaseorders/edit', 'purchaseorderscontroller@edit');
$router->post('/purchaseorders/update', 'purchaseorderscontroller@update');
$router->get('/purchaseorders/show', 'purchaseorderscontroller@show');
$router->post('/purchaseorders/mark-ordered', 'purchaseorderscontroller@markordered');
$router->get('/purchaseorders/print', 'purchaseorderscontroller@printpage');
$router->post('/purchaseorders/close', 'purchaseorderscontroller@markclosed');

// purchase invoices
$router->get('/purchaseinvoices', 'purchaseinvoicescontroller@index');
$router->get('/purchaseinvoices/show', 'purchaseinvoicescontroller@show');
$router->get('/purchaseinvoices/print', 'purchaseinvoicescontroller@printpage');
$router->post('/purchaseinvoices/create-from-po', 'purchaseinvoicescontroller@createfrompo');

// receipts (from purchase invoices)
$router->post('/receipts', 'receiptscontroller@store');
$router->post('/receipts/delete', 'receiptscontroller@destroy');
$router->get('/receipts/print', 'receiptscontroller@printgrn');

// supplier payments (AP)
$router->get('/supplierpayments', 'supplierpaymentscontroller@index');
$router->post('/supplierpayments', 'supplierpaymentscontroller@store');
$router->post('/supplierpayments/delete', 'supplierpaymentscontroller@destroy');

// sales returns (credit notes)
$router->post('/salesreturns', 'salesreturnscontroller@store');
$router->get('/salesreturns/print', 'salesreturnscontroller@printnote');

// purchase returns (debit notes)
$router->post('/purchasereturns', 'purchasereturnscontroller@store');
$router->get('/purchasereturns/print', 'purchasereturnscontroller@printnote');

$router->dispatch();
