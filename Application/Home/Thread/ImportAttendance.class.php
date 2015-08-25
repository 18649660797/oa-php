<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/25
 * Time: 上午9:15
 */

namespace Home\Thread;


use Home\Service\AttendanceServiceImpl;
use Think\Behavior;

class ImportAttendance extends Behavior
{
    public function run(&$params="")
    {
        $service = new AttendanceServiceImpl();
        $service->upload();
    }
}