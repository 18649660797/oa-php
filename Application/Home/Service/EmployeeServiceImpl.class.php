<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/28
 * Time: 下午8:25
 */

namespace Home\Service;


class EmployeeServiceImpl implements EmployeeService
{
    function suggestRealNames($realName, $callback)
    {
        header("Content-type: application/json");
        $result = M("Employee")->where(array("real_name" => array("like", "%$realName%")))->getField("real_name", true);
        echo $callback . "(" . json_encode($result) . ")";
    }

}