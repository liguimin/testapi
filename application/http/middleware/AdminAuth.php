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

class AdminAuth
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

        //检查是否有权限访问该接口
          $check_result=$this->checkPermission($userinfo['id']);
          if(!$check_result['has_permission']){
              return redirect(url('/v1/error',['msg'=>urlencode('对不起你没有权限访问该接口：'.$check_result['method'].'  '.$check_result['route']),'code'=>403]));
          }

        $request->userinfo = $userinfo;
        $request->token    = $token;
        return $next($request);
    }


    /**
     * 检查是否有权限访问接口
     * @param $user_id
     * @return bool
     */
    private function checkPermission($user_id)
    {
        $role_ids = UserRoleModel::where('user_id', $user_id)->column('role_id');

        $m_permission = new PermissionModel();
        $types        = ResourceModel::TYPE;
        $resources    = $m_permission->getResourcesByRoleId($role_ids, [
            $types['API']
        ]);

        $route  = request()->routeInfo()['rule'];
        $method = request()->method();

        $has_permission = false;
        $methods        = ResourceModel::METHOD;
        foreach ($resources as $key => $val) {
            $identify = 'v1/'.str_replace(':id', '<id>', $val['identify']);
            if ($route === $identify && $val['method'] == $methods[$method]) {
                $has_permission = true;
                break;
            }
        }

        return [
            'has_permission' => $has_permission,
            'method'         => $method,
            'route'          => $route
        ];
    }

}
