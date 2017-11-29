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
    $router->post('attach', 'AccountController@attach');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});

$router->group(['prefix' => 'admin', 'middleware' => ['token', 'admin']], function () use ($router) {
    $router->post('index', 'AdminController@index');
    $router->post('channelList', 'AdminController@channelList');
    $router->post('userList', 'AdminController@userList');
    $router->post('accountList', 'AdminController@accountList');
    $router->post('payOrderList', 'AdminController@payOrderList');
    $router->post('approveChannel', 'AdminController@approveChannel');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});

$router->group(['prefix' => 'channel', 'middleware' => ['token', 'partner']], function () use ($router) {
    $router->post('index', 'ChannelController@index');
    $router->post('add', 'ChannelController@add');
    $router->post('update', 'ChannelController@update');
    $router->post('delete', 'ChannelController@delete');
    $router->post('all', 'ChannelController@all');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});

$router->group(['prefix' => 'pay', 'middleware' => []], function () use ($router) {
    $router->post('index', 'PayController@index');
    $router->post('add', 'PayController@add');
    $router->post('pay', 'PayController@pay');
    $router->post('callbackForWeChat', 'PayController@callbackForWeChat');
    $router->post('callbackForAliPay', 'PayController@callbackForAliPay');
    $router->post('callbackForTenPay', 'PayController@callbackForTenPay');
    $router->post('test', 'PayController@test');
    $router->post('resultForAliPay', 'PayController@resultForAliPay');
    $router->get("/{foobar}", function ($foobar) use ($router) {
        return "/" . $foobar;
    });
});