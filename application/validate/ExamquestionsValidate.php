<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\validate;


use think\Validate;

class ExamquestionsValidate extends Validate
{
    public $field = [
        'id'             => 'ID',
        'question'       => '题干',
        'type'           => '题型',
        'correct_answer' => '正确答案',
        'answer_a'       => 'A答案',
        'answer_b'       => 'B答案',
        'answer_c'       => 'C答案',
        'answer_d'       => 'D答案',
        'state'          => '状态',
    ];

    protected $rule = [
        'id'             => 'require|number',
        'question'       => 'require',
        'type'           => 'require|number',
        'correct_answer' => 'require',
        'answer_a'       => 'require',
        'answer_b'       => 'require',
        'answer_c'       => 'require',
        'answer_d'       => 'require',
        'state'          => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_create'     => ['question' ,'type','correct_answer','state'],
        's_updState'   => ['id', 'state'],
        's_delete'     => ['id'],
        's_edit'       => ['id'],
        's_update'     => ['id','question' ,'type','correct_answer','state'],
    ];
}