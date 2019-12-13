<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/19
 * Time: 17:12
 */

namespace app\api\model;


use liguimin\utils\Fuc;
use think\Db;
use think\facade\Log;
use think\Model;

class MenuModel extends Model
{
    protected $pk = 'id';
    protected $table = 'ad_menu';

    //是否节点
    const IS_NODE = [
        'NO'  => 0,
        'YES' => 1
    ];

    //状态列表
    const STATES = [
        'DISABLED' => 0,//禁用
        'ENABLED'  => 1,//启用
    ];

    //是否公共
    const IS_PUBLIC = [
        'NO'  => 0,//否
        'YES' => 1,//是
    ];

    /**
     * 获取所有菜单树
     * @return array
     */
    public function getTree()
    {
        $data = self::where('pid', '>', 0)->select()->toArray();
        return Fuc::getTree($data, 1);
    }

    /**
     * 获取所有的节点
     * @return array
     */
    public function getNodeList()
    {
        return $data = self::where('is_node', self::IS_NODE['YES'])->order('sort_num')->select()->toArray();
    }

    /**
     * 获取所有的菜单节点
     * @return array
     */
    public function getTreeNode()
    {
        $data = $this->getNodeList();
        return Fuc::getTree($data, 0);
    }


    /**
     * 获取角色有权限访问的菜单
     * @param $role_id
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getMenuByRoleId($role_id)
    {
        $m_role_menu     = new RoleMenuModel();
        $role_menu_table = $m_role_menu->getTable();

        if (is_array($role_id)) {
            $where[] = ['role_id', 'in', $role_id];
        } else {
            $where[] = ['role_id', '=', $role_id];
        }

        $where[] = ['A.is_public', '=', self::IS_PUBLIC['NO']];
        $where[] = ['B.is_del', '=', 0];
        $where[] = ['A.is_node', '=', self::IS_NODE['NO']];
        $where[] = ['A.state', '=', 1];
        $where[] = ['A.depath', '>', 0];

        //查询该角色有权限的私有菜单
        $private_menu = Db::table($this->table)
            ->alias('A')
            ->join("{$role_menu_table} B", 'A.id=B.menu_id')
            ->where($where)
            ->field('A.*')
            ->select();

        //查询公共菜单
        $is_public   = self::IS_PUBLIC;
        $is_node     = self::IS_NODE;
        $public_menu = Db::table($this->table)
            ->where(function ($query) use ($is_public, $is_node) {
                $query->where('is_public', $is_public['YES'])
                    ->whereOr('is_node', $is_node['YES']);
            })
            ->where('depath', '>', 0)
            ->where('state', 1)
            ->select();

        //排序
        $menu_list = array_merge($private_menu, $public_menu);
        //去重
        usort($menu_list, function ($pre, $next) {
            return $pre['sort_num'] - $next['sort_num'];
        });

        return $menu_list;
    }
}