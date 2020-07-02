<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/31
 * Time: 12:43
 */

namespace app\common;


use think\facade\Cache;

class Sms
{
    /**
     * 发送短信验证码
     * @param $mobile
     * @param $len
     * @return string
     */
    public function send($mobile,$len){
        $nums=range(1,9);
        $captcha='';
        for($i=0;$i<$len;$i++){
            $captcha.=array_rand($nums);
        }

        Cache::store(Head::REDIS_CONFIG)->set('m_'.$mobile,$captcha,300);

        return $captcha;
    }

    /**
     * 校验验证码
     * @param $mobile
     * @param $captcha
     * @return bool
     */
    public function verify($mobile,$captcha){
        $srv_captcha=Cache::store(Head::REDIS_CONFIG)->get('m_'.$mobile);
        return $srv_captcha==$captcha;
    }
}