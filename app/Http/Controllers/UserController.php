<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 10:53:02
 */

namespace App\Http\Controllers;


class UserController extends Controller
{

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        return "user";
    }

    public function login()
    {
        return "login";
    }
}