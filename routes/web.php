<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


/**
 * @var $router Laravel\Lumen\Routing\Router
 **/
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/demo', ['uses' => 'ExampleController@demo']);

$router->group(['prefix' => 'user', 'middleware' => ['user']], function () use ($router) {
    $router->get('index', 'UserController@index');
    $router->get('login', 'UserController@login');
    $router->get('info', 'UserController@info');
});

$router->group(['prefix' => 'account', 'middleware' => []], function () use ($router) {
    $router->post('index', 'AccountController@index');
    $router->post('login', 'AccountController@login');
    $router->post('tempLogin', 'AccountController@tempLogin');
    $router->post('sendSmsCode', 'AccountController@sendSmsCode');
    $router->post('bindPhone', 'AccountController@bindPhone');
    $router->post('info', 'AccountController@info');
    $router->get("/", function () use ($router) {
        return "/";
    });
});