<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use think\Facade\Db;

class Auth
{
    /**
     * 显示列表
     */
    public function index()
    {

        //获取菜单
        $auth = Db::table('rbac_auth')->select()->toArray();
        $auth = $this->getAuthTree($auth);

        return view('index',['auth'=>$auth]);
    }

    public function add(Request $request)
    {
        if ($request->isPost()) 
        {
            $data = $request->param();

            $res = Db::table('rbac_auth')->insert($data);
            if ($res) {
                return json(['code'=>200,'msg'=>'添加权限成功']);
            }else{
                return json(['code'=>401,'msg'=>'添加失败']);
            }
        }

        //获取菜单
        $auth = Db::table('rbac_auth')->where("is_menu",1)->select()->toArray();
        $auth = $this->getAuthTree($auth);

        return view('add',['auth'=>$auth]);
    }

    public function edit(Request $request,$id)
    {
        $info = Db::table('rbac_auth')->where("id",$id)->find();

        if ($request->isPost()) 
        {
            $data = $request->param();

            $res = Db::table('rbac_auth')->where("id",$id)->update($data);
            if ($res) {
                return json(['code'=>200,'msg'=>'修改权限成功']);
            }else{
                return json(['code'=>401,'msg'=>'修改失败']);
            }
        }

        //获取菜单
        $auth = Db::table('rbac_auth')->where("is_menu",1)->select()->toArray();
        $auth = $this->getAuthTree($auth);

        return view('edit',['auth'=>$auth,'info'=>$info]);
    }

    public function del(Request $request,$id)
    {

        //判断是否有子菜单 
        $count = Db::table('rbac_auth')->where("auth_pid",$id)->count();
        if($count > 0){
            return json(['code'=>401,'msg'=>'删除失败，该权限下有子权限']);
        }
        Db::table('rbac_auth')->where("id",$id)->delete();
        return json(['code'=>200,'msg'=>'删除权限成功']);
    }

    public function getAuthTree($arr,$pid=0,$level=0){
        static $list = [];

        foreach($arr as $key=>$val){
            if($val['auth_pid'] == $pid){
                $val['level'] = $level;
                $list[] = $val;

                unset($arr[$key]);

                $this->getAuthTree($arr,$val['id'],$level+1);
            }
        }
        return $list;
    }


}
