<?php
namespace Home\Model;
use Think\Model;

/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午4:21
 */
class Attendance extends Model
{
    protected $_map = array(
        'workDate' =>'work_date',
        'amTime'  =>'am_time',
        'pmTime'  =>'pm_time',
    );
    var $name;
    var $id;
    var $department;
}