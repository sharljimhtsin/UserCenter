<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 15:06:47
 */

namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class CatchAllOptionsRequestsProvider extends ServiceProvider
{
    public function register()
    {
        $request = app('request');

        if ($request->isMethod('OPTIONS')) {
            app()->options($request->path(), function () {
                return response('', 200);
            });
        }
    }
}