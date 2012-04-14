<?php
session_start(); 
if (strlen($_SESSION['random_key'])==0){
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s'));
	$_SESSION['user_file_ext']= "";
}

$upload_dir = "upload_pic"; 				// 存放上传图片的目录
$upload_path = $upload_dir."/";				// 存放裁切后的图像
$large_image_prefix = "resize_"; 			// 上传后重命名未裁切图片的附加字符
$thumb_image_prefix = "thumbnail_";			// 上传后重命名已裁切图片的附加字符
$large_image_name = $large_image_prefix.$_SESSION['random_key'];     // 上传后未裁切的图片名的命名规则
$thumb_image_name = $thumb_image_prefix.$_SESSION['random_key'];     // 上传后已裁切的图片名的命名规则
$max_file = "3"; 							// 上传图片大小，这里默认3M
$max_width = "500";							// 上传图片的最大宽度
$thumb_width = "100";						// 裁切后小图的初始宽度
$thumb_height = "100";						// 裁切后小图的初始高度
// 以下数组存放允许上传的文件类型
$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");
$allowed_image_ext = array_unique($allowed_image_types); 
foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)." ";
}
//download by http://www.codefans.net
function resizeImage($image,$width,$height,$scale) {
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
	
	switch($imageType) {
		case "image/gif":
	  		imagegif($newImage,$image); 
			break;
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
	  		imagejpeg($newImage,$image,90); 
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage,$image);  
			break;
    }
	
	chmod($image, 0777);
	return $image;
}
//裁切主函数
function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale){
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
	switch($imageType) {
		case "image/gif":
	  		imagegif($newImage,$thumb_image_name); 
			break;
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
	  		imagejpeg($newImage,$thumb_image_name,90); 
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage,$thumb_image_name);  
			break;
    }
	chmod($thumb_image_name, 0777);
	return $thumb_image_name;
}

function getHeight($image) {
	$size = getimagesize($image);
	$height = $size[1];
	return $height;
}

function getWidth($image) {
	$size = getimagesize($image);
	$width = $size[0];
	return $width;
}

$large_image_location = $upload_path.$large_image_name.$_SESSION['user_file_ext'];
$thumb_image_location = $upload_path.$thumb_image_name.$_SESSION['user_file_ext'];


if(!is_dir($upload_dir)){
	mkdir($upload_dir, 0777);
	chmod($upload_dir, 0777);
}


if (file_exists($large_image_location)){
	if(file_exists($thumb_image_location)){
		$thumb_photo_exists = "<img src=\"".$upload_path.$thumb_image_name.$_SESSION['user_file_ext']."\" alt=\"裁切区域的图像\"/>";
	}else{
		$thumb_photo_exists = "";
	}
   	$large_photo_exists = "<img src=\"".$upload_path.$large_image_name.$_SESSION['user_file_ext']."\" alt=\"大图\"/>";
} else {
   	$large_photo_exists = "";
	$thumb_photo_exists = "";
}

if (isset($_POST["upload"])) { 
	$userfile_name = $_FILES['image']['name'];
	$userfile_tmp = $_FILES['image']['tmp_name'];
	$userfile_size = $_FILES['image']['size'];
	$userfile_type = $_FILES['image']['type'];
	$filename = basename($_FILES['image']['name']);
	$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	
	if((!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {
		
		foreach ($allowed_image_types as $mime_type => $ext) {
			if($file_ext==$ext && $userfile_type==$mime_type){
				$error = "";
				break;
			}else{
				$error = "文件 <strong>".$image_ext."</strong> 不是期望的格式。<br />";
			}
		}
		if ($userfile_size > ($max_file*1048576)) {
			$error.= "图片不要超过 ".$max_file."MB";
		}
		
	}else{
		$error= "请先选择图像";
	}
	if (strlen($error)==0){
		
		if (isset($_FILES['image']['name'])){
			$large_image_location = $large_image_location.".".$file_ext;
			$thumb_image_location = $thumb_image_location.".".$file_ext;
			
			$_SESSION['user_file_ext']=".".$file_ext;
			
			move_uploaded_file($userfile_tmp, $large_image_location);
			chmod($large_image_location, 0777);
			
			$width = getWidth($large_image_location);
			$height = getHeight($large_image_location);
			if ($width > $max_width){
				$scale = $max_width/$width;
				$uploaded = resizeImage($large_image_location,$width,$height,$scale);
			}else{
				$scale = 1;
				$uploaded = resizeImage($large_image_location,$width,$height,$scale);
			}
			if (file_exists($thumb_image_location)) {
				unlink($thumb_image_location);
			}
		}
		header("location:".$_SERVER["PHP_SELF"]);
		exit();
	}
}

if (isset($_POST["upload_thumbnail"]) && strlen($large_photo_exists)>0) {
	$x1 = $_POST["x1"];
	$y1 = $_POST["y1"];
	$x2 = $_POST["x2"];
	$y2 = $_POST["y2"];
	$w = $_POST["w"];
	$h = $_POST["h"];
	$scale = $thumb_width/$w;
	$cropped = resizeThumbnailImage($thumb_image_location, $large_image_location,$w,$h,$x1,$y1,$scale);
	header("location:".$_SERVER["PHP_SELF"]);
	exit();
}


if ($_GET['a']=="delete" && strlen($_GET['t'])>0){
	$large_image_location = $upload_path.$large_image_prefix.$_GET['t'];
	$thumb_image_location = $upload_path.$thumb_image_prefix.$_GET['t'];
	if (file_exists($large_image_location)) {
		unlink($large_image_location);
	}
	if (file_exists($thumb_image_location)) {
		unlink($thumb_image_location);
	}
	header("location:".$_SERVER["PHP_SELF"]);
	exit(); 
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
	<meta name="generator" content="WebMotionUK" />
	<title>jQuery+php图片裁切</title>
	<script type="text/javascript" src="http://static.local-dev.cn/js/jquery-pack.js"></script>
	<script type="text/javascript" src="http://static.local-dev.cn/js/jquery.imgareaselect.min.js"></script>
</head>
<!--
   ┏━━━━━━━━━━━━━━━━━━━━━┓
   ┃             源 码 爱 好 者               ┃
   ┣━━━━━━━━━━━━━━━━━━━━━┫
   ┃                                          ┃
   ┃           提供源码发布与下载             ┃
   ┃                                          ┃
   ┃        http://www.codefans.net           ┃
   ┃                                          ┃
   ┃            互助、分享、提高              ┃
   ┗━━━━━━━━━━━━━━━━━━━━━┛
-->

<body>
<?php
if(strlen($large_photo_exists)>0){
	$current_large_image_width = getWidth($large_image_location);
	$current_large_image_height = getHeight($large_image_location);?>
<script type="text/javascript">
function preview(img, selection) { 
	var scaleX = <?php echo $thumb_width;?> / selection.width; 
	var scaleY = <?php echo $thumb_height;?> / selection.height; 
	
	$('#thumbnail + div > img').css({ 
		width: Math.round(scaleX * <?php echo $current_large_image_width;?>) + 'px', 
		height: Math.round(scaleY * <?php echo $current_large_image_height;?>) + 'px',
		marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
		marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
	});
	$('#x1').val(selection.x1);
	$('#y1').val(selection.y1);
	$('#x2').val(selection.x2);
	$('#y2').val(selection.y2);
	$('#w').val(selection.width);
	$('#h').val(selection.height);
} 

$(document).ready(function () { 
	$('#save_thumb').click(function() {
		var x1 = $('#x1').val();
		var y1 = $('#y1').val();
		var x2 = $('#x2').val();
		var y2 = $('#y2').val();
		var w = $('#w').val();
		var h = $('#h').val();
		if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
			alert("请先选择上传图片");
			return false;
		}else{
			return true;
		}
	});
}); 

$(window).load(function () { 
	
	$('#thumbnail').imgAreaSelect({ aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>', onSelectChange: preview }); 
	startSelection(event);
});

</script>

<?php }?>
<h1>jQuery+php上传图片并裁切</h1>
<?php
if(strlen($error)>0){
	echo "<ul><li><strong>错误!</strong></li><li>".$error."</li></ul>";
}
if(strlen($large_photo_exists)>0 && strlen($thumb_photo_exists)>0){
	echo $large_photo_exists."&nbsp;".$thumb_photo_exists;
	echo "<p><a href=\"".$_SERVER["PHP_SELF"]."?a=delete&t=".$_SESSION['random_key'].$_SESSION['user_file_ext']."\">删除图像</a></p>";
	echo "<p><a href=\"".$_SERVER["PHP_SELF"]."\">再来一张</a></p>";
	$_SESSION['random_key']= "";
	$_SESSION['user_file_ext']= "";
}else{
		if(strlen($large_photo_exists)>0){?>
		<h2>请拖动鼠标选择裁切区域：</h2>
		<div align="center">
			<img src="<?php echo $upload_path.$large_image_name.$_SESSION['user_file_ext'];?>" style="float: left; margin-right: 10px;" id="thumbnail" alt="Create Thumbnail" />
			<div style="border:1px #e5e5e5 solid; float:left; position:relative; overflow:hidden; width:<?php echo $thumb_width;?>px; height:<?php echo $thumb_height;?>px;">
				<img src="<?php echo $upload_path.$large_image_name.$_SESSION['user_file_ext'];?>" style="position: relative;" alt="Thumbnail Preview" />
			</div>
			<br style="clear:both;"/>
			<form name="thumbnail" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
				<input type="hidden" name="x1" value="" id="x1" />
				<input type="hidden" name="y1" value="" id="y1" />
				<input type="hidden" name="x2" value="" id="x2" />
				<input type="hidden" name="y2" value="" id="y2" />
				<input type="hidden" name="w" value="" id="w" />
				<input type="hidden" name="h" value="" id="h" />
				<input type="submit" name="upload_thumbnail" value="保存" id="save_thumb" />
			</form>
		</div>
	<hr />
	<?php 	} ?>
	<form name="photo" enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
	<input type="file" name="image" size="30" /> <input type="submit" name="upload" value="Upload" />
	</form>

<?php } ?>
</body>
</html>
