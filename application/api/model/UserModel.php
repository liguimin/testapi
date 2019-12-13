<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/25
 * Time: 9:32
 */

namespace app\api\model;


use think\Model;

class UserModel extends Model
{
    protected $pk = 'id';
    protected $table = 'ad_user';
}