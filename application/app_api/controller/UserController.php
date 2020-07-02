<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/31
 * Time: 12:08
 */

namespace app\app_api\controller;


use app\common\exception\MsgException;
use app\common\Sms;
use app\model\ExUserModel;
use app\validate\ExUserValidate;
use liguimin\utils\Fuc;
use think\Request;

class UserController extends Base
{
    /**
     * 保存手机号
     * @param Request $request
     */
    public function save(Request $request)
    {
        $params = $request->post();
        $this->validateParams(new ExUserValidate(),'s_save',$params);

        //检查验证码
        $sms=new Sms();
        $is_verify_pass=$sms->verify($params['mobile'],$params['captcha']);
        if(!$is_verify_pass) throw new MsgException('验证码不正确！');

        $m = new ExUserModel();
        $m->insert([
            'mobile'      => $params['mobile'],
            'create_time' => Fuc::getNow(),
        ]);
    }
}