$(function(){

/* ojia_index选项卡切换js start */
/*
	$(".head_inner_searched_room").click(function(){
		$(".head_inner_searched_room").css({background:"#333366",color:"#fff"});
		$(".head_inner_searched_effected").css({background:"#fff",color:"#43405c"});
		$(".head_inner_searched_element").css({background:"#fff",color:"#43405c"});
	});
	$(".head_inner_searched_effected").click(function(){
		$(".head_inner_searched_room").css({background:"#fff",color:"#43405c"});
		$(".head_inner_searched_effected").css({background:"#333366",color:"#fff"});
		$(".head_inner_searched_element").css({background:"#fff",color:"#43405c"});
	});
	$(".head_inner_searched_element").click(function(){
		$(".head_inner_searched_room").css({background:"#fff",color:"#43405c"});
		$(".head_inner_searched_effected").css({background:"#fff",color:"#43405c"});
		$(".head_inner_searched_element").css({background:"#333366",color:"#fff"});
	});	
	
*/
/* ojia_index选项卡切换js end */
	
	
/* ojia_index 弹出框js start */

	$(".index_clickbox_box").hide();
	var screenwidth,screenheight,mytop,getPosLeft,getPosTop;
	screenwidth = $(window).width();				
	screenheight = $(window).height();
	mytop = $(document).scrollTop();
	getPosLeft = screenwidth/2-485;
	getPosTop = screenheight/2-298;
	$(".index_clickbox_box").css({"left":getPosLeft,"top":getPosTop});
	$(window).resize(function(){
		screenwidth = $(window).width();
		screenheight = $(window).height();
		mytop = $(document).scrollTop();
		getPosLeft = screenwidth/2-485;
		getPosTop = screenheight/2-298;
		$(".index_clickbox_box").css({"left":getPosLeft,"top":getPosTop+mytop});
	});	
	$(window).scroll(function(){
		screenwidth = $(window).width();
		screenheight = $(window).height();
		mytop = $(document).scrollTop();
		if(mytop>($(document).height())/2){
			mytop=($(document).height())/2;
		}
		getPosLeft = screenwidth/2-485;
		getPosTop = screenheight/2-298;
		$(".index_clickbox_box").css({"left":getPosLeft,"top":getPosTop+mytop});
	});
	$(".index_clickbox_a").click(function(){
		$(".index_clickbox_box").fadeIn("fast");
		$("body").append("<div id='greybackground'></div>");
		var documentheight = $(document).height();
		$("#greybackground").css({"opacity":"0.5","height":documentheight});
		return false;
	});
	$(".box_close").click(function(){
		$(".index_clickbox_box").hide();
		$("#greybackground").remove();
		return false;
	});
});

/* ojia_index 弹出框js end */
