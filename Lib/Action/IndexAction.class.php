<?php
class IndexAction extends CommonAction
{
	public function index(){
		$this->display();
	}
	public function login(){
		$accout=$_POST['account'];
		$password=$_POST['password'];
		if($accout=="adminxx"&&$password=="adminzz"){
				$this->display("Article:article");
		}else{
			$this->assign("error","帐号或密码错误");
			$this->display("index");
		}
	}
}
?>