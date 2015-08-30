<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午1:49
 */

namespace Home\Controller;

use Home\Service\AttendanceServiceImpl;
use Home\Utils\DateUtils;
use Home\Utils\ExcelUtils;
use Home\Utils\RenderUtil;
use Home\Utils\StringUtils;

class AttendanceController extends BasicController
{
    public function import()
    {
        $thread = new AttendanceServiceImpl();
        $thread->import();
        redirect("viewList");
    }

    public function excel()
    {
        import("Org.Util.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', '林嘉斌');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', '调休');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', '2015-08-10 09:00:00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', '2015-08-10 18:00:00');
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '林嘉斌');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', '请假');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', '2015-08-11 13:30:00');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', '2015-08-11 18:00:00');
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        ExcelUtils::excel($objPHPExcel);
    }

    public function data()
    {
        $result = query("Attendance", true);
        $dao = M("Attendance");
        $rows = array();
        foreach ($result["rows"] as $one) {
            $eId = $one["e_id"];
            $workDate = $one["work_date"];
            $tmpArr = explode("-", $workDate);
            if ($tmpArr[2] == 1) {
                continue;
            }
            $tmpArr[2] = StringUtils::fillZero($tmpArr[2] - 1);
            $workDate = join("-", $tmpArr);
            $yesterday = $dao->where(array("e_id" => array("eq", $eId), "work_date" => array("eq", $workDate)))->find();
            $one["yesterday"] = $yesterday["pm_time"];
            $rows[] = $one;
        }
        $result["rows"] = $rows;
        echo json_encode($result);
    }

    public function viewList()
    {
        $this->display(T("attendance/list"));
    }

    public function init()
    {
        $this->display(T("attendance/init"));
    }

    public function drop()
    {
        $this->display(T("attendance/drop"));
    }

    public function upload()
    {
        $this->display(T("attendance/upload"));
    }

    function GetData($val)
    {
        $jd = GregorianToJD(1, 1, 1970);
        $gregorian = JDToGregorian($jd + intval($val) - 25569);
        return date("h:i", $gregorian);
        /**显示格式为 “月/日/年” */
    }

    public function initMonth($month = "")
    {
        if ($month) {
            $service = new AttendanceServiceImpl();
            $service->init($month);
            echo json_encode(RenderUtil::success());
        }
    }

    public function dropMonth($month = "")
    {
        if ($month) {
            $dao = M("Attendance");
            $dao->where(array("work_date" => array("like", "$month%")))->delete();
            echo json_encode(RenderUtil::success());
        }
    }

    public function getDays($month)
    {
        header("Content-type: application/json");
        $result = array();
        foreach (DateUtils::getDaysByMonth($month) as $one) {
            $result[] = array("text" => $one, "id" => $one, "value" => $one);
        }
        echo json_encode($result);
    }

    public function unsetDays($days)
    {
        header("Content-type: application/json");
        $dao = M("Attendance");
        $inArray = array();
        $days = explode(";", $days);
        for ($i = 0; $i < count($days); $i++) {
            $inArray[] = $days[$i];
        }
        $condition = array();
        $condition["work_date"] = array("in", join(",", $days));
        $dao->status = 0;
        $dao->where($condition);
        $dao->save();
        echo json_encode(RenderUtil::success());
    }

    public function getRealNames($realName = "", $callback)
    {
        $service = new EmployeeServiceImpl();
        $service->suggestRealNames($realName, $callback);
    }

    public function edit($id = "")
    {
        if ($id) {
            $dao = D("Attendance");
            $entity = $dao->relation(true)->find($id);
            $this->assign("entity", $entity);
        }
        $this->display(T("attendance/edit"));
    }

    public function save($id = "")
    {
        $entity = array();
        if ($id) {
            $dao = D("Attendance");
            $entity = $dao->relation(true)->find($id);
        }
        $entity["work_date"] = I("work_date");
        $entity["am_time"] = I("am_time");
        $entity["pm_time"] = I("pm_time");
        $entity["remark"] = I("remark");
        $dao = M("Attendance");
        if ($id) {
            $dao->save($entity);
        } else {
            $dao->add($entity);
        }
        echo json_encode(RenderUtil::success("操作成功！"));
    }

    public function analysis($month)
    {
        if ($month) {
            $attendanceService = new AttendanceServiceImpl();
            // 当月考勤根据员工分组
            $attendanceGroup = $attendanceService->getAttendanceGroupByMonth($month);
            // 获取当月的所有异常情况
            $exceptionGroup = $attendanceService->getExceptionGroupByMonth($month);
            // 分析导出excel
            $objPHPExcel = $attendanceService->analysisAttendanceByMonth($attendanceGroup, $exceptionGroup);
            ExcelUtils::excel($objPHPExcel);
        }
    }



}