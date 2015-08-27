<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/27
 * Time: 下午11:07
 */

namespace Home\Model;


use Think\Model\RelationModel;

class ExceptionModel extends RelationModel
{
    protected $_link = array(
        'employee'=>array(
            'mapping_type'      => self::BELONGS_TO,
            'class_name'        => 'Employee',
            'foreign_key'   => 'e_id',
            'relation_foreign_key' => 'e_id',
//            'mapping_name'  => 'employee'
            // 定义更多的关联属性
            'as_fields' => 'real_name,department,attendance_cn',
        )
    );
    var $id;
    var $type;
    var $begin_time;
    var $end_time;
    var $remark;
    var $e_id;
}