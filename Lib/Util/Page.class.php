<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

class Page extends Think {
    // 起始行数
    public $firstRow	;
    // 列表每页显示行数
    public $listRows	;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页栏每页显示的页数
    protected $rollPage   ;
	// 分页显示定制
    //protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页  %first%  %prePage%  %upPage% %linkPage% %downPage% %nextPage%  %end%');
  	protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>'<ul>%first%  %upPage% %linkPage% %downPage%  %end% </ul> <span class="page_total"> %totalRow%%header% 共%totalPage%页</span> ');
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->rollPage = C('PAGE_ROLLPAGE');
        $this->listRows = !empty($listRows)?$listRows:C('PAGE_LISTROWS');
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[C('VAR_PAGE')])?$_GET[C('VAR_PAGE')]:1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 分页显示输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<li class='page_li_guide'><a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a></li>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<li class='page_li_guide'><a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a></li>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<li class='page_li_guide'><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";
            $theFirst = "<li class='page_li_guide'><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<li class='page_li_guide'><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";
            $theEnd = "<li class='page_li_guide'><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li class='page_li_num'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
               // if($this->totalPages != 1){
                    $linkPage .= "<li class='page_current'>".$page."</li>";
               // }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
   /**
     +----------------------------------------------------------
     * 元素列表分页显示输出fenye
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show_fenye() {
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<li class='page_li_guide'><a href='".$url."&".$p."=$upRow' class='page_li_guide_a1'>".$this->config['prev']."</a></li>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<li class='page_li_guide'><a href='".$url."&".$p."=$downRow' class='page_li_guide_a2'>".$this->config['next']."</a></li>";
         
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<li class='page_li_guide'><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";
     
            $theFirst = "<li class='page_li_guide'><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<li class='page_li_guide'><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";
            $theEnd = "<li class='page_li_guide'><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li class='page_li_num_a1'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                   
                }else{
                    break;
                }
            }else{
               // if($this->totalPages != 1){
                    $linkPage .= "<li class='page_current_a1'>".$page."</li>";
               // }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
    /**
     * 
     * js异步加载分页
     */
//	public function show_fenyejs() {
//        if(0 == $this->totalRows) return '';
//        $p = C('VAR_PAGE');
//        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
//        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
//        $parse = parse_url($url);
//        if(isset($parse['query'])) {
//            parse_str($parse['query'],$params);
//            unset($params[$p]);
//            $url   =  $parse['path'].'?'.http_build_query($params);
//        }
//        //上下翻页字符串
//        $upRow   = $this->nowPage-1;
//        $downRow = $this->nowPage+1;
//        if ($upRow>0){
//            //$upPage="<li class='page_li_guide'><a href='".$url."&".$p."=$upRow' class='page_li_guide_a1'>".$this->config['prev']."</a></li>";
//            $upPage="<li class='page_li_guide' title='".$upRow."'><a href='javascript:void(0);' class='page_li_guide_a1'>".$this->config['prev']."</a></li>";
//        }else{
//            $upPage="";
//        }
//
//        if ($downRow <= $this->totalPages){
//            //$downPage="<li class='page_li_guide'><a href='".$url."&".$p."=$downRow' class='page_li_guide_a2'>".$this->config['next']."</a></li>";
//            $downPage="<li class='page_li_guide' title='".$downRow."'><a href='javascript:void(0);' class='page_li_guide_a2'>".$this->config['next']."</a></li>";
//        }else{
//            $downPage="";
//        }
//        // << < > >>
//        if($nowCoolPage == 1){
//            $theFirst = "";
//            $prePage = "";
//        }else{
//            $preRow =  $this->nowPage-$this->rollPage;
//            $prePage = "<li class='page_li_guide'><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";
//     
//            $theFirst = "<li class='page_li_guide'><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
//        }
//        if($nowCoolPage == $this->coolPages){
//            $nextPage = "";
//            $theEnd="";
//        }else{
//            $nextRow = $this->nowPage+$this->rollPage;
//            $theEndRow = $this->totalPages;
//            $nextPage = "<li class='page_li_guide'><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";
//            $theEnd = "<li class='page_li_guide'><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
//        }
//        // 1 2 3 4 5
//        $linkPage = "";
//        for($i=1;$i<=$this->rollPage;$i++){
//            $page=($nowCoolPage-1)*$this->rollPage+$i;
//            if($page!=$this->nowPage){
//                if($page<=$this->totalPages){
//                    //$linkPage .= "<li class='page_li_num_a1'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
//                    $linkPage .= "<li class='page_li_num_a1' title='".$page."'><a href='javascript:void(0);'>".$page."</a></li>";
//                }else{
//                    break;
//                }
//            }else{
//               // if($this->totalPages != 1){
//                    $linkPage .= "<li class='page_current_a1'>".$page."</li>";
//               // }
//            }
//        }
//        $pageStr	 =	 str_replace(
//            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
//            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
//        return $pageStr;
//    }
	public function show_fenyejs() {
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            //$upPage="<li class='page_li_guide'><a href='".$url."&".$p."=$upRow' class='page_li_guide_a1'>".$this->config['prev']."</a></li>";
            $upPage="<li class='charity_pages_next' title='".$upRow."'><a href='".$url."&".$p."=".$upRow."' class='page_li_guide_charitya1'>".$this->config['prev']."</a></li>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            //$downPage="<li class='page_li_guide'><a href='".$url."&".$p."=$downRow' class='page_li_guide_a2'>".$this->config['next']."</a></li>";
            $downPage="<li class='charity_pages_next' title='".$downRow."'><a href='".$url."&".$p."=".$downRow."' class='page_li_guide_charitya2'>".$this->config['next']."</a></li>";
        }else{
            $downPage="";
        }
//        // << < > >>
//        if($nowCoolPage == 1){
//            $theFirst = "";
//            $prePage = "";
//        }else{
//            $preRow =  $this->nowPage-$this->rollPage;
//            $prePage = "<li class='charity_pages_next'><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";
//     
//            $theFirst = "<li class='charity_pages_next'><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
//        }
//       if($nowCoolPage == $this->coolPages){
//            $nextPage = "";
//            $theEnd="";
//       }else{
//            $nextRow = $this->nowPage+$this->rollPage;
//            $theEndRow = $this->totalPages;
//            $nextPage = "<li class='page_li_guide'><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";
//            $theEnd = "<li class='page_li_guide'><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
//        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    //$linkPage .= "<li class='page_li_num_a1'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                    $linkPage .= "<li class='charity_pages_li' title='".$page."'><a href='".$url."&".$p."=".$page."'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
               // if($this->totalPages != 1){
                    $linkPage .= "<li class='charity_current_a1'>".$page."</li>";
               // }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
    /**
     * 
     * 
     * 
     *** 
    */
    public function show_fenyejst() {
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            //$upPage="<li class='page_li_guide'><a href='".$url."&".$p."=$upRow' class='page_li_guide_a1'>".$this->config['prev']."</a></li>";
            //$upPage="<li class='charity_pages_next' title='".$upRow."'><a href='javascript:void(0);' class='page_li_guide_charitya1'>".$this->config['prev']."</a></li>";
            $upPage="<li class='charity_pages_next'><a href='".$url."&".$p."=$upRow' class='page_li_guide_charitya1'>".$this->config['prev']."</a></li>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            //$downPage="<li class='page_li_guide'><a href='".$url."&".$p."=$downRow' class='page_li_guide_a2'>".$this->config['next']."</a></li>";
            //$downPage="<li class='charity_pages_next' title='".$downRow."'><a href='javascript:void(0);' class='page_li_guide_charitya2'>".$this->config['next']."</a></li>";
            $downPage="<li class='charity_pages_next'><a href='".$url."&".$p."=$downRow' class='page_li_guide_charitya2'>".$this->config['next']."</a></li>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<li class='charity_pages_next'><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";
     
            $theFirst = "<li class='charity_pages_next'><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
        }
       if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
       }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<li class='page_li_guide'><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";
            $theEnd = "<li class='page_li_guide'><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    //$linkPage .= "<li class='page_li_num_a1'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                    //$linkPage .= "<li class='charity_pages_li' title='".$page."'><a href='javascript:void(0);'>".$page."</a></li>";
                    $linkPage .= "<li class='charity_pages_li'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
               // if($this->totalPages != 1){
                    $linkPage .= "<li class='charity_current_a1'>".$page."</li>";
               // }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
    
    
	/**
	 * 
	 * 将结果集分多页显示，并用JS控制分页
	 * @param int $n 将结果集分成n页，建议<=5页
	 */
    public function show_more($n) {
        if(0 == $this->totalRows) return '';
        if($n<=0) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻页字符串 上一页 下一页
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<li class='page_li_guide'><a href='javascript:void(0);' id='page_prev'>".$this->config['prev']."</a></li>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<li class='page_li_guide'><a href='javascript:void(0);' id='page_next'>".$this->config['next']."</a></li>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<li class='page_li_guide'><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";
            $theFirst = "<li class='page_li_guide'><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<li class='page_li_guide'><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";
            $theEnd = "<li class='page_li_guide'><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        $jsFirstPage=($this->nowPage/$n)*$n+1;
        $jsEndPage=($this->nowPage/$n)*$n+$n;
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                	if($page>=$jsFirstPage&&$page<=$jsEndPage){
                		$linkPage .= "<li class='page_li_num' id='page_js_".$page."' ><a href='javascript:void(0);' class='page_js'>".$page."</a></li>";
                	}else{
                    	$linkPage .= "<li class='page_li_num'><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                	}
                }else{
                    break;
                }
            }else{
                //if($this->totalPages != 1){
                    $linkPage .= "<li class='page_current' id='page_js_".$page."' >".$page."</li>";
                //}
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
    
   /**
    * 
    * 结果集起始页
    * @param $p 当前页
    * @param $n 将结果集分成n页，建议<=5页
    */
    public function start_page($p,$n){
    	if($p>0&&$n>0){
    		return floor(($p/$n))*$n+1;
    	}else{
    		return 1;
    	}
    }
    
    public function getTotalPages(){
    	return $this->totalPages;
    }

}
?>