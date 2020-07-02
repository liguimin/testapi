<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/23
 * Time: 15:37
 */

namespace app\app_api\controller;



use app\api\model\UserLoginLogModel;
use app\api\model\UserModel;
use app\common\exception\MsgException;
use app\common\Head;
use think\facade\Cache;
use think\facade\Request;

class SessionController extends Base
{
    protected $middleware = [
        'AppAuth' => ['except' => ['login']],
    ];

    /**
     * 登录
     * @return \think\response\Json
     */
    public function login()
    {
        //查询用户信息
        $userinfo = UserModel::where('username', input('username'))->find();
        //检查用户名是否存在
        if (empty($userinfo)) throw new MsgException('用户名不存在！');
        //检查密码是否正确
        if (!password_verify(input('pwd'), $userinfo->pwd)) throw new MsgException('密码不正确！');
        //记录登录日志
        $user_log_srv = new UserLoginLogModel();
        $user_log_srv->save([
            'username' => $userinfo->username,
            'user_id'  => $userinfo->id,
            'ip'       => Request::ip()
        ]);
        //保存用户信息
        $token = $this->setUserInfo($userinfo->username, $userinfo->toArray());
        $this->jsonReturn(['token' => $token]);
    }

    /**
     * 设置用户信息
     * @param $username
     * @param $userinfo
     * @return string
     */
    protected function setUserInfo($username, $userinfo)
    {
        $token = md5(base64_encode(config('api.token_md5_salt') . $username . time()));
        Cache::store(Head::REDIS_CONFIG)->set($token, $userinfo, config('api.token_expire'));
        return $token;
    }

    /**
     * 获取用户信息
     * @param $token
     * @return mixed
     */
    public static function getUserInfo($token)
    {
        return Cache::store(Head::REDIS_CONFIG)->get($token);
    }

    /**
     * 刷新token有效时间
     * @param $token
     * @param $userinfo
     */
    public static function refreshToken($token,$userinfo){
        Cache::store(Head::REDIS_CONFIG)->set($token, $userinfo, config('api.token_expire'));
    }

    /**
     * 退出登录
     */
    public function logout(){
        $token=request()->param('token');
        Cache::store(Head::REDIS_CONFIG)->rm($token);
        $this->jsonReturn(true);
    }
}