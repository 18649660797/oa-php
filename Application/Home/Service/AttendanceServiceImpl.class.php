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
use Home\Utils\EncodeUtils;

class AttendanceServiceImpl implements AttendanceService
{
    function upload()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg', 'xls', "xlsx");// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();
        import("Org.Util.PHPExcel");

        $filePath = "./Uploads/" . $info["file"]["savepath"] . $info["file"]["savename"];
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
        $allColumn = $currentSheet->getHighestColumn();
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        $dataList = array();
        for ($currentRow = 4; $currentRow <= $allRow; $currentRow++) {
            $data = array();
            $realName = $currentSheet->getCell("B$currentRow")->getValue();
            $workDate = $currentSheet->getCell("E$currentRow")->getValue();
            $workDate = $val = date('Y-m-d', strtotime($workDate));
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
            if ($data["am_time"]) {
                $data["am_time"] = date("Y-m-d H:i:s", strtotime($workDate . $data["am_time"]));
            }
            $data["pm_time"] = $pm1;
            if (!$data["pm_time"]) {
                $data["pm_time"] = $pm2;
            }
            if ($data["pm_time"]) {
                $data["pm_time"] = date("Y-m-d H:i:s", strtotime($workDate . $data["pm_time"] . ":00"));
            }
            $dataList[] = $data;
        }
        $month = $dataList[0]["work_date"];
        $month = substr($month, 0, 7);
        $dao = D("Attendance");
        $dao->relation(true);
        $attendanceList = $dao->where(array("work_date" => array("like", "$month%")))->select();
        foreach ($attendanceList as $one) {
            foreach ($dataList as $data) {
                if (EncodeUtils::encode($one["employee"]["real_name"]) == EncodeUtils::encode($data["real_name"])) {
                    if ($data["am_time"]) {
                        $one["am_time"] = $data["am_time"];
                    }
                    if ($data["pm_time"]) {
                        $one["pm_time"] = $data["pm_time"];
                    }
//                    var_dump($one);
                }
            }
        }
        $dao -> save($attendanceList);
    }

    function init($month)
    {
        $days = DateUtils::getDaysByMonth($month);
        $employees = M("Employee") -> order("attendance_cn asc") -> select();
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

}