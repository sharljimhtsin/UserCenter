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
use App\Lib\Utils;
use App\PayOrder;
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
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
        } else {
            $userObj = $userResult->toArray();
            return Utils::echoContent(Utils::CODE_OK, ["user" => $userObj]);
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
            return Utils::echoContent(Utils::CODE_NO_DATA);
        } else {
            return Utils::echoContent(Utils::CODE_OK, ["list" => $result->toArray()]);
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
            return Utils::echoContent(Utils::CODE_NO_DATA);
        } else {
            return Utils::echoContent(Utils::CODE_OK, ["list" => $result->toArray()]);
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
            return Utils::echoContent(Utils::CODE_NO_DATA);
        } else {
            return Utils::echoContent(Utils::CODE_OK, ["list" => $result->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payOrderList(Request $request)
    {
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 20);
        $result = PayOrder::getQuery()->get()->forPage($page, $limit);
        if (is_null($result)) {
            return Utils::echoContent(Utils::CODE_NO_DATA);
        } else {
            return Utils::echoContent(Utils::CODE_OK, ["list" => $result->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveChannel(Request $request)
    {
        $this->validate($request, ["channel_id" => "required"]);
        $channel_id = $request->input("channel_id", null);
        $result = Channel::getQuery()->find($channel_id);
        if (is_null($result)) {
            return Utils::echoContent(Utils::CODE_CHANNEL_NOT_EXIST);
        } else {
            $obj = $result->toArray();
            if ($obj["is_test"] == 0) {
                return Utils::echoContent(Utils::CODE_CHANNEL_ALREADY_APPROVED);
            }
            $obj["is_test"] = 0;
            $result->fill($obj)->save();
            return Utils::echoContent(Utils::CODE_OK);
        }
    }
}