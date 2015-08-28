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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Hello');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF808080');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');
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
            $dao = D("Attendance");
            $condition = array();
            $condition["work_date"] = array(array("like", "$month%"));
//            $condition["status"] = array(array("eq", 1));
            $dataList = $dao->relation(true)->where($condition)->select();
            $map = array();
            // 分组数据
            foreach ($dataList as $data) {
                if ($map[$data["e_id"]]) {
                    $map[$data["e_id"]][] = $data;
                } else {
                    $map[$data["e_id"]] = array($data);
                }
            }
            $exceptionDao = D("Exception");
            $condition = array();
            $condition["start_time"] = array(array("like", "$month%"));
            $exceptionList = $exceptionDao->relation(true)->where($condition)->select();
            import("Org.Util.PHPExcel");
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
            $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
            $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
            $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
            $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
            // Add some data
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
            $objPHPExcel->getActiveSheet()->SetCellValue('A1', '说明：蓝色填充为3次9:15前迟到机会，绿色填充为外出、加班晚到等未计考勤情况，黄色填充为违反制度情况。 ');
//            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
//            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FFFFFFFF');
            $objPHPExcel->getActiveSheet()->SetCellValue('A2', '部门');
            $objPHPExcel->getActiveSheet()->SetCellValue('B2', '姓名');
            $objPHPExcel->getActiveSheet()->SetCellValue('C2', '周期');
            $objPHPExcel->getActiveSheet()->SetCellValue('D2', '日期');
            $objPHPExcel->getActiveSheet()->SetCellValue('E2', '上班');
            $objPHPExcel->getActiveSheet()->SetCellValue('F2', '下班');
            $objPHPExcel->getActiveSheet()->SetCellValue('G2', '事假');
            $objPHPExcel->getActiveSheet()->SetCellValue('H2', '病假');
            $objPHPExcel->getActiveSheet()->SetCellValue('I2', '调休');
            $objPHPExcel->getActiveSheet()->SetCellValue('J2', '备注');
            $objPHPExcel->getActiveSheet()->SetCellValue('K2', '迟到');
            $objPHPExcel->getActiveSheet()->SetCellValue('L2', '早退');
            $objPHPExcel->getActiveSheet()->SetCellValue('M2', '旷工');
            $rows = 3;
            // 分析数据
            foreach ($map as $data) {
                $times = 0;
                for ($i = 0; $i < count($data); $i++) {
                    $one = $data[$i];
                    $previous = $data[$i - 1];
                    $workDate = $one["work_date"];
                    $amTime = $one["am_time"];
                    $pmTime = $one["pm_time"];
                    if ($amTime == $pmTime) {
                        if (strtotime($amTime) > strtotime("12:00")) {
                            $amTime = null;
                        } else {
                            $pmTime = null;
                        }
                    }
                    $status = $one["status"];
                    $eId = $one["e_id"];
                    $remark = $one["remark"];
                    $objPHPExcel->getActiveSheet()->SetCellValue("A$rows", $one["department"]);
                    $objPHPExcel->getActiveSheet()->SetCellValue("B$rows", $one["real_name"]);
                    $objPHPExcel->getActiveSheet()->SetCellValue("C$rows", DateUtils::getWeek($workDate));
                    $objPHPExcel->getActiveSheet()->SetCellValue("D$rows", $workDate);
                    $objPHPExcel->getActiveSheet()->SetCellValue("E$rows", $amTime);
                    $objPHPExcel->getActiveSheet()->SetCellValue("F$rows", $pmTime);
                    $objPHPExcel->getActiveSheet()->SetCellValue("J$rows", $remark);
                    if ($status == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle("C$rows")->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_BLUE);
                    } else {
                        if (!$amTime && !$pmTime) {
                            $objPHPExcel->getActiveSheet()->SetCellValue("M$rows", "7.5h");
                        }
                        if (strtotime($amTime) > strtotime("09:00")) {
                            $delay = (strtotime($amTime) - strtotime("09:00")) / 60;
                            if ($previous && $delay < 60 && strtotime($previous["pm_time"]) > strtotime("21:30")) {
                                $objPHPExcel->getActiveSheet()->getStyle("E$rows")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                                $objPHPExcel->getActiveSheet()->getStyle("E$rows")->getFill()->getStartColor()->setARGB('FF008800');
                            } else if ($delay <= 15) {
                                if ($times++ < 3) {
                                    $objPHPExcel->getActiveSheet()->getStyle("E$rows")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                                    $objPHPExcel->getActiveSheet()->getStyle("E$rows")->getFill()->getStartColor()->setARGB('FF0000FF');
                                }
                            }
                            $objPHPExcel->getActiveSheet()->SetCellValue("K$rows", $delay);
                        }
                        if ($pmTime && strtotime($pmTime) < strtotime("18:00")) {
                            $objPHPExcel->getActiveSheet()->SetCellValue("L$rows", (strtotime("18:00") - strtotime($pmTime)) / 60);
                        }
                        if (!$amTime || !$pmTime) {
                            foreach ($exceptionList as $exception) {
                                $beginTime = $exception["begin_time"];
                                $endTime = $exception["end_time"];
                                $pos1 = strpos($beginTime, $workDate);
                                $pos2 = strpos($endTime, $workDate);
                                if ($eId == $exception["e_id"] && ((is_numeric($pos1) && $pos1 > -1) || (is_numeric($pos2) && $pos2 > -1))) {
                                    $times = (strtotime($endTime) - strtotime($beginTime) - 1.5*3600) / 60.0 / 60.0;
                                    switch($exception["type"]) {
                                        case 1:
                                            $objPHPExcel->getActiveSheet()->SetCellValue("G$rows", "$times" . "h");
                                            break;
                                        case 2:
                                            $objPHPExcel->getActiveSheet()->SetCellValue("H$rows", "$times" . "h");
                                            break;
                                        case 3:
                                            $objPHPExcel->getActiveSheet()->SetCellValue("I$rows", "$times" . "h");
                                            break;
                                        case 4:
                                            $objPHPExcel->getActiveSheet()->SetCellValue("J$rows", "$times" . "h");
                                            break;
                                    }
                                    if (!$remark && $exception["type"] != 4) {
                                        $objPHPExcel->getActiveSheet()->SetCellValue("J$rows", $exception["remark"]);
                                    }
                                }
                            }
                        }
                    }
                    $rows++;
                }
            }
            // Rename sheet
            $objPHPExcel->getActiveSheet()->setTitle('sheet1');
            ExcelUtils::excel($objPHPExcel);
        }
    }

}