<?php
namespace app\api\controller;

use app\api\model\ExamquestionsModel;
use app\api\model\RoleModel;
use app\api\model\User;
use app\api\model\UserModel;
use app\api\model\UserRoleModel;
use app\common\exception\MsgException;
use app\validate\ExamquestionsValidate;
use app\validate\UserValidate;
use liguimin\utils\Fuc;
use think\Db;
use think\exception\ValidateException;
use think\facade\Env;
use think\facade\Log;

class ExamquestionsController extends Base
{
    const ENABLE_STATE = 1;//启用状态
    const DISABLED_STATE = 0;//禁用状态

    /**
     * 获取列表
     */
    public function index()
    {
        $page = input('page');
        $page_size = input('page_size');
        $m_questions = new ExamquestionsModel();

        $question = input('question', '');
        $state = input('state', '');
        $s_create_time = input('s_create_time', '');
        $e_create_time = input('e_create_time', '');

        $where = [];
        //按用户名查询
        if ($question !== '') {
            $where[] = ['question', '=', $question];
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
        $count = $m_questions->where($where)->count();

        //数据记录
        $data = $m_questions->where($where)->order('id desc')->page($page, $page_size)->select();

        $index = Fuc::getOffset($page, $page_size);
        foreach ($data as $key => $val) {
            $val['index'] = ++$index;
            if ($val['type'] == 2) {
                $val['correct_answer'] = $val['correct_answer'] == 0 ? '错' : '对';
            } elseif ($val['type'] == 3) {
                $val['correct_answer'] = implode('', json_decode($val['correct_answer']));
            }
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

    }

    /**
     * 添加用户
     */
    public function save()
    {
        $params = request()->param();

        //验证参数
        $this->validateParams(new ExamquestionsValidate(), 's_create', $params);

        //检查题目是否已存在
        $is_exist = ExamquestionsModel::where('question', $params['question'])->value('question');
        if (!empty($is_exist)) {
            $this->jsonReturn([], '该题目已存在！', 409);
        }

        $exam = new ExamquestionsModel();

        //多选题时需要对答案进行json编码
        if ($params['type'] == 3) {
            $correct_answer = json_encode($params['correct_answer']);
        } else {
            $correct_answer = $params['correct_answer'];
        }

        $insert_data = [
            'question'       => $params['question'],
            'type'           => $params['type'],
            'correct_answer' => $correct_answer,
            'create_time'    => Fuc::getNow(),
        ];

        if ($params['type'] != 2) {
            $insert_data = array_merge($insert_data, [
                'answer_a' => $params['answer_a'],
                'answer_b' => $params['answer_b'],
                'answer_c' => $params['answer_c'],
                'answer_d' => $params['answer_d'],
            ]);
        }

        $exam->save($insert_data);

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
        $this->validateParams(new ExamquestionsValidate(), 's_updState', $params);

        //修改状态
        $exam = new ExamquestionsModel();
        $state = $params['state'] == self::ENABLE_STATE ? self::DISABLED_STATE : self::ENABLE_STATE;
        $exam->save([
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
        $this->validateParams(new ExamquestionsValidate(), 's_edit', ['id' => $id]);

        //查询数据
        $data = ExamquestionsModel::where('id', $id)->find();

        $this->jsonReturn([
            'data' => $data,
        ]);
    }

    /**
     * 修改
     * @param $id
     */
    public function update($id)
    {
        $params = request()->param();

        //验证参数
        $this->validateParams(new ExamquestionsValidate(), 's_edit', $params);

        //检查题目是否已存在
        $is_exist = ExamquestionsModel::where('question', $params['question'])
            ->where('id', '<>', $params['id'])
            ->value('question');
        if (!empty($is_exist)) {
            $this->jsonReturn([], '该题目已存在！', 409);
        }

        $exam = new ExamquestionsModel();

        //多选题时需要对答案进行json编码
        if ($params['type'] == 3) {
            $correct_answer = json_encode($params['correct_answer']);
        } else {
            $correct_answer = $params['correct_answer'];
        }

        $update_data = [
            'question'       => $params['question'],
            'type'           => $params['type'],
            'correct_answer' => $correct_answer,
            'update_time'    => Fuc::getNow(),
        ];

        if ($params['type'] != 2) {
            $update_data = array_merge($update_data, [
                'answer_a' => $params['answer_a'],
                'answer_b' => $params['answer_b'],
                'answer_c' => $params['answer_c'],
                'answer_d' => $params['answer_d'],
            ]);
        }

        $exam->where('id', $params['id'])->update($update_data);

        //返回结果
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
        $this->validateParams(new ExamquestionsValidate(), 's_delete', ['id' => $id]);

        //删除数据
        $user = new ExamquestionsModel();
        $user->where('id', '=', $id)->delete();

        //返回结果
        $this->jsonReturn(true);
    }


    /**
     * 考题文件
     */
    private function uploadFile()
    {
        $file = request()->file('exam_file');
        if (empty($file)) {
            throw new MsgException('上传文件失败!');
        }

        $relative_path = 'uploads' . DIRECTORY_SEPARATOR . 'exam' . DIRECTORY_SEPARATOR;
        $relative_url_path = '/uploads/exam/';
        $absolute_path = Env::get('root_path') . 'public' . DIRECTORY_SEPARATOR . $relative_path;
        $url_path = request()->domain() . '/' . $relative_path;
        $info = $file->move($absolute_path);

        if ($info) {
            $img_url = $relative_url_path . str_replace('/', '\\', $info->getSaveName());
            $save_path = $relative_path . $info->getSaveName();
            $preview_url = $url_path . str_replace('/', '\\', $info->getSaveName());

            return [
                'img_url'     => $img_url,
                'save_path'   => $save_path,
                'preview_url' => $preview_url,
            ];
        } else {
            throw new MsgException($file->getError());
        }
    }

    /**
     * 上传单选题文件
     * @throws MsgException
     */
    public function uploadSingle()
    {
        $file_info = $this->uploadFile();
        $file_content = file_get_contents($file_info['save_path']);
        preg_match_all("/[0-9]+\. [\s\S]*?[A-D]+。/", $file_content, $match);

        //从题目中再进行循环匹配每个答案
        $insert_data = [];
        $count = 0;
        foreach ($match[0] as $key => $val) {
            //匹配出题目
            preg_match_all("/[0-9]+\. [\s\S]*?[\n\r]/", $val, $question);
            $question = preg_replace("/[0-9]+?\. /", '', $question[0][0]);

            //匹配出四个答案
            preg_match_all("/[A-D]\. [\s\S]*?[\n\r]/", $val, $answer);
            foreach ($answer[0] as $k => $v) {
                $answer[0][$k] = preg_replace("/[A-D]?\. /", '', $v);
            }

            //匹配出正确答案
            preg_match("/正确答案为：?[A-D]。/", $val, $correct_answer_str);
            preg_match("/[A-D]/", $correct_answer_str[0], $correct_answer);

            $regex = "/[\f\n\r\t\v]/";
            $curr_data = [
                'question'       => preg_replace($regex, '', $question),
                'type'           => 1,
                'correct_answer' => $correct_answer[0],
                'answer_a'       => preg_replace($regex, '', $answer[0][0]),
                'answer_b'       => preg_replace($regex, '', $answer[0][1]),
                'answer_c'       => preg_replace($regex, '', $answer[0][2]),
                'create_time'    => Fuc::getNow(),
            ];

            if (isset($answer[0][3])) {
                $curr_data['answer_d'] = preg_replace($regex, '', $answer[0][3]);
            }

            $insert_data[] = $curr_data;

            $count++;
        }

        //入库
        Db::startTrans();
        try {
            $exam = new ExamquestionsModel();
            $exam->saveAll($insert_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

        $this->jsonReturn(true);
    }


    /**
     * 上传判断题
     * @throws MsgException
     */
    public function uploadJudge()
    {
        $file_info = $this->uploadFile();
        $file_content = file_get_contents($file_info['save_path']);
        preg_match_all("/[0-9]+\. [\s\S]*?正确答案为：[对错]+/", $file_content, $match);

        //从题目中再进行循环匹配每个答案
        $insert_data = [];
        foreach ($match[0] as $key => $val) {
            //匹配出题目
            preg_match_all("/[0-9]+\. [\s\S]*?[\n\r]/", $val, $question);
            $question = preg_replace("/[0-9]+?\. /", '', $question[0][0]);

            //匹配出正确答案
            preg_match("/正确答案为：?[对错]+/", $val, $correct_answer_str);
            //查找对错
            $correct = strpos($correct_answer_str[0], '对');
            if ($correct !== false) {
                $correct_answer = 1;
            } else {
                $correct = strpos($correct_answer_str[0], '错');
                if ($correct === false) throw new MsgException('未找到正确答案！');
                $correct_answer = 0;
            }

            $regex = "/[\f\n\r\t\v]/";
            $curr_data = [
                'question'       => preg_replace($regex, '', $question),
                'type'           => 2,
                'correct_answer' => $correct_answer,
                'create_time'    => Fuc::getNow(),
            ];

            $insert_data[] = $curr_data;
        }

        //入库
        Db::startTrans();
        try {
            $exam = new ExamquestionsModel();
            $exam->saveAll($insert_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

        $this->jsonReturn(true);
    }


    /**
     * 上传多选题文件
     * @throws MsgException
     */
    public function uploadMulti()
    {
        $file_info = $this->uploadFile();
        $file_content = file_get_contents($file_info['save_path']);
        preg_match_all("/[0-9]+\. [\s\S]*?[A-F]+。/", $file_content, $match);

        //从题目中再进行循环匹配每个答案
        $insert_data = [];
        foreach ($match[0] as $key => $val) {
            //匹配出题目
            preg_match_all("/[0-9]+\. [\s\S]*?[\n\r]/", $val, $question);
            $question = preg_replace("/[0-9]+?\. /", '', $question[0][0]);

            //匹配出四个答案
            preg_match_all("/[A-F]\. [\s\S]*?[\n\r]/", $val, $answer);
            foreach ($answer[0] as $k => $v) {
                $answer[0][$k] = preg_replace("/[A-F]?\. /", '', $v);
            }


            //匹配出正确答案
            preg_match("/正确答案为：?[A-F,]+。/", $val, $correct_answer_str);
            preg_match("/[A-F,]+/", $correct_answer_str[0], $correct_answer);

            $regex = "/[\f\n\r\t\v]/";
            $curr_data = [
                'question'       => preg_replace($regex, '', $question),
                'type'           => 3,
                'correct_answer' => json_encode(explode(',', $correct_answer[0])),
                'answer_a'       => preg_replace($regex, '', $answer[0][0]),
                'answer_b'       => preg_replace($regex, '', $answer[0][1]),
                'answer_c'       => preg_replace($regex, '', $answer[0][2]),
                'create_time'    => Fuc::getNow(),
            ];

            if (isset($answer[0][3])) {
                $curr_data['answer_d'] = preg_replace($regex, '', $answer[0][3]);
            }

            if (isset($answer[0][4])) {
                $curr_data['answer_e'] = preg_replace($regex, '', $answer[0][4]);
            }

            if (isset($answer[0][5])) {
                $curr_data['answer_f'] = preg_replace($regex, '', $answer[0][5]);
            }


            $insert_data[] = $curr_data;
        }

        //入库
        Db::startTrans();
        try {
            $exam = new ExamquestionsModel();
            $exam->saveAll($insert_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new MsgException($e->getMessage());
        }

        $this->jsonReturn(true);
    }

}