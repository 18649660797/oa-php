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
        $service = new ExceptionServiceImpl();
        $service->import(true);
        redirect("viewList");
    }

}