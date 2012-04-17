<?php
	return array (
	
	//'配置项'=>'配置值'
	
	'APP_DEBUG' => true,
	
	'DB_TYPE' => 'mysql', 
	
	'DB_HOST' => '127.0.0.1', 
	
	'DB_NAME' => 'hm', 
	
	'DB_USER' => 'root', 
	
	'DB_PWD' => 'mysql123', 
	
	'DB_PORT' => '3306', 
	
	'LOG_RECORD' => true, // 开启日志记录   

	'DB_PREFIX' => '',

	'UPLOAD_PATH'=>'./Public/Upload',

	'LOG_RECORD_LEVEL'  =>  array('EMERG','ALERT','CRIT','ERR','WARN','NOTICE','INFO','DEBUG','SQL'), 
	//'LOG_RECORD_LEVEL'  =>  array('EMERG','ALERT','CRIT','ERR'), 
	//自动导入工具类
	'AUTO_LOAD_PATH'=> 'Think.Util.,ORG.Util.',
	'TMPL_ACTION_ERROR'     => 'Public:error', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'   => 'Public:success', // 默认成功跳转对应的模板文件
	'DB_FIELDTYPE_CHECK'=>true,  // 开启字段类型验证
	
	'PROJECT_HOME'=>'D:\\hm',
	
	'WWW'=>'http://www.local-dev.cn',
	'IMG'=>'http://img.local-dev.cn',
	'STATIC'=> 'http://static.local-dev.cn',
	);

