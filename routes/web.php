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

$router->group(['prefix' => 'user', 'middleware' => []], function () use ($router) {
    $router->post('index', 'UserController@index');
    $router->post('info', 'UserController@info');
    $router->post('updateInfo', 'UserController@updateInfo');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});

$router->group(['prefix' => 'account', 'middleware' => []], function () use ($router) {
    $router->post('index', 'AccountController@index');
    $router->post('login', 'AccountController@login');
    $router->post('tempLogin', 'AccountController@tempLogin');
    $router->post('telephoneLogin', 'AccountController@telephoneLogin');
    $router->post('sendSmsCodeNoToken', 'AccountController@sendSmsCodeNoToken');
    $router->post('sendSmsCode', 'AccountController@sendSmsCode');
    $router->post('bindPhone', 'AccountController@bindPhone');
    $router->post('register', 'AccountController@register');
    $router->post('modifyPassword', 'AccountController@modifyPassword');
    $router->post('resetPassword', 'AccountController@resetPassword');
    $router->post('info', 'AccountController@info');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});

$router->group(['prefix' => 'admin', 'middleware' => ['token', 'admin']], function () use ($router) {
    $router->post('index', 'AdminController@index');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});

$router->group(['prefix' => 'channel', 'middleware' => ['token', 'partner']], function () use ($router) {
    $router->post('index', 'ChannelController@index');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});