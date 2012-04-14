<?php
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
class weiboAction extends Action{
	function authorize(){
		$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
		$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
		redirect($code_url);
	}
	function callback(){
		if (isset($_REQUEST['code'])) {
			$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = WB_CALLBACK_URL;
		try {
			$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
			}
		}if ($token) {
			$_SESSION["token"]=$token;
			exit('授权成功<a href="javascript:history.go(-2)">返回</a>');
		}else{
			echo "授权失败";
		}
	}
	function sendmessage(){
		$access_token=$_SESSION["token"]["access_token"];
		$articlelist=$_POST["articlelist"];
		if($access_token!=null)
		{
			$sucessnum=0;
			$faildnum=0;
			$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
			for($i=0;$i<count($articlelist);$i++){
				$Article=M("Article");
				$article=$Article->find($articlelist[$i]);
				sleep(3);
				$ret = $c->update($article["summary"]);
				if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
						$faildnum=$faildnum+1;
					}else{
						$sucessnum=$sucessnum+1;
				}
			}
			$this->ajaxReturn(1,"发送成功".$sucessnum."条,发送失败".$faildnum."条",0);
		}else{
			
			$this->ajaxReturn(1,C("WWW")."/index.php?m=weibo&a=authorize",1);
		}
	}
	function sendonemessage(){
		$access_token=$_SESSION["token"]["access_token"];
		$messageid=$_POST["messageid"];
		if($access_token!=null)
		{
			$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
			$Messageshow=M("Messageshow");
			$text=$Messageshow->find($messageid);
			sleep(3);
			$ret = $c->update($text["introduction"]);
			if ( isset($ret['error_code']) && $ret['error_code'] > 0 ) {
					$this->ajaxReturn(1,"发送失败",2);
				}else{
					$this->ajaxReturn(1,"发送成功",0);
				}
		}else{
			$this->ajaxReturn(1,"/index.php?m=weibo&a=authorize",1);
		}
	}
}