<?php

namespace app\api\controller;

use app\api\model\PermissionModel;
use app\api\model\PermissionResourceModel;
use app\api\model\ResourceModel;
use app\api\model\RoleModel;
use app\api\model\UserRoleModel;
use app\common\exception\MsgException;
use app\validate\PermissionValidate;
use liguimin\utils\Fuc;
use think\Controller;
use think\Db;
use think\exception\ValidateException;
use think\facade\Log;
use think\Request;

class PermissionController extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $params = $request->param();
        $this->validateParams(new PermissionValidate(), 's_save', $params);

        $m_permission = new PermissionModel();
        //查父级ID信息
        $p_info = $m_permission->where('id', $params['pid'])->find();
        if (empty($p_info)) throw new MsgException('上级信息不存在！');

        Db::startTrans();
        try {
            $now = Fuc::getNow();
            //添加一条权限记录
            $m_permission->insert([
                'pid'         => $params['pid'],
                'name'        => $params['name'],
                'state'       => $params['state'],
                'path'        => $p_info['path'] . $params['pid'] . '>',
                'depath'      => $p_info['depath'] + 1,
                'is_node'     => $params['is_node'],
                'sort_num'    => $params['sort_num'],
                'create_time' => $now
            ]);

            if ($params['is_node'] == PermissionModel::NOT_NODE && !empty($params['checked_resources'])) {
                $permission_last_id = $m_permission->getLastInsID();

                //添加关联记录
                $m_permission_resource = new PermissionResourceModel();
                $insert_resource       = [];
                foreach ($params['checked_resources'] as $key => $val) {
                    $insert_resource[] = [
                        'permission_id' => $permission_last_id,
                        'resource_id'   => $val,
                        'create_time'   => $now
                    ];
                }
                if (!empty($insert_resource)) {
                    $m_permission_resource->insertReplace($insert_resource);
                }

            }
            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

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
        $this->validateParams(new PermissionValidate(), 's_edit', ['id' => $id]);

        //获取节点树
        $list      = PermissionModel::getNodeList();
        $tree_node = Fuc::getTree($list, 0, 'pid', 'id', 'children', function ($val) {
            $curr_val = [];
            if (!empty($val)) {
                $curr_val = [
                    'title'    => $val['name'],
                    'value'    => $val['id'],
                    'key'      => $val['id'],
                    'children' => $val['children']
                ];
            }
            return $curr_val;
        });

        //获取当前ID的信息
        $info = PermissionModel::where('id', $id)->find();

        //获取当前已关联的资源
        $permission_resources = PermissionResourceModel::where('permission_id', $id)->where('is_del', 0)->field('resource_id')->select()->toArray();
        $select_keys          = array_column($permission_resources, 'resource_id');

        //返回数据
        $this->jsonReturn([
            'treeNode'   => $tree_node,
            'info'       => $info,
            'targetKeys' => empty($select_keys) ? [] : $select_keys
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
        //验证参数
        $params = $request->param();
        Log::error($params);
        $params['id'] = $id;
        $this->validateParams(new PermissionValidate(), 's_update', $params);

        $m_permission = new PermissionModel();
        //查父级ID信息
        $p_info = $m_permission->where('id', $params['pid'])->find();
        if (empty($p_info)) throw new MsgException('上级信息不存在！');

        //查当前ID信息
        $curr_data=$m_permission->where('id',$id)->find();

        //检查选中的keys
        $target_keys    = Fuc::getValue($params, 'checked_resources');
        $re_target_keys = Fuc::getValue($params, 're_checked_resources');
        if ($params['is_node'] == PermissionModel::NOT_NODE) {
            if (!is_array($target_keys)) throw new ValidateException('选中的key必须是一个数组！');
            if (!is_array($re_target_keys)) throw new ValidateException('初始选中的key必须是一个数组！');
        }

        $now = Fuc::getNow();
        Db::startTrans();
        try {
            $edit_data=[
                'pid'         => $params['pid'],
                'name'        => $params['name'],
                'state'       => $params['state'],
                'is_node'     => $params['is_node'],
                'sort_num'    => $params['sort_num'],
                'update_time' => $now
            ];

            //检查传入的上级ID是否和数据库一致
            if ($curr_data['pid'] != $params['pid']) {
                $p_info = $m_permission->where('id', $params['pid'])->find();
                if (empty($p_info)) throw new ValidateException('上级ID不存在！');
                $edit_data['depath'] = $p_info['depath'] + 1;
                $edit_data['path']   = $p_info['path'] . $params['pid'] . '>';

                //将其子菜单全部转移到新的父级ID下
                $m_permission->where('path', 'like', '%>' . $id . '>%')->update([
                    'path'   => Db::raw("replace(path,'{$curr_data['path']}','{$edit_data['path']}')"),
                    'depath' => Db::raw("(length(path)-length(replace(path,'>',''))-2)")
                ]);
            }
            //修改权限信息
            $m_permission->where('id', $id)->update($edit_data);



            if ($params['is_node'] == PermissionModel::NOT_NODE) {
                $m_permission_resource = new PermissionResourceModel();
                //删除的关联资源
                $del_keys = array_diff($re_target_keys, $target_keys);
                if (!empty($del_keys)) {
                    $m_permission_resource->where('permission_id', $id)->where('resource_id', 'in', $del_keys)->update([
                        'is_del'   => 1,
                        'del_time' => $now
                    ]);
                }

                //增加的关联资源
                $insert_keys = array_diff($target_keys, $re_target_keys);
                if (!empty($insert_keys)) {
                    $insert_resource = [];
                    foreach ($insert_keys as $key => $val) {
                        $insert_resource[] = [
                            'permission_id' => $id,
                            'resource_id'   => $val,
                            'create_time'   => $now
                        ];
                    }
                    if (!empty($insert_resource)) {
                        $m_permission_resource->insertReplace($insert_resource);
                    }
                }
            }

            Db::commit();

            $this->jsonReturn(true);

        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->validateParams(new PermissionValidate(), 's_del', ['id' => $id]);

        Db::startTrans();
        try {
            //删除该ID及其子权限
            PermissionModel::where('id', $id)->whereOr('path', 'like', '%>' . $id . '>%')->delete();

            Db::commit();
            $this->jsonReturn(true);;
        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }
    }

    /**
     * 获取节点树
     */
    public function getTreeNode()
    {
        $list      = PermissionModel::getNodeList();
        $tree_node = Fuc::getTree($list, 0, 'pid', 'id', 'children', function ($val) {
            $curr_val = [];
            if (!empty($val)) {
                $curr_val = [
                    'title'    => $val['name'],
                    'value'    => $val['id'],
                    'key'      => $val['id'],
                    'children' => $val['children']
                ];
            }
            return $curr_val;
        });
        $this->jsonReturn([
            'tree_node' => $tree_node
        ]);
    }


    /**
     * 获取当前登录用户的权限
     * @return array
     */
    public function getUserPermission()
    {
        $userinfo = request()->param('userinfo');
        $role_ids = UserRoleModel::where('user_id', $userinfo['id'])->column('role_id');

        $m_permission = new PermissionModel();
        $types        = ResourceModel::TYPE;
        $resources    = $m_permission->getResourcesByRoleId($role_ids, [
            $types['ROUTE'],
            $types['BTN']
        ]);

        $route_resources = [];
        foreach ($resources as $key => $val) {
            if ($val['type'] == $types['ROUTE']) {
                $route_resources[] = $val;
            }
        }

        $btn_resource = [];
        foreach ($resources as $key => $val) {
            if ($val['type'] == $types['BTN']) {
                $btn_resource[] = $val;
            }
        }

        $this->jsonReturn([
            'permissions'=>[
                'routePermissions' => $route_resources,
                'btnPermissions'   => $btn_resource
            ]
        ]);
    }
}
