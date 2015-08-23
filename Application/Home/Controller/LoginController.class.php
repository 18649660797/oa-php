<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午3:37
 */

namespace Home\Controller;


use Home\Service\LoginServiceImpl;
use Home\Utils\RenderUtil;
use Think\Controller;

class LoginController extends Controller
{
    public function index() {
        $this->display(T("./login"));
    }

    public function auth() {
        $userName = I("username");
        $passWord = I("password");
        $loginService = new LoginServiceImpl();
        $user = $loginService -> login($userName, $passWord);
        if ($user) {
            session("S_UID", $user["id"]);
        } else {
            echo json_encode(RenderUtil::error("登录名或密码错误！"));
            return;
        }
        echo json_encode(RenderUtil::success("登录成功！"));
    }

}