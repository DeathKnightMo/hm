	function getid(o){ return (typeof o == "object")?o:document.getElementById(o);}
	function getNames(obj,name,tij)
	{
		//alert(obj);
		var plist = getid(obj).getElementsByTagName(tij);
		var rlist = new Array();
		for(i=0;i<plist.length; ++i){if(plist[i].getAttribute("name") == name){rlist[rlist.length] = plist[i];}}
		return rlist;
	}

	function fiterplay(obj,num,t,name,c1,c2)
	{
		var fitlist = getNames(obj,name,t);
		for(i=0;i<fitlist.length;++i)
		{
			if(i == num)
			{
				fitlist[i].className = c1;
				fadeIn(fitlist[i],120,100);
				
			}
			else
			{			
				fitlist[i].className = c2;
				fadeOut(fitlist[i],120,0);
				
			}
		}
	}
	function fiterplaya(obj,num,t,name,c1,c2)
	{
		var fitlist = getNames(obj,name,t);
		for(i=0;i<fitlist.length;++i)
		{
			if(i == num)
			{				
				fitlist[i].className = c1;
			}
			else
			{			
				fitlist[i].className = c2;
			}
		}
	}	
	//底层共用
	var iBase = {
	    Id: function(name){
	        return document.getElementById(name);
	    },
		//设置元素透明度,透明度值按IE规则计,即0~100
	    SetOpacity: function(ev, v){
	        ev.filters ? ev.style.filter = 'alpha(opacity=' + v + ')' : ev.style.opacity = v / 100;
	    }
	}
	//淡入效果(含淡入到指定透明度) 
	function fadeIn(elem, speed, opacity){
		/* 
		 * 参数说明      
		 * elem==>需要淡入的元素     
		 * speed==>淡入速度,正整数(可选)     
		 * opacity==>淡入到指定的透明度,0~100(可选)      
		 */    
		speed = speed || 20;     
		opacity = opacity || 100;     //显示元素,并将元素值为0透明度(不可见)     
		elem.style.display = 'block';     
		iBase.SetOpacity(elem, 0);     //初始化透明度变化值为0    
		var val = 0;     //循环将透明值以5递增,即淡入效果     
		(function(){        
			iBase.SetOpacity(elem, val);        
			val += 5;         
			if (val <= opacity) {             
				setTimeout(arguments.callee, speed)         
			}     
		})(); 		 
	}
	//淡出效果(含淡出到指定透明度) 
	function fadeOut(elem, speed, opacity){
		/*    
		 * 参数说明  
		 * elem==>需要淡入的元素     
		 * speed==>淡入速度,正整数(可选)      
		 * opacity==>淡入到指定的透明度,0~100(可选)      
		 */   
		speed = speed || 20;     
		opacity = opacity || 0;  //初始化透明度变化值为0     
		var val = 100;     //循环将透明值以5递减,即淡出效果     
		(function(){ 
			iBase.SetOpacity(elem, val);         
			val -= 5;         
			if (val >= opacity) {             
				setTimeout(arguments.callee, speed);         
			}else if (val < 0) {           
				//元素透明度为0后隐藏元素           
				elem.style.display = 'none';        
			}    
		})(); 
	} 
	function play(obj,num)
	{
		var s = getid('simg_index');
		var i = getid('info');
		var b = getid('bimg_index');
		try	
		{
			with(b)
			{
				filters[0].Apply();	
				fiterplay(b,num,"div","f","dis_index","undis_index");	
				fiterplaya(s,num,"div","f","","f1");
				//fiterplaya(i,num,"div","f","dis","undis");
				filters[0].play();
			}
		}
		catch(e)
		{
				fiterplay(b,num,"div","f","dis_index","undis_index");
				fiterplaya(s,num,"div","f","","f1");	
				//fiterplaya(i,num,"div","f","dis","undis");
		}
	}

	var autoStart = 0;
	var n = 0;
	var s = getid("simg_index");
	var x = getNames(s,"f","div");
	function clearAuto() {clearInterval(autoStart);};
	function setAuto(){autoStart=setInterval("auto(n)", 6000)}
	function auto()	{


		n++  ;
		if(n>(x.length-1))
		{ n = 0;
		clearAuto();
		setAuto();
		 }
		play(x[n],n);
		
	}
	function ppp(){
	setAuto();
	
	}
ppp();