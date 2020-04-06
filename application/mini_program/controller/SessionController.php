<?php /**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/23
 * Time: 15:37
 */

namespace app\mini_program\controller;


use app\common\exception\MsgException;
use app\common\Head;
use app\mini_program\validate\SessionValidate;
use liguimin\utils\Curl;
use think\Db;
use think\facade\Cache;
use think\facade\Log;
use think\Request;

class SessionController extends Base
{
    protected $middleware = [
//        'AdminAuth' => ['except' => ['login']],
    ];

    /**
     * 登录
     * @return \think\response\Json
     */
    public function wxlogin(Request $request)
    {
        //验证参数
        $params = $request->param();
        $this->validateParams(new SessionValidate(), 's_login', $params);

        //向微信服务器发起请求，获取session_key
        $curl = new Curl();
        $curl->get('https://api.weixin.qq.com/sns/jscode2session', [
            'appid'      => config('mini_program.mini_program_info.appid'),
            'secret'     => config('mini_program.mini_program_info.secret'),
            'js_code'    => $params['code'],
            'grant_type' => 'authorization_code',
        ]);
        $res=$curl->getResponseBody();
        $res=json_decode($res,true);
        if(empty($res)) throw new MsgException('登录失败！');
        if(isset($res['errcode'])&&$res['errcode']!=0) throw new MsgException($res['errmsg']);

        //设置用户信息
        $token=self::setUserinfo($res);
        $this->jsonReturn(['token' => $token]);
    }

    /**
     * 设置用户信息
     * @param $userinfo
     * @return string
     */
    public static function setUserinfo($userinfo){
        $token = md5(base64_encode(config('api.token_md5_salt') . $userinfo['openid'] . time()));
        Cache::store(Head::REDIS_CONFIG)->set($token, $userinfo, config('mini_program.token_expire'));
        return $token;
    }

    /**
     * 获取用户信息
     * @param $token
     * @return mixed
     */
    public static function getUserInfo($token)
    {
        Db::query();
        return Cache::store(Head::REDIS_CONFIG)->get($token);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = request()->param('token');
        Cache::store(Head::REDIS_CONFIG)->rm($token);
        $this->jsonReturn(true);
    }
}