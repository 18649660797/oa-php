<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/24
 * Time: 下午9:20
 */

namespace Home\Utils;


class DateUtils
{
    public static function getDaysByMonth($month)
    {
        $tmp = explode("-", $month);
        $j = cal_days_in_month(CAL_GREGORIAN, $tmp[1], $tmp[1]);//获取当前月份天数
        $start_time = strtotime(date("$month-01"));  //获取本月第一天时间戳
        $array = array();
        for ($i = 0; $i < $j; $i++) {
            $array[] = date('Y-m-d', $start_time + $i * 86400); //每隔一天赋值给数组
        }
        return $array;
    }

    public static function getWeek($date)
    {
        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        return "星期" . $weekarray[date("w", strtotime($date))];
    }

}