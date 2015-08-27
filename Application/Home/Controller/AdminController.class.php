<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午7:59
 */

namespace Home\Controller;


use Home\Utils\RenderUtil;

class AdminController extends BasicController
{
    public function data()
    {
        echo json_encode(query("AuthUser"));
    }

    public function edit($id = "")
    {
        $entity = array();
        if ($id) {
            $entity = M("AuthUser")->find($id);
            $groupIds = M("AuthGroupAccess")->where(array("uid" => array("eq", $id)))->getField("group_id", true);
            $this->assign("groupIds", join(",", $groupIds));
        }
        $this->assign("entity", $entity);
        $this->display(T("admin/edit"));
    }

    public function save($id = "")
    {
        $dao = M("AuthUser");
        $entity = array();
        if ($id) {
            $entity = $dao->find($id);
        }
        $entity["username"] = I("username");
        $entity["password"] = md5(I("password"));
        if ($id) {
            $dao->save($entity);
            M("AuthGroupAccess")->where(array("uid" => array("eq", $id)))->delete();
        } else {
            $id = $dao->add($entity);
        }
        $groups = explode(",", I("groups"));
        $dao2 = M("AuthGroupAccess");
        foreach ($groups as $group) {
            $data = array();
            $data["uid"] = $id;
            $data["group_id"] = $group;
            $dao2->add($data);
        }
        echo json_encode(RenderUtil::success());
    }

    public function delete($ids)
    {
        $dao = M("AuthUser");
        $dao->delete($ids);
        M("AuthGroupAccess")->where(array("uid" => array("in", $ids)))->delete();
        echo json_encode(RenderUtil::success("删除成功！"));
    }

    public function getGroups()
    {
        header("Content-type: application/json");
        $dataList = M("AuthGroup")->select();
        $result = array();
        foreach ($dataList as $one) {
            $result[] = array("id" => $one["id"], "value" => $one["id"], "text" => $one["title"]);
        }
        echo json_encode($result);
    }

}