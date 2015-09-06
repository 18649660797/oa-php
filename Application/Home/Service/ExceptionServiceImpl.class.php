<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/9/1
 * Time: 下午7:55
 */

namespace Home\Service;


use Home\Utils\ExcelUtils;

class ExceptionServiceImpl implements ExceptionService
{
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
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            $data = array();
            $realName = $currentSheet->getCell("A$currentRow")->getValue();
            $type = $currentSheet->getCell("B$currentRow")->getValue();
            $beginTime = $currentSheet->getCell("C$currentRow")->getValue();
            $endTime = $currentSheet->getCell("D$currentRow")->getValue();
            $remark = $currentSheet->getCell("E$currentRow")->getValue();
            $data["e_id"] = $employeeMap[$realName];
            if (ExcelUtils::isDate($currentSheet->getCell("C$currentRow"))) {
                $data["begin_time"] = ExcelUtils::phpDateToObjectDate($beginTime);
            }
            if (ExcelUtils::isDate($currentSheet->getCell("D$currentRow"))) {
                $data["end_time"] = ExcelUtils::phpDateToObjectDate($endTime);
            }
            $data["type"] = $exceptionMap[$type];
            $data["remark"] = $remark;
            $dataList[] = $data;
        }
        M("Exception")->addAll($dataList);
    }

}