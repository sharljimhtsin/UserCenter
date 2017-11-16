<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        //
    }

    public function demo(Request $request)
    {
        var_dump($request->all());
        return "demo";
    }

    //
}
