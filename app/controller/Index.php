<?php
namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
   //管理员主页
    public function index()
    {
       return view('index');
    }
    //退出登录
    public function loginout()
    {
      session('admin_id',null);
      session('admin_name',null);

      return redirect("/admin/login");
    }
}
