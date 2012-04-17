//$(document).ready(function() {
//
//	// first example
//	$("#browser").treeview();
//
//	// second example
//	$("#navigation").treeview( {
//		persist : "location",
//		collapsed : true,
//		unique : true
//	});
//
//	// third example
//	$("#red").treeview( {
//		animated : "fast",
//		collapsed : true,
//		unique : true,
//		persist : "cookie",
//		toggle : function() {
//			window.console && console.log("%o was toggled", this);
//		}
//	});
//
//	// fourth example
//	$("#black, #gray").treeview( {
//		control : "#treecontrol",
//		persist : "cookie",
//		cookieId : "treeview-black"
//	});
//
//});

String.prototype.trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
}
String.prototype.ltrim = function() {
	return this.replace(/(^\s*)/g, "");
}
String.prototype.rtrim = function() {
	return this.replace(/(\s*$)/g, "");
}

function bookmark() {
	var title = document.title
	var url = document.location.href
	if (window.sidebar)
		window.sidebar.addPanel(title, url, "");
	else if (window.opera && window.print) {
		var mbm = document.createElement('a');
		mbm.setAttribute('rel', 'sidebar');
		mbm.setAttribute('href', url);
		mbm.setAttribute('title', title);
		mbm.click();
	} else if (document.all)
		window.external.AddFavorite(url, title);
}
var startPage=0;
var endPage=0;
var currentPage=0;
var into=0;
function page_show_more(url,totalCount,nowPage,n){//totalCount总的页数
	 startPage=Math.floor(nowPage/n)*n+1;//JS 起始页 
	 endPage=Math.floor(nowPage/n)*n+n; //JS 结束页
	 currentPage=nowPage;//当前页
	 into=n;
	//alert(currentPage);
	$("#page_prev").click(function(){
		currentPage--;
		if(currentPage<=0)
			return;
		if(currentPage>=startPage){
			prevPage();
		}else{
			window.location.href=url+"&p="+currentPage;
		}
		if(currentPage==1){
			$("#page_prev").parent().hide();
		}else{
			$("#page_prev").parent().show();
		}
	});
	
	$("#page_next").click(function(){

		currentPage++;
		
		if(currentPage>totalCount)
			return;
		if(currentPage<=endPage){
			nextPage();
		}else{
			window.location.href=url+"&p="+currentPage;
		}
		if(currentPage==totalCount){
			$("#page_next").parent().hide();
		}else{
			$("#page_next").parent().show();
		}
	});
	
	$(".page_js").click(function(){
		currentPage=$(this).html().trim();
		clickPage();

		if(currentPage==totalCount){
			$("#page_next").parent().hide();
		}else{
			$("#page_next").parent().show();
		}
		if(currentPage==1){
			$("#page_prev").parent().hide();
		}else{
			$("#page_prev").parent().show();
		}
	});

}

function prevPage(){
	var curPage=currentPage-startPage+1;
	$(".page_js_row").hide();
	$(".page_js_row_"+curPage).show();
	var next=currentPage+1;
	$("#page_js_"+next).html("<a href='javascript:void(0);' class='page_js'>"+next+"</a>");
	$("#page_js_"+next).removeClass("page_current");
	$("#page_js_"+next).addClass("page_li_num");
	$("#page_js_"+currentPage).html(currentPage);
	$("#page_js_"+currentPage).removeClass("page_li_num");
	$("#page_js_"+currentPage).addClass("page_current");
	$("#page_js_"+next).find("a").bind('click',function(){
		currentPage=$(this).html().trim();
		clickPage();
	});
}

function nextPage(){
	var curPage=into-(endPage-currentPage);
	$(".page_js_row").hide();
	$(".page_js_row_"+curPage).show();
	var prev=currentPage-1;
	//alert(currentPage);
	$("#page_js_"+prev).html("<a href='javascript:void(0);' class='page_js'>"+prev+"</a>");
	$("#page_js_"+prev).removeClass("page_current");
	$("#page_js_"+prev).addClass("page_li_num");
	$("#page_js_"+currentPage).html(currentPage);
	$("#page_js_"+currentPage).removeClass("page_li_num");
	$("#page_js_"+currentPage).addClass("page_current");
	$("#page_js_"+prev).find("a").bind('click',function(){
		currentPage=$(this).html().trim();
		clickPage();
	});
	
}

function clickPage(){
	var curPage=currentPage-startPage+1;
	//alert("curPage："+curPage);
	$(".page_js_row").hide();
	$(".page_js_row_"+curPage).show();
	var before=$(".page_current");
	var beforePage=before.html().trim();
	//alert(beforePage);
	before.html("<a href='javascript:void(0);' class='page_js'>"+beforePage+"</a>");
	before.removeClass("page_current");
	before.addClass("page_li_num");
	$("#page_js_"+currentPage).html(currentPage);
	$("#page_js_"+currentPage).removeClass("page_li_num");
	$("#page_js_"+currentPage).addClass("page_current");
	$("#page_js_"+beforePage).find("a").bind('click',function(){
		currentPage=$(this).html().trim();
		clickPage();
	});
	
}

function pageGoto(url,num){

	if(num.match(/^[0-9]+$/)==null){
		alert("请填写一个整数！");
		return;
	}

	if(num>=0){
		if(num<=endPage&&num>=startPage){
			if(num==currentPage){
				return;
			}
			currentPage=num;
			clickPage();
		}else{
			window.location.href=url+"&p="+num;
		}
	}else{
		alert("请填写一个大于零的整数！");
	}
}

function insertDiv(){
	//alert("insertDiv");
	var embedHtml = "<div style='margin:0 auto;width:1065px;'><div class='open-box'><div class='flash-box' id='street'></div></div>";
	//var embedHtml = "<div style='margin:0 auto;width:1005px;'><div class='open-box'><div class='flash-box' id='street'></div><div class='flash-close'><a href='javascript:closeStreet()'>关闭</a></div></div><div class='black-overlay' onclick='closeStreet()'></div><iframe class='t-iframe'></iframe></div>";
	$('body').append(embedHtml);
	$(".open-box").css('top', (parent.document.documentElement.clientHeight- 590)/2+parent.document.documentElement.scrollTop+parent.document.body.scrollTop+'px');
	$(".open-box").css("position",'absolute');
	$("#street").css({"line-height":'700px',"height":"700px"});
	//$('.black-overlay').css({"height":(parent.document.body.clientHeight)+"px", "width": (parent.document.body.clientWidth)+"px" });
	//$('.t-iframe').css({"height":(parent.document.documentElement.clientHeight )+"px", "width": (parent.document.body.clientWidth)+"px" });
}

function getFlash(w,h,pageUrl,flashVars){
	return "<div style='width:1005px;float:left'><object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"" + w + "\" height=\"" + h + "\" id=\"street\" name=\"street\">"+
	"<param name=\"movie\" value=\"" + pageUrl + "/flash/oujia.swf\" />"+
	"<param name=\"FlashVars\" value=\"" + flashVars + "\"/>"+
	"    <param name=\"quality\" value=\"high\" />"+
	"    <param name=\"bgcolor\" value=\"#000000\" />"+
	"    <param name=\"allowScriptAccess\" value=\"always\" />"+
	"    <param name=\"allowFullScreen\" value=\"true\" />"+
	"    <embed src=\" " + pageUrl + "/flash/oujia.swf\" FlashVars=\"" + flashVars + "\" quality=\"high\" bgcolor=\"#000000\" width=\"" + w + "\" height=\"" + h + "\" name=\"street\" align=\"center\" quality=\"high\" allowScriptAccess=\"always\"  type=\"application/x-shockwave-flash\" allowFullScreen=\"true\" pluginspage=\"http://www.adobe.com/go/getflashplayer\" />"+
	"    </object></div>";
}
function openFlash(param){
	var w=1025;
	var h=700;
	var pageUrl="http://static.local-dev.cn"
	var flashVars="";
	if(null==param||""==param.trim()){
		flashVars="configUrl=http://static.local-dev.cn/flash/config.xml&initId=62_1_0";
	}else{
		flashVars="configUrl=http://static.local-dev.cn/flash/config.xml&initId="+param;
	}
	//alert(111);
	//dispStreet(w,h,pageUrl, flashVars);
	insertDiv();
	$("#street").html(getFlash(w,h,pageUrl,flashVars)+"<span style='text-align:right;width:60px;display:block;float:left;height:700px;line-height:700px;background-color:#555253;'><a style='color:#fff;' href='javascript:closeStreet()'>关闭</a></span>");
}
function closeStreet(){
	$('.flash-box').empty();
	$(".open-box").remove();
	$(".black-overlay").remove();
	$(".t-iframe").remove();
}

function fullWindow(){
    if (window.screen) {//判断浏览器是否支持window.screen判断浏览器是否支持screen    
        var sw = screen.availWidth;   //定义一个myw，接受到当前全屏的宽    
        var sh = screen.availHeight;  //定义一个myw，接受到当前全屏的高
        var csw=document.body.clientWidth;
        var csh=document.body.clientHeight;
        if(sw<csw||sh<csh){
        	window.moveTo(0, 0);           //把window放在左上脚    
        	window.resizeTo(sw, sh);     //把当前窗体的长宽跳转为sw,sh    
        }
     }  

}

//图片等比缩放并居中
var flag=false; 
function DrawImage(ImgD,w,h){ 
    var image=new Image(); 
    image.src=ImgD.src; 
    if(image.width>0 && image.height>0){ 
        flag=true; 
        if(image.width/image.height>= w/h){ 
            if(image.width>w){ 
                ImgD.width=w; 
                ImgD.height=(image.height*w)/image.width; 
            }else{ 
                ImgD.width=image.width; 
                ImgD.height=image.height; 
            } 
           // ImgD.alt=image.width+"x"+image.height; 
        } 
        else{ 
            if(image.height>h){ 
                ImgD.height=h; 
                ImgD.width=(image.width*h)/image.height; 
            }else{ 
                ImgD.width=image.width; 
                ImgD.height=image.height; 
            } 
           // ImgD.alt=image.width+"x"+image.height; 
        } 
    } 
} 
