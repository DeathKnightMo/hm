<?php

define('NO_CACHE_RUNTIME',True);
define('APP_DEBUG', true);

//设置对编译缓存的内容是否进行去空白和注释
define('STRIP_RUNTIME_SPACE',false);

// 加载框架入口文件 
require("../ThinkPHP/ThinkPHP.php");


//实例化一个网站应用实例
App::run();

?>