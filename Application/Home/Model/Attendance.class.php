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
    var $name;
    var $id;
    var $department;
    var $work_date;
    var $am_time;
    var $pm_time;
}