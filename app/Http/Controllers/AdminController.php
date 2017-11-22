<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 15:19:27
 */

namespace App\Http\Controllers;


use App\Account;
use App\Channel;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function channelList(Request $request)
    {
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 20);
        $result = Channel::getQuery()->get()->forPage($page, $limit);
        if (is_null($result)) {
            return response()->json(["error" => "no data"]);
        } else {
            return response()->json(["list" => $result->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userList(Request $request)
    {
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 20);
        $result = User::query()->get()->forPage($page, $limit);
        if (is_null($result)) {
            return response()->json(["error" => "no data"]);
        } else {
            return response()->json(["list" => $result->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountList(Request $request)
    {
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 20);
        $result = Account::getQuery()->get()->forPage($page, $limit);
        if (is_null($result)) {
            return response()->json(["error" => "no data"]);
        } else {
            return response()->json(["list" => $result->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveChannel(Request $request)
    {
        $channel_id = $request->input("channel_id", null);
        if (is_null($channel_id)) {
            return response()->json(["error" => "param error"]);
        }
        $result = Channel::getQuery()->find($channel_id);
        if (is_null($result)) {
            return response()->json(["error" => "channel not exist"]);
        } else {
            $obj = $result->toArray();
            if ($obj["is_test"] == 0) {
                return response()->json(["error" => "channel already approved"]);
            }
            $obj["is_test"] = 0;
            $result->fill($obj)->save();
            return response()->json(["status" => "ok"]);
        }
    }
}