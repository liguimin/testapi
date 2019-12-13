<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/25
 * Time: 16:47
 */

namespace app\api\model;


use think\Db;
use think\Model;

class PermissionResourceModel extends Model
{
    protected $pk='id';
    protected $table='ad_permission_resource';


    /**
     * 批量添加，如果唯一索引冲突，则将其设置为未删除
     * @param $data
     * @return mixed
     */
    public function insertReplace($data){
        $values_sql='';
        foreach($data as $key=>$val){
            $values_sql.=",({$val['permission_id']},{$val['resource_id']},'{$val['create_time']}')";
        }

        $values_sql=ltrim($values_sql,',');

        $sql="
        insert into
        {$this->table}
        (permission_id,resource_id,create_time)
        values
        {$values_sql}
        on duplicate key update
        is_del=0
        ";

        $res=Db::execute($sql);

        return $res;
    }
}