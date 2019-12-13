<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/19
 * Time: 17:12
 */

namespace app\api\model;


use think\Db;
use think\Model;

class RoleMenuModel extends Model
{
    protected $pk='id';
    protected $table='ad_role_menu';

    /**
     * 根据角色ID获取关联的菜单ID
     * @param $role_id
     * @return array
     */
    public function getIdsByRoleId($role_id){
        return self::where('role_id',$role_id)->where('is_del',0)->column('menu_id');
    }

    /**
     * 批量添加，如果唯一索引冲突，则将其设置为未删除
     * @param $data
     * @return mixed
     */
    public function insertReplace($data){
        $values_sql='';
        foreach($data as $key=>$val){
            $values_sql.=",({$val['role_id']},{$val['menu_id']},'{$val['create_time']}')";
        }

        $values_sql=ltrim($values_sql,',');

        $sql="
        insert into
        {$this->table}
        (role_id,menu_id,create_time)
        values
        {$values_sql}
        on duplicate key update
        is_del=0,
        update_time=now()
        ";

        $res=Db::query($sql);

        return $res;
    }
}