<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/18
 * Time: 10:05
 */

namespace app\api\model;


use think\Db;
use think\facade\Log;
use think\Model;
use think\model\Pivot;

class RolePermissionModel extends Pivot
{
    protected $pk='id';
    protected $table='ad_role_permission';

    /**
     * 根据角色ID获取该角色的权限ID列表
     * @param $role_id
     * @return array
     */
    public static function getIdListByRole($role_id){
        return self::where('role_id',$role_id)
            ->where('is_del',0)
            ->column('permission_id');
    }

    /**
     * 批量添加，如果唯一索引冲突，则将其设置为未删除
     * @param $data
     * @return mixed
     */
    public function insertReplace($data){
        $values_sql='';
        foreach($data as $key=>$val){
            $values_sql.=",({$val['role_id']},{$val['permission_id']},'{$val['create_time']}')";
        }

        $values_sql=ltrim($values_sql,',');

        $sql="
        insert into
        {$this->table}
        (role_id,permission_id,create_time)
        values
        {$values_sql}
        on duplicate key update
        is_del=0,
        update_time=now()
        ";

        $res=Db::execute($sql);

        return $res;
    }
}