	function getid(o){ return (typeof o == "object")?o:document.getElementById(o);}
	function getNames(obj,name,tij)
	{
		var plist = getid(obj).getElementsByTagName(tij);
		var rlist = new Array();
		for(i=0;i<plist.length; ++i){if(plist[i].getAttribute("name") == name){rlist[rlist.length] = plist[i];}}
		return rlist;
	}

	function fiterplay(obj,num,t,name,c1,c2)
	{
		var fitlist = getNames(obj,name,t);
		for(i=0;i<fitlist.length;i++)
		{
			if(i == num)
			{
				//alert("c1--"+c1);
				//alert($(fitlist[i]).html());
				fitlist[i].className = c1;
			}
			else
			{
				//alert(c2);
				fitlist[i].className = c2;
			}
		}
	}
	function s_fiterplay(obj,num,t,name,c1,c2)
	{
		
		var fitlist = getNames(obj,name,t);
		//alert(fitlist.length+"--"+num);
		for(i=0;i<fitlist.length;i++)
		{
			//alert(c1);
			if((i+1) == num)
			{
				//alert("c1--"+c1);
				//alert(num);
				//alert(m);
				//alert("i"+i);
				var cur=i+1;
				//alert(cur);
				//fitlist[i].className = "f_"+c1;
				$(".f_"+cur+" img").attr("src","http://static.local-dev.cn/images/user_tryclick_"+cur+".png");
				if(i==0){
					$(".f_7"+" img").attr("src","http://static.local-dev.cn/images/user_trying_"+7+".png");
				}else{
					$(".f_"+i+" img").attr("src","http://static.local-dev.cn/images/user_trying_"+i+".png");
				}
				
				//break;
				
				
			}
			else
			{
				
				//$(".f_"+i+" img").attr("src","http://static.local-dev.cn/images/user_trying_"+i+".png");
				
				
				
			}
		}
	}
	function play(obj,num)
	{
		var s = getid('simg');		
		var b = getid('bimg');
		try	
		{
			with(b)
			{
				filters[0].Apply();	
				fiterplay(b,num,"div","f","dis","undis");	
				s_fiterplay(s,num,"div","s",num,"");				
				filters[0].play();
			}
		}
		catch(e)
		{
				fiterplay(b,num,"div","f","dis","undis");
				s_fiterplay(s,num,"div","s",num,"");	
				
		}
	}

	var autoStart = 0;
	var n = 0;		
	var s = getid("simg");
	var x = getNames(s,"s","div");
	
	function clearAuto() {clearInterval(autoStart);};	
	function setAuto(){autoStart=setInterval("auto(n)", 2000)}
	function auto() {
		n++  ;
		if(n>(x.length)){
			n = 1;
			clearAuto();
			setAuto();
		 }
		play(x[n],n);

	}
	function ppp(){
	setAuto();	
	}
	ppp();