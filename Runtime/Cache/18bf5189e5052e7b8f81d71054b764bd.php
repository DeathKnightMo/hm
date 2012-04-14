<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">
function check_all(obj,cName)
{
    var checkboxs = document.getElementsByName(cName);
    for(var i=0;i<checkboxs.length;i++){checkboxs[i].checked = obj.checked;}
}
</script>
<p><input type="checkbox" name="all" onclick="check_all(this,'c')" />全选/全不选 <input type="button" value="增加" onclick="addatr()"></p>
<span style="margin-right:100px;margin-left:30px">标题</span>
<span>简介</span>
<?php if(is_array($artList)): $i = 0; $__LIST__ = $artList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): ++$i;$mod = ($i % 2 )?><div><input type="checkbox" name="c" value="<?php echo ($art["id"]); ?>" />
<span style="margin-right:100px"><?php echo ($art["title"]); ?></span>
<span><?php echo ($art["summary"]); ?></span>
</div><?php endforeach; endif; else: echo "" ;endif; ?>
<input type="button" value="转发" onclick="sendtowb()">
<script type="text/javascript">
function addatr(){
	 window.location.href="<?php echo C("WWW");?>/index.php?m=Article&a=article&category=3";
}
function sendtowb(){
	var selectedItems = new Array();
	var j=0;
	var v = document.getElementsByName("c"); 
 	for (i = 0; i < v.length; i++) { 
 		if (v[i].checked) { 
 		selectedItems[j]=v[i].value; 
 		j++;
 		} 
 	}
 	if (selectedItems.length==0) {
 		alert("请先选择要转发的资讯");
 	} else{
 		$.post("<?php echo C("WWW");?>/weibo/sendmessage",{"articlelist":selectedItems},function(data){
    		if(data.status==0)
    		{
    			alert(data.info);
    		}else{
    			window.location.href=data.info;
    		}
    	},"json");
 	};
    
}
</script>