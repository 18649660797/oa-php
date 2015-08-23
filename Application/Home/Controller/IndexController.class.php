<?php
namespace Home\Controller;

class IndexController extends BasicController {
    public function index(){
        $this -> display(T("./main"));
    }
    public function main(){
        $this -> display(T("./main"));
    }
}