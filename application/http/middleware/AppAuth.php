<?php

namespace app\http\middleware;

use app\api\controller\Session;
use app\api\controller\SessionController;
use app\api\model\PermissionModel;
use app\api\model\ResourceModel;
use app\api\model\UserRoleModel;
use app\common\Head;
use think\facade\Cache;
use think\facade\Log;

class AppAuth
{
    public function handle($request, \Closure $next)
    {
        //验证token
        $token = $request->header('Authorization');
        if (empty($token)) {
            return redirect(url('/v1/error', ['msg' => '请传入token', 'code' => 401]));
        }
        //验证token格式
        if (strpos($token, 'Bearer ') === false) {
            return redirect(url('/v1/error', ['msg' => 'token格式不正确', 'code' => 401]));
        }
        $token = str_replace('Bearer ', '', $token);
        //验证token是否正确
        $userinfo = SessionController::getUserInfo($token);
        if (empty($userinfo)) {
            return redirect(url('/v1/error', ['msg' => '鉴权失败，请先登录', 'code' => 401]));
        }
        //刷新token时间
        SessionController::refreshToken($token, $userinfo);

        $request->userinfo = $userinfo;
        $request->token = $token;
        return $next($request);
    }
}
