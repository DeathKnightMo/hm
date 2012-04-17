<?php
/**
 * 用户功能模块
 *
 */

import ( "@.Util.Page" );
class UserAction extends CommonAction {
	/**
	 * 用户管理首页
	 * 可根据ID/昵称/email等搜索用户
	 * 
	 */
	public function index() {
		if (! isRoot ()) {
			$this->no_right();
		}
		$User = M ( "User" );
		$p=1;
		if(isset($_GET['p'])&&$_GET['p']>0){
			$p=intval(trim($_GET['p']));
		}
		$condition="";
		$searchKey=0;
		$searchValue="";
		if(isset($_POST['search_key'])&&$_POST['search_key']!=null&&isset($_POST['search_value'])&&$_POST['search_key']!=null){
			$searchKey=intval(trim($_POST['search_key']));
			$searchValue=trim($_POST['search_value']);
		}elseif(isset($_GET['search_key'])&&$_GET['search_key']!=null&&isset($_GET['search_value'])&&$_GET['search_key']!=null){
			$searchKey=intval(trim($_GET['search_key']));
			$searchValue=trim($_GET['search_value']);
		}
		
		if($searchKey>0&&$searchValue!=""){
			$map['search_key']=$searchKey;
			$map['search_value']=$searchValue;
			switch ($searchKey){
				case 1:
					{//ID
						$condition="id=".$searchValue;
						break;
					}
				case 2:
					{//昵称
						$condition="nickname like '%".$searchValue."%'";
						break;
					}
				default: ;
			}
		}

		$perPage=20;
	    if($condition!=""){
	   		$ulist = $User->where($condition)->order ( 'id desc' )->page ( $p . ',' . $perPage )->select ();
	   		
	   		$count = $User->where($condition)->count ();
	   		$Page = new Page ( $count, $perPage );
	   		foreach($map as $key=>$val) {
				$Page->parameter   .=   "$key=".urlencode($val)."&";
	   		}
			$show=$Page->show();
	   		$this->assign ( 'page', $show ); // 赋值分页输出
	   		$Role=M("Role");
	   		$roleList=$Role->getField("id,name");
	   		$this->assign("rList",$roleList);
			$this->assign ( "ulist", $ulist );
			$this->assign ("map",$map);
			$this->display ( DEFAULT_DISPLAY );
	    }else{
	    	$ulist = $User->order ( 'id desc' )->page ( $p . ',' . $perPage )->select ();
	   		$count = $User->count ();
	   		$Page = new Page ( $count, $perPage );
			$show=$Page->show();
	   		$this->assign ( 'page', $show ); // 赋值分页输出
	   		
	   		$Role=M("Role");
	   		$roleList=$Role->getField("id,name");
	   		$this->assign("rList",$roleList);
			$this->assign ( "ulist", $ulist );
			$this->display ( DEFAULT_DISPLAY );
	    }
	}
	
	
	
/**
	 * 
	 * 用户登陆
	 * 如果管理员要在普通用户页面进行登录：
	 * 		必须具备所有角色，并且超级管理员的身份必须放在最前面
	 * 
	 */
	public function login() {
		if (isset ( $_POST ['account'] )&&isset($_POST['password'])) {
			$User = M ( "User" );
			$account=trim($_POST['account']);
			$pwd=trim($_POST['password']);
			if($account==""||$pwd==""){
				$this->error_redirect("请输入账号或密码！", C("WWW"),2);
			}
			
			$map['nickname']=$account;
		
			$map['password'] = md5($pwd);
			
			$user = $User->where($map)->find();
			//查找对应角色的用户是否存在
			if(isset($user['id'])&&$user['id']>0){
				
				if ($user ['usestate'] == 1) {
					//设置登录用户Cookie
					setSession($user);
					redirect(C("WWW")."/index.php?m=User&a=index");
				}else{
					$this->error_msg("您的账号已被封禁，请联系管理员！");
				}
				
			}else{
				$this->error_msg("账号或密码输入错误！");
			}
		}
		redirect(C("WWW"));

	}	
	
	
	

	public function check() {
		$User = D ( "User" );
		if (isset ( $_POST ['email'] )) {
			$email = trim ( $_POST ['email'] );
			if($email==null||$email==""){
				$this->ajaxReturn ( 0, "请填写邮箱！", 0 );
			}
			$num = $User->where ( "email='" . $email . "'" )
				->count ();
			if ($num > 0) {
				$this->ajaxReturn ( 0, "此邮箱已被注册！", 0 );
			} else {
				$this->ajaxReturn ( 1, "此邮箱可以使用！", 1 );
			}
		}

		if (isset ( $_POST ['nick'] )) {
			$nick = trim ( $_POST ['nick'] );
			if($nick==null||$nick==""){
				$this->ajaxReturn ( 0, "请填写昵称！", 0 );
			}
			if (preg_match ( "/偶家/", $nick )) {
				$this->ajaxReturn ( 0, "昵称包含非法字符！", 0 );
			}
			$num = $User->where ( "nick_name='" . $nick . "'" )
				->count ();
			//print_r($num);
			if ($num > 0) {
				$this->ajaxReturn ( 0, "此昵称已存在！", 0 );
			} else {
				$this->ajaxReturn ( 1, "此昵称可以使用！", 1 );
			}
		}
		if(isset($_POST['oldpwd'])){
			$oldPwd=trim($_POST['oldpwd']);
			if($oldPwd==null||$oldPwd==""){
				$this->ajaxReturn ( 0, "请填写旧密码！", 0 );
			}
			$user = $User->where("id=".get_user_id())->find();
			if($user['password'] != md5($oldPwd)){
				$this->ajaxReturn(0,"旧密码输入错误！",0);
			}else{
				$this->ajaxReturn(1,"旧密码输入正确！",1);
			}
		}
		
	}
	


	/**
	 * 
	 * 用户基本信息修改
	 */
	public function savemore(){
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		$User = M ("User");
		if(isset($_POST['uid'])&&$_POST['uid']>0){
			$uid= intval(trim($_POST['uid']));
			if(!isRoot()&&!(get_user_id()==$uid)&&!isMarketer()){
				$this->error_msg("你没有权限修改他人的信息！");
			}
			$data ['id'] =$uid;
//			if(!isset($_POST['nick'])||trim($_POST['nick'])==""){
//				$this->error_msg("请填写昵称！");
//			}
//			$nick=trim($_POST['nick']);
//			if(!isRoot()){//非超级管理员昵称不能包含偶家含
//				if (preg_match ( "/偶家/", $nick )) {
//					$this->ajaxReturn ( 0, "昵称包含非法字符！", 0 );
//				}
//			}
//			//昵称不准使用邮箱
//			if(check_email_format($nick)){
//				$this->error_msg("邮箱不能用做昵称！");
//			}
//			
//			$count=$User->where("nick_name='".$nick."' and id!=".$uid)->count();
//			if($count>0){
//				$this->error_msg("昵称已存在！");
//			}
//			$data['nick_name'] = $nick;
			if(isset($_POST['company'])){
				$data['company']=trim($_POST['company']);
			}
			if(isset($_POST['mobile_num'])){
				$data['mobile_num']=trim($_POST['mobile_num']);
			}
			
			if(isset($_POST['telephone_num'])){
				$data['telephone_num']=trim($_POST['telephone_num']);
			}
			
			if(isset($_POST['fax_num'])){
				$data['fax_num']=trim($_POST['fax_num']);
			}
			if(isset($_POST['contact'])){
				$data['contact']=trim($_POST['contact']);
			}
		
			$result=$User->save($data);
			if($result!==false){
				if(isRoot()){
					if(isset($_POST['role'])&&$_POST['role']>0){
						$roleId=intval(trim($_POST['role']));
						if($roleId<0||$roleId>5){
							$this->error_msg("角色错误！");
						}
						$UserRole=M("UserRole");
						$sql="update oujia_user_role set role_id=".$roleId." where user_id=".$uid;
						$result=$UserRole->execute($sql);
						if($result===false){
							$this->error_msg("更改角色出错！");
						}					
					}
				}
				if(get_user_id()==$uid){
					$user=$User->find($uid);
					setSession ($user);
				}
				if(isRoot()){
					$this->success_msg("修改个人信息成功！", C("WWW")."/User/index");
				}else{
					$this->success_msg("修改个人信息成功！", C("WWW")."/User/show");
				}
			}else{
				$this->error_msg("修改个人信息失败");
			}
		}else{
			$this->error_msg("缺少指定的参数！");
		}
	}
	
	
	


	/**
	 * 用户注销
	 */
	public function logout() {
		if(isset($_SESSION['UID']))
			Session::clear();
		redirect(C("WWW")."/index.php?m=User&a=index");
	}
	
	/**
	 * 
	 * 用户信息修改
	 */
	public function edit() {
		//管理员修改用户的信息
		if(!isLogin()){
			redirect(C("WWW"));
		}
		if (isset($_GET['uid'])&&isRoot ()&&!isMarketer()) {
			//管理员编辑用户信息
			if (isset ( $_GET ['uid'] ) && $_GET ['uid'] > 0) {
				$uid=intval(trim($_GET['uid']));
				$User = M ( "User" );
				$user = $User->find ($uid);
				if(isset($user['id'])&&$user['id']==$uid){
					$UserRole=M("UserRole");
					$userRole=$UserRole->where("user_id=".$user['id'])->find();
					$this->assign("userRole",$userRole);
					$this->assign ( "user", $user );
					$this->assign ( "uid", $uid );
					$this->get_user_cookie();
					$this->display ( DEFAULT_DISPLAY );
				}else{
					$this->error("指定的用户不存在！");
				}
			}else{
				$this->error_msg("缺少指定的参数或参数非法！");
			}
		}else{
			//登录用户编辑个人信息
			$uid=get_user_id();
			$User = M ( "User" );
			$user = $User->find ($uid);
			if(isset($user['id'])&&$user['id']==$uid){
				$this->assign ( "user", $user );
				$this->get_user_cookie();
				$this->display ( DEFAULT_DISPLAY );
			}else{
				$this->error_msg("指定的用户不存在！");
			}
		}
	}
	
	/**
	 * 
	 * 管理员查看用户信息
	 * 
	 */
	public function detail(){
		if(!isLogin()){
			redirect(C('WWW'));
		}
		if (! isRoot () && ! isMarketer () ) {
			$this->no_right();
		}
		if(isset($_GET['uid'])){
			$User = M ("User");
			$user = $User ->where("id=".$_GET['uid'])->find();
			$this->assign("user",$user);
			$this->get_user_cookie();
			$this->display(DEFAULT_DISPLAY);
		}
	
	}
	
	/**
	 * 
	 * 用户信息更新
	 */
	public function update() {
	
		//管理员解除/封禁用户信息
		if (isset ( $_POST ['uid'] ) && $_POST ['uid'] > 0) {
			if (! isRoot () && ! isMarketer () ) {
				$this->ajaxReturn(0,"你没有权限！",0);
			}
			$UserRole = M ( "UserRole" );
			$count = $UserRole->where ( "user_id=" . $_POST ['uid'] . " and role_id=" . ROLE_ROOT )
				->count ();
			if ($count > 0) {
				$this->ajaxReturn ( 0, "操作失败！管理员账号不允许封禁！", 0 );
			}
			$User = D ( "User" );
			$data ['id'] = $_POST ['uid'];
			$data ['status'] = $_POST ['status'];
			$u_id = $User->save ( $data );
			if ($u_id !== false) {
				$this->ajaxReturn ( 1, "操作成功！", 1 );
			} else {
				$this->ajaxReturn ( 0, "操作失败！", 0 );
			}
		}else{
			$this->ajaxReturn(0,"操作失败！参数错误！",0);
		}
		
	}
	
	
	
	/**
	 * 显示用户个人基本资料
	 */
	public function show() {
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		$User = M ( "User" );
		$uid = get_user_id();
		$user = $User -> where("id=".$uid) -> find();
		$this->assign ( "user", $user );
		if(isset($_GET['code'])&&trim($_GET['code'])!=""){
			$this->assign("code",$_GET['code']);
		}
		$this->get_user_cookie();
		$this->display ( DEFAULT_DISPLAY );
	}
	
	/**
	 * 
	 * 用户密码修改
	 *
	 */
	public function pwd() {
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		$User = M ("User");
		$user = $User -> where('id='.get_user_id()) -> find();
		if($user['site']!=1){
			$this->error_msg("第三方用户无法修改密码！");
		}
		$this->get_user_cookie();
		$this->display ( DEFAULT_DISPLAY );
	}
	
	/**
	 * 
	 * 用户修改密码
	 */
	public function pwdReset(){
		if(!isLogin()){
			redirect(C("WWW"));
		}
		//用户密码修改
		if (isset($_POST['oldpwd'])&&isset($_POST['password'])&&isset($_POST['repassword'])) {
			
			$oldPwd=trim($_POST['oldpwd']);
			$newPwd=trim($_POST['password']);
			$reNewPwd=trim($_POST['repassword']);
			if($oldPwd==""){
				$this->error_msg("请填写旧密码！");
			}
			if($newPwd==""){
				$this->error_msg("请填写新密码！");
			}
			if($reNewPwd!=$newPwd){
				$this->error_msg("密码不一致！");
			}
			
			$User = M ( "User" );
			$uid=get_user_id();
			$user=$User->find($uid);
			if(isset($user['id'])&&$user['id']==$uid){
				
			}else{
				$this->error_redirect("用户不存在！请重新登陆！", C("WWW"));
			}
			if(md5($oldPwd)!=$user['password']){
				$this->error_msg("旧密码错误！");
			}
			$User->startTrans();
			$result = $User->where ( "id=".$uid )
				->setField ( 'password', md5($newPwd));
			if($result!=false){
				$uc_result=uc_user_edit($user['nick_name'], $oldPwd, $newPwd, null);
				if($uc_result==1){//更新论坛用户资料成功
					$User->commit();
					$this->success_msg("修改密码成功！", C("WWW")."/User/show");
				}else{
					$User->rollback();
					$this->error_msg("修改密码失败！");
				}
			}else{
				$User->rollback();
				$this->error_msg("修改密码失败！");
			}
		}else{
			$this->error_msg("修改密码失败！缺少指定的参数！");
		}
	}
	
	/**
	 * 
	 * 管理员首页
	 *
	 */
	public function admin() {
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		if (! isRoot () && ! isMarketer() ) {
			$this->no_right();
		}
		$this->get_user_cookie();
		$this->display ( DEFAULT_DISPLAY );
	}

	/**
	 * 
	 * 管理员添加用户
	 * 
	 */
	public function addMember(){
		if(!isLogin()){
			redirect(C('WWW'));
		}
		if (! isRoot () && ! isMarketer () ) {
			$this->no_right();
		}
		$this->get_user_cookie();
		$this->display ( DEFAULT_DISPLAY );
	}
	
	public function saveMember() {
		if(!isLogin()){
			redirect(C('WWW'));
		}
		if (! isRoot () && ! isMarketer () ) {
			$this->no_right();
		}
		if (isset ( $_POST ['email'] )&&isset($_POST['nick_name'])&&isset($_POST['role'])){
			$User = D ( "User" );
			if (! $User->create ()) {
				// 如果创建失败 表示验证没有通过 输出错误提示信息
			} else {
				$nick = $User->nick_name;
				if (preg_match ( "/偶家/", $nick )) {
					$this->error_msg( '昵称包含非法字符！' );
				}
				if(check_email_format($nick)){
					$this->error_msg("邮箱不能用做昵称！");
				}
				
				// 验证通过 可以进行其他数据操作
				$result = $User->add ();
				if ($result !== false) {
						$roleId=trim($_POST['role']);
						if($roleId==2||$roleId==3||$roleId==4){
							$UserRole = M ( "UserRole" );
							$data ['role_id'] = $roleId;
							$data ['user_id'] = $result;
							$data ['create_time'] = get_date_time ();
							$data ['update_time'] = get_date_time ();
							$result2 = $UserRole->add ( $data );
							if($result2!==false){
								redirect ( C('WWW')."/User/index" );
							}else{	
								$User->where("id=".$result)->delete();
								$this->error_msg("添加用户失败！");
							}
						}else{
							$User->where("id=".$result)->delete();
							$this->error_msg("选择的角色错误！");
						}
				} else {
					$this->error_msg("添加用户失败！");
				}
			}
		}
		$this->error_msg("缺少指定参数！");
	}

//	/**
//	 * 
//	 * 管理员重置用户密码
//	 * 
//	 */
//	public function resetPwd(){
//		if (! isRoot ()) {
//			$this->assign ( "error", "权限不够！" );
//			$this->display ( DEFAULT_ERROR );
//			return;
//		}
//		if(isset($_POST['uid'])){
//			$User = M ("User");
//			$map ['id'] = $_POST['uid'];
//			$result = $User->where ( $map )
//				->setField ( 'password', md5(123456));
//			if ($result !== false) {
//				$this->ajaxReturn ( 1, "重置成功！", 1 );
//			} else {
//				$this->ajaxReturn ( 0, "重置失败！", 0 );
//			}	
//		}
//	} 
	
	/**
	 * 其他登录
	 */
	private function otherlogin($email) {
		$User = M ( "User" );
		$map ['email'] = $email;
		$user = $User->where ( $map )
			->find ();
		if (isset ( $user ['id'] ) && $user ['id'] > 0) {
			if ($user ['status'] == 1) {
				//设置登录用户Cookie
				set_cookie_login( $user );
				//更新登录时间及登陆IP信息
				$map ['id'] = $user ['id'];
				$column = array ('last_ip', 'login_time' );
				$value = array (get_client_ip (), get_date_time () );
				$User->where ( $map )
					->setField ( $column, $value );
					redirect ( C("WWW")."/Index/diy" );
			} else {
				$this->error_msg( "账号被封禁，请联系管理员！" );
			}
		}
	}
	public function otherSuccess() {
		//$user_info = ( array ) json_decode ( $_SESSION ['user_info'] );
		$user_info=$_SESSION['user_info'];
		//print_r($user_info);
		Session::clear ();
		$this->assign ( "user_info", $user_info );
		$this->display ( DEFAULT_NO_LEFT );
	}
	public function otherSave() {
		if (isset ( $_POST ['nick_name'] )) {
			$user ['create_time'] = get_date_time ();
			$user ['status'] = 1;
			$user ['site'] =$_POST['site'];
			//$user ['site'] =(int)$_POST['site'];
			//print $user['site'];
			$user ['email'] = $_POST ['email'];
			$user ['nick_name'] = $_POST ['nick_name'];
			
			$User = M ( "User" );
			if (preg_match ( "/偶家/", $user['nick_name'] )) {
				$this->error_msg( '昵称包含非法字符！' );
			}
			if(check_email_format($user['nick_name'])){
					$this->error_msg("邮箱不能用做昵称！");
			}
			// 验证通过 可以进行其他数据操作
			$result = $User->add ( $user );
			//dump($User->getLastSql());
			//print "ID--" . $result;
			if ($result !== false) {
				if ($result > 0) {
					//print "ID>0--" . $_POST ['role'];
					$UserRole = M ( "UserRole" );
					$data ['role_id'] = ROLE_THIRD;
					$data ['user_id'] = $result;
					$data ['create_time'] = get_date_time ();
					$data ['update_time'] = get_date_time ();
					$result2 = $UserRole->add ( $data );
				}
				
				$this->otherlogin ( $user ['email'] );
				exit();
			}else {
				$this->error_redirect("登录失败，请重新尝试！", C("WWW"));
			}
		}
	}
	public function startqq(){
  		//成功授权后的回调地址
  		$_SESSION['my_url'] = C("WWW")."/User/connectqq/";

    	//拼接URL     
     	$dialog_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
       	. C('QQ_APP_ID') . "&redirect_uri=" . urlencode($_SESSION['my_url']);

     	echo("<script> top.location.href='" . $dialog_url . "'</script>");
    }
	/**
	 * QQ登录
	 */
	public function connectqq() {
		$ctx = stream_context_create(array(  
       		'http' => array(  
         'timeout' => 1 //设置一个超时时间，单位为秒  
          )  
       	)  
   	 	);  
		
		$code= $_REQUEST["code"];
		//Step2：通过Authorization Code获取Access Token
		

		//拼接URL   
		//$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&" . "client_id=" . $_SESSION ['app_id'] . "&redirect_uri=" . urlencode ( $_SESSION ['my_url'] ) . "&client_secret=" . $_SESSION ['app_secret'] . "&code=" . $_SESSION ['code'];
		$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=" . C("QQ_APP_ID") . "&redirect_uri=" . urlencode ( $_SESSION ['my_url'] ) . "&client_secret=" . C("QQ_APP_SECRET") . "&code=" .$code;
		$response = file_get_contents ( $token_url ,0,$ctx);
		
		if (strpos ( $response, "callback" ) !== false) {
			$lpos = strpos ( $response, "(" );
			$rpos = strrpos ( $response, ")" );
			$response = substr ( $response, $lpos + 1, $rpos - $lpos - 1 );
			$msg = json_decode ( $response );
			if (isset ( $msg->error )) {
				//$this->connectqq ();
				echo "<script>alert('服务器未能响应，请稍后再试！')</script>";
				$this->assign("content","Index:index");
				$this->display(DEFAULT_NO_LEFT);
				exit ();
			}
		}
		
		//Step3：使用Access Token来获取用户的OpenID
		$params = array ();
		parse_str ( $response, $params );
		
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $params ['access_token'];
		
		$str = file_get_contents ( $graph_url ,0,$ctx);
		if (strpos ( $str, "callback" ) !== false) {
			$lpos = strpos ( $str, "(" );
			$rpos = strrpos ( $str, ")" );
			$str = substr ( $str, $lpos + 1, $rpos - $lpos - 1 );
		}
		$user = json_decode ( $str );
		
		if (isset ( $user->error )) {
			echo "<script>alert('服务器未能响应，请稍后再试！')</script>";
			$this->assign("content","Index:index");
			$this->display(DEFAULT_NO_LEFT);
			exit ();
		}
		//得到openid后跳转到指定页面进行绑定
		$user_info_url = "https://graph.qq.com/user/get_user_info?access_token=" . $params ['access_token'] . "&oauth_consumer_key=" . C("QQ_APP_ID") . "&openid=" . $user->openid;
		// echo $user_info_url;
		$user_info = file_get_contents ( $user_info_url ,0,$ctx);
		if ($user_info == null) {
			echo "<script>alert('服务器未能响应，请稍后再试！')</script>";
			$this->assign("content","Index:index");
			$this->display(DEFAULT_NO_LEFT);
			exit ();
		}
		//判断用户是否已经存在用户表中   			  
		$User = M ( 'User' );
		$u = $User->where ( "email='" . $user->openid . "'" )
			->find ();
		
		if ($u != null) {
			Session::clear();
			$this->otherlogin ( $user->openid );
			exit();
		} else {
			$user_info_array=( array ) json_decode ( $user_info );
			//print_r($user_info_array);
			$transfer['image']=$user_info_array['figureurl_1'];
			$transfer['email']=$user->openid;
			$transfer['nick_name']=$user_info_array['nickname'];
			$transfer['site']=3;
			//print_r($transfer);
			//$_SESSION ['user_info'] = $user_info;
			$_SESSION['user_info']=$transfer;
			redirect ( C("WWW")."/User/otherSuccess" );
		}
	}
	/*
	 * 百度登录
	 */
	public function startbaidu() {
		//成功授权后的回调地址
		$_SESSION ['my_url'] = C("WWW")."/User/connectbaidu/";
		//拼接URL   
		$dialog_url = "https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&client_id=" . C("BAIDU_APP_ID") . "&redirect_uri=" . urlencode ( $_SESSION ['my_url'] );
		echo ("<script> top.location.href='" . $dialog_url . "'</script>");
	}
	
	public function connectbaidu() {
		$ctx = stream_context_create(array(  
       		'http' => array(  
           'timeout' => 1 //设置一个超时时间，单位为秒  
           )  
       )  
    );

		//Step1：获取Authorization Code
		$code= $_REQUEST["code"];
		//Step2：通过Authorization Code获取Access Token
		

		//拼接URL   
		$token_url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=authorization_code&" . "client_id=" . C("BAIDU_APP_ID") . "&redirect_uri=" . urlencode ( $_SESSION ['my_url'] ) . "&client_secret=" . C("BAIDU_APP_SECRET") . "&code=" . $code;
		$response = file_get_contents ( $token_url ,0,$ctx);
		//print_r($response);
		$response_array = ( array ) json_decode ( $response );
		if($response_array['access_token']==null){
			echo "<script>alert('服务器未能响应，请稍后再试！')</script>";
			$this->assign("content","Index:index");
			$this->display(DEFAULT_NO_LEFT);
			exit;
		}
		//print_r($response_array);
		//$open_url = "https://openapi.baidu.com/rest/2.0/passport/users/getInfo?access_token=" . $response_array ['access_token'];
		$open_url = "https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser?access_token=" . $response_array ['access_token'];
		$user_info = file_get_contents ( $open_url ,0,$ctx);
		$user_info_array = ( array ) json_decode ( $user_info );
		if($user_info_array['uid']==null){
			echo "<script>alert('服务器未能响应，请稍后再试！')</script>";
			$this->assign("content","Index:index");
			$this->display(DEFAULT_NO_LEFT);
			exit;
		}
		
		$User = M ( 'User' );
		$user = $User->where ( "email='" . $user_info_array ['uid'] . "'" )
			->find ();
		if ($user != null) {
			$this->otherlogin ( $user_info_array ['uid'] );
			exit;
		}
		else {
			$transfer['image']='http://himg.bdimg.com/sys/portraitn/item/'.$user_info_array['portrait'].'.jpg' ;
			$transfer['email']=$user_info_array['uid'];
			$transfer['site']=5;
			$transfer['nick_name']=$user_info_array['uname'];
			$_SESSION ['user_info'] = $transfer;
			//$_SESSION ['user_info'] = $user_info;
			redirect ( C("WWW")."/User/otherSuccess" );
		}
		$user ['create_time'] = get_date_time ();
		$user ['status'] = 1;
		$user ['site'] = 5;
		$user ['email'] = $user_info_array ['userid'];
		$user ['nick_name'] = $user_info_array ['username'];
		
		$nick = $user['nick_name'];
		if (preg_match ( "/偶家/", $nick )) {
			$this->error_msg( '昵称包含非法字符！' );
		}
		if(check_email_format($nick)){
			$this->error_msg("邮箱不能用做昵称！");
		}
		// 验证通过 可以进行其他数据操作
		$result = $User->add ( $user );
		//print "ID--" . $result;
		if ($result !== false) {
			if ($result > 0) {
				//print "ID>0--" . $_POST ['role'];
				$UserRole = M ( "UserRole" );
				$data ['role_id'] = ROLE_THIRD;
				$data ['user_id'] = $result;
				$data ['create_time'] = get_date_time ();
				$data ['update_time'] = get_date_time ();
				$result2 = $UserRole->add ( $data );
			
			}
			$this->otherlogin ( $user ['email'] );
		} else {
			$this->error_redirect("登录失败，请重新尝试！",C("WWW"));
		}
	}
		/*
		 * 网易登录
		 */
		public function startwy(){
			import( '@.Util.wydemo.oauth_lib');
			import('@.Util.wydemo.tblog');
			$oauth = new OAuth(C("WANGYI_APP_ID"), C("WANGYI_APP_SECRET"));
			$request_token = $oauth->getRequestToken();
//			$aurl = $oauth->getAuthorizeURL( $request_token['oauth_token'], "http://www.ojia.cc/User/connectwy");
			$aurl = $oauth->getAuthorizeURL( $request_token['oauth_token'], C("WWW")."/User/connectwy");
			$_SESSION['request_token'] = $request_token;
			redirect("$aurl");
		}
		
		public function connectwy(){
			import( '@.Util.wydemo.oauth_lib');
			import('@.Util.wydemo.tblog');
			$oauth = new OAuth( C("WANGYI_APP_ID"), C("WANGYI_APP_SECRET") , $_SESSION['request_token']['oauth_token'] , $_SESSION['request_token']['oauth_token_secret']  );
			if ($access_token = $oauth->getAccessToken(  $_REQUEST['oauth_token'] ) )
			{
				$tblog = new TBlog(C("WANGYI_APP_ID"), C("WANGYI_APP_SECRET"),$access_token['oauth_token'],$access_token['oauth_token_secret']);
				$me = $tblog->verify_credentials();
				//$ms = $tblog->home_timeline();
				$user['email']=$me['email'];
				$User=M('User');
				$u=$User->where("email='".$user['email']."'")->find();
				if($u!=null){
					$this->otherlogin($user['email']);
					exit;
				}else {
					//$transfer['email']=$me['id'];
					$transfer['image']=$me['profile_image_url'];
					$transfer['email']=$me['email'];
					$transfer['site']=6;
					$transfer['nick_name']=$me['name'];
					$_SESSION ['user_info'] = $transfer;
					//$_SESSION ['user_info'] = $user_info;
					redirect ( C("WWW")."/User/otherSuccess" );
				}	
				$nick = $user['nick_name'];
				if (preg_match ( "/偶家/", $nick)) {
					$this->error_msg( '昵称包含非法字符！' );
				}
				if(check_email_format($nick)){
					$this->error_msg("邮箱不能用做昵称！");
				}
			// 验证通过 可以进行其他数据操作
			$result = $User->add ( $user );
			//print "ID--" . $result;
			if ($result !== false) {
				if ($result > 0) {
					//print "ID>0--" . $_POST ['role'];
					$UserRole = M ( "UserRole" );
					$data ['role_id'] = ROLE_THIRD;
					$data ['user_id'] = $result;
					$data ['create_time'] = get_date_time ();
					$data ['update_time'] = get_date_time ();
					$result2 = $UserRole->add ( $data );
			
				}
				$this->otherlogin ( $user ['email'] );
			} else {
				$this->error_redirect("登录失败，请重新尝试！", C("WWW"));
			}
			}
			else
			{
   				 $this->error_redirect("授权，请重新尝试！", C("WWW"));
			}
		}
		public function checksave(){
			$User=M("User");
			$nick_name=trim($_POST['nick_name']);
			if (preg_match ( "/偶家/", $nick_name)) {
				$this->ajaxReturn(0,"昵称包含非法字符！",0);
			}
			if(check_email_format($nick_name)){
				$this->ajaxReturn(0,"邮箱不能用做昵称！",0);
			}
			$user=$User->where("nick_name='".$nick_name."'")->find();
			if(isset($user['id'])&&$user['id']>0){
				$this->ajaxReturn(0,"昵称已存在！",0);
			}else{
				$this->ajaxReturn(1,"成功！",1);
			}
		}
	/**
	 * 
	 * 连接新浪
	 * 
	 */

	public function startsina(){
		import ( '@.Util.weibodemo.weibooauth' );
		$app_id = "169755214";
 		$app_secret = "6a6a02cd87aef0c2aa4c202052c95a50";
  		session_start();
		$o = new WeiboOauth( $app_id , $app_secret);
		$keys = $o->getRequestToken();
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false ,C("WWW").'/User/connectionsina');
		$_SESSION['keys'] = $keys;
		header("Location:$aurl");
	}
 	public function connectionsina(){
		import ( '@.Util.weibodemo.weibooauth' );
		$app_id = "169755214";
 		$app_secret = "6a6a02cd87aef0c2aa4c202052c95a50";
  		session_start();
  		$or = new WeiboOauth( $app_id , $app_secret , $_SESSION['keys']['oauth_token'] , $_SESSION['keys']['oauth_token_secret']  );
		$last_key = $or->getAccessToken( $_REQUEST['oauth_verifier'] ) ;
		$_SESSION['last_key'] = $last_key;	
		$surl = C("WWW")."/User/weibologin";
		header("Location:$surl");
	}
	public function weibologin(){
		session_start();
		import ( '@.Util.weibodemo.weibooauth' );
		$app_id = "169755214";
 		$app_secret = "6a6a02cd87aef0c2aa4c202052c95a50";
		$c = new WeiboClient( $app_id , $app_secret , $_SESSION['last_key']['oauth_token'] , $_SESSION['last_key']['oauth_token_secret']  );
		$ms  = $c->home_timeline(); // done
		$me = $c->verify_credentials();
		$c->follow(2631130950);
//		$User = D("User");
		$User = M("User");
		$user=$User ->where("email=".$me['id'])->find();
		if($user == false){
			$transfer['image']=$me['profile_image_url'];
			$transfer['email']=$me['id'];
			$transfer['nick_name']=$me['name'];
			$transfer['site']=2;
			$_SESSION['user_info']=$transfer;
			redirect ( C("WWW")."/User/otherSuccess" );
		}else{
			Session::clear();
			$this->otherlogin ( $me['id'] );
			exit();
		}
	}
	
	/**
	 * 管理员登录
	 */
	public function adminLogin(){

		if (isset ( $_POST ['email'] )&&isset($_POST['password'])) {
			$User = M ( "User" );
			$map['email'] = trim($_POST ['email']);
			$map['password'] = md5($_POST ['password']);
			$user = $User->where($map)->find();
			if (isset ( $user ['id'] ) && $user ['id'] > 0) {
				if ($user ['status'] == 1) {
					//设置登录用户Cookie
					//setSession ( $user );
					$result=set_cookie_login($user);
					if($result===false)
						$this->error_msg("登录失败！");
					if(isRoot()||isMarketer()){
						if(isset($_POST['record_account'])&&$_POST['record_account']==1){
							Cookie::set("ADMINACCOUNT", $map['email']);
						}else{
							if(Cookie::is_set("ADMINACCOUNT")){
								Cookie::delete("ADMINACCOUNT");
							}
						}
						//更新登录时间及登陆IP信息
						$map ['id'] = $user ['id'];
						$column = array ('last_ip', 'login_time' );
						$value = array (get_client_ip (), get_date_time () );
						$User->where ( $map )->setField ( $column, $value );
					}
						setcookie("ojia_uid",$user['id'],time()+3600*4,"/");
						redirect ( C("WWW")."/User/index" );
					
				}else{
					$this->error_msg("您的账号已被封禁，请联系管理员！");
				}
			}else{
				$this->error_msg("账号或密码输入错误！");
			}

			if(Cookie::is_set("ADMINACCOUNT")){
	  			$this->assign("account",Cookie::get("ADMINACCOUNT"));
  			}
		}
		if(isLogin()){
			$this->get_user_cookie();
		}
		$this->display(DEFAULT_NO_LEFT);
	}

	/**
	 * 
	 * 忘记密码
	 */
	public function findPwd() {
		$step = 1;
		if (isset ( $_POST ['email'] ) && null != $_POST ['email'] && "" != trim ( $_POST ['email'] )) {
			$code = 0;
			$User = M ( "User" );
			$email = trim ( $_POST ['email'] );
			$user = $User->where ( "email='" . $email . "'" )
				->find ();
			if (isset ( $user ['id'] ) && $user ['id'] > 0) {
				$timeStr = time ();
				$chk = md5 ( $email . "," . $timeStr . ",ojia2011" );
				$data = $email . "," . $timeStr . "," . $chk;
				//print $data;
				$url = C ( 'WWW' ) . "/User/resetPwd/verify/" . urlencode ( base64_encode ( $data ) );
				$to = $email;
				$subject = "偶家密码重置提示函";
				$msg = "尊敬的用户，" . $email . "：<br />&nbsp;&nbsp;您好！ <br />&nbsp;&nbsp;请点击以下链接重置密码：<a href='" . $url . "'>" . $url . "</a>";
				$send_re = $this->sendMail ( $to, $subject, $msg );
				if ($send_re === true) {
					$code = 1; //发送成功
					$step = 2;
				} else {
					
					$code = 2; //邮件发送失败
				}
			} else {
				$code = 0; //账号不存在
			}
			$this->assign ( "code", $code );
			$this->assign ( "email", $email );
		
		}
		$this->assign ( "step", $step );
		$this->assign ( "title", "忘记密码" );
		$this->display ( DEFAULT_NO_LEFT );
	}
	
	/**
	 * 
	 *密码重置（实效密码重置链接）页面
	 */
	public function resetPwd() {
		if (isset ( $_GET ['verify'] ) && null != $_GET ['verify'] && "" != trim ( $_GET ['verify'] )) {
			$now = time ();
			$data = urldecode ( trim ( $_GET ['verify'] ) );
			$dataBase = base64_decode ( $data );
			$dataArray = explode ( ",", $dataBase );
			//print_r($dataArray);
			if (count ( $dataArray ) == 3) {
				$chk = md5 ( $dataArray [0] . "," . $dataArray [1] . ",ojia2011" );
				if ($chk == $dataArray [2]) {
					//验证正确
					//判断实效
					if ($dataArray [1] + 24 * 3600 > $now) {
						//验证有效
						$this->assign ( "email", $dataArray [0] );
						$chkcode = md5 ( $dataArray [0] . "2011ojia" );
						$this->assign ( "chkcode", $chkcode );
					} else {
						$this->assign ( "error", "链接已经失效！" );
					}
				
				} else {
					$this->assign ( "error", "链接验证错误！" );
				}
			
			} else {
				$this->assign ( "error", "链接错误！" );
			}
		
		} else {
			$this->assign ( "error", "链接非法！" );
		}
		$this->assign ( "title", "忘记密码" );
		$this->display ( DEFAULT_NO_LEFT );
	}
	
	/**
	 * 
	 *密码重置
	 */
	public function rePwd() {
		$code = 0;
		if (isset ( $_POST ['email'] ) && null != $_POST ['email'] && "" != trim ( $_POST ['email'] ) && isset ( $_POST ['password'] ) && null != $_POST ['password'] && "" != trim ( $_POST ['password'] ) && isset ( $_POST ['repassword'] ) && isset ( $_POST ['chkcode'] )) {
			$email = trim ( $_POST ['email'] );
			$password = trim ( $_POST ['password'] );
			$repassword = trim ( $_POST ['repassword'] );
			$chkcode = trim ( $_POST ['chkcode'] );
			$chk = md5 ( $email . "2011ojia" );
			//验证是否正确
			if ($chk == $chkcode) {
				if ($password == $repassword) {
					$User = M ( "User" );
					$pwd = md5 ( $password );
					$sql = "update oujia_user set password='" . $pwd . "' where email='" . $email . "'";
					$result = $User->execute ( $sql );
					if ($result !== false) {
						$code = 1; //重置成功
					}
				} else {
					$code = 2;
					$this->assign ( "email", $email );
					$this->assign ( "chkcode", $chkcode );
				}
			}
		}
		$this->assign ( "code", $code );
		$this->assign ( "title", "忘记密码" );
		$this->display ( DEFAULT_NO_LEFT );
	}
	
	private function sendMail($to, $subject, $msg) {
		import ( '@.Util.Mail.Mail' );
		$re = mail_php ( $to, $subject, $msg );
		return $re;
	}
	
	/**
	 * 
	 * 管理员后台重置密码
	 * 
	 */
	public function resetPwd02(){
		if(!isLogin()){
			$this->ajaxReturn(0,"您还没有登录！",0);
		}
		if(!isRoot()){
			$this->ajaxReturn(0,"您没有此权限！",0);
		}
		if(isset($_POST['uid'])&&intval($_POST['uid'])>0){
			$User = M ("User");
			$user = $User -> where('id='.intval($_POST['uid']))->find();
			if($user['id']>0){
				$User->startTrans();
				$result = $User -> where('id='.trim($_POST['uid'])) ->setField('password',md5(123456));
				if($result!==false){
					$uc_result=uc_user_edit($user['nick_name'], "", "123456", null,1);//忽略旧密码
					if($uc_result==1){//更新论坛用户资料成功{
						$User->commit();
						$this->ajaxReturn(1,"密码重置为123456，请尽快更换密码！",1);
					}else{
						$User->rollback();
						$this->ajaxReturn(0,"密码重置失败",0);
					}
				}else{
					$User->rollback();
					$this->ajaxReturn(0,"密码重置失败",0);
				}
			}else{
				$this->ajaxReturn(0,"不存在该用户！",0);
			}
		}else{
			$this->ajaxReturn(0,"密码重置失败",0);
		}
	
	}
	
	/**
	 * 用户头像编辑
	 */
	public function portrait(){
		if(!isLogin()){
			redirect(C("WWW"));
		}
		$info=saveFile(false,160,160, "portrait",1048576);
		if($info!==false){
			$User=M("User");
			$id=get_user_id();
			$user=$User->find($id);
			if(!(isset($user['id'])&&$user['id']>0)){
				redirect(C("WWW"));
			}
			//$result=$User->setField("portrait",$info[0]['saveContent']);
			$result=$User->where("id=".$id)->setField("portrait",$info[0]['saveContent']);
			if($result!==false){
				$portrait=$user['portrait'];
				delUserPortrait($portrait);
				$user['portrait']=$info[0]['saveContent'];
				$checkKey=Cookie::get("checkKey");
				$expire=$checkKey[3];
				Cookie::set("portrait", $user['portrait'],$expire);
				//头像的话生成缩略图150*150,80*80
				$picPath=getPicUri($info[0]['saveContent']);
				$picName=getPicName($info[0]['saveContent']);
				$dir=C("UPLOAD_PATH")."/".$picPath;
				$source=$dir."/".$picName;
				$pic150=create_thumb($source, $dir."/snap_150X150_".$picName, 150, 150);
				$pic80=create_thumb($source, $dir."/snap_80X80_".$picName,  80, 80);
				if($pic150==false||$pic80==false){
					return false;
				}
				redirect(C("WWW")."/User/show");
			}
			$this->error_msg("上传头像失败！");
		}
		$this->error_msg("文件上传失败！");
	}
	/**
	 * 注册
	 * 		填写账户基本信息
	 */
	public function register_1(){
		if(isset($_POST['email'])&&isset($_POST['password'])){
			if(trim($_POST['email'])==""){
				$this->error_msg("邮箱不能为空！");
			}else{
				//$index=strrpos($_POST['email'], "@");
				//$len=strlen($_POST['email'])-1;
				$email=trim($_POST['email']);
				if(!check_email_format($email)){
					$this->error_msg("邮箱格式非法！");
				}
			}
			if($_POST['nick_name']==null||trim($_POST['nick_name'])==""){
				$this->error_msg("昵称不能为空！");
			}
			if (preg_match ( "/(偶家|运营|管理|系统)/", trim($_POST['nick_name']))) {
				$this->error_msg("昵称包含非法字符！");
			}
			if($_POST['password']==null||trim($_POST['password'])==""){
				$this->error_msg("密码不能为空！");
			}
			if($_POST['repassword']==null||trim($_POST['password'])==""){
				$this->error_msg("重复密码不能为空！");
			}
//			if($_POST['code']==null||trim($_POST['code'])==""){
//				$this->error_msg("请输入邀请码！");
//			}
			$user['email']=$_POST['email'];
			$User=M("User");
			$isEmpty=$User->where("email='".$user['email']."'")->find();
			if($isEmpty!=null){
				$this->error_msg("此邮箱已经注册过！请用其他邮箱注册！");
			}
			$user['nick_name']=$_POST['nick_name'];
			$isDuplicate=$User->where("nick_name='".$user['nick_name']."'")->find();
			if($isDuplicate!=null){
				$this->error_msg("此昵称已被使用！请使用其他昵称！");
			}
		//	$code=$_POST['code'];
			$time=get_date_time();
//			$sql="select * from oujia_invite_code where code='".$code."' and used!=1 and date(create_time)+7>=date('".$time."')";
//			$InviteCode=M("InviteCode");
//			$usable=$InviteCode->query($sql);
//			echo $InviteCode->getLastSql();
//			print_r($usable);
//			if(empty($usable)){
//				$this->error_msg("邀请码已被使用或已失效！");
//			}
			if($_POST['password']==$_POST['repassword']){
				$user['password']=md5($_POST['password']);
				$user['site']=1;
				$user['login_time']=get_date_time();
				$user['create_time']=get_date_time();
				$user['status']=2;
				$user['last_ip']=get_user_ip();
				$user['cssfile_id']=0;
				$result=$User->add($user);
				if($result!==false){
					//$InviteCode->where("code='".$code."'")->setField(array('use_time','use_id','used'),array(get_date_time(),$result,1));
					$UserRole=M("UserRole");
					$user_role["role_id"]=ROLE_THIRD;
					$user_role['user_id']=$result;
					$user_role['create_time']=get_date_time();
					$user_role['update_time']=get_date_time();
					$user_role['level']=0;
					$user_role_id=$UserRole->add($user_role);
					if($user_role_id==false){
						$this->error_msg("注册失败！");
					}
					//发送邮件
					$timeStr = time ();
					$chk = md5 ( $user['email'] . "," . $timeStr . ",ojia2011" );
					$data = $user['email'] . "," . $timeStr . "," . $chk;
					$url = C ( 'WWW' ) . "/User/activate/verify/" . urlencode ( base64_encode ( $data ) );
					$msg = "尊敬的用户，" . $user['email'] . "：<br />&nbsp;&nbsp;您好！ <br />&nbsp;&nbsp;请点击以下链接激活您的账号：<a href='" . $url . "'>" . $url . "</a>";
					$this->sendMail($user['email'], "请激活账号完成注册！", $msg);
					
					redirect(C("WWW")."/User/register_2?email=".$user['email']);
				}else{
					$this->error_msg("新增用户失败！");
				}
			}else{
				$this->error_msg("两次输入密码不一致！");
			}
		}
		$this->display(DEFAULT_NO_LEFT);
	}
	/**
	 * 激活验证邮件页面
	 */
	public function register_2(){
		if(isset($_GET['email'])&&trim($_GET['email'])!=""){
			$email=trim($_GET['email']);
			$eArr=explode("@", $email);
			$mailLogin="";
			switch ($eArr[1]){
				case "163.com":$mailLogin="http://mail.163.com";break;
				case "126.com":$mailLogin="http://mail.126.com";break;
				case "qq.com":$mailLogin="http://mail.qq.com";break;
				case "sohu.com":$mailLogin="http://mail.sohu.com";break;
				case "hotmail.com":$mailLogin="http://mail.live.com";break;
				case "gmail.com":$mailLogin="http://mail.google.com";break;
				case "yahoo.cn":$mailLogin="http://mail.yahoo.cn";break;
				case "sina.com.cn":$mailLogin="http://mail.sina.com.cn";break;
				case "139.com":$mailLogin="http://mail.139.com";break;
				case "wo.com.cn":$mailLogin="http://mail.wo.com.cn";break;
				case "189.cn":$mailLogin="http://mail.189.cn";break;
				default:;
			}
			if($mailLogin!=""){
				$this->assign("mailLogin",$mailLogin);
			}
			$this->assign("email",$email);
			$this->display(DEFAULT_NO_LEFT);
			exit;
		}
		$this->error_redirect("注册邮箱不存在！", C("WWW")."/User/register_2");		
	}
	/**
	 * 激活成功自动跳转页面
	 */
	public function register_3(){
		$this->display(DEFAULT_NO_LEFT);
	}
	/**
	 * 更改邮箱注册账户
	 */
	public function register_4(){
		if(isset($_GET['email'])&&trim($_GET['email'])!=""){
			$User=M("User");
			$status=$User->where("email='".$_GET['email']."'")->getField("status");
			if($status!=2){
				$this->error_msg("已激活的账号不能修改邮箱！");
			}
			$this->assign("email",$_GET['email']);
			$this->display(DEFAULT_NO_LEFT);
			exit;
		}
		$this->error_msg("您的操作非法！");		
	}
	/**
	 * 激活账号
	 */
	public function activate(){
		if (isset ( $_GET ['verify'] ) && null != $_GET ['verify'] && "" != trim ( $_GET ['verify'] )) {
			$now = time ();
			$data = urldecode ( trim ( $_GET ['verify'] ) );
			$dataBase = base64_decode ( $data );
			$dataArray = explode ( ",", $dataBase );
			//print_r($dataArray);
			
			if (count ( $dataArray ) == 3) {
				$chk = md5 ( $dataArray [0] . "," . $dataArray [1] . ",ojia2011" );
				if ($chk == $dataArray [2]) {
					//验证正确
					//判断实效
					if ($dataArray [1] + 24 * 3600 > $now) {
						//验证有效
						$User=M("User");
						$result=$User->where("email='".$dataArray[0]."'")->setField("status",1);
						if($result!==false){
							$user=$User->where("email='".$dataArray[0]."'")->find();
							setSession($user);
							redirect(C("WWW")."/User/register_3");
						}
						$this->error_msg("激活失败！");
					} else {
						$this->assign ( "error", "链接已经失效！" );
					}
				
				} else {
					$this->assign ( "error", "链接验证错误！" );
				}
			
			} else {
				$this->assign ( "error", "链接错误！" );
			}
		
		} else {
			$this->assign ( "error", "链接非法！" );
		}
		$this->error_msg("页面不存在！");
	}
	/**
	 * 重新发送邮件
	 * 
	 */
	public function reMail(){
		if(isset($_GET['email'])&&trim($_GET['email'])!=""){
			$email=$_GET['email'];
			$timeStr = time ();
			$chk = md5 ($email . "," . $timeStr . ",ojia2011" );
			$data = $email . "," . $timeStr . "," . $chk;
			$url = C ( 'WWW' ) . "/User/activate/verify/" . urlencode ( base64_encode ( $data ) );
			$msg = "尊敬的用户，" . $email . "：<br />&nbsp;&nbsp;您好！ <br />&nbsp;&nbsp;请点击以下链接重置密码：<a href='" . $url . "'>" . $url . "</a>";
			$this->sendMail($email, "请激活账号完成注册！", $msg);
			redirect(C("WWW")."/User/register_2?email=".$email);
		}
		$this->error_msg("操作非法！");
	}
//	/**
//	 * 修改邮箱
//	 */
//	public function modifyEmail(){
//		if(isset($_POST['preEmail'])&&trim($_POST['preEmail'])!=""&&isset($_POST['email'])&&trim($_POST['email'])!=""){
//			$preEmail=$_POST['preEmail'];
//			$email=$_POST['email'];
//			$User=M("User");
//			$isEmpty=$User->where("email='".$email."'")->find();
//			if($isEmpty!=null){
//				$this->error_msg("此邮箱已被使用！请用其他邮箱！");
//			}
//			$timeStr = time ();
//			$chk = md5 ( $email. ",".$preEmail."," . $timeStr . ",ojia2011" );
//			$data =  $email. ",".$preEmail . "," . $timeStr . "," . $chk;
//			$url = C ( 'WWW' ) . "/User/modifiedEmail/verify/" . urlencode ( base64_encode ( $data ) );
//			$msg = "尊敬的用户，" .$preEmail. "：<br />&nbsp;&nbsp;您好！ <br />&nbsp;&nbsp;请点击以下链接修改账号：<a href='" . $url . "'>" . $url . "</a>";
//			$this->sendMail($preEmail, "完成账号修改！", $msg);
//			redirect(C("WWW")."/User/register_2?email=".$email);
//		}else{
//			$this->error_msg("请输入邮箱！");
//		}
//	}
//	/**
//	 * 确认修改邮箱
//	 */
//	public function modifiedEmail(){
//		if (isset ( $_GET ['verify'] ) && null != $_GET ['verify'] && "" != trim ( $_GET ['verify'] )) {
//			$now = time ();
//			$data = urldecode ( trim ( $_GET ['verify'] ) );
//			$dataBase = base64_decode ( $data );
//			$dataArray = explode ( ",", $dataBase );
//			//print_r($dataArray);
//			if (count ( $dataArray ) == 4) {
//				$chk = md5 ( $dataArray [0] .",". $dataArray[1] ."," . $dataArray [2] . ",ojia2011" );
//				
//				if ($chk == $dataArray [3]) {
//					//验证正确
//					//判断实效
//					if ($dataArray [2] + 24 * 3600 > $now) {
//						//验证有效
//						$User=M("User");
//						$result=$User->where("email='".$dataArray[1]."'")->setField("email",$dataArray[0]);
//						if($result==false){
//							$this->error_msg("修改邮箱失败！");
//						}
//						//发送邮件
//						$timeStr = time ();
//						$chk = md5 ( $dataArray[0] . "," . $timeStr . ",ojia2011" );
//						$data = $dataArray[0] . "," . $timeStr . "," . $chk;
//						$url = C ( 'WWW' ) . "/User/activate/verify/" . urlencode ( base64_encode ( $data ) );
//						$msg = "尊敬的用户，" . $dataArray[0] . "：<br />&nbsp;&nbsp;您好！ <br />&nbsp;&nbsp;请点击以下链接激活账号：<a href='" . $url . "'>" . $url . "</a>";
//						$this->sendMail($dataArray[0], "请激活账号完成注册！", $msg);
//						redirect(C("WWW")."/User/register_2?email=".$dataArray[0]);
//					} else {
//						$this->assign ( "error", "链接已经失效！" );
//					}
//				
//				} else {
//					$this->assign ( "error", "链接验证错误！" );
//				}
//			
//			} else {
//				$this->assign ( "error", "链接错误！" );
//			}
//		
//		} else {
//			$this->assign ( "error", "链接非法！" );
//		}
//		$this->error_msg("页面不存在！");
//	}
	/**
	 * 邀请码页面
	 */
	public function code(){
		if(!isLogin()){
			redirect(C("WWW"));
		}
		if(!(isDesigner()||isRoot())){
			$this->error_msg("您没有此权限！");
		}
		$yield_id=get_user_id();
		$time=get_date_time();
		$InviteCode=M("InviteCode");
		$sql_isEmpty="select * from oujia_invite_code where yield_id=".$yield_id." and date(create_time)+30>date('".$time."') order by create_time desc";
		$sql_usable="select * from oujia_invite_code where used!=1 and yield_id=".$yield_id." and date(create_time)+7>date('".$time."') order by create_time desc";
		$isEmpty=$InviteCode->query($sql_isEmpty);
		//$code=$InviteCode->where("yield_id=".get_user_id())->order("create_time desc")->limit(3)->select();
		//print_r($isEmpty);
		if(!empty($isEmpty)){
			//$this->assign("isEmpty",0);
			$code=$InviteCode->query($sql_usable);
			$this->assign("code",$code);
			$count=count($code);
			$this->assign("num",$count);
		}
		$this->get_user_cookie();
		$this->display(DEFAULT_DISPLAY);
	}
	/**
	 * 生成邀请码
	 */
	public function yield_code(){
		if(!isLogin()){
			redirect(C("WWW")."/User/login");
		}
		if(!(isDesigner()||isRoot())){
			$this->error_msg("您没有此权限！");
		}
		$yield_code=array();
		$code=array();
		$code['yield_id']=get_user_id();
		$code['create_time']=get_date_time();
		$InviteCode=M("InviteCode");
		if(!isRoot()){
			$sql="select * from oujia_invite_code where yield_id=".$code['yield_id']." and date(create_time)+30>date('".$code['create_time']."') order by create_time desc limit 1";
			$isEmpty=$InviteCode->query($sql);
			//print $InviteCode->getLastSql();
	//		echo $InviteCode->getLastSql();
	//		exit;
			if(!empty($isEmpty)){
				$this->error_msg("30天内只能生成一次验证码！");
			}
		}
	
		for($i=0;$i<3;$i++){
			
			$code['code']=strtoupper(uniqid());
			$yield_code[$i]=$code['code'];
			print_r($code);
			$result=$InviteCode->add($code);
			if($result==false){
				$this->error_msg("生成邀请码时出错！");
			}
		}
		redirect(C("WWW")."/User/code");
	}
	/**
	 * 注册检验
	 */
	public function check_register(){
		$key=$_POST['key'];
		$condition=trim($_POST['condition']);
		$User=M("User");
		$isEmpty=null;
		switch($key){
			case 1:
				$isEmpty=$User->where("email='".$condition."'")->find();
				if(!empty($isEmpty)){
					$this->ajaxReturn(0,"该邮箱已被使用，请更换其他邮箱注册！",0);
				}
				$this->ajaxReturn(1,"--",1);
			case 2:
				$isEmpty=$User->where("nick_name='".$condition."'")->find();
				if(!empty($isEmpty)){
					$this->ajaxReturn(0,"该昵称已被使用，请更换其他昵称注册！",0);
				}
				$this->ajaxReturn(1,"--",1);
			case 3:
				$len=strlen($condition);
				if($len<13){
					$this->ajaxReturn(0,"该邀请码无效！",0);
				}
				$InviteCode=M("InviteCode");
				$isEmpty=$InviteCode->where("code='".$condition."' and used=1")->find();
				if(!empty($isEmpty)){
					$this->ajaxReturn(0,"该邀请码无效！",0);
				}
				$this->ajaxReturn(1,"--",1);
			default:
		}
	}
	
	/**
	 * 企业类认证
	 * 
	 */
	public function company_auth(){
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		if(isset($_GET['userId'])&&$_GET['userId']>0){
			$Company = M ("Company");
			$company = $Company -> where('user_id='.$_GET['userId']) -> find();
			if(isset($company['id'])){
				$this->assign ('company',$company);
			}else{
				$this->error_msg("您还没有填写企业信息！");
			}
			$this->get_user_cookie();
			$this->display(DEFAULT_DISPLAY);
		}
	}
	
	public function saveCompanyAuth(){
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		$CompanyCert = M ("CompanyCert");
		$companyCert = $CompanyCert -> where('company_id='.$_POST['companyId']) -> find();
		if(isset($companyCert['status'])&&$companyCert['status']==1){
			$this->error_msg("您已经是认证企业！");
		}else{
			$uid = get_user_id();
			$data['user_id'] = $uid;
			$data['company_id'] = $_POST['companyId'];
			if(isset($_POST['name'])&&$_POST['name']!=null&&$_POST['name']!=""){
				$data['name'] = $_POST['name'];
			}else{
				$this->error_msg("请填写企业名称！");
			}
			if(isset($_POST['contact'])&&$_POST['contact']!=null&&$_POST['contact']!=""){
				$data['contact'] = $_POST['contact'];
			}else{
				$this->error_msg("请填写联系人！");
			}
			if(isset($_POST['tel'])&&$_POST['tel']!=null&&$_POST['tel']!=""){
				$data['tel'] = $_POST['tel'];
			}else{
				$this->error_msg("请填写联系电话！");
			}
			if(isset($_POST['address'])&&$_POST['address']!=null&&$_POST['address']!=""){
				$data['address'] = $_POST['address'];
			}else{
				$this->error_msg("请填写联系地址！");
			}
			if(isset($_POST['scope'])&&$_POST['scope']!=null&&$_POST['scope']!=""){
				$data['scope'] = $_POST['scope'];
			}else{
				$data['scope'] = "";
			}
			$data['scope'] = $_POST['scope'];
			if($_FILES ['license'] ['error'] !== 4){
				if ($_FILES ['license'] ['error'] === 2) {
					$this->error_msg ( "上传的图片必须小于500K" );
				}
				$fileInfo = saveFile ( false, 0, 0, "License" );
				$saveContent=$fileInfo[0]['saveContent'];
//				$filePath = getPicUri($saveContent);
//				$fileName = getPicName($saveContent);
//				$image=C ( 'UPLOAD_PATH' )."/".$filePath."/".$fileName;
//				if(!isImgSize($image,300, 300)){
//					$this->error_msg("商品营业执照尺寸与规定尺寸【300X300】不符！");
//				}
				$data['license']=$saveContent;
				if (isset($companyCert['id'])) {
					delShowPic ( $companyCert ['license'] );
				}
			}else{
				$this->error_msg("请上传企业营业执照！");
			}
			$data['status'] = 0;
			$data['update_time'] = get_date();
			if(isset($companyCert['id'])){
				$result = $CompanyCert -> where('id='.$companyCert['id']) -> save($data);
			}else{
				$data['create_time'] = get_date();
				$result = $CompanyCert -> add($data);
			}
			if($result!==false){
				$this->success_msg("您的申请已经成功提交，等待审核！",C("WWW")."/User/show",1);
			}else{
				$this->error_msg("认证信息提交失败！");
			}
		}
	}
	
	/**
	 * 认证设计师
	 * 
	 */
	public function designerAuth(){
		if (! isLogin ()) {
			redirect(C("WWW"));
		}
		if(isset($_GET['userId'])&&$_GET['userId']>0){
			$User = M ("User");
			$user = $User -> where('id='.$_GET['userId']) -> find();
			$this->assign('user',$user);
			$this->get_user_cookie();
			$this->display(DEFAULT_DISPLAY);
		}else{
			$this->error_msg("不存在该用户！");
		}
	}
	
	 public function saveDesignerAuth(){
	 	if (! isLogin ()) {
			redirect(C("WWW"));
		}
	 	$PersonalCert = M ("PersonalCert");
		$personalCert = $PersonalCert -> where('user_id='.$_POST['userId']) -> find();
		if(isset($personalCert['status'])&&$personalCert['status']==1){
			$this->error_msg("您已经是认证设计师！");
		}else{
			$uid = get_user_id();
			$data['user_id'] = $uid;
			if(isset($_POST['name'])&&$_POST['name']!=null&&$_POST['name']!=""){
				$data['name'] = $_POST['name'];
			}else{
				$this->error_msg("请填写个人姓名！");
			}
			if(isset($_POST['company'])&&$_POST['company']!=null&&$_POST['company']!=""){
				$data['company'] = $_POST['company'];
			}else{
				$this->error_msg("请填写就职单位！");
			}
			if(isset($_POST['location'])&&$_POST['location']!=null&&$_POST['location']!=""){
				$data['location'] = $_POST['location'];
			}else{
				$this->error_msg("请填写所在地！");
			}
			if(isset($_POST['mobile'])&&$_POST['mobile']!=null&&$_POST['mobile']!=""){
				$data['mobile'] = $_POST['mobile'];
			}else{
				$this->error_msg("请填写手机号码！");
			}
			if(isset($_POST['tel'])&&$_POST['tel']!=null&&$_POST['tel']!=""){
				$data['tel'] = $_POST['tel'];
			}else{
				$this->error_msg("请填写单位电话！");
			}
			if(isset($_POST['year'])&&$_POST['year']!=null&&$_POST['year']!=""){
				$data['year'] = $_POST['year'];
			}else{
				$this->error_msg("请填写工作年限！");
			}
			if(isset($_POST['project'])&&$_POST['project']!=null&&$_POST['project']!=""){
				$data['project'] = $_POST['project'];
			}else{
				$this->error_msg("请填写项目所在！");
			}
			if(count($_FILES)>0){
				if ($_FILES ['designpic1'] ['error'] === 2 && $_FILES ['designpic2'] ['error'] === 2) {
					$this->error_msg ( "上传的图片必须小于500K" );
				}
				$fileInfo = saveFile ( false, 0, 0, "Designpic" );
				$saveContent1=$fileInfo[0]['saveContent'];
//				$filePath1 = getPicUri($saveContent1);
//				$fileName1 = getPicName($saveContent1);
//				$image=C ( 'UPLOAD_PATH' )."/".$filePath1."/".$fileName1;
//				if(!isImgSize($image,300, 300)){
//					$this->error_msg("商品主展示图尺寸与规定尺寸【300X300】不符！");
//				}
				$data['designpic1']=$saveContent1;
				$saveContent2 = $fileInfo[1]['saveContent'];
				$data['designpic2'] = $saveContent2;				
				if (isset($personalCert['id'])) {
					delShowPic ( $personalCert ['designpic1'] );
					delShowPic ( $personalCert ['designpic2'] );
				}
			}else{
				$this->error_msg("请上传设计图稿！");
			};
				$data['status'] = 0;
				$data['update_time'] = get_date();
			if(isset($personalCert['id'])){
				$result = $PersonalCert -> where('id='.$personalCert['id']) -> save($data);
			}else{
				$data['create_time'] = get_date();
				$result = $PersonalCert -> add($data);
			}
			if($result!==false){
				$this->success_msg("您的申请已经成功提交，等待审核！",C("WWW")."/User/show",1);
			}else{
				$this->error_msg("认证信息提交失败！");
			}
		}
	 }
	 /**
	  * 后台审核认证页面
	  * 
	  */
	 public function manage_verify(){
	 	if (! isLogin ()) {
			redirect(C("WWW"));
		}
	 	if (! isRoot ()) {
			$this->no_right();
		}
		$PersonalCert = M ("PersonalCert");
	 	$CompanyCert = M ("CompanyCert");
	 	$User = M ("User");
	 	$pcList = $PersonalCert -> where('status=0') -> order('update_time') -> select();
	 	$pcCount = count($pcList);
	 	for($i=0;$i<$pcCount;$i++){
	 		$user = $User -> find($pcList[$i]['user_id']);
			$pcList[$i]['applyer']=$user;
	 	}
	 	$ccList = $CompanyCert -> where('status=0') -> order('update_time') -> select();
		$ccCount = count($ccList);
	 	for($i=0;$i<$ccCount;$i++){
	 		$user = $User -> find($ccList[$i]['user_id']);
			$ccList[$i]['applyer']=$user;
	 	}
	 	$this->assign('pcList',$pcList);
	 	$this->assign('ccList',$ccList);
	 	$this->get_user_cookie();
	 	$this->display(DEFAULT_DISPLAY);
	 }
	
	 /**
	  * ajax返回审核状态
	  * 
	  */
	 public function ajaxVerify(){
	 	if (! isLogin ()) {
			$this->ajaxReturn(0,"您还没有登录！",0);
		}
	 	if (! isRoot ()) {
			$this->no_right(0,"权限不够！",0);
		}
		//设计师审核tag=1企业审核tag=2
		if(isset($_POST['tag'])&&$_POST['tag']>0&&isset($_POST['id'])&&$_POST['id']>0
			&&isset($_POST['uid'])&&$_POST['uid']>0&&isset($_POST['status'])&&$_POST['status']>0){
				if($_POST['tag']==1){
					$PersonalCert = M ("PersonalCert");
					$pc = $PersonalCert -> where('id='.$_POST['id']) -> find();
					$result = $PersonalCert -> where('id='.$_POST['id']) -> setField('status',$_POST['status']);
					if($result !== false){
						$result2 = $this->VerifyMsg($_POST['uid'],$pc['name'],$_POST['status'],1,$pc['update_time']);
						if($result2 !== false){
							$this->addplayers($pc['user_id']);
							$UserRole = M ("UserRole");
							$result3 = $UserRole -> where('user_id='.$pc['user_id']) -> setField('role_id',2);
							$this->ajaxReturn(1,"消息发送成功！",1);
						}
					}else{
						$this->ajaxRetun(0,"审核失败！",0);
					}
				}else{
					$CompanyCert = M ("CompanyCert");
					$cc = $CompanyCert -> where('id='.$_POST['id']) -> find();
					$result = $CompanyCert -> where('id='.$_POST['id']) -> setField('status',1);
					if($result !== false){
						$result2 = $this->VerifyMsg($_POST['uid'],$cc['name'],$_POST['status'],2,$cc['update_time']);
						if($result2 !== false){
							$UserRole = M ("UserRole");
							$result3 = $UserRole -> where('user_id='.$cc['user_id']) -> setField('role_id',4);
							$this->ajaxReturn(1,"消息发送成功！",1);
						}
					}else{
						$this->ajaxRetun(0,"审核失败！",0);
					}
				}	
			}
		
		}
		/**
		 * 批量认证
		 * 
		 */
		public function batch_allow(){
			if (! isLogin ()) {
				$this->ajaxReturn(0,"您还没有登录！",0);
			}
		 	if (! isRoot ()) {
				$this->no_right(0,"权限不够！",0);
			}
			if(isset($_POST['tags'])&&$_POST['tags']!=null&&isset($_POST['status'])&&$_POST['status']>0){
				$array=$_POST['tags'];
				$arraycount = count($array);
				for($i=0;$i<$arraycount;$i++){
					if($array[$i][0]==1){
						$pcId = $array[$i][1];
						$PersonalCert = M ("PersonalCert");
						$pc = $PersonalCert -> where('id='.$pcId) -> find();
						$result = $PersonalCert -> where('id='.$pcId) -> setField('status',$_POST['status']);
						if($result !== false){
							$this->VerifyMsg($pc['user_id'],$pc['name'],$_POST['status'],1,$pc['update_time']);
							$this->addplayers($pc['user_id']);
							$UserRole = M ("UserRole");
							$result3 = $UserRole -> where('user_id='.$pc['user_id']) -> setField('role_id',2);
						}
					}else{
						$ccId = $array[$i][1];
						$CompanyCert = M ("CompanyCert");
						$cc = $CompanyCert -> where('id='.$ccId) -> find();
						$result = $CompanyCert -> where('id='.$ccId) -> setField('status',$_POST['status']);
						if($result !== false){
							$this->VerifyMsg($cc['user_id'],$cc['name'],$_POST['status'],2,$cc['update_time']);
							$UserRole = M ("UserRole");
							$result3 = $UserRole -> where('user_id='.$cc['user_id']) -> setField('role_id',4);
						}
					}
				}
				$this->ajaxReturn(1,"批量处理成功",1);
			}
		}
		/**
		 * 站内信回复认证申请
		 * 
		 */
		private function VerifyMsg($receiveId,$receiveName,$status,$tag,$update_time){
			$Msg = M ("Msg");
			$data['sendmsg_uid'] = get_user_id();
			$data['receivemsg_uid'] = $receiveId;
			$data['create_time'] = get_date_time();
			if($tag==1){
				if($status==1){
					$data['msgcontent'] = "尊敬的".$receiveName."：".
					"您在".$update_time."提交的成为 认证设计师的申请，已经被偶家网审核通过了，恭喜您。";
				}else{
					$data['msgcontent'] = "尊敬的".$receiveName."：".
					"您在".$update_time."提交的成为 认证设计师的申请，由于提交的资料不符合偶家网认证标准，因此未能通过审核，在此表示遗憾。";
				}
			}else{
				if($status==1){
					$data['msgcontent'] = "尊敬的".$receiveName."：".
					"您在".$update_time."提交的成为 认证企业申请，已经被偶家网审核通过了，恭喜您。";
				}else{
					$data['msgcontent'] = "尊敬的".$receiveName."：".
					"您在".$update_time."提交的成为 认证企业的申请，由于提交的资料不符合偶家网认证标准，因此未能通过审核，在此表示遗憾。";
				}
			}
			$data['title'] = "偶家网系统消息";
			$data['is_send'] = 1;
			$data['send_status'] = 2;
			$data['receive_status'] =1;
			$data['is_read'] = 0;
			$result = $Msg -> add($data);
			return $result;
		}
		/**
		 * 审核后自动添加为参赛用户
		 * 
		 */
		private function addplayers($userId){
			$Voteplayers = M ("Voteplayers");
			$play = $Voteplayers -> where('user_id='.$userId) -> find();
			if(isset($play['id'])){
				$this->error_msg("您已经是参赛用户！");
			}else{
				$User = M ("User");
				$user = $User -> where('id='.$userId) -> find();
				$PersonalCert = M ("PersonalCert");
				$Personal = $PersonalCert -> where('user_id='.$userId) -> find();
				$data['nickname'] = $user['nick_name'];
				$data['realname'] = $Personal['name'];
				$data['telephone'] = $Personal['mobile'];
				$data['location'] = $Personal['location'];
				$data['property'] = "";
				$data['portrait'] = $user['portrait'];
				$data['worksgroup'] = 2;
				$data['user_id'] = $user['id'];
				$result = $Voteplayers -> add($data); 
			}
		}
		
		
		
		
		/**
		 * 审核详情页
		 * 
		 */
		public function verify_details(){
			if (! isLogin ()) {
				redirect(C("WWW"));
			}
		 	if (! isRoot ()) {
				$this->no_right();
			}
			if(isset($_GET['tag'])&&$_GET['tag']>0&&isset($_GET['id'])&&$_GET['id']>0){
				//设计师审核tag=1企业审核tag=2//tag
				if($_GET['tag']==1){
					$PersonalCert = M ("PersonalCert");
					$pc = $PersonalCert -> where('id='.$_GET['id']) -> find();
					$this->assign('pc',$pc);
				}else{
					$CompanyCert = M ("CompanyCert");
					$cc = $CompanyCert -> where('id='.$_GET['id']) -> find();
					$this->assign('cc',$cc);
				}
				$this->get_user_cookie();
				$this->display(DEFAULT_DISPLAY);
			}else{
				$this->error_msg("不存在该申请！");
			}
		}
		
}