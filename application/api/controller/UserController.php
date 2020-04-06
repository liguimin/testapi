<?php
namespace app\api\controller;

use app\api\model\RoleModel;
use app\api\model\User;
use app\api\model\UserModel;
use app\api\model\UserRoleModel;
use app\common\exception\MsgException;
use app\validate\UserValidate;
use liguimin\utils\Fuc;
use think\Db;
use think\exception\ValidateException;
use think\facade\Env;
use think\facade\Log;

class UserController extends Base
{
    protected $middleware = [
        'AdminAuth' => ['except' => ['login']],
    ];

    const ENABLE_STATE = 1;//启用状态
    const DISABLED_STATE = 0;//禁用状态

    /**
     * 获取列表
     */
    public function index()
    {
        $page = input('page');
        $page_size = input('page_size');
        $m_user = new UserModel();

        $username = input('username', '');
        $name = input('name', '');
        $birthday = input('birthday', '');
        $state = input('state', '');
        $s_create_time = input('s_create_time', '');
        $e_create_time = input('e_create_time', '');

        $where = [];
        //按用户名查询
        if ($username !== '') {
            $where[] = ['username', '=', $username];
        }

        //按姓名查询
        if ($name !== '') {
            $where[] = ['name', 'like', '%' . $name . '%'];
        }

        //按生日查询
        if ($birthday !== '') {
            $where[] = ['birthday', '=', $birthday];
        }

        //按状态查询
        if ($state !== '') {
            $where[] = ['state', '=', $state];
        }

        //按创建开始时间查询
        if ($s_create_time !== '') {
            $where[] = ['create_time', '>=', $s_create_time];
        }

        //按创建结束时间查询
        if ($e_create_time !== '') {
            $where[] = ['create_time', '<=', $e_create_time];
        }

        //数据条数
        $count = $m_user->where($where)->count();

        //数据记录
        $data = $m_user->where($where)->order('id desc')->page($page, $page_size)->select();

        $index = Fuc::getOffset($page, $page_size);
        foreach ($data as $key => $val) {
            $val['index'] = ++$index;
            $val['avatar'] = request()->domain() . $val['avatar'];
            unset($data[$key]['pwd']);
        }

        $this->jsonReturn([
            'count' => $count,
            'data'  => $data,
        ]);
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $role_list = RoleModel::all();
        $this->jsonReturn([
            'roleList' => $role_list,
        ]);
    }

    /**
     * 添加用户
     */
    public function save()
    {
        $params = request()->param();

        //验证参数
        $this->validateParams(new UserValidate(), 's_create', $params);


        //检查用户名是否存在
        $is_exist = UserModel::where('username', $params['username'])->value('username');
        if (!empty($is_exist)) {
            $this->jsonReturn([], '用户名已存在！', 409);
        }

        //检查是否填写角色
        if (empty($params['checked_role'])) throw new ValidateException('请选择角色！');

        Db::startTrans();
        try {
            //添加用户
            $user = new UserModel();
            $user->save([
                'username'    => $params['username'],
                'pwd'         => password_hash($params['pwd'], PASSWORD_DEFAULT),
                'state'       => $params['state'],
                'name'        => Fuc::getValue($params, 'name', ''),
                'mobile'      => Fuc::getValue($params, 'mobile', ''),
                'birthday'    => Fuc::getValue($params, 'birthday', ''),
                'remarks'     => Fuc::getValue($params, 'remarks', ''),
                'avatar'      => Fuc::getValue($params, 'avatar', ''),
                'create_time' => Fuc::getNow(),
            ]);
            $last_user_id = $user->getLastInsID();

            //添加关联角色
            $m_user_role = new UserRoleModel();
            $user_role_save_data = [];
            foreach ($params['checked_role'] as $val) {
                $user_role_save_data[] = [
                    'user_id'     => $last_user_id,
                    'role_id'     => $val,
                    'create_time' => Fuc::getNow(),
                ];
            }
            $m_user_role->insertAll($user_role_save_data);

            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

        //返回结果
        $this->jsonReturn(true);
    }

    /**
     * 修改状态
     */
    public function updState()
    {
        $params = request()->put();

        //验证参数
        $this->validateParams(new UserValidate(), 's_updState', $params);

        //修改状态
        $user = new UserModel();
        $state = $params['state'] == self::ENABLE_STATE ? self::DISABLED_STATE : self::ENABLE_STATE;
        $user->save([
            'state' => $state,
        ], ['id' => $params['id']]);

        //返回结果
        $this->jsonReturn([
            'state' => $state,
        ]);
    }

    /**
     * 获取修改数据
     * @param $id
     */
    public function edit($id)
    {
        //验证参数
        $this->validateParams(new UserValidate(), 's_delete', ['id' => $id]);

        //查询数据
        $data = UserModel::where('id', $id)->find();
        unset($data['pwd']);
        $data['preview_url'] = request()->domain() . $data['avatar'];
        $data['birthday'] = empty($data['birthday']) ? null : $data['birthday'];

        //查询所有角色
        $role_list = RoleModel::all();

        //查询已选角色
        $checked_role = UserRoleModel::where('user_id', $id)->column('role_id');

        $this->jsonReturn([
            'data'        => $data,
            'roleList'    => $role_list,
            'checkedRole' => $checked_role,
        ]);
    }

    /**
     * 修改
     * @param $id
     */
    public function update($id)
    {
        $params = request()->put();
        $params['id'] = $id;

        //验证参数
        $this->validateParams(new UserValidate(), 's_update', $params);

        //检查是否填写角色
        if (empty($params['checked_role'])) throw new ValidateException('请选择角色！');

        Db::startTrans();
        try {
            $update_data = [
                'name'        => $params['name'],
                'mobile'      => $params['mobile'],
                'state'       => $params['state'],
                'birthday'    => $params['birthday']?$params['birthday']:'',
                'remarks'     => $params['remarks'],
                'avatar'      => $params['avatar'],
                'update_time' => Fuc::getNow(),
            ];

            //修改信息
            UserModel::where('id', $id)->update($update_data);

            //找出删除的角色,如果不为空则删除
            $m_user_role = new UserRoleModel();
            $del_role = array_diff($params['re_checked_role'], $params['checked_role']);
            if (!empty($del_role)) {
                $m_user_role->where('user_id', $id)->where('role_id', 'in', $del_role)->delete();
            }

            //找出添加的角色，如果不为空则添加
            $insert_role = array_diff($params['checked_role'], $params['re_checked_role']);
            if (!empty($insert_role)) {
                $user_role_save_data = [];
                foreach ($insert_role as $val) {
                    $user_role_save_data[] = [
                        'user_id'     => $id,
                        'role_id'     => $val,
                        'create_time' => Fuc::getNow(),
                    ];
                }
                $m_user_role->insertAll($user_role_save_data);
            }

            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

        //返回数据
        $this->jsonReturn(true);
    }


    /**
     * 修改用户密码
     * @param $id
     */
    public function updPwd($id)
    {
        $params = request()->put();
        $params['id'] = $id;

        //验证参数
        $this->validateParams(new UserValidate(), 's_updPwd', $params);

        //修改密码
        UserModel::where('id', $id)->update([
            'pwd'         => password_hash($params['pwd'], PASSWORD_DEFAULT),
            'update_time' => Fuc::getNow(),
        ]);

        //返回数据
        $this->jsonReturn(true);
    }

    /**
     * 删除
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        //验证参数
        $this->validateParams(new UserValidate(), 's_delete', ['id' => $id]);

        //如果头像存在，则删除头像
        $info = UserModel::get($id);

        //不允许删除admin账号
        if($info['username']=='admin') throw new ValidateException('不能删除admin账号');
        if (!empty($info) && file_exists($info['avatar'])) {
            unlink($info['avatar']);
        }

        //删除数据
        $user = new UserModel();
        $user->where('id', '=', $id)->delete();

        //返回结果
        $this->jsonReturn(true);
    }

    /**
     * 上传头像
     */
    public function uploadAvatar()
    {
        $file = request()->file('avatar');
        if (empty($file)) {
            throw new MsgException('头像字段不存在!');
        }

        $relative_path = 'uploads' . DIRECTORY_SEPARATOR . 'avatar' . DIRECTORY_SEPARATOR;
        $relative_url_path = '/uploads/avatar/';
        $absolute_path = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $relative_path;
        $url_path = request()->domain() . '/' . $relative_path;
        $info = $file->move($absolute_path);

        if ($info) {
            $img_url = $relative_url_path . str_replace('/', '\\', $info->getSaveName());
            $save_path = $relative_path . $info->getSaveName();
            $preview_url = $url_path . str_replace('/', '\\', $info->getSaveName());

            $this->jsonReturn([
                'img_url'     => $img_url,
                'save_path'   => $save_path,
                'preview_url' => $preview_url,
            ]);
        } else {
            throw new MsgException($file->getError());
        }
    }

    /**
     * 获取用户信息
     */
    public function getUserinfo()
    {
        $user_id = request()->param('userinfo')['id'];
        $userinfo = UserModel::get($user_id);
        unset($userinfo['pwd']);
        $userinfo['avatar'] = request()->domain() . $userinfo['avatar'];
        $this->jsonReturn([
            'userinfo' => $userinfo,
        ]);
    }

    /**
     * 修改当前登录用户的密码
     */
    public function selfUpdPwd()
    {
        $params = request()->param();

        //验证参数
        $this->validateParams(new UserValidate(), 's_selfUpdPwd', $params);

        $user_id = request()->param('userinfo')['id'];

        //修改密码
        UserModel::where('id', $user_id)->update([
            'pwd' => password_hash($params['pwd'], PASSWORD_DEFAULT),
        ]);

        $this->jsonReturn(true);
    }
}