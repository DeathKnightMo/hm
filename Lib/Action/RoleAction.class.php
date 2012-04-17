<?php
/**
 * 角色功能模块
 * 需超级管理员权限
 * @author 莫柯明
 *
 */
class RoleAction extends CommonAction {
		/**
	 * 
	 * 用户角色信息一览
	 */
	public function index() {
//		if (! isRoot ()) {
//			$this->no_right();
//		}
		
		$Role = M ( "Role" );
		$list = $Role->findAll ();
		$this->assign ( "list", $list );

		$this->display ( DEFAULT_DISPLAY );
	}
	
	/**
	 * 创建角色
	 */
	public function create() {
		//TODO 管理员才能创建
		if (! isRoot ()) {
			$this->no_right();
		}

		$this->display ( DEFAULT_DISPLAY );
	}
	
	/**
	 * 
	 * 保存角色
	 */
	public function save() {
		if (! isRoot ()) {
			$this->no_right();
		}
		
		$Role = D ( "Role" );
		if (! $Role->create ()) {
			$this->error_msg( $Role->getError ());
		} else {
			//print 'sdsd'.$Role->role_name;
			$result = $Role->add ();
			if ($result !== false) {
				$this->success_msg("创建角色成功！",  C('WWW')."/Role/index");
			} else {
				$this->error_msg("创建角色失败！");
			}
		}
	
	}
	
	/**
	 * 
	 * 编辑角色
	 */
	public function edit() {
		if (! isRoot ()) {
			$this->no_right();
		}
		if ($_GET ['id']) {
			$Role = M ( 'Role' );
			$role = $Role->getById ( $_GET ['id'] );
			$this->assign ( "role", $role );
		}

		$this->display ( DEFAULT_DISPLAY );
	}
	
	/**
	 * 
	 * 角色更新
	 */
	public function update() {
		if (! isRoot ()) {
			$this->no_right();
		}
		if ($_POST ['id']&&$_POST['id']>0) {
			$Role = D ( 'Role' );
			if (! $Role->create ()) {
				$this->error_msg( $Role->getError ());
			} else {
				$result = $Role->where ( 'id=' . $Role->id )
					->setField ( array ('role_name','description', 'update_time' ), array ($Role->role_name,$Role->description, $Role->update_time ) );
				if ($result !== false) {
					$this->success_msg("编辑角色成功！", C('WWW')."/Role/show/id/" . $_POST ['id']);
				} else {
					$this->error_msg("编辑角色失败！");
				}
			}
		
		} else {
			$this->error_msg("无效的参数！");
		}

		$this->display ( DEFAULT_DISPLAY );
	}
	
	/**
	 * 
	 * 显示角色信息
	 */
	public function show() {
		if (! isRoot ()) {
			$this->no_right();
		}
		if (isset($_GET ['id'])&&$_GET['id']>0) {
			$Role = M ( 'Role' );
			$role = $Role->getById ( $_GET ['id'] );
			$this->assign ( "role", $role );
		}else{
			$this->error_msg("无效的参数！");
		}

		$this->display ( DEFAULT_DISPLAY );
	}
}