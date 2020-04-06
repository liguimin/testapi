<?php

namespace app\api\controller;

use app\api\model\PermissionResourceModel;
use app\api\model\ResourceModel;
use app\validate\ResourceValidate;
use liguimin\utils\Fuc;
use think\Controller;
use think\exception\ValidateException;
use think\facade\Log;
use think\Request;

class ResourceController extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = input('page');
        $page_size = input('page_size');
        $m_source = new ResourceModel();

        $where = [];

        //根据资源名称搜索
        $s_name = input('name');
        if (!empty($s_name)) {
            $where[] = ['name', 'like', '%' . $s_name . '%'];
        }

        //根据资源标识搜索
        $s_identify = input('identify');
        if (!empty($s_identify)) {
            $where[] = ['identify', 'like', '%' . $s_identify . '%'];
        }

        //根据类型搜索
        $s_type = input('type');
        if (!Fuc::isEmpty($s_type, false)) {
            $where[] = ['type', '=', $s_type];
        }

        //根据状态搜索
        $s_state = input('state');
        if (!Fuc::isEmpty($s_state, false)) {
            $where[] = ['state', '=', $s_state];
        }

        //根据方法搜索
        $s_mehtod = input('method');
        if (!Fuc::isEmpty($s_mehtod, false)) {
            $where[] = ['method', '=', $s_mehtod];
        }

        //根据是否公共搜索
        $s_is_public = input('is_public');
        if (!Fuc::isEmpty($s_is_public, false)) {
            $where[] = ['is_public', '=', $s_is_public];
        }

        //根据开始时间搜索
        $s_create_time = input('s_create_time');
        if (!empty($s_create_time)) {
            $where[] = ['create_time', '>=', $s_create_time];
        }

        //根据结束时间搜索
        $e_create_time = input('e_create_time');
        if (!empty($e_create_time)) {
            $where[] = ['create_time', '<=', $e_create_time];
        }


        //数据条数
        $count = $m_source->where($where)->count();

        //数据记录
        $data = $m_source->where($where)->order('id desc')->page($page, $page_size)->select()->toArray();

        $index = Fuc::getOffset($page, $page_size);
        foreach ($data as $key => $val) {
            $val['index'] = ++$index;
            $val['key'] = $val['id'];
            $val['label'] = $val['name'];
            $val['disabled'] = false;
            $val['method_name'] = Fuc::getValue(ResourceModel::METHOD_NAMES, $val['method']);
            $data[$key] = $val;
        }

        //首次加载且是修改权限，则后台的data需要将已选的资源列表全部返回
        $h_data = [];
        $permission_id = input('permission_id');
        if (!empty($permission_id) && $page == 1) {
            $resource_ids = PermissionResourceModel::where('permission_id', $permission_id)->where('is_del', 0)->column('resource_id');
            $h_data = ResourceModel::where('id', 'in', $resource_ids)->select()->toArray();
        }

        $this->jsonReturn([
            'count'  => $count,
            'data'   => Fuc::arrayUnique(array_merge($data, $h_data), 'id'),
            'h_data' => $h_data,
        ]);
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
        //校验参数
        $params = $request->param();
        $this->validateParams(new ResourceValidate(), 's_save', $params);
        //如果是API，则必须选择请求方法
        $method = Fuc::getValue($params, 'method', 0);
        $method = $method ? $method : 0;
        $types = ResourceModel::TYPE;
        if ($params['type'] == $types['API'] && empty($method)) throw new ValidateException('请选择请求方法！');
        if ($params['type'] != $types['API']) {
            $method = 0;
        }

        $m_resource = new ResourceModel();
        //检查标识是否已存在
        $is_exist = $m_resource->where('identify', $params['identify'])->where('method', $method)->find();
        if (!empty($is_exist)) throw new ValidateException('该标识已存在，请不要重复添加！');

        $insert_data = [
            'name'        => $params['name'],
            'type'        => $params['type'],
            'identify'    => $params['identify'],
            'method'      => $method,
            'state'       => $params['state'],
            'is_public'   => $params['is_public'],
            'create_time' => Fuc::getNow(),
        ];
        //添加资源
        $m_resource->insert($insert_data);

        $insert_data['id'] = intval($m_resource->getLastInsID());
        $insert_data['index'] = 0;
        $this->jsonReturn([
            'insert_data' => $insert_data,
        ]);
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
        $this->validateParams(new ResourceValidate(), 's_edit', ['id' => $id]);
        $data = ResourceModel::where('id', $id)->find();

        $this->jsonReturn([
            'data' => $data,
            'test' => ['test' => '1'],
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
        $this->validateParams(new ResourceValidate(), 's_update', $params);

        //如果是API，则必须选择请求方法
        $params['method'] = Fuc::getValue($params, 'method', 0);
        if ($params['type'] == ResourceModel::TYPE['API'] && empty($params['method'])) throw new ValidateException('请选择请求方法！');

        //检查标识是否已存在
        $m_resource = new ResourceModel();
        $is_exist = $m_resource->where('identify', $params['identify'])->where('method', $params['method'])->where('id', '<>', $id)->find();
        if (!empty($is_exist)) throw new ValidateException('该标识已存在！');

        //执行修改
        $m_resource->where('id', $id)->update([
            'name'        => $params['name'],
            'type'        => $params['type'],
            'identify'    => $params['identify'],
            'method'      => $params['method'],
            'state'       => $params['state'],
            'is_public'   => $params['is_public'],
            'update_time' => Fuc::getNow(),
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
        $this->validateParams(new ResourceValidate(), 's_del', ['id' => $id]);
        ResourceModel::where('id', $id)->delete();

        $this->jsonReturn(true);
    }

    /**
     * 修改状态
     * @param $id
     */
    public function updState($id)
    {
        $params = request()->param();
        $params['id'] = $id;
        $this->validateParams(new ResourceValidate(), 's_updState', $params);

        $states = ResourceModel::STATES;
        //修改状态
        $user = new ResourceModel();
        $state = $params['state'] == $states['ENABLED'] ? $states['DISABLED'] : $states['ENABLED'];
        $user->save([
            'state' => $state,
        ], ['id' => $id]);

        //返回结果
        $this->jsonReturn([
            'state' => $state,
        ]);
    }
}
