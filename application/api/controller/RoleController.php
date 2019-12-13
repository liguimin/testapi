<?php

namespace app\api\controller;

use app\api\model\MenuModel;
use app\api\model\PermissionModel;
use app\api\model\RoleMenuModel;
use app\api\model\RoleModel;
use app\api\model\RolePermissionModel;
use app\common\exception\MsgException;
use app\validate\RoleMenuValidate;
use app\validate\RolePermissionValidate;
use app\validate\RoleValidate;
use liguimin\utils\Fuc;
use think\Controller;
use think\Db;
use think\exception\ValidateException;
use think\facade\Log;
use think\Request;

class RoleController extends Base
{
    //状态
    const ENABLE_STATE = 1;
    const DISABLE_STATE = 0;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $m_role = new RoleModel();

        //查询条件
        $where = [];

        //查记录
        $data = $m_role
            ->where($where)
            ->order('sort_num')
            ->select();

        $index = 0;
        foreach ($data as $key => $val) {
            $val['index'] = ++$index;
            $data[$key]   = $val;
        }

        $this->jsonReturn([
            'data' => $data
        ]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //验证参数
        $params = $request->post();
        $this->validateParams(new RoleValidate(), 's_create', $params);
        $m_role = new RoleModel();
        $m_role->save([
            'name'        => $params['name'],
            'state'       => $params['state'],
            'sort_num'    => $params['sort_num'],
            'create_time' => Fuc::getNow()
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
        //验证参数
        $this->validateParams(new RoleValidate(), 's_edit', ['id' => $id]);

        //查询信息
        $data = RoleModel::where('id', $id)->find();

        $this->jsonReturn($data);
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
        $params       = $request->param();
        $params['id'] = $id;
        $this->validateParams(new RoleValidate(), 's_edit', $params);

        $res = RoleModel::where('id', $id)->update([
            'name'        => $params['name'],
            'state'       => $params['state'],
            'sort_num'    => $params['sort_num'],
            'update_time' => Fuc::getNow()
        ]);

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
        //验证
        $this->validateParams(new RoleValidate(), 's_delete', ['id' => $id]);

        //删除
        $res = RoleModel::where('id', $id)->delete();

        $this->jsonReturn(true);
    }

    /**
     * 更新状态
     *
     * @param $id
     */
    public function setState($id)
    {
        $params       = request()->put();
        $params['id'] = $id;

        //验证参数
        $this->validateParams(new RoleValidate(), 's_updState', $params);

        //修改信息
        $state = $params['state'] == self::ENABLE_STATE ? self::DISABLE_STATE : self::ENABLE_STATE;
        $res   = RoleModel::where('id', $id)->update([
            'state'       => $state,
            'update_time' => Fuc::getNow()
        ]);

        //返回结果
        $this->jsonReturn([
            'state' => $state
        ]);
    }


    /**
     * 获取权限
     * @param $id
     */
    public function getRolePermission($id)
    {
        //查出所有的权限
        $tree = PermissionModel::getTree();

        //获取角色的权限
        $role_permission = RolePermissionModel::getIdListByRole($id);

        $this->jsonReturn([
            'tree'            => $tree,
            'role_permission' => $role_permission
        ]);
    }

    /**
     * 修改角色的权限
     * @param $id
     * @throws MsgException
     */
    public function saveRolePermission($id)
    {
        $role_permission    = request()->put('role_permission', []);//修改的权限ID集
        $re_role_permission = request()->put('re_role_permission', []);//原来的权限ID集
        $this->validateParams(new RolePermissionValidate(), 's_save', ['role_id' => $id]);

        //找出本次操作取消掉的权限
        $del_role_permission = array_diff($re_role_permission, $role_permission);

        //找出本次操作添加的权限
        $add_role_permission = array_diff($role_permission, $re_role_permission);

        if (!empty($del_role_permission) || !empty($add_role_permission)) {
            Db::startTrans();
            try {
                $m_role_permission = new RolePermissionModel();

                //取消权限
                if (!empty($del_role_permission)) {
                    $m_role_permission->where('role_id', $id)->where('permission_id', 'in', $del_role_permission)->update([
                        'is_del'   => 1,
                        'del_time' => Fuc::getNow()
                    ]);
                }


                //增加权限
                if (!empty($add_role_permission)) {
                    $insert_data = [];
                    foreach ($add_role_permission as $key => $val) {
                        $insert_data[] = [
                            'role_id'       => $id,
                            'permission_id' => $val,
                            'create_time'   => Fuc::getNow()
                        ];
                    }

                    $m_role_permission->insertReplace($insert_data);
                }

                Db::commit();

            } catch (\Exception $e) {
                Db::rollback();
                throw new MsgException($e->getMessage());
            }

        }

        $this->jsonReturn(true);
    }

    /**
     * 获取角色菜单
     * @param $id
     */
    public function getRoleMenu($id)
    {
        $m_menu    = new MenuModel();
        $menu_tree = $m_menu->getTree();

        $m_role_menu   = new RoleMenuModel();
        $role_menu_ids = $m_role_menu->getIdsByRoleId($id);
        $role_menu_ids=array_map(function($val){
            return (string)$val;
        },$role_menu_ids);

        $this->jsonReturn([
            'menu_tree'     => array_values($menu_tree),
            'role_menu_ids' => $role_menu_ids
        ]);
    }

    /**
     * 修改角色的权限
     * @param $id
     * @throws MsgException
     */
    public function saveRoleMenu($id)
    {
        $role_menu    = request()->put('role_menu_ids', []);
        $re_role_menu = request()->put('re_role_menu_ids', []);
        $this->validateParams(new RoleMenuValidate(), 's_save', ['role_id' => $id]);

        //找出本次操作取消掉的权限
        $del_role_menu = array_diff($re_role_menu, $role_menu);

        //找出本次操作添加的权限
        $add_role_menu = array_diff($role_menu, $re_role_menu);

        if (!empty($del_role_menu) || !empty($add_role_menu)) {
            Db::startTrans();
            try {
                $m_role_menu = new RoleMenuModel();

                //取消权限
                if (!empty($del_role_menu)) {
                    $m_role_menu->where('role_id', $id)->where('menu_id', 'in', $del_role_menu)->update([
                        'is_del'   => 1,
                        'del_time' => Fuc::getNow()
                    ]);
                }


                //增加权限
                if (!empty($add_role_menu)) {
                    $insert_data = [];
                    foreach ($add_role_menu as $key => $val) {
                        $insert_data[] = [
                            'role_id'     => $id,
                            'menu_id'     => $val,
                            'create_time' => Fuc::getNow()
                        ];
                    }

                    $m_role_menu->insertReplace($insert_data);
                }

                Db::commit();

            } catch (\Exception $e) {
                Db::rollback();
                throw new MsgException($e->getMessage());
            }

        }

        $this->jsonReturn(true);
    }


}
