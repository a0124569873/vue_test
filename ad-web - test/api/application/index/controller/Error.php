<?php
namespace app\index\controller;
use think\Controller;
use think\Request;

class Error extends Controller{

    public function index(Request $request){
        $controlName = $request->controller();
        return $this->control($controlName);
    }

    public function _empty($name){
        return $this->control($name);
    }

    protected function control($name){
         $this->redirect('/errorpage');
    }
}