<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/24
 * Time: 15:39
 */

namespace app\api\controller;


use app\common\exception\MsgException;
use think\Controller;
use think\exception\ValidateException;
use think\facade\Log;
use think\Validate;

class Base extends Controller
{
    protected $middleware = [
        'AdminAuth'
    ];

    protected function initialize()
    {
        parent::initialize();
        //设置错误处理级别
        error_reporting(config('api.err_lv'));
        //注册错误处理机制
        @set_exception_handler(array($this, 'exceptionHandler'));
    }

    /**
     * 返回json
     * @param $data
     * @param string $msg
     * @param int $code
     * @return \think\response\Json
     */
    protected function jsonReturn($data, $msg = '', $code = 200)
    {
        json([
            'data' => $data,
            'msg'  => $msg
        ],$code)->send();
        exit();
    }

    /**
     * 验证参数
     * @param Validate $validate
     * @param $params
     */
    protected function validateParams(Validate $validate,$scene,$params){
        if(!$validate->scene($scene)->check($params)){
            throw new ValidateException($validate->getError());
        }
    }

    /**
     * 错误和异常处理方法
     * @param \Exception $e
     */
    public function exceptionHandler( $e){
        $log_msg="file:{$e->getFile()}-*****-line:{$e->getLine()}-*****-msg:{$e->getMessage()}";
        $log_msg=mb_convert_encoding($log_msg,'UTF-8','GBK');
        if($e instanceof MsgException||$e instanceof ValidateException){ //服务端主动抛出的异常消息原样返回
            if(config('api.save_msg_exp_log')){
                Log::error($log_msg);
            }
            $this->jsonReturn([],$e->getMessage(),400);
        }else{ //其他异常
            Log::error($log_msg);
            if(config('api.debug')){
                $msg=$log_msg;
            }else{
                $msg=config('api.sys_err_msg');
            }
            $this->jsonReturn([],$msg,400);
        }
    }
}