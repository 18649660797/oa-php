<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午2:53
 */

namespace Home\Controller;


use Think\Auth;
use Think\Controller;

class BasicController extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->checkRight();
    }


    private function checkRight(){
        $auth= new Auth();
        if (!session("S_UID")) {
            redirect("/index.php/home/login");
            return;
        }
        $r = $auth-> check("super", session("S_UID"));
        if(!$r){
            $uri = $_SERVER["PHP_SELF"];
            switch ($uri) {
                case "/index.php":
                case "/index.php/home/index/main":
                    return;
            }
            $uri = explode("/", $uri);
            $url = "/" . $uri[1] . "/"  . $uri[2] . "/" . $uri[3];
            $flag = $auth-> check($url, session("S_UID"));
            if (!$flag) {
                $this->error('没有权限！', "/index.php/home/login");
            }
        }
    }
}