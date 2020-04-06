<?php
/**
 * ------------------------
 * |*****api接口相关配置*****|
 * ------------------------
 */

return [
    /**
     * 错误报告级别
     * 0 关闭错误报告
     * E_ERROR | E_WARNING | E_PARSE 报告运行错误
     * E_ALL 报告所有错误
     * E_ALL & ~E_NOTICE  报告 E_NOTICE 之外的所有错误
     */
    'err_lv'            => E_ALL,

    /**
     * 是否开启调试模式，在调试模式下
     * true 开启 系统异常原样返回
     * false 关闭 系统异常仅记录到runtime/log
     */
    'debug'             => true,

    //仅debug 关闭时生效，出错时向前端抛出该消息
    'sys_err_msg'       => '系统出错，请联系管理员',

    //是否记录手工抛出的异常消息到runtime/log
    'save_msg_exp_log'  => true,

    //token的盐
    'token_md5_salt'    => '382c9ab0c4ee36c8824586bblf38a1e6',

    //token有效期(s)
    'token_expire'      => 7200,

    //微信小程序配置
    'mini_program_info' => [
        'appid'  => 'wx597fbb5abbd10eeb',
        'secret' => '86f177d9c9fb1b644cab12a052aa921b',
    ],
];