<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/7
 * Time: 17:56
 */

namespace app\app_api\controller;


use app\common\Sms;
use think\Request;

class SmsController extends Base
{
    /**
     * 发送验证码
     * @param Request $request
     */
    public function send(Request $request)
    {
        $mobile = $request->param('mobile');
        $sms = new Sms();
        $captcha = $sms->send($mobile, 6);
        $this->jsonReturn([
            'captcha' => $captcha,
        ]);
    }
}