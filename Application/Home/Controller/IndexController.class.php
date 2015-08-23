<?php
namespace Home\Controller;

class IndexController extends BasicController {
    public function index(){
        $this -> display(T("./index"));
    }
    public function main(){
        $this -> display(T("./main"));
    }
}