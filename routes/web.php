<?php

use Core\Router;

$router = Router::getInstance();

// Protected home route - requires authentication
$router->group(['middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/', 'Admin\DashboardController@index');
    $router->get('/dashboard', 'Admin\DashboardController@index');
    
    // Admin Users
    $router->get('/admin/users', 'Admin\UsersController@index');
    $router->get('/admin/users/create', 'Admin\UsersController@create');
    $router->post('/admin/users/create', 'Admin\UsersController@store');
    $router->get('/admin/users/invite', 'Admin\UsersController@invite');
    $router->post('/admin/users/invite', 'Admin\UsersController@sendInvite');
    $router->get('/admin/users/edit/{id}', 'Admin\UsersController@edit');
    $router->post('/admin/users/edit/{id}', 'Admin\UsersController@update');
    $router->get('/admin/users/delete/{id}', 'Admin\UsersController@delete');
});

$router->get('/test', 'TestController@index');

// Authentication routes
$router->get('/login', 'Front\HomeController@login');
$router->post('/login', 'Front\HomeController@postLogin');
$router->get('/logout', 'Front\HomeController@logout');
$router->get('/forgot-password', 'Front\HomeController@forgotPassword');
$router->post('/forgot-password', 'Front\HomeController@postForgotPassword');
$router->get('/reset-password/{token}', 'Front\PasswordResetController@showResetForm');
$router->post('/reset-password', 'Front\PasswordResetController@resetPassword');
$router->get('/invite/{code}', 'Front\HomeController@invite');
$router->post('/create-account', 'Front\HomeController@createAccount');

// API routes
$router->group(['prefix' => 'api'], function($router) {
    $router->get('/status', function() {
        return json_encode(['status' => 'ok', 'version' => '2.0']);
    });
});

// Admin routes (with middleware)
$router->group(['prefix' => 'admin', 'middleware' => ['AuthMiddleware', 'AdminMiddleware']], function($router) {
    $router->get('/', 'Admin\DashboardController@index');
});

// Customer routes
$router->group(['prefix' => 'customer', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/dashboard', 'Customer\DashboardController@dashboard');
});
