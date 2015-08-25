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
//        session("S_UID", 1);
        if (!session("S_UID")) {
            redirect("/index.php/home/login");
            return;
        }
        $r = $auth-> check("super", session("S_UID"));
        if(!$r){
            $this->error('没有权限！', 3);
        }
    }
}