<?php
class ArticleAction extends CommonAction
{
	public function article(){
		if(isset($_GET['category'])&&intval($_GET['category'])>0){
			$this->assign("category",intval($_GET['category']));
		}
		$this->display();
	}
	public function addarticle(){
		$title=$_POST['title'];
		$summary=$_POST['summary'];
		$content=$_POST['content'];
		if(isset($_POST['category'])&&intval($_POST['category'])>0){
			$data['categoryid']=intval($_POST['category']);
		}
		$createdate=get_date_time();
		$data["title"]=$title;
		$data["summary"]=$summary;
		$data["content"]=strip_tags($content);
		$data["createdate"]=$createdate;
		$article=M("Article");
		$result=$article->add($data);
		if($result==false){
			$this->error_msg("保存失败，请重试！");
		}else{
			$this->assign("articleid",$result);
			$this->display("uploadpic");
		}
	}
	public function addpic(){
		$articleid=$_POST['articleid'];
		$articlepic=M("Articlepic");
		$successpic=array();
		$faildpic=array();
		import ( "ORG.Net.UploadFile" );
		$upload = new UploadFile ();
		$upload->allowExts = array ('jpg', 'gif', 'png', 'jpeg', 'swf' ); // 设置附件上传类型
		$date = date ( "Y/m/d" );
		//print "date===" . $date;
		$upload->savePath = C ( 'UPLOAD_PATH' )  . '/' . $date . '/'; // 设置附件上传目录
		//print "savePath----" . $upload->savePath;
		muti_mkdir ( $upload->savePath );
		$upload->saveRule = "uniqid";
		if (! $upload->upload ()) { // 上传错误提示错误信息
			exit ( $upload->getErrorMsg () . "  " );
			//$this->error($upload->getErrorMsg());
			return false;
		} else { // 上传成功获取上传文件信息
			$info = $upload->getUploadFileInfo();//这里是获取的所有的图片信息    
  			$file=$_FILES["image"]["name"];          
  			for($i=0;$i<count($file);$i++){
  				$pictitle=$_POST['pictitle'.$i];
				$data["articleid"]=$articleid;
				$picSaveName = msubstr ( $info [$i] ['savename'], 0, 13 );
				$ext=$info [$i] ['extension'];
				//保存上传文件信息至DB
				$picurl = C("IMG").'/'.$date .'/'.$picSaveName.".".$ext;
  				$data["articleid"]=$articleid;
  				$data["pictitle"]=$pictitle;
  				$data["picurl"]=$picurl;
  				$result=$articlepic->add($data);
				if($result!==false){
					array_push($successpic, $pictitle);
				}else{
					array_push($faildpic, $pictitle);
				}
  			}
		}
		$this->assign("articleid",$articleid);
		$this->assign("successpic",$successpic);
		$this->assign("faildpic",$faildpic);
		$this->display("uploadpic");
	}
	public function preview(){
		$articleid=$_GET['articleid'];
		$Article=M("Article");
		$articlepic=M("Articlepic");
		$article=$Article->find($articleid);
		$piclist=$articlepic->where("articleid=".$articleid)->findAll();
		$this->assign("article",$article);
		$this->assign("piclist",$piclist);
		$this->assign("piclistcount",count($piclist));
		$this->display();
	}
	public function publish(){
		$articleid=$_GET['articleid'];
		$Article=M("Article");
		$articlepic=M("Articlepic");
		$article=$Article->find($articleid);
		// $content=$article["content"];
		// $contentlist=$content.implode("<br>",$content);
		$piclist=$articlepic->where("articleid=".$articleid)->findAll();
		$this->assign("article",$article);
		// $this->assign("contentlist",$contentlist);
		// $this->assign("countcontent",count($contentlist)-1);
		$this->assign("piclist",$piclist);
		$this->buildHtml($articleid, 'HTML/','preview');
		$result=$Article->where("id=".$articleid)->setField("pageurl",C("WWW")."/"."HTML/".$articleid.'.shtml');
		if($result!==false){
			$this->ajaxReturn(1,C("WWW")."/"."HTML/".$articleid.'.shtml',0);
		}else{
			$this->ajaxReturn(2,"err",1);
		}
	}
	public function art_list(){
		if(isset($_GET['cid'])&&intval($_GET['cid'])>0){
			$cid=intval($_GET['cid']);
			$Article=M("Article");
			$artList=$Article->where("categoryid=".$cid)->order("id desc")->select();
			$art0List=array();
			$art1List=array();
			$art2List=array();
			$art3List=array();
			for($i=0;$i<count($artList);$i++){
				switch ($i%4){
					case 0:{$num=count($art0List);$art0List[$num]=$artList[$i];break;}
					case 1:{$num=count($art1List);$art1List[$num]=$artList[$i];break;}
					case 2:{$num=count($art2List);$art2List[$num]=$artList[$i];break;}
					case 3:{$num=count($art3List);$art3List[$num]=$artList[$i];break;}
					default:;
				}
			}
			$this->assign("art0List",$art0List);
			$this->assign("art1List",$art1List);
			$this->assign("art2List",$art2List);
			$this->assign("art3List",$art3List);
			
		}
		$this->display();
	}
	
	public function art_view(){
		if(isset($_GET['id'])&&intval($_GET['id'])>0){
			$id=intval($_GET['id']);
			$Article=M("Article");
			$art=$Article->find($id);
			if(isset($art['id'])&&$art['id']>0){
				$this->assign("art",$art);
				$ArtPic=M("Articlepic");
				$artPicList=$ArtPic->where("articleid=".$id)->select();
				//print_r($artPicList);
				$this->assign("artPicList",$artPicList);
			}
		}
		$this->display();
	}
}
?>