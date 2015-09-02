<?php

/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午2:06
 */
namespace Home\Service;

use Home\Model\Attendance;
use Home\Utils\DateUtils;

class AttendanceServiceImpl implements AttendanceService
{
    /**
     * 导入考勤信息
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    function import()
    {
        import("Org.Util.PHPExcel");
        $filePath = $_FILES["file"]["tmp_name"];
        /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
//        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        $dataList = array();
        for ($currentRow = 4; $currentRow <= $allRow; $currentRow++) {
            $data = array();
            $attendanceCn = $currentSheet->getCell("A$currentRow")->getValue();
            $realName = $currentSheet->getCell("B$currentRow")->getValue();
            $workDate = $currentSheet->getCell("E$currentRow")->getValue();
            $am1 = $currentSheet->getCell("G$currentRow")->getValue();
            $am2 = $currentSheet->getCell("H$currentRow")->getValue();
            $pm1 = $currentSheet->getCell("I$currentRow")->getValue();
            $pm2 = $currentSheet->getCell("J$currentRow")->getValue();
            $data["real_name"] = $realName;
            $data["work_date"] = $workDate;
            $data["am_time"] = $am1;
            if (!$data["am_time"]) {
                $data["am_time"] = $am2;
                if (!$data["am_time"]) {
                    $data["am_time"] = $pm1;
                }
            }
            $data["pm_time"] = $pm2;
            if (!$data["pm_time"]) {
                $data["pm_time"] = $pm1;
                if (!$data["pm_time"]) {
                    $data["pm_time"] = $am2;
                }
            }
            $data["attendanceCn"] = $attendanceCn;
            $dataList[] = $data;
        }
        $month = $dataList[0]["work_date"];
        $month = substr($month, 0, 7);
        $dao = D("Attendance");
        $dao->relation(true);
        $attendanceList = $dao->where(array("work_date" => array("like", "$month%")))->select();
        $dao2 = M("Attendance");
        foreach ($attendanceList as $one) {
            foreach ($dataList as $data) {
                if ($one["attendance_cn"] == $data["attendanceCn"] && $one["work_date"] == $data["work_date"]) {
                    if ($data["am_time"]) {
                        $one["am_time"] = $data["am_time"];
                    }
                    if ($data["pm_time"]) {
                        $one["pm_time"] = $data["pm_time"];
                    }
                    $dao2->save($one);
                }
            }
        }
    }

    function init($month)
    {
        $days = DateUtils::getDaysByMonth($month);
        $employees = M("Employee")->order("attendance_cn asc")->select();
        $dao = M("Attendance");
        foreach ($employees as $employee) {
            foreach ($days as $day) {
                $data = array();
                $data["name"];
                $data["e_id"] = $employee["id"];
                $data["work_date"] = $day;
                $dao->add($data);
            }
        }
    }

    function getAttendanceGroupByMonth($month)
    {
        $attendanceDao = D("Attendance");
        $attendanceDao->alias("a")->join("edy_employee e on e.id=a.e_id");
        $condition = array();
        $condition["a.work_date"] = array(array("like", "$month%"));
        // 当月所有考勤记录
        $curMonthAttendances = $attendanceDao->relation(true)->where($condition)->getField("a.id,a.e_id,a.work_date,a.am_time,a.pm_time,a.status,e.real_name,a.remark,e.department", true);
        // 员工分组
        $attendanceGroup = array();
        // 分组数据
        foreach ($curMonthAttendances as $attendance) {
            if (array_key_exists($attendance["e_id"], $attendanceGroup)) {
                $attendanceGroup[$attendance["e_id"]][] = $attendance;
            } else {
                $attendanceGroup[$attendance["e_id"]] = array($attendance);
            }
        }
        return $attendanceGroup;
    }

    function getExceptionGroupByMonth($month)
    {
        $exceptionDao = D("Exception");
        $exceptionDao->alias("a")->join("edy_employee e on e.id=a.e_id");
        $condition = array();
        $condition["a.begin_time"] = array(array("like", "$month%"));
        $exceptionList = $exceptionDao->where($condition)->getField("a.begin_time,a.end_time,a.e_id,a.type,a.remark");
        // 异常分组
        $exceptionGroup = array();
        // 分组数据
        foreach ($exceptionList as $exception) {
            if (array_key_exists($exception["e_id"], $exceptionGroup)) {
                $exceptionGroup[$exception["e_id"]][] = $exception;
            } else {
                $exceptionGroup[$exception["e_id"]] = array($exception);
            }
        }
        return $exceptionGroup;
    }

    function newPhpExcel($creator, $operator, $desc)
    {
        import("Org.Util.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $property = $objPHPExcel->getProperties();
        $property->setCreator($creator);
        $property->setLastModifiedBy($operator);
        $property->setTitle("Office 2007 XLSX Test Document");
        $property->setSubject("Office 2007 XLSX Test Document");
        $property->setDescription($desc);
        return $objPHPExcel;
    }

    function analysisAttendanceByMonth($attendanceGroup, $exceptionGroup)
    {
        import("Org.Util.PHPExcel");
        $admin = session("S_UNAME");
        $objPHPExcel = $this->newPhpExcel($admin, $admin, "考勤分析");
        // 选择工作簿
        $objPHPExcel->setActiveSheetIndex(0);
        // 工作簿
        $selectSheet = $objPHPExcel->getActiveSheet();
        // 设置自适应列
        $selectSheet->getColumnDimension('D')->setWidth(10);
        $selectSheet->getColumnDimension('J')->setAutoSize(true);
        $selectSheet->getColumnDimension('K')->setWidth(20);
        $selectSheet->getColumnDimension('L')->setWidth(20);
        // 合并单元格
        $selectSheet->mergeCells('A1:J1');
        // 填充表头
        $selectSheet->SetCellValue('A1', '说明：蓝色填充为3次9:15前迟到机会，绿色填充为外出、加班晚到等未计考勤情况，黄色填充为违反制度情况。 ');
        $selectSheet->SetCellValue('A2', '部门');
        $selectSheet->SetCellValue('B2', '姓名');
        $selectSheet->SetCellValue('C2', '周期');
        $selectSheet->SetCellValue('D2', '日期');
        $selectSheet->SetCellValue('E2', '上班');
        $selectSheet->SetCellValue('F2', '下班');
        $selectSheet->SetCellValue('G2', '事假');
        $selectSheet->SetCellValue('H2', '病假');
        $selectSheet->SetCellValue('I2', '调休');
        $selectSheet->SetCellValue('J2', '备注');
        $selectSheet->SetCellValue('K2', '迟到');
        $selectSheet->SetCellValue('L2', '早退');
        $selectSheet->SetCellValue('M2', '旷工');
        // 从第三行开始填充数据
        $rows = 3;
        // 个人每月15分钟内迟到限制次数
        $delayTimesLimit = 3;
        // 个人每月早退限制次数
        $overlayTimesLimit = 4;
        // 需要打卡的时间
        $amNeedFit = "09:03";
        $amNeedFitTime = strtotime($amNeedFit);
        $pmNeedFit = "18:00";
        $pmNeedFitTime = strtotime($pmNeedFit);
        // 遍历每个人
        foreach ($attendanceGroup as $attendanceList) {
            // 个人每月15分钟内迟到次数
            $delayTimes = 0;
            // 个人每月早退次数
            $overlayTimes = 0;
            // 个人乐捐金额
            $applyMoneyAm = 0;
            $applyMoneyPm = 0;
            // 遍历每天
            for ($i = 0; $i < count($attendanceList); $i++) {
                $remarkTmp = "";
                // 考勤记录
                $attendance = $attendanceList[$i];
                // 前一天记录
                $previous = $i > 1 ? $attendanceList[$i - 1] : 0;
                // 考勤日
                $workDate = $attendance["work_date"];
                // 上午打卡
                $amTime = $attendance["am_time"];
                // 下午打卡
                $pmTime = $attendance["pm_time"];
                // 考勤状态 0：不需要考勤 1:需要考勤
                $status = $attendance["status"];
                // 员工id
                $eId = $attendance["e_id"];
                // 备注
                $remark = $attendance["remark"];
                // 清除掉上午和下午打卡时间一致的情况
//                if ($amTime == $pmTime) {
//                    if (strtotime($amTime) > strtotime("12:00")) {
//                        $amTime = null;
//                    } else {
//                        $pmTime = null;
//                    }
//                }
                if (strtotime("12:00") - strtotime($pmTime) > 0) {
                    $pmTime = null;
                }
                $selectSheet->SetCellValue("A$rows", $attendance["department"]);
                $selectSheet->SetCellValue("B$rows", $attendance["real_name"]);
                $selectSheet->SetCellValue("C$rows", DateUtils::getWeek($workDate));
                $selectSheet->SetCellValue("D$rows", $workDate);
                $selectSheet->SetCellValue("E$rows", $amTime);
                $selectSheet->SetCellValue("F$rows", $pmTime);
                $selectSheet->SetCellValue("J$rows", $remark);
                if ($status == 0) { // 不需要考勤
                    $selectSheet->getStyle("C$rows")->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_BLUE);
                } else {
                    $continue = true;
                    $amNeedFit_ = $amNeedFit;
                    if (strtotime($workDate) >= strtotime("2015-08-21")) {
                        $amNeedFit_ = "09:08";
                    }
                    $pmNeedFit_ = $pmNeedFit;
                    // 如果此人当月有异常情况
                    $exceptionList = array_key_exists($eId, $exceptionGroup) ? $exceptionGroup[$eId] : null;
                    if ($exceptionList) {
                        foreach ($exceptionList as $exception) {
                            $beginTime = $exception["begin_time"];
                            $endTime = $exception["end_time"];
                            $pos1 = strpos($beginTime, $workDate);
                            $pos2 = strpos($endTime, $workDate);
                            if ((is_numeric($pos1) && $pos1 > -1) || (is_numeric($pos2) && $pos2 > -1)) {
                                $tmpEnd = substr($endTime, 11, 5);
                                $tmpEndTime = strtotime($tmpEnd);
                                $tmpBegin = substr($beginTime, 11, 5);
                                $tmpBeginTime = strtotime($tmpBegin);
                                $delayTime = ($tmpEndTime - $tmpBeginTime) / 3600.0;
                                if ($tmpBeginTime < strtotime("12:00") && $tmpEndTime > strtotime("12:00")) {
                                    $delayTime -= 1.5;
                                }
                                if ($delayTime >= 7.5) {
                                    $continue = false;
                                } else if ($tmpBeginTime > $amNeedFitTime && $tmpEndTime < $pmNeedFitTime) {
                                    // 如果处于中间的话，不处理
                                } else if ($tmpBeginTime == strtotime("09:00") && $tmpEndTime <= strtotime("18:00")) {
                                    if ($tmpEnd == "12:00") {
                                        $amNeedFit_ = "13:30";
                                    } else {
                                        $amNeedFit_ = $tmpEnd;
                                    }
                                } else if ($tmpEndTime == strtotime("18:00") && $tmpBeginTime >= $amNeedFitTime) {
                                    if ($tmpBegin == "13:00") {
                                        $pmNeedFit_ = $tmpBegin;
                                    } else {
                                        $pmNeedFit_ = "12:00";
                                    }
                                }
                                switch ($exception["type"]) {
                                    case 1:
                                        $selectSheet->SetCellValue("G$rows", "$delayTime" . "h");
                                        break;
                                    case 2:
                                        $selectSheet->SetCellValue("H$rows", "$delayTime" . "h");
                                        break;
                                    case 3:
                                        $selectSheet->SetCellValue("I$rows", "$delayTime" . "h");
                                        break;
                                    case 4:
                                        $delayTime = round($delayTime, 1);
                                        $remarkTmp .= "外出$delayTime" . "h;";
                                        break;
                                    case 5:
                                        $delayTime = round($delayTime, 1);
                                        $remarkTmp .= "丧假$delayTime" . "h;";
                                        break;
                                    case 6:
                                        $delayTime = round($delayTime, 1);
                                        $remarkTmp .= "年假$delayTime" . "h;";
                                        break;
                                }
                                if (!$remark && $exception["type"] != 4 && $exception["remark"]) {
                                    $remarkTmp .= $exception["remark"] . ";";
                                }
                                break;
                            }
                        }
                    }
                    if ($continue) {
                        // 如果上午和下午都没有打卡记录的话，记为矿工
                        if (!$amTime && !$pmTime) {
                            $selectSheet->SetCellValue("M$rows", "7.5h");
                        } else {
                            if (!$amTime || strtotime($amTime) > strtotime($amNeedFit_)) { // 迟到
                                $delay = 0;
                                if (!$amTime) {
//                                    $delay = (strtotime($pmTime) - strtotime($amNeedFit_))/60 - 90;
                                } else {
                                    $delay = (strtotime($amTime) - strtotime($amNeedFit_)) / 60;
                                }
                                if ($eId == "424") {
                                    $a = 1;
                                }
                                $color = "";
                                // 如果迟到在一个小时内，并且前天加班到九点半后
                                if ($previous && $delay < 60 && strtotime($previous["pm_time"]) > strtotime("21:30")) {
                                    $color = "FF9AFF9A";
                                } else if ($delay <= 15) { // 如果迟到在十五分钟内
                                    // 如果迟到次数还没到
                                    if ($delayTimes++ < $delayTimesLimit) {
                                        $color = "FF00F5FF";
                                    } else {
                                        $applyMoneyAm += 10;
                                        $remarkTmp .= "迟到乐捐$applyMoneyAm 元;";
                                        $color = "FFFFFF00";
                                    }
                                } else {
                                    $color = "FFFFFF00";
                                    if ($delay <= 30) {
                                        $applyMoneyAm += 10;
                                        $remarkTmp .= "迟到乐捐$applyMoneyAm 元;";
                                        $color = "FFFFFF00";
                                    } else if ($delay > 30 && $delay <= 60) {
                                        $remarkTmp .= "迟到扣除1h工资;";
                                    } else if ($delay > 60 && $delay <= 180) {
                                        $remarkTmp .= "迟到扣除3h工资;";
                                    } else if ($delay > 180) {
                                        $remarkTmp .= "迟到扣除1天工资;";
                                    }
                                }
                                $selectSheet->SetCellValue("K$rows", "迟到 $delay 分钟");
                                $selectSheet->getStyle("E$rows")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                                $selectSheet->getStyle("E$rows")->getFill()->getStartColor()->setARGB($color);
                                $selectSheet->getStyle("E$rows")->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
                                $selectSheet->getStyle("E$rows")->getBorders()->getAllBorders()->getColor()->setARGB("FF000000");
                            }
                            if (!$pmTime || strtotime($pmTime) < strtotime($pmNeedFit_)) { // 早退
                                $selectSheet->getStyle("F$rows")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                                $selectSheet->getStyle("F$rows")->getFill()->getStartColor()->setARGB('FFFFFF00');
                                $selectSheet->getStyle("F$rows")->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
                                $selectSheet->getStyle("F$rows")->getBorders()->getAllBorders()->getColor()->setARGB("FF000000");
                                if ($pmTime) {
                                    $overlay = (strtotime($pmNeedFit_) - strtotime($pmTime)) / 60;
                                    $selectSheet->SetCellValue("L$rows", "早退 $overlay 分钟");
                                }
                                if ($overlayTimes++ < $overlayTimesLimit) {
                                    $remarkTmp .= "早退补卡;";
                                } else {
                                    $applyMoneyPm += 10;
                                    $remarkTmp .= "迟到乐捐$applyMoneyPm 元;";
                                }
                            }
                        }
                    }
                    if ($remarkTmp) {
                        $selectSheet->SetCellValue("J$rows", $remarkTmp);
                    }
                }
                $rows++;
            }
            $rows++;
        }
        // Rename sheet
        $selectSheet->setTitle('sheet1');
        return $objPHPExcel;
    }


}