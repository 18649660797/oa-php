<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午7:59
 */

namespace Home\Controller;


class AdminController extends BasicController
{
    public function viewList() {
        $this->display(T("admin/list"));
    }

}