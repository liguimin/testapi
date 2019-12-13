<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/17
 * Time: 11:14
 */

namespace app\api\model;


use liguimin\utils\Fuc;
use think\Db;
use think\facade\Log;
use think\Model;

class PermissionModel extends Model
{
    protected $pk = 'id';
    protected $table = 'ad_permission';

    //是否为节点
    const IS_NODE = 1;//是
    const NOT_NODE = 0;//否


    /**
     * 获取权限树
     * @return array
     */
    public static function getTree()
    {
        //查所有的权限列表
        $list = self::where('depath', '>', 0)->order('sort_num,id')->select()->toArray();

        //获取树形列表
        $tree = Fuc::getTree($list, 1);

        return $tree;
    }

    /**
     * 获取所有节点
     * @return array
     */
    public static function getNodeList()
    {
        return self::where('is_node', self::IS_NODE)->select()->toArray();
    }

    /**
     * 获取所有节点树
     * @return array
     */
    public static function getTreeNode()
    {
        //获取所有节点
        $list = self::getNodeList();

        //获取树形节点
        $tree = Fuc::getTree($list, 0);

        return $tree;
    }

    /**
     * 多对多关联资源
     * @return \think\model\relation\BelongsToMany
     */
    public function resource()
    {
        return $this->belongsToMany('ResourceModel');
    }

    /**
     * 获取角色有权限访问的资源
     * @param $role_id
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getResourcesByRoleId($role_id, $resource_type = false)
    {
        $m_role_permission     = new RolePermissionModel();
        $role_permission_table = $m_role_permission->getTable();

        $m_permission_resource     = new PermissionResourceModel();
        $permission_resource_table = $m_permission_resource->getTable();

        $m_resource     = new ResourceModel();
        $resource_table = $m_resource->getTable();

        if (is_array($role_id)) {
            $where[] = ['role_id', 'in', $role_id];
        } else {
            $where[] = ['role_id', '=', $role_id];
        }

        $where[] = ['D.is_public', '=', ResourceModel::IS_PUBLIC['PRIVATE']];
        $where[] = ['A.is_del', '=', 0];
        $where[] = ['C.is_del', '=', 0];
        $where[] = ['D.state', '=', 1];

        if ($resource_table !== false) {
            if (is_array($resource_type)) {
                $where[] = ['D.type', 'in', $resource_type];
            } else {
                $where[] = ['D.type', '=', $resource_type];
            }
        }

        //查询该角色有权限的私有资源
        $private_resource = Db::table($role_permission_table)
            ->alias('A')
            ->join("{$this->table} B", 'A.permission_id=B.id')
            ->join("{$permission_resource_table} C", 'B.id=C.permission_id')
            ->join("{$resource_table} D", 'C.resource_id=D.id')
            ->where($where)
            ->field('D.*')
            ->select();

        //查询公共资源
        $public_resource = Db::table($resource_table)
            ->where('is_public', ResourceModel::IS_PUBLIC['PUBLIC'])
            ->where('state', 1)
            ->select();

        return array_merge($private_resource,$public_resource);
    }

}