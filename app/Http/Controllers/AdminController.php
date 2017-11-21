<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 15:19:27
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class AdminController extends Controller
{

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        $this->middleware("auth", ['except' => []]);
    }

    public function index(Request $request)
    {
        $request->user();
    }
}