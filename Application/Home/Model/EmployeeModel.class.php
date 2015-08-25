<?php
namespace Home\Model;
use Think\Model;

/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午4:21
 */
class EmployeeModel extends Model
{
    protected $_map = array(
        'realName' =>'real_name',
        'attendanceCn' =>'attendance_cn'
    );
    var $id;
}