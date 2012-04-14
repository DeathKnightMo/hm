<?php if (!defined('THINK_PATH')) exit();?><script language="javascript" src="<?php echo C("STATIC");?>/js/hm_tree.js"></script>
<script language="javascript" src="<?php echo C("STATIC");?>/js/jquery-1.4.2.min.js"></script>
<div style="width:1000px;margin:0 auto;">
<div style="width:280px;float:left;">
 <p>栏目分类</p>
 <div id=tree></div>

<a name="obj" id="obj" ></a>
 <div id="category_obj" style="display:none;">
 	<table >
 		<tr>
 			<td width="40%" align="right">分类名：</td>
 			<td><input id="c_name"　type="text" name="c_name" value="" /></td>
 		</tr>
 		<tr>
 			<td width="40%" align="right">上级分类：</td>
 			<td id="f_name">--</td>
 		</tr>
 		<tr>
 			<td width="40%" align="right">&nbsp;</td>
 			<td>
 				<input id="f_id" type="hidden" name="fid" value="" />
 				<input type="button" name="" value="确定" onclick="saveCategory();" >
 			</td>
 		</tr>
 	</table>
 </div>
 <p class="center">
	<input type="button" onclick="delNode();" value="删除分类">		
	<input type="button" onclick="addCategory()" value="增加分类">
	<!--  
	<input type="button" onclick="if(tv.selected){var o =tv.selected.previous();if(o)o.select();}" value="上一个节点">
	<input type="button" onclick="if(tv.selected){var o =tv.selected.next();if(o)o.select();}" value="下一个节点">
	<input type="button" onclick="showNodeForm();" value="新增模块对象" />
	<input type="button" onclick="getNodeObjs()" value="查看节点模块对象" />
	-->
</p>
</div>
<div style="width:710px;float:left;border: 1px solid;">
<div id="art_list"　style="width:700px;">
</div>
<p>
<a id="art_show" href="http://redbud12.shp02.host.35.com/hm/index.php?m=Article&a=art_list&cid=3" target="_blank">前端显示</a>
</p>
</div>
<div style="clear:both;"></div>
</div>
</div>
<script>
var tv = new treeview("treeview","tree",true,true);
var id_0 = new node("栏目分类","id_0","","");
var nResult=0;


	treeview.prototype.onnodeclick = function(sender){
		//alert("caption:" + sender.caption + ",id:" + sender.id + ",deepth:" + sender.level + ",tag:" + sender.tag);
		//alert("islast:" + sender.isLast +",indent:" + sender.indent);		
//		alert(sender.baseNode.outerHTML);
		//alert(sender.level);
		$("#category_obj").hide();
		if(sender.level==2){
			//alert(sender.pid);
			var cid=sender.pid.split("_")[1];
			$("#art_list").load("<?php echo C("WWW");?>/index.php?m=Category&a=art_list",{"category":cid});
			
		}
		return false;
	}
	
	treeview.prototype.onnodecheck = function(sender){
		
		//alert(sender.caption + " selected:" + sender.checked);
	}
	treeview.prototype.onselectchange = function(before,after){
	//	if(before)
	//		alert(before.caption + "," + after.caption);
	//	else
	//		alert(after.caption);
	}

//	treeview.prototype.onnodekeydown = function(sender,e){var e = event||e;alert(e.keyCode);}
	treeview.prototype.onnodedrag		= function(from,to){
		to.moveToChild(from);
	}
	tv.add(id_0);
	<?php $parentId = '0'; ?>
	<?php if(isset($cList)): ?><?php if(is_array($cList)): $i = 0; $__LIST__ = $cList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate): ++$i;$mod = ($i % 2 )?>var id_<?php echo ($cate["id"]); ?>=new node("<?php echo ($cate["name"]); ?>","id_<?php echo ($cate["id"]); ?>");
	id_<?php echo ($cate["fatherid"]); ?>.add(id_<?php echo ($cate["id"]); ?>);
	
	<?php $parentId = $cate["fatherid"]; ?><?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>


	function addCategory(){
		var nn = tv.selected;
		//if(!nn)nn=tv; 
		if(!nn){
			alert("请选择一个分类！");
			return;
		}
		if(nn.level>=2){
			alert("栏目分类最多只有二级！");
			return;
		}
		var parentId=nn.pid.split("_")[1];
		var pName=nn.caption;
		$("#f_id").val(parentId);
		if(parentId==0){
			$("#f_name").html("--");
		}else{
			$("#f_name").html(pName);
		}
		$("#category_obj").show();
	}
	
	function saveCategory(){
		var cName=$("#c_name").val().trim();
		if(cName==null||cName==""){
			alert("请填写分类名！");
			return;
		}
		var fId=$("#f_id").val();
		if(fId>=0){
			nResult=0;	
			jQuery.post("<?php echo C("WWW");?>/index.php?m=Category&a=save",{"fid":fId,"cname":cName},function(data){
				if(data.status==0){
					alert(data.info);
					if(data.data!=1){
						$("#category_obj").hide();
					}
					return;
				}
				nResult=data.data;	
				if(nResult>0){
					var nn = tv.selected;
					var idStr="id_"+nResult;
					nn.add(new node(cName,idStr));
					$("#category_obj").hide();
				}			
			},"json");	
		}else{
			$("#category_obj").hide();
			alert("请重新分类");
		}
		
	}
    function delNode(){
		if(tv.selected){
			var nn=tv.selected;
			//alert(nn.pid);	
			var nodeId=nn.pid.split("_")[1];
			if(nodeId==0){
				alert("栏目分类无需删除！");
				return;
			}
						
			if(nn.nodes.length>0){
				var cname=nn.caption;
				if(!confirm("栏目："+cname+" 下存在子栏目分类，删除该栏目分类会一并删除其子分类，确定删除吗？")){
					return;
				}
			}
			
			jQuery.post("<?php echo C("WWW");?>/index.php?m=Category&a=del",{"id":nodeId},function(data){
				if(data.status==0){
					alert(data.info);
					return;
				}
				nn.remove();
			},"json");
			//nn.remove();
		}else{
			alert("请选择一个分类！");
		}
     }


	tv.create(document.getElementById("tree"));

</script>