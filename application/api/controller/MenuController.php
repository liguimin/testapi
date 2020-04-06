<?php

namespace app\api\controller;

use app\api\model\MenuModel;
use app\api\model\PermissionModel;
use app\api\model\RoleMenuModel;
use app\api\model\RoleModel;
use app\api\model\RolePermissionModel;
use app\api\model\UserRoleModel;
use app\common\exception\MsgException;
use app\validate\MenuValidate;
use app\validate\RolePermissionValidate;
use app\validate\RoleValidate;
use liguimin\utils\Fuc;
use think\Controller;
use think\Db;
use think\exception\ValidateException;
use think\facade\Log;
use think\Request;

class MenuController extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $m_menu = new MenuModel();
        $node_lsit = $m_menu->getNodeList();
        $tree_node = Fuc::getTree($node_lsit, 0, 'pid', 'id', 'children', function ($val) {
            $curr_val = [];
            if (!empty($val)) {
                $curr_val = [
                    'title'    => $val['menu_name'],
                    'label'    => $val['menu_name'],
                    'value'    => $val['id'],
                    'key'      => $val['id'],
                    'children' => $val['children'],
                ];
            }
            return $curr_val;
        });

        $this->jsonReturn([
            'list'      => $node_lsit,
            'tree_node' => $tree_node,
        ]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $params = $request->post();
        $this->validateParams(new MenuValidate(), 's_save', $params);

        //查上级信息
        $m_menu = new MenuModel();
        $p_info = $m_menu->where('id', $params['pid'])->find();
        if (empty($p_info)) throw new ValidateException('上级ID不存在！');

        $is_node = MenuModel::IS_NODE;

        //添加菜单
        $m_menu->insert([
            'pid'         => $params['pid'],
            'menu_name'   => $params['menu_name'],
            'is_public'   => $params['is_public'],
            'path'        => $p_info['path'] . $params['pid'] . '>',
            'sort_num'    => $params['sort_num'],
            'is_node'     => $params['is_node'],
            'state'       => $params['state'],
            'depath'      => $p_info['depath'] + 1,
            'route'       => $params['is_node'] == $is_node['YES'] ? 0 : $params['route'],
            'create_time' => Fuc::getNow(),
        ]);

        $this->jsonReturn(true);

    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $this->validateParams(new MenuValidate(), 's_edit', ['id' => $id]);
        //获取树节点
        $m_menu = new MenuModel();
        $node_lsit = $m_menu->getNodeList();
        $tree_node = Fuc::getTree($node_lsit, 0, 'pid', 'id', 'children', function ($val) {
            $curr_val = [];
            if (!empty($val)) {
                $curr_val = [
                    'title'    => $val['menu_name'],
                    'label'    => $val['menu_name'],
                    'value'    => $val['id'],
                    'key'      => $val['id'],
                    'children' => $val['children'],
                ];
            }
            return $curr_val;
        });

        //获取该条数据信息
        $data = $m_menu->where('id', $id)->find();

        $this->jsonReturn([
            'tree_node' => $tree_node,
            'list'      => $node_lsit,
            'data'      => $data,
        ]);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $params = $request->param();
        $params['id'] = $id;
        $this->validateParams(new MenuValidate(), 's_update', $params);

        $is_node = MenuModel::IS_NODE;
        $m_menu = new MenuModel();
        $curr_data = $m_menu->where('id', $id)->find();
        $edit_data = [
            'pid'         => $params['pid'],
            'menu_name'   => $params['menu_name'],
            'is_public'   => $params['is_public'],
            'sort_num'    => $params['sort_num'],
            'is_node'     => $params['is_node'],
            'route'       => $params['is_node'] == $is_node['YES'] ? 0 : $params['route'],
            'state'       => $params['state'],
            'update_time' => Fuc::getNow(),
        ];

        Db::startTrans();
        try {

            //检查传入的上级ID是否和数据库一致
            if ($curr_data['pid'] != $params['pid']) {
                $p_info = $m_menu->where('id', $params['pid'])->find();
                if (empty($p_info)) throw new ValidateException('上级ID不存在！');
                $edit_data['depath'] = $p_info['depath'] + 1;
                $edit_data['path'] = $p_info['path'] . $params['pid'] . '>';

                //将其子菜单全部转移到新的父级ID下
                $m_menu->where('path', 'like', '%>' . $id . '>%')->update([
                    'path'   => Db::raw("replace(path,'{$curr_data['path']}','{$edit_data['path']}')"),
                    'depath' => Db::raw("(length(path)-length(replace(path,'>',''))-2)"),
                ]);
            }

            //修改本条数据
            $m_menu->where('id', $id)->update($edit_data);
            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }


        $this->jsonReturn(true);
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->validateParams(new MenuValidate(), 's_delete', ['id' => $id]);
        Db::startTrans();
        try {
            //删除单条信息
            MenuModel::where('id', $id)->delete();
            //删除所有子菜单
            MenuModel::where('path', 'like', '%>' . $id . '>%')->delete();

            Db::commit();
            $this->jsonReturn(true);
        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

    }

    /**
     * 更新状态
     *
     * @param $id
     */
    public function updState($id)
    {
    }


    /**
     * 获取当前登录用户的菜单树
     */
    public function getUserMenu()
    {
        $userinfo = request()->param('userinfo');
        $role_ids = UserRoleModel::where('user_id', $userinfo['id'])->column('role_id');

        $m_menu = new MenuModel();
        $menu_list = $m_menu->getMenuByRoleId($role_ids);

        //获得所有路由和要展开的菜单Key
        $is_node = MenuModel::IS_NODE;
        $menu_route_list = [];
        $open_keys = [];
        foreach ($menu_list as $key => $val) {
            if ($val['is_node'] == $is_node['YES']) {
                $open_keys[] = (string)$val['id'];
            } else {
                $menu_route_list[] = $val['route'];
            }
            $menu_list[$key]['id'] = (string)$val['id'];
        }

        //获得树形菜单
        $menu_tree = Fuc::getTree($menu_list, 1);

        $this->jsonReturn([
            'menu_list'       => $menu_list,
            'menu_tree'       => $menu_tree,
            'menu_route_list' => $menu_route_list,
            'open_keys'       => $open_keys,
        ]);
    }

}
