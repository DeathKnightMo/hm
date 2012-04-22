<?php
class CategoryAction extends CommonAction{
	
	public function index(){
		$this->has_right("Category","index");
		$Category=M("Category");
		$cList=$Category->select();
		$this->assign("cList",$cList);
		$this->display(DEFAULT_DISPLAY);
	}
	
	/**
	 * 
	 * ajax添加分类
	 */
	public function save(){
		if(isset($_POST['fid'])&&intval($_POST['fid'])>=0&&isset($_POST['cname'])&&trim($_POST['cname'])!=""){
			$fid=intval($_POST['fid']);
			$cname=trim($_POST['cname']);
			$Category=M("Category");
			if($fid>0){
				$count=$Category->where("id=".$fid)->count();
				if($count==0){
					$this->ajaxReturn(0,"上级不存在，请重新选择一个分类！",0);
				}
			}
			$count=$Category->where("name='".$cname."' and fatherid=".$fid)->count();
			if($count>0){
				$this->ajaxReturn(1,"同级分类下已存在此分类名！",0);
			}
			$data['fatherid']=$fid;
			$data['name']=$cname;
			$result=$Category->add($data);
			
			if($result!==false){
				$this->ajaxReturn($result,"创建分类成功！",1);
			}else{
				$this->ajaxReturn(0,"创建分类失败！",0);
			}
		}else{
			$this->ajaxReturn(0,"参数错误，请重试！",0);
		}
	}
	
	/**
	 * 
	 * ajax删除分类
	 */
	public function del(){
		if(isset($_POST['id'])&&intval($_POST['id'])>0){
			$id=intval($_POST['id']);
			$Category=M("Category");
			$result=$Category->where("id=".$id)->delete();
			if($result!==false){
				$Category->where("fatherid=".$id)->delete();
				$this->ajaxReturn(1,"删除栏目成功！",1);
			}else{
				$this->ajaxReturn(0,"删除栏目失败！",0);
			}
		}else{
			$this->ajaxReturn(0,"参数错误，请重试！",0);
		}
	}
	
	/**
	 * 
	 * ajax 载入文章列表
	 */
	public function article(){
		if(isset($_POST['category'])&&intval($_POST['category'])>0){
			$cid=intval($_POST['category']);
			$Category=M("Category");
			$cate=$Category->find($cid);
			if(isset($cate['id'])&&$cate['id']>0){
				$this->assign("cid",$cid);
				$Article=M("Article");
				$aList=$Article->where("categoryid=".$cid)->field("id,title,summary")->order("id desc")->select();
				$this->assign("aList",$aList);
			}
		}
		$this->display();
	}
	

}