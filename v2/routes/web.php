<?php

use Core\Router;

$router = Router::getInstance();

// Basic routes for testing
$router->get('/', function() {
    echo "<h1>Hotel DigiLab V2</h1>";
    echo "<p>Framework-less MVC Application is working!</p>";
    echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
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
