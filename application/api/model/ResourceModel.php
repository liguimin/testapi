<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/25
 * Time: 9:32
 */

namespace app\api\model;


use think\Model;

class ResourceModel extends Model
{
    protected $pk = 'id';
    protected $table = 'ad_resource';

    //状态
    const STATES = [
        'DISABLED' => 0,
        'ENABLED'  => 1,
    ];

    //资源类型
    const TYPE = [
        'API'   => 1,
        'ROUTE' => 2,
        'BTN'   => 3,
        'FIELD' => 4
    ];


    //方法类型
    const METHOD = [
        'NONE'   => 0,
        'GET'    => 1,
        'POST'   => 2,
        'PUT'    => 3,
        'PATCH'  => 4,
        'DELETE' => 5
    ];

    //是否公共资源
    const IS_PUBLIC = [
        'PRIVATE' => 0,//私有
        'PUBLIC'  => 1//共有
    ];
}