<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:46:59
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class AccountController extends Controller
{

    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        $this->middleware('token', ['only' => []]);
    }

    public function index(Request $request)
    {

    }
}