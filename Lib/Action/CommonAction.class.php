<?php
class CommonAction extends Action {
	//空操作  404页面
	function _empty(){
		header("HTTP/1.0 404 Not Found");
		$this->error_msg("很抱歉，您访问的页面不存在！");
		//$this->display(':Public:404');
		//header("Location: /Public/404.html");
	}
	
	/**
	 * 
	 * 返回用户无权限提示信息
	 */
	function no_right(){
		$this->error_msg("很抱歉，您没有权限访问此页面！",0);
	}
	
	/**
	 * 
	 * 用户是否有访问权限
	 * @param $act action名
	 * @param $fun function名
	 */
	
	protected function has_right($act,$fun){
		$roleId=get_user_role();
		if($roleId===false){
			$this->no_right();
		}
		if($act==""||$fun==""){
			$this->no_right();
		}
		$Permissions=M("Permissions");
		$Role=M("Role");
		$role=$Role->find($roleId);
		$per=$Permissions->where("action='".$act."' and fun='".$fun."'")->find();
		if(isset($per['id'])&&$per['id']>0){
			$rolePer="";
			//角色权限
			if(isset($role['permissions'])&&trim($role['permissions'])!="")
				$rolePer=trim($role['permissions']);
			$perId="/,".$per['id'].",/";
			if(!preg_match($perId, ",".$rolePer.",")){
				$this->no_right();
			}
		}
	}
	/**
	 * 
	 * 返回错误提示信息
	 * @param $message 返回信息
	 * @param $type    返回状态
	 */
	function error_msg($message,$type=1){
		$this->assign("msg",$message);
		$this->assign("type",$type);
		$this->display("Public:error");
		exit();	
	}
	/**
	 * 
	 * 返回错误信息并跳转
	 * @param $message 错误提示信息
	 * @param $jumpUrl 跳转URL
	 * @param $time	        跳转时间
	 */
	function error_redirect($message,$jumpUrl,$time=3){
		header("refresh:{$time};url={$jumpUrl}");
		$this->assign("msg",$message);
		$this->assign("jumpUrl",$jumpUrl);
		$this->assign("time",$time);
		$this->assign("type",-1);
		$this->display("Public:error_msg");
		exit();	
	}
	
	/**
	 * 
	 * 操作成功返回信息
	 * @param  $message 操作成功信息
	 * @param  $jumpUrl 跳转链接
	 * @param  $time 跳转时间，单位s
	 */
	function success_msg($message,$jumpUrl,$time=3){
		header("refresh:{$time};url={$jumpUrl}");
		$this->assign("msg",$message);
		$this->assign("jumpUrl",$jumpUrl);
		$this->assign("time",$time);
		$this->display("Public:success_msg");
		exit();	
	}
}