<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cache;

class Index extends Controller
{
    public function index()
    {
        if (IsLogin() === 0) {
            return view('login');
        }
        return view('index');
    }

    public function login()
    {
        return view('login');
    }

    public function register()
    {
        return view('register');
    }

    public function password_find()
    {
        return view('password_find');
    }

    public function password_reset($token)
    {
        if (!Cache::get($token)) {
            return view('password_reset_expired');
        }

        $this->assign([
            'email' => Cache::get($token),
            'token' => $token
        ]);
        return $this->fetch('password_reset');
    }
}
