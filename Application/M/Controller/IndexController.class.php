<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/9/1
 * Time: 下午4:30
 */

namespace M\Controller;


use Think\Controller;

class IndexController extends Controller
{
    public function index() {
        $this->display(T("./index"));
    }
}