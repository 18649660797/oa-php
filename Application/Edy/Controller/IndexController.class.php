<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/18
 * Time: 下午3:38
 */

namespace Edy\Controller;
use Think\Controller;

class IndexController extends Controller
{
    public function index() {
        $this -> display(T("./index"));
    }
}