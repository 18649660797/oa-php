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


}