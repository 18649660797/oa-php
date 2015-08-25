<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/24
 * Time: 下午11:10
 */

namespace Home\Utils;


class EncodeUtils
{
    public static function encode($val) {
        $val = iconv('utf-8','gbk', $val);
//        $val = iconv('gbk','utf-8', $val);
        return $val;
    }
}