<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午1:49
 */

namespace Home\Controller;

use Home\Service\AttendanceServiceImpl;
use Home\Service\EmployeeServiceImpl;
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
        $objPHPExcel->setActiveSheetIndex(0);

        $employeeList = M("Employee")->select();
        $employeeMap = array();
        foreach ($employeeList as $one) {
            $employeeMap[$one["attendance_cn"]] = $one["real_name"];
        }
        $exceptionJsonMap = array(1 => "事假", 2 => "病假", 3 => "调休", 4 => "外出", 5=>"丧假", 6=> "年假", 7=>"产假",8=>"婚假");
        $exceptionGroup = array(
            "产品部"=>array(
                "A19"=>array(
                    array(1, "08-17", "09:00", "18:00"),
                    array(1, "08-18", "11:00", "18:00"),
                    array(5, "08-04", "09:00", "18:00"),
                    array(5, "08-05", "09:00", "18:00")
                ),
                "A28"=>array(
                    array(1, "08-11", "09:00", "12:00"),
                    array(1, "08-17", "09:00", "12:00"),
                    array(1, "08-12", "09:00", "11:00"),
                    array(1, "08-20", "09:00", "12:00")
                ),
                "A55"=>array(
                    array(1, "08-04", "09:00", "18:00"),
                    array(1, "08-05", "09:00", "18:00"),
                    array(1, "08-06", "09:00", "18:00"),
                    array(1, "08-22", "17:00", "18:00")
                ),
                "A60"=>array(
                    array(1, "08-17", "09:00", "12:00"),
                    array(1, "08-05", "13:30", "18:00")
                ),
                "A31"=>array(
                    array(3, "08-10", "09:00", "18:00")
                ),
                "A71"=>array(
                    array(1, "08-19", "13:30", "18:00")
                ),
                "A61"=>array(
                    array(1, "08-20", "09:00", "10:00")
                ),
                "A45"=>array(
                    array(1, "08-07", "13:30", "18:00")
                ),
                "A4"=>array(
                    array(3, "08-17", "09:00", "12:00")
                ),
                "A20"=>array(
                    array(3, "08-19", "09:00", "18:00"),
                    array(3, "08-20", "09:00", "18:00")
                ),
                "A43"=>array(
                    array(1, "08-09", "09:00", "18:00")
                )
            ),
            "研发部"=>array(
                "A53"=>array(
                    array(3, "08-28", "09:00", "10:00"),
                    array(3, "08-24", "09:00", "18:00"),
                    array(3, "08-10", "09:00", "18:00")
                ),
                "A33"=>array(
                    array(2, "08-21", "09:00", "18:00"),
                    array(3, "08-14", "09:00", "18:00"),
                    array(3, "08-10", "09:00", "18:00")
                ),
                "A6"=>array(
                    array(3, "08-29", "09:00", "18:00"),
                    array(3, "08-19", "09:00", "12:00"),
                    array(3, "08-08", "09:00", "18:00")
                ),
                "A27"=>array(
                    array(3, "08-08", "09:00", "18:00")
                ),
                "A7"=>array(
                    array(3, "08-27", "09:00", "09:30"),
                    array(3, "08-11", "09:00", "18:00")
                ),
                "A40"=>array(
                    array(3, "08-29", "09:00", "18:00"),
                    array(1, "08-08", "09:00", "18:00")
                ),
                "A49"=>array(
                    array(3, "08-22", "09:00", "18:00"),
                    array(3, "08-08", "09:00", "18:00")
                ),
                "A10"=>array(
                    array(3, "08-10", "09:00", "09:30"),
                    array(3, "08-14", "09:00", "09:30"),
                    array(3, "08-19", "09:00", "09:30"),
                    array(3, "08-21", "09:00", "09:30"),
                    array(3, "08-24", "09:00", "09:30"),
                    array(3, "08-18", "09:00", "10:00"),
                    array(3, "08-26", "09:00", "14:00")
                ),
                "A47"=>array(
                    array(3, "08-31", "09:00", "18:00"),
                    array(3, "08-20", "10:00", "15:00")
                ),
                "A46"=>array(
                    array(3, "08-29", "10:00", "18:00"),
                    array(3, "08-08", "09:00", "18:00")
                ),
                "A62"=>array(
                    array(3, "08-12", "09:00", "12:00"),
                    array(3, "08-29", "13:30", "18:00")
                )
            ),
            "行政部"=>array(
                "A15"=>array(
                    array(1, "08-21", "09:00", "10:30"),
                    array(1, "08-12", "09:00", "18:00")
                )
            ),
            "运营"=>array(
                "A48"=>array(
                    array(1, "08-14", "13:30", "18:00"),
                    array(1, "08-28", "17:00", "18:00"),
                    array(1, "08-29", "09:00", "18:00")
                ),
                "A58"=>array(
                    array(1, "08-06", "16:00", "18:00"),
                    array(1, "08-03", "09:00", "18:00")
                ),
                "A35"=>array(
                    array(1, "08-27", "13:30", "18:00"),
                    array(1, "08-26", "09:00", "18:00"),
                    array(1, "08-08", "09:00", "18:00"),
                    array(1, "08-20", "09:00", "18:00")
                )
            ),
            "市场"=>array(
                "A36"=>array(
                    array(1, "08-20", "15:00", "18:00"),
                    array(1, "08-21", "09:00", "12:00"),
                    array(1, "08-27", "09:00", "18:00"),
                    array(1, "08-28", "09:00", "12:00")
                ),
                "A52"=>array(
                    array(1, "08-08", "09:00", "18:00"),
                    array(1, "08-09", "09:00", "15:30"),
                    array(3, "08-05", "09:00", "18:00"),
                    array(1, "08-04", "13:30", "18:00"),
                    array(1, "08-03", "13:30", "18:00")
                ),
                "A54"=>array(
                    array(1, "08-24", "09:00", "15:30"),
                    array(1, "08-18", "09:00", "14:30"),
                    array(1, "08-17", "09:00", "18:00"),
                    array(1, "08-31", "09:00", "18:00")
                ),
                "A13"=>array(
                    array(1, "08-22", "09:00", "18:00"),
                    array(6, "08-03", "09:00", "18:00"),
                    array(1, "08-04", "14:00", "18:00")
                ),
                "A26"=>array(
                    array(2, "08-27", "09:00", "18:00"),
                    array(1, "08-22", "15:00", "18:00")
                ),
                "A14"=>array(
                    array(1, "08-22", "09:00", "18:00"),
                    array(1, "08-29", "09:00", "18:00"),
                ),
                "A34"=>array(
                    array(1, "08-25", "09:00", "18:00")
                ),
                "A11"=>array(
                    array(2, "08-17", "09:00", "11:00")
                ),
                "A12"=>array(
                    array(6, "08-19", "09:00", "18:00")
                )
            ),
            "销售"=>array(
                "A21"=>array(
                    array(2, "08-11", "09:00", "18:00"),
                    array(2, "08-17", "09:00", "18:00")
                ),
                "A66"=>array(
                    array(1, "08-14", "15:00", "18:00"),
                    array(1, "08-17", "09:00", "18:00")
                ),
                "A65"=>array(
                    array(1, "08-29", "13:30", "18:00")
                ),
                "A41"=>array(
                    array(1, "08-28", "09:00", "18:00"),
                    array(1, "08-29", "09:00", "18:00")
                ),
                "A30"=>array(
                    array(1, "08-25", "09:00", "12:00")
                ),
            )
        );
        $rows = 1;
        foreach ($exceptionGroup as $exceptionJson) {
            foreach ($exceptionJson as $key => $exceptionJson2) {
                foreach ($exceptionJson2 as $one) {
                    $objPHPExcel->getActiveSheet()->SetCellValue("A$rows", $employeeMap[$key]);
                    $objPHPExcel->getActiveSheet()->SetCellValue("B$rows", $exceptionJsonMap[$one[0]]);
                    $objPHPExcel->getActiveSheet()->SetCellValue("C$rows", "2015-" . $one[1] . " " . $one[2] . ":00");
                    $objPHPExcel->getActiveSheet()->SetCellValue("D$rows", "2015-" . $one[1] . " " . $one[3] . ":00");
                    $objPHPExcel->getActiveSheet()->SetCellValue("E$rows", $one[4]);
                    $rows++;
                }
            }
        }
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        ExcelUtils::excel($objPHPExcel, "请假登记");
    }

    public function data()
    {
        $_REQUEST["like_e.real_name"] = $_REQUEST["realName"];
        $fields = "a.id,a.work_date,a.am_time,a.pm_time,a.remark,e.real_name,e.department";
        $result = query("Attendance", true, $fields);
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
            ExcelUtils::excel($objPHPExcel, "考勤分析数据");
        }
    }

}