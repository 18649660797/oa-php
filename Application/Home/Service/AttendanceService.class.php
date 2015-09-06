<?php

/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午2:05
 */
namespace Home\Service;
interface AttendanceService
{
    function import();
    function init($month);
    function getAttendanceGroupByMonth($month);
    function getExceptionGroupByMonth($month);
    function analysisAttendanceByMonth($month);
}