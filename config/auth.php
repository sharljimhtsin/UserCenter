<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 10:03:39
 */

namespace App\config;

/**
 * @link https://github.com/laravel/lumen-framework/blob/5.5/config/auth.php
 */
return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
    ],
    'guards' => [
        'api' => ['driver' => 'api'],
    ],
    'providers' => [
        //
    ],
    'passwords' => [
        //
    ]
];