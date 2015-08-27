<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午8:16
 */

namespace Home\Controller;


use Home\Utils\RenderUtil;
use Think\Controller;

class EmployeeController extends Controller
{
    public function viewList() {
        $this->display(T("employee/list"));
    }

    public function import() {
        $this->display(T("employee/import"));
    }

    public function data() {
        $result = query("Employee");
        echo json_encode($result);
    }

    public function save($id = "") {
        $employee = array();
        if ($id) {
            $dao = M("Employee");
            $employee = $dao->find($id);
        }
        $employee["real_name"] = I("realName");
        $employee["attendance_cn"] = I("attendanceCn");
        $dao = M("Employee");
        if ($id) {
            $dao->save($employee);
        } else {
            $dao->add($employee);
        }
        echo json_encode(RenderUtil::success("操作成功！"));
    }

    public function edit($id = "") {
        if ($id) {
            $dao = M("Employee");
            $entity = $dao->find($id);
            $this->assign("entity", $entity);
        }
        $this->display(T("employee/edit"));
    }

    public function add() {
        $this->display(T("employee/edit"));
    }

    public function delete($ids) {
        $dao = M("Employee");
        $dao->delete($ids);
        echo json_encode(RenderUtil::success("删除员工成功！"));
    }

    public function excel() {
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
        $jsonTmp = array();
        $attendance = M("Employee");
        /**从第二行开始输出，因为excel表中第一行为列名*/
        for($currentRow = 4;$currentRow <= $allRow;$currentRow++){
            /**从第A列开始输出*/
            $data = array();
            $attendanceCn = $currentSheet -> getCell("A$currentRow") -> getValue();
            $realName = $currentSheet -> getCell("B$currentRow") -> getValue();
            $department = $currentSheet -> getCell("D$currentRow") -> getValue();
            $realName = $this -> encode($realName);
            $department = $this -> encode($department);
            $data["attendance_cn"] = $attendanceCn;
            $data["real_name"] = $realName;
            $data["department"] = $department;
            if ($data["real_name"] && !$jsonTmp[$data["real_name"]]) {
                $jsonTmp[$data["real_name"]] = 1;
                $attendance -> add($data);
            }
        }
        redirect("list");
    }

    function encode($str) {
        $str = iconv('utf-8','gbk', $str);
        $str = iconv('gbk','utf-8', $str);
        return $str;
    }

}