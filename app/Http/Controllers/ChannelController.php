<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 11:00:58
 */

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;

class ChannelController extends Controller
{

    /**
     * ChannelController constructor.
     */
    public function __construct()
    {
        $this->middleware("partner", []);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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