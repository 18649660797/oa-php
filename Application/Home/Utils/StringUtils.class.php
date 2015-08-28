<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/28
 * Time: 下午8:56
 */

namespace Home\Utils;


class StringUtils
{
    public static function fillZero($val)
    {
        if (is_numeric($val)) {
            return $val > 9 ? $val : "0$val";
        } else {
            return $val;
        }
    }
}