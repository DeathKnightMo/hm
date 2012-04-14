<?php
class CategoryAction extends CommonAction{
	
	public function index(){
		$Category=M("Category");
		$cList=$Category->select();
		$this->assign("cList",$cList);
		$this->display();
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
	public function art_list(){
		if(isset($_POST['category'])&&intval($_POST['category'])>0){
			$cid=intval($_POST['category']);
			$Article=M("Article");
			$artList=$Article->where("categoryid=".$cid)->order("id desc")->select();
			$this->assign("artList",$artList);
		}
		$this->display();
	}
	
}