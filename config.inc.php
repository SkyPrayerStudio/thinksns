<?php
if (!defined('SITE_PATH')) exit();

return array(
    // 数据库常用配置
    'DB_TYPE'            => 'mysql',             // 数据库类型
    'DB_HOST'            => SAE_MYSQL_HOST_M.",".SAE_MYSQL_HOST_S,  // 数据库服务器地址 主,从
    'DB_NAME'            => SAE_MYSQL_DB,        // 数据库名
    'DB_USER'            => SAE_MYSQL_USER,      // 数据库用户名
    'DB_PWD'             => SAE_MYSQL_PASS,      // 数据库密码
    'DB_PORT'            => SAE_MYSQL_PORT,      // 数据库端口
    'DB_DEPLOY_TYPE'     => 1,                   // 是否开启分布式数据库 0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'     => true,                // 是否开启数据库读写分离
    'DB_PREFIX'          => 'ts_',               // 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
    'DB_CHARSET'         => 'utf8',              // 数据库编码
    'DB_FIELDS_CACHE'    => false,               // 启用字段缓存
    //'COOKIE_DOMAIN'    => '.thinksns.com',    // COOKIE域,请替换成你自己的域名 以.开头
    // COOKIE 加密串
    'SECURE_CODE'       =>  'T123!Q@W#E$R',	// COOKIE加密串.每个站点都不同,否则有安全漏洞.
    // 默认应用
    'DEFAULT_APPS'       => array('api', 'admin', 'home', 'myop', 'weibo', 'wap', 'w3g'),
    // 是否开启调试模式 (开启AllInOne模式时设置无效, 将自动置为false)
    'APP_DEBUG'          => false,
    // 是否开启URL Rewrite
    'URL_ROUTER_ON'      => false,
    // 是否开启模版缓存
    'TMPL_CACHE_ON'      => false,

	// SAE相关设置
	'TS_STORAGE_TYPE'	=> 'SAEST', //无需修改
	'TS_STORAGE_DOMAIN'	=> 'uploads',//修改为你在SAE storage中创建的域
	'TS_STORAGE_URL'	=> 'http://saeapppname-storagedomain.stor.sinaapp.com',//修改为你在SAE中的应用名和附件存储的域

	'TS_CACHE_TYPE'		=> 'SAEMC',//无需修改
	'TS_CACHE_SERVER'	=> '127.0.0.1:11211',//无需修改
	'TS_THUMB_TYPE'		=> 'SAE',//无需修改
    // 404页面
    'ERROR_PAGE'         => U('home/Public/error404'),
);