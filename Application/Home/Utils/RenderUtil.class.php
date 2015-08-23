<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午4:18
 */

namespace Home\Utils;


class RenderUtil
{
    public static function success($msg = "") {
        $result = array("result" => true, "message" => $msg);
        return $result;
    }

    public static function error($msg = "") {
        $result = array("result" => false, "error" => $msg);
        return $result;
    }


}