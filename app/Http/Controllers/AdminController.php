<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 15:19:27
 */

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminController extends Controller
{

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        $this->middleware("admin", ['except' => []]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        /**
         * @var $userResult User
         */
        $userResult = $request->user();
        if (is_null($userResult)) {
            return response()->json(["error" => "user not exist"]);
        } else {
            $userObj = $userResult->toArray();
            return response()->json(["user" => $userObj]);
        }
    }
}