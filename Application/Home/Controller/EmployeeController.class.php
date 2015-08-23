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

    public function data() {
        $result = query("Employee");
        echo json_encode($result);
    }

    public function viewAdd() {
        $this->display(T("employee/add"));
    }

    public function save() {
        $employee = array();
        $employee["real_name"] = I("realName");
        $employee["attendance_cn"] = I("attendanceCn");
        $dao = M("Employee");
        $dao->add($employee);
        echo json_encode(RenderUtil::success("添加员工成功！"));
    }

    public function delete($ids) {
        $dao = M("Employee");
        $dao->delete($ids);
        echo json_encode(RenderUtil::success("删除员工成功！"));
    }

}