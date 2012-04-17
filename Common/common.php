<?php
//Session状态定义
define ( 'SESSION_NEW', 1 );
define ( 'SESSION_UPDATE', 2 );

//默认输出模板定义
define ( 'DEFAULT_DISPLAY', "Layout:default" );//登陆后有列表
define	('DEFAULT_NO_LEFT', "Layout:index");//登陆后无左侧列表
define ( 'DEFAULT_ERROR', "Public:error" );

define("ROLE_ROOT", 1);
//默认分页显示每页的记录数
define ( 'PAGE_NUM_DEFAULT', 24 );

load ( "extend" );
/**
 * 获取登陆用户信息
 */
function getLoginUser() {
	if (isLogin ()) {
		$User = M ( "User" );
		$User->create ();
		$map ['id'] = $_SESSION ['UID'];
		
		$user = $User->where ( $map )
			->find ();
		if ($user != null)
			return $user;
	}
	return false;
}

/**
 * 
 * 设置登陆用户的SESSION
 * @param $name
 * @param $value
 */
function setSession($user) {
	//用户登录
		Session::set ( "nick", $user ['nickname'] );
		Session::set ( "UID", $user ['id'] );
		Session::set ( "RID", $user ['role'] );
		

}

/**
 * 
 * 判断用户是否登录
 */
function isLogin() {
	if (isset ( $_SESSION ['nick'] )) {
		
		return true;
	}
	
	return false;
}

function getUserRole() {
	if (isLogin ()) {
		//TODO 查询用户角色
	}
}

function isRoot() {
	if (isLogin ()) {
		if (intval ( $_SESSION ['RID'] ) === ROLE_ROOT)
			return true;
		return false;
	}
	return false;
}

function get_user_id() {
	if (isLogin ())
		return $_SESSION ['UID'];
	return false;
}
function get_user_role(){
	if (isLogin ())
		return $_SESSION ['RID'];
	return false;
}
/**
 * 默认获取当前时间
 * 格式为：1970-01-01 11:30:45
 * $differ 单位秒，距离现在的时间，
 * 		        负数表示多少秒之前的时间
 * 		        正数表示多少秒之后的时间
 */
function get_date_time($differ=0) {
	//import ( "ORG.Util.Date" );
	//$date = new Date ();
	$format = "%Y-%m-%d %H:%M:%S";
	$date=time()+$differ;
	return strftime($format, $date);
	//return $date->format ( $format = "%Y-%m-%d %H:%M:%S" );
}
/**
 * 获取当前日期
 * 
 */
function get_date() {
	import ( "ORG.Util.Date" );
	$date = new Date ();
	return $date->format ( $format = "%Y-%m-%d" );
}

/**
 * 获取用户IP
 */
function get_user_ip() {
	$ip = get_client_ip ();
	return $ip;
}

/**
 * 
 * 验证邮箱格式是否正确
 */
function check_email_format($email){
	if($email==null||trim($email)==""){
		return false;
	}
	$exp = "^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$";
	if(eregi($exp,$email)){
		return true;
	}		
	return false;
}

/**
 * 
 *在修改用户信息时，检查除了本身原先的nick外，
 *是否还存在相同的nick
 * @param $nick
 * @param $email
 */
function check_unique_nick($nick, $email) {
	$User = M ( "User" );
	$User->create ();
	
	$sql = "nick_name='" . $nick . "' and email !='" . $email . "'";
	$count = $User->where ( $sql )
		->count ();
	//print $User->getLastSql();
	if ($count > 0) {
		//print "count==".$count;
		return false;
	}
	return true;
}

/**
 * 
 * 检查用户旧密码是否正确
 * @param $password
 * @param $id
 */
function check_old_password($password, $id) {
	$User = M ( "User" );
	$User->create ();
	
	$map ['password'] = md5 ( $password );
	$map ['id'] = $id;
	$count = $User->where ( $map )
		->count ();
	//print $User->getLastSql();
	if ($count === 1) {
		return true;
	}
	return false;
}

/**
 * 
 * update的时候判断除自身外是否有相同的空间名称
 * @param $id
 * @param $name
 */
function check_room_unique_name($name, $id) {
	//print $id."---".$name;
	$Room = M ( "Room" );
	$sql = "id!=" . $id . " and name='" . $name . "'";
	$count = $Room->where ( $sql )
		->count ();
	if ($count > 0) {
		return false;
	}
	return true;
}
/**
 * 
 * 文件上传
 * @param $thum  是否生成缩略图
 * @param $thumMaxHeight  缩略图的最大高度
 * @param $thumbMaxWidth	缩略图的最大宽度
 * @param $domain	指定上传文件的主目录
 */
function saveFile($thumb = false, $thumbMaxWidth = 160, $thumbMaxHeight = 160, $domain) {
	import ( "ORG.Net.UploadFile" );
	
	$upload = new UploadFile (); // 实例化上传类
	

	$upload->maxSize = C ( 'UPLOAD_SIZE' ); // 设置附件上传大小
	

	$upload->allowExts = array ('jpg', 'gif', 'png', 'jpeg', 'swf' ); // 设置附件上传类型
	$date = date ( "Y/m/d" );
	//print "date===" . $date;
	$upload->savePath = C ( 'UPLOAD_PATH' ) . '/' . $domain . '/' . $date . '/'; // 设置附件上传目录
	//print "savePath----" . $upload->savePath;
	muti_mkdir ( $upload->savePath );
	//设置是否生成缩略图及其最大宽、高
	if ($thumb === true) {
		$upload->thumb = $thumb;
		$upload->thumbMaxWidth = $thumbMaxWidth;
		$upload->thumbMaxHeight = $thumbMaxHeight;
		$upload->thumbPrefix = "snap_" . $thumbMaxWidth . "X" . $thumbMaxHeight . "_";
	}
	$upload->saveRule = "uniqid"; //文件名生成规则
	

	if (! $upload->upload ()) { // 上传错误提示错误信息
		exit ( $upload->getErrorMsg () . "  " );
		//$this->error($upload->getErrorMsg());
		return false;
	} else { // 上传成功获取上传文件信息
		

		$info = $upload->getUploadFileInfo ();
		//print "上传信息：" . $info [0] ['savepath'];
		$start = strlen ( C ( 'UPLOAD_PATH' ) ) + 1;
		for($i = 0; $i < count ( $info ); $i ++) {
			$str = $info [$i] ['savepath'];
			//print "path---" . $str;
			$picPath = msubstr ( $str, $start, (strlen ( $str ) - $start - 1) );
			$picSaveName = msubstr ( $info [$i] ['savename'], 0, 13 );
			//保存上传文件信息至DB
			$info [$i] ['saveContent'] = "folder=" . $picPath . ",uid=" . $picSaveName . ",ext=" . $info [$i] ['extension'] . ",swidth=" . $thumbMaxWidth . ",sheight=" . $thumbMaxHeight . ",name=" . $info [$i] ['name'] . ",size=" . $info [$i] ['size'];
		}
		//print "real====".$info[0]['realsavepath'];
		return $info;
	}
}

/**
 * 
 * 上传文件所在位置
 * @param $picMsg
 */
function getPicUri($picMsg) {
	if(null==$picMsg||""==trim($picMsg)){
		return "";
	}
	$array = explode ( ",", $picMsg );
	$folder = msubstr ( $array [0], 7, strlen ( $array [0] ) );
	return $folder;
}

/**
 * 
 * 上传文件保存后的名称
 * @param  $picMsg
 */
function getPicName($picMsg) {
	if(null==$picMsg||""==trim($picMsg)){
		return "";
	}
	$array = explode ( ",", $picMsg );
	$uid = msubstr ( $array [1], 4, strlen ( $array [1] ) );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
	return $uid . "." . $ext;
}

/**
 * 
 * 上传文件的缩略图名称
 * @param $picMsg
 */
function getSnapPicName($picMsg,$swidth=160,$sheight=160) {
	if(null==$picMsg||""==trim($picMsg)){
		return "";
	}
	$array = explode ( ",", $picMsg );
	$uid = msubstr ( $array [1], 4, strlen ( $array [1] ) );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
//	$swidth = msubstr ( $array [3], 7, strlen ( $array [3] ) );
//	$sheight = msubstr ( $array [4], 8, strlen ( $array [4] ) );
	return "snap_" . $swidth . "X" . $sheight . "_" . $uid . "." . $ext;
}
/**
 * 
 * 上传文件的后缀类型
 * @param $picMsg
 */
function getPicExt($picMsg) {
	if(null==$picMsg||""==trim($picMsg)){
		return "";
	}
	$array = explode ( ",", $picMsg );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
	return $ext;
}

/**
 * 
 * 上传文件及缩略图在本地的地址
 * @param $picMsg 文件信息
 * @param $swidth 缩略图宽 	0表示缩略图不存在
 * @param $sheight 缩略图高  	0表示缩略图不存在
 */
function getPicLocalPath($picMsg,$swidth=0,$sheight=0) {
	if(null==$picMsg||""==trim($picMsg)){
		return false;
	}
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$sname = getSnapPicName ( $picMsg,$swidth,$sheight );
	//$project = C ( 'PROJECT_HOME' ) . msubstr ( C ( 'UPLOAD_PATH' ), 1, strlen ( C ( 'UPLOAD_PATH' ) ) );
	$project=C('UPLOAD_PATH');
	$path = $project . "/" . $folder . "/" . $name;
	$all_path [0] = $path;
	if($swidth>0&&$sheight>0){
		$spath = $project . "/" . $folder . "/" . $sname;
		$all_path [1] = $spath;
	}
	//print_r($all_path);
	return $all_path;
}

function delGoodsBasicPics($picMsg){
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$project=C('UPLOAD_PATH');
	$all_path [0] = $project . "/" . $folder . "/" . $name;
	$all_path [1] =	$project . "/" . $folder . "/snap_200X200_" . $name;
	$all_path [2] =	$project . "/" . $folder . "/snap_140X140_" . $name;
	del_files($all_path);
}

/**
 * 
 * 删除效果图
 * @param $picMsg
 */
function delModelRoomPics($picMsg){
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$project=C('UPLOAD_PATH');
	$all_path [0] = $project . "/" . $folder . "/" . $name;
	$all_path [1] =	$project . "/" . $folder . "/snap_190X102_" . $name;
//	$all_path [2] =	$project . "/" . $folder . "/snap_484X260_" . $name;
//	$all_path [3] =	$project . "/" . $folder . "/snap_740X398_" . $name;
	del_files($all_path);
}
/**
 * 创建多级目录
 */
function muti_mkdir($dir) {
	if (! is_dir ( $dir )) {
		if (! muti_mkdir ( dirname ( $dir ) )) {
			return false;
		}
		if (! mkdir ( $dir, 0777 )) {
			return false;
		}
	}
	return true;
}

/**
 * 
 * 批量删除文件
 * @param $files
 */
function del_files($files) {
	if (! empty ( $files )) {
		//print count($files);
		for($i = 0; $i < count ( $files ); $i ++) {
			if (file_exists ( $files [$i] )) {
				//print $files[$i];//."===i=".$i;
				unlink ( $files [$i] );
			}
		}
	}
}
/**
 * 
 * 空间分类
 * @param $categoryId
 */
function getRoomCategory($categoryId) {
	$category = C ( 'ROOM_CATEGORY' );
	return $category [$categoryId];
}

function getSuitRoomCategory($suit){
	$suits="";
	if($suit!==null||trim($suit)!=""){
		$category = C ( 'ROOM_CATEGORY' );
		$suitArray=explode("_", $suit);
		for($i=0;$i<count($suitArray);$i++){
			$suits.=$category[$suitArray[$i]];
		}
	}
	return $suits;
}
/**
 * 
 * 空间模块
 * @param $moduleId
 */
function getRoomModule($moduleId) {
	$module = C ( 'ROOM_MODULE' );
	return $module [$moduleId];
}

/**
 * 
 * 一张原图+最多一张阴影图的情况
 * @param $thumb
 * @param $thumbMaxWidth
 * @param $thumbMaxHeight
 * @param $domain
 * @param $shadow 是否有阴影图有的话传入saveContent
 */
function saveGoodsPic($thumb = false, $thumbMaxWidth = 160, $thumbMaxHeight = 160, $domain, $shadow = false) {
	import ( "ORG.Net.UploadFile" );
	$upload = new UploadFile (); // 实例化上传类
	$upload->maxSize = C ( 'UPLOAD_SIZE' ); // 设置附件上传大小
	$upload->allowExts = array ('jpg', 'gif', 'png', 'jpeg' ); // 设置附件上传类型
	$date = date ( "Y/m/d" );
	//print "date===" . $date;
	$upload->savePath = C ( 'UPLOAD_PATH' ) . '/' . $domain . '/' . $date . '/'; // 设置附件上传目录
	//print "savePath----" . $upload->savePath;
	muti_mkdir ( $upload->savePath );
	//设置是否生成缩略图及其最大宽、高
	if ($thumb === true) {
		$upload->thumb = $thumb;
		$upload->thumbMaxWidth = $thumbMaxWidth;
		$upload->thumbMaxHeight = $thumbMaxHeight;
		$upload->thumbPrefix = "snap_" . $thumbMaxWidth . "X" . $thumbMaxHeight . "_";
	}
	$upload->saveRule = "uniqid"; //文件名生成规则
	

	if (! $upload->upload ()) { // 上传错误提示错误信息
		exit ( $upload->getErrorMsg () );
		//$this->error($upload->getErrorMsg());
		return false;
	} else { // 上传成功获取上传文件信息
		$info = $upload->getUploadFileInfo ();
		//print "上传信息：" . $info [0] ['savepath'];
		$start = strlen ( C ( 'UPLOAD_PATH' ) ) + 1;
		for($i = 0; $i < count ( $info ); $i ++) {
			$str = $info [$i] ['savepath'];
			//print "path---" . $str;
			$picPath = msubstr ( $str, $start, (strlen ( $str ) - $start - 1) );
			$picSaveName = msubstr ( $info [$i] ['savename'], 0, 13 );
			//保存上传文件信息至DB
			$info [$i] ['saveContent'] = "folder=" . $picPath . ",uid=" . $picSaveName . ",ext=" . $info [$i] ['extension'] . ",swidth=" . $thumbMaxWidth . ",sheight=" . $thumbMaxHeight . ",name=" . $info [$i] ['name'] . ",size=" . $info [$i] ['size'];
		}
		$oldname = $info [1] ['savepath'] . $info [1] ['savename'];
		$newname = $info [0] ['savepath'] . "hot_" . $info [0] ['savename'];
		rename ( $oldname, $newname );
		if ($thumb) {
			$oldname = $info [1] ['savepath'] . "snap_" . $thumbMaxWidth . "X" . $thumbMaxHeight . "_" . $info [1] ['savename'];
			//这里可能会遇到原图跟阴影图的后缀名不一致的问题
			$newname = $info [0] ['savepath'] . "snap_" . $thumbMaxWidth . "X" . $thumbMaxHeight . "_hot_" . $info [0] ['savename'];
			rename ( $oldname, $newname );
		}
		if ($shadow) {
			//存在阴影图是时，重命名阴影图
			//$old_pic_items = split('[,=]', $info[1]['saveContent']);
			$oldname = $info [2] ['savepath'] . $info [2] ['savename'];
			//这里可能会遇到原图跟阴影图的后缀名不一致的问题
			$newname = $info [0] ['savepath'] . "shadow_" . $info [0] ['savename'];
			rename ( $oldname, $newname );
			if ($thumb) {
				$oldname = $info [2] ['savepath'] . "snap_" . $thumbMaxWidth . "X" . $thumbMaxHeight . "_" . $info [2] ['savename'];
				//这里可能会遇到原图跟阴影图的后缀名不一致的问题
				$newname = $info [0] ['savepath'] . "snap_" . $thumbMaxWidth . "X" . $thumbMaxHeight . "_shadow_" . $info [0] ['savename'];
				rename ( $oldname, $newname );
			}
		}
		//print_r($info);
		return $info;
	}

}
/**
 * 
 * 获取商品阴影图的名称
 * @param  $picMsg
 */
function getShadowPicName($picMsg) {
	$array = explode ( ",", $picMsg );
	$uid = msubstr ( $array [1], 4, strlen ( $array [1] ) );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
	return "shadow_" . $uid . "." . $ext;
}

/**
 * 
 * 获取商品阴影图的缩略图名称
 * @param $picMsg
 */
function getShadowSnapPicName($picMsg,$swidth=160,$sheight=160) {
	$array = explode ( ",", $picMsg );
	$uid = msubstr ( $array [1], 4, strlen ( $array [1] ) );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
//	$swidth = msubstr ( $array [3], 7, strlen ( $array [3] ) );
//	$sheight = msubstr ( $array [4], 8, strlen ( $array [4] ) );
	return "snap_" . $swidth . "X" . $sheight . "_shadow_" . $uid . "." . $ext;
}

/**
 * 
 * 上传阴影图及缩略图在本地的地址
 * @param $picMsg
 */
function getShadowPicLocalPath($picMsg,$swidth=160,$sheight=160) {
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getShadowPicName ( $picMsg );
	$sname = getShadowSnapPicName ( $picMsg,$swidth,$sheight );
	$project=C('UPLOAD_PATH');
	$path = $project . "/" . $folder . "/" . $name;
	$spath = $project . "/" . $folder . "/" . $sname;
	$all_path [0] = $path;
	$all_path [1] = $spath;
	//print_r($all_path);
	return $all_path;
}


/**
 * 
 * 获取商品热区图的名称
 * @param  $picMsg
 */
function getHotPicName($picMsg) {
	$array = explode ( ",", $picMsg );
	$uid = msubstr ( $array [1], 4, strlen ( $array [1] ) );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
	return "hot_" . $uid . "." . $ext;
}

/**
 * 
 * 获取商品热区的缩略图名称
 * @param $picMsg
 */
function getHotSnapPicName($picMsg,$swidth=160,$sheight=160) {
	$array = explode ( ",", $picMsg );
	$uid = msubstr ( $array [1], 4, strlen ( $array [1] ) );
	$ext = msubstr ( $array [2], 4, strlen ( $array [2] ) );
//	$swidth = msubstr ( $array [3], 7, strlen ( $array [3] ) );
//	$sheight = msubstr ( $array [4], 8, strlen ( $array [4] ) );
	return "snap_" . $swidth . "X" . $sheight . "_hot_" . $uid . "." . $ext;
}

/**
 * 
 * 上传热区图及缩略图在本地的地址
 * @param $picMsg
 */
function getHotPicLocalPath($picMsg,$swidth=160,$sheight=160) {
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getHotPicName ( $picMsg );
	$sname = getHotSnapPicName($picMsg,$swidth,$sheight);
	$project=C('UPLOAD_PATH');
	$path = $project . "/" . $folder . "/" . $name;
	$spath = $project . "/" . $folder . "/" . $sname;
	$all_path [0] = $path;
	$all_path [1] = $spath;
	//print_r($all_path);
	return $all_path;
}



/**
 * 
 * 模块对象关联的商品数+1
 * @param $moduleId
 */
function addModuleGoodsNum($moduleId){
	$Module=M("Module");
	$Module->setInc("goods_num","id=".$moduleId);
}

/**
 * 
 * 模块对象关联的商品数-1
 * @param $moduleId
 */
function subModuleGoodsNum($moduleId){
	$Module=M("Module");
	$Module->setDec("goods_num","id=".$moduleId);
}

/**
 * 
 * 指定模块对象下的子模块数+1
 * @param unknown_type $moduleId
 */
function addModuleNum($moduleId){
	$Module=M("Module");
	$Module->setInc("module_num","id=".$moduleId);
	//print $Module->getLastSql();
}

/**
 * 
 * 指定模块对象下的子模块数-1
 * @param unknown_type $moduleId
 */
function subModuleNum($moduleId){
	$Module=M("Module");
	$Module->setDec("module_num","id=".$moduleId);
}

/**
 * 
 * 保存图片流
 * 
 */
function saveimg($data,$filePath,$fileName){
	muti_mkdir($filePath);
	$saveName=$filePath.$fileName;
    if (! empty ( $data )) {  
    	//创建并写入数据流，然后保存文件  
        if (@$fp = fopen ( $saveName, 'w+' )) {  
        	fwrite ( $fp, $data );  
            fclose ( $fp );  
            return true;
        }else{
        	return false;
        }
    }
    return false;
}


/**
 * 
 * 生产缩略图
 * @param $image  原图名称（包括路径）
 * @param $thumbname  缩略图名称（包括路径）
 * @param $type		原图像的格式
 * @param $maxWidth 生成缩略图的宽度
 * @param $maxHeight	生成缩略图的高度
 */
function create_thumb($image,$thumbname,$type,$maxWidth,$maxHeight){
	if(!file_exists($image))
	    return false;
	import('ORG.Util.Image');
	$result=Image::thumb($image, $thumbname,$type,$maxWidth,$maxHeight);
	if($result!==false)
		return true;
	return false;				
}

/**
 * 
 * 判断图片的尺寸是否符合要求
 * @param $image 原图
 * @param $width 原图宽度
 * @param $height 原图高度
 */
function isImgSize($image,$width,$height){
	if($width>0&&$height>0){
		$imgSize=getimagesize($image);
		if($imgSize[0]==$width&&$imgSize[1]==$height){
			return true;
		}
		return false;
	}
	return false;
}
	

/**
 * 
 * 需完善
 * @param $time
 */
function toDate($time){
	if( empty($time)) {
		return '';
	}
	$dateArray=explode(" ",$time);
	return $dateArray[0];
	
}
/**
 * 
 * 生成随机密码
 * 
 */
function create_password($pw_length = 8){
    $randpwd = '';
    for ($i = 0; $i < $pw_length; $i++)
    {
        $randpwd .= chr(mt_rand(33, 126));
    }
    return $randpwd;
}	


	  
/**
 * 
 * 过滤数组的value值 正则匹配 非完全匹配
 * @param  $array
 * @param  $partten
 */
function fast_array_filter($array,$partten){  
    $partten = '/' . preg_quote($partten) .'/';  
    return  preg_grep ($partten, $array);  
}  
/**
 * 
 * 过滤数组的key值 正则匹配 非完全匹配
 * @param $array
 * @param $partten
 */  
function fast_array_keys_filter($array,$partten){  
     $partten = '/' . preg_quote($partten) .'/';  
     $keys = preg_grep($partten,array_keys($array));  
     //$retArray = array_combine ($keys , array_fill(0,count($keys),0) );  
     $retArray = array_flip($keys);
     return array_intersect_key($array,$retArray);  
}  


/**
 * 
 * array_filter 的callback函数，
 * 去除数组的空值
 * @param unknown_type $value
 */
function delEmpty($value){  
	if ($value==="") {  
    	return false;  
    }  
	return true;  
} 


/**
 * 删除单张缩略图
 */   
function delShowPic($picMsg){
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$project=C('UPLOAD_PATH');
	$path = $project . "/" . $folder . "/" . $name;
	//del_files($all_path);
	unlink($path);
}
/**
 * 删除材质图
 */
function delElementPics($picMsg){
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$project=C('UPLOAD_PATH');
	$all_path [0] = $project . "/" . $folder . "/" . $name;
	$all_path [1] =	$project . "/" . $folder . "/hot_" . $name;
	$all_path [2] =	$project . "/" . $folder . "/shadow_" . $name;
	del_files($all_path);
}

/**
 * 
 * 删除元素缩略图
 * @param $picMsg
 */
function delElementSnapPics($picMsg){
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$project=C('UPLOAD_PATH');
	$all_path [0] = $project . "/" . $folder . "/" . $name;
	$all_path [1] =	$project . "/" . $folder . "/snap_120X120_" . $name;
	$all_path [2] =	$project . "/" . $folder . "/snap_300X300_" . $name;
	del_files($all_path);
}
/**
 * 获取用户角色类型名
 */
function getRoleName($rid){
	switch($rid){
		case 1:
			return "超级管理员";
		case 2:
			return "个人设计师";
		case 3:
			return "装修公司";
		case 4:
			return "供应商";
		case 5:
			return "普通注册用户";
		case 6:
			return "偶家市场部";
		case 7:
			return "偶家空间设计部";
		default:
			return "";
	}
}
/**
 * 删除被取代的用户头像
 */
function delUserPortrait($picMsg){
	$all_path = array ();
	$folder = getPicUri ( $picMsg );
	$name = getPicName ( $picMsg );
	$project=C('UPLOAD_PATH');
	$all_path [0] = $project . "/" . $folder . "/" . $name;
	
	del_files($all_path);

}


/**
 * 
 * 返回标签名，各标签用空格隔开，此方法最多支持3个标签
 * @param $tags
 * 
 */
function getTagName($tags){
	$tagsStr=trim($tags);
	if($tagsStr==null||$tagsStr=="")
		return "";
	$arr=preg_split('/[_,]/', $tagsStr);
	switch (count($arr)){
		case 2:$tagsStr=$arr[1];break;
		case 4:$tagsStr=$arr[1]." ".$arr[3];break;
		case 6:$tagsStr=$arr[1]." ".$arr[3]." ".$arr[5];break;
		default: $tagsStr="";
	}
	return $tagsStr;
}
