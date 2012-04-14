// 测试的代码，学习jquery对象开发
//2011-4-13
//2011-5-26  增加是否自动轮换、首个获取焦点，以及轮换等待时间，指定获取焦点的类名称


(function($){
	$.fn.tabs = function ( options ) {
      var  defaults = {
			 //默认绑定的鼠标方法，支持 click，hover
			 target: null,//不指定目标，则只是按钮出现轮换效果
			 firstfocus:0,//默认选项组的第一个获取焦点效果
			 autochange:false,//默认不开启自动滚动效果
			 delay:3000,//默认的轮换等待时间，如果autochange为true有效
			 focusclass:'hover',//默认指定的获取焦点的类名称
			 tf : "click"
       } ;
       // 通过用户指定的参数来覆盖默认值实现设置，但是这个设置的话必须是通过这种方式调用，不能再外部指定参数值，因此不方便在运行中改变参数.
       var   opts = $. extend ( defaults , options );
	   // 基础方法
	   
	   var $total = this.size();//tab按钮数量
	   var ff = (parseInt(opts.firstfocus,10) && (opts.firstfocus<0 || opts.firstfocus+1> $total))?opts.firstfocus:0;
	  // alert(ff);
	   this.removeClass(opts.focusclass).eq(ff).addClass(opts.focusclass);
	   
	   var $target = null;
			if(opts.target){
				$target = $(opts.target);
				$target.hide();
				$target.eq(ff).show();
			}
	   	var $p =  this;
			
		//自动轮换方法
		function autoChange(){
			//i当前对象在同辈对象中的index
			var cI = 0;
				if($("."+opts.focusclass,$p)){cI = $("."+opts.focusclass).index();}
			var nI = 0;
				nI =(cI+1<$total)?cI+1:0;
			$p.removeClass(opts.focusclass);
			$p.eq(nI).addClass(opts.focusclass);
			if(opts.target){
				$target.hide();
				$target.eq(nI).show();
			}
			
			if(opts.autochange){
				setTimeout(function(){autoChange()},opts.delay);
			}
			
		};
		//是否启动自动变换
		if(opts.autochange){
			setTimeout(function(){autoChange()},opts.delay);
		}
			
		
		//自动轮换
		
			
		
	   return this.each( function() {
		   //初始化
		    var $this = $(this);
		    var $index = $this.index();
			//绑定鼠标事件,绑定单击事件
			if(opts.tf=="click" ||opts.tf==null ){
				$this.click(function(){
					var curindex = $this.index();
					$this.siblings().removeClass(opts.focusclass);
					$this.addClass(opts.focusclass);
					$target.hide();
					$target.eq(curindex).show();
					
				});
			};
			//绑定鼠标事件,绑定hover事件
			if(opts.tf=="hover"){
				$this.hover(function(){
					var curindex = $this.index();
					$this.siblings().removeClass(opts.focusclass);
					$this.addClass(opts.focusclass);
					$target.hide();
					$target.eq(curindex).show();},function(){}
				);
			};
			
		}) ; 
	 
}
})(jQuery);