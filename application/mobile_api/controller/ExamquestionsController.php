<?php
namespace app\mobile_api\controller;

use app\api\model\ExamquestionsModel;
use think\Db;
use think\facade\Log;

class ExamquestionsController extends Base
{
    /**
     * 查看列表
     */
    public function index()
    {
        $type = request()->param('type', '');
        $ids=request()->param('ids','');

        $where = [];

        //根据id查询
        if ($ids!=='') {
            $where[] = ['id', 'in', json_decode($ids,true)];
        }

        //根据类型查询
        if ($type !== '') {
            $where[] = ['type', '=', $type];
        }

        $exam = new ExamquestionsModel();
        $count = $exam->where($where)->count();
        $data = $exam->where($where)->select();

        foreach($data as $key=>$val){
            if($val['type']==3){
                $val['correct_answer']=json_decode($val['correct_answer']);
                $val['correct_answer_text']=implode('',$val['correct_answer']);
            }
            $data[$key]=$val;
        }

        $this->jsonReturn([
            'data'  => $data,
            'count' => $count,
        ]);
    }
}