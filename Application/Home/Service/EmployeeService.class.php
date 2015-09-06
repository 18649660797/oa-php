<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/28
 * Time: 下午8:25
 */

namespace Home\Service;


interface EmployeeService
{
    function suggestRealNames($realName, $callback);
    function suggestAttendanceCn($attendanceCn, $callback);
}