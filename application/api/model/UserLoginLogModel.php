<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/27
 * Time: 14:16
 */

namespace app\api\model;


use think\Model;

class UserLoginLogModel extends Model
{
    protected $pk='id';
    protected $table='ad_user_login_log';
}