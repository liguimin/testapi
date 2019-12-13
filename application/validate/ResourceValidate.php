<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\validate;


use think\Validate;

class ResourceValidate extends Validate
{
    public $field = [
        'id'        => 'ID',
        'name'      => '资源名称',
        'type'      => '资源类型',
        'identify'  => '资源标识',
        'method'    => '请求方法',
        'is_public' => '是否公共',
        'state'     => '状态',
    ];

    protected $rule = [
        'id'        => 'require|number',
        'name'      => 'require',
        'type'      => 'require|number',
        'identify'  => 'require',
        'method'    => 'require|number',
        'is_public' => 'require|number',
        'state'     => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_save'     => ['name', 'type', 'identify', 'is_public', 'state'],
        's_updState' => ['id', 'state'],
        's_del'      => ['id'],
        's_edit'     => ['id'],
        's_update'   => ['id', 'name', 'type', 'identify', 'is_public', 'state'],
    ];
}