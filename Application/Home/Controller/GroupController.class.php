<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午7:59
 */

namespace Home\Controller;


use Home\Utils\RenderUtil;

class GroupController extends BasicController
{
    public function data()
    {
        echo json_encode(query("AuthGroup"));
    }

    public function edit($id = "")
    {
        $entity = array();
        if ($id) {
            $entity = M("AuthGroup")->find($id);
        }
        $this->assign("entity", $entity);
        $this->display(T("group/edit"));
    }

    public function save($id = "")
    {
        $dao = M("AuthGroup");
        $entity = array();
        if ($id) {
            $entity = $dao->find($id);
        }
        $entity["title"] = I("title");
        $entity["rules"] = I("rules");
        if ($id) {
            $dao->save($entity);
        } else {
            $entity["status"] = 1;
            $dao->add($entity);
        }
        echo json_encode(RenderUtil::success());
    }


    public function getRules()
    {
        header("Content-type: application/json");
        $dataList = M("AuthRule")->select();
        $result = array();
        foreach ($dataList as $one) {
            $result[] = array("id" => $one["id"], "value" => $one["id"], "text" => $one["title"]);
        }
        echo json_encode($result);
    }

    public function delete($ids) {
        $dao = M("AuthGroup");
        $dao->delete($ids);
        echo json_encode(RenderUtil::success("删除成功！"));
    }

}