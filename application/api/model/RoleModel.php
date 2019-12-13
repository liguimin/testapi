<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/19
 * Time: 17:12
 */

namespace app\api\model;


use think\Model;

class RoleModel extends Model
{
    protected $pk='id';
    protected $table='ad_role';

    public function permissions(){
        return $this->belongsToMany('PermissionModel');
    }
}