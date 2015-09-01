<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/27
 * Time: 下午10:34
 */

namespace Home\Controller;


use Home\Service\EmployeeServiceImpl;
use Home\Service\ExceptionServiceImpl;
use Home\Utils\RenderUtil;

class ExceptionController extends BasicController
{
    public function viewList()
    {
        $this->display(T("exception/list"));
    }

    public function edit($id = "")
    {
        if ($id) {
            $dao = D("Exception");
            $entity = $dao->relation(true)->find($id);
            $this->assign("entity", $entity);
        }
        $this->display(T("exception/edit"));
    }

    public function getRealNames($name = "", $callback)
    {
        $service = new EmployeeServiceImpl();
        $service->suggestRealNames($name, $callback);
    }

    public function data()
    {
        $result = query("Exception", true);
        echo json_encode($result);
    }

    public function save($id)
    {
        $dao = M("Exception");
        $entity = array();
        if ($id) {
            $entity = $dao->find($id);
        }
        $eId = M("Employee")->where(array("real_name" => array("eq", I("name"))))->getField("id");
        $entity["e_id"] = $eId;
        $entity["begin_time"] = I("begin_time");
        $entity["end_time"] = I("end_time");
        $entity["type"] = I("type");
        $entity["remark"] = I("remark");
        if ($id) {
            $dao->save($entity);
        } else {
            $dao->add($entity);
        }
        $this->ajaxReturn(RenderUtil::success());
    }

    public function delete($ids)
    {
        $dao = M("Exception");
        $dao->delete($ids);
        echo json_encode(RenderUtil::success("删除成功！"));
    }

    public function viewImport()
    {
        $this->display(T("exception/import"));
    }

    public function import()
    {
        $service = new ExceptionServiceImpl();
        $service->import();
        redirect("viewList");
    }

    public function importAuto()
    {
//        $service = new ExceptionServiceImpl();
//        $service->import(true);
//        redirect("viewList");
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
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        $employeeDao = D("Employee");
        $employeeList = $employeeDao->select();
        $employeeMap = array();
        foreach ($employeeList as $employee) {
            $employeeMap[$employee["real_name"]] = $employee["id"];
        }
        $exceptionMap = array("事假" => 1, "病假" => 2, "调休" => 3, "外出" => 4, "丧假" => 5, "年假" => 6, "婚假" => 7, "产假" => 8);
        $dataList = array();
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        $objPHPExcel->setActiveSheetIndex(0);
        $rows = 1;
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        ExcelUtils::excel($objPHPExcel, "请假登记");
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $data = array();
            $realName = $currentSheet->getCell("A$currentRow")->getValue();
            $type = $currentSheet->getCell("B$currentRow")->getValue();
            $beginTime = $currentSheet->getCell("C$currentRow")->getValue();
            $endTime = $currentSheet->getCell("D$currentRow")->getValue();
            $remark = $currentSheet->getCell("E$currentRow")->getValue();
            $objPHPExcel->getActiveSheet()->SetCellValue("A$rows", $realName);
            $objPHPExcel->getActiveSheet()->SetCellValue("B$rows", $exceptionMap[$type]);
            $objPHPExcel->getActiveSheet()->SetCellValue("C$rows", $beginTime);
            $objPHPExcel->getActiveSheet()->SetCellValue("D$rows", $endTime);
            $objPHPExcel->getActiveSheet()->SetCellValue("E$rows", $remark);
            $rows++;
        }
        $dataList = array(
            "郑伟"=>array(
                array("08-11", "10:00", "18:00"),
                array("08-12", "09:00", "18:00"),
                array("08-13", "09:00", "18:00"),
                array("08-14", "16:45", "18:00"),
                array("08-19", "09:00", "18:00"),
                array("08-20", "09:00", "18:00"),
                array("08-22", "09:00", "18:00"),
                array("08-24", "09:00", "18:00")
            ),
            "高洪凤"=>array(
                array("08-19", "09:00", "18:00"),
                array("08-20", "09:00", "18:00"),
                array("08-26", "15:50", "18:00")
            ),
            "易翔"=>array(
                array("08-07", "10:30", "18:00"),
                array("08-11", "10:00", "18:00"),
                array("08-12", "09:00", "18:00"),
                array("08-13", "09:00", "18:00"),
                array("08-14", "16:50", "18:00"),
                array("08-18", "09:00", "15:00"),
                array("08-19", "15:50", "18:00"),
                array("08-27", "15:50", "18:00")
            )
        );
    }

    public function viewAutoImport()
    {
        $this->display(T("exception/importAuto"));
    }

}