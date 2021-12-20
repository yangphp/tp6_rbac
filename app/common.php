<?php

/**
 * 管理员密码加密
 */
function admin_pwd($str)
{
	$secret = config("app.admin_pwd_secret");

	return md5(md5($secret).md5($str));
}
/**
 * 是否显示菜单
 */
function show_action($menu,$menu_list)
{
	if ($menu_list == 'all') 
	{
		return "";
	}else{
		if(!in_array($menu,$menu_list)){
			return " style='display:none' ";
		}
	}
}
