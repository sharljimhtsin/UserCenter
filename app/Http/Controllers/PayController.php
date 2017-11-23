<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 10:47:37
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class PayController extends Controller
{

    /**
     * PayController constructor.
     */
    public function __construct()
    {
        $this->middleware("user", ["except" => ["callback"]]);
    }

    public function index(Request $request)
    {

    }

    public function add(Request $request)
    {

    }

    public function pay(Request $request)
    {

    }

    public function callback(Request $request)
    {

    }
}