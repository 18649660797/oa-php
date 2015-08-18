<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/15
 * Time: 下午1:49
 */

namespace Home\Controller;
use Home\Model\Employee;
use Home\Utils\ExcelUtils;
use Org\Util\ArrayList;
use Org\Util\String;
use Think\Controller;
class AttendanceController extends Controller {
    public function view(){
        $this -> display(T("attendance/upload"));
    }

    public function upload() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg', 'xls', "xlsx");// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload -> upload();
        import("Org.Util.PHPExcel");

        $filePath = "./Uploads/" . $info["file"]["savepath"] . $info["file"]["savename"];
        /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return ;
            }
        }

        $PHPExcel = $PHPReader->load($filePath);
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        $jsonArray = array(
            'A' => 'department',
            'B' => 'name',
            'C' => 'work_date',
            'D' => 'am_time',
            'F' => 'pm_time'
        );
        /**从第二行开始输出，因为excel表中第一行为列名*/
        for($currentRow = 3;$currentRow <= $allRow;$currentRow++){
            /**从第A列开始输出*/
            $attendance = M("Attendance");
            $data = array();
            for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){
                if (!$jsonArray[$currentColumn]) {
                    continue;
                }
//                $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/
                $val = $currentSheet -> getCell("$currentColumn$currentRow") -> getValue();
                if ($currentColumn == 'C') {
                    $val = date('Y-m-d',strtotime($val));
                } else if ($currentColumn > 'C') {
                    $val = date('Y-m-d h:i:s', strtotime($val));
                } else {
                    $val = iconv('utf-8','gbk', $val);
                    $val = iconv('gbk','utf-8', $val);
                }
                $data[$jsonArray[$currentColumn]] = $val;
            }
//            var_dump($data);
            if ($data["name"]) {
                $attendance -> add($data);
            }
        }
    }

    public function excel() {
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

    public function data() {
        $data = M("Attendance");
        $result["rows"] = $data  -> limit(I("start") . "," . I("limit")) -> select();
        $result["results"] = 1000;
        echo json_encode($result);
    }

    public function viewList() {
        $this -> display(T("attendance/list"));
    }

    function GetData($val){
        $jd = GregorianToJD(1, 1,1970);
        $gregorian = JDToGregorian($jd+intval($val)-25569);
        return date("h:i", $gregorian);/**显示格式为 “月/日/年” */
    }


}