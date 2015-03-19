<?php

if (!defined('THINK_PATH'))
    exit();
$array = array(
    'APP_DEBUG' => true,
    'DB_TYPE' => 'mysqli',
    'DB_HOST' => '211.100.56.166',
    'DB_NAME' => 'dopool_cpreport',
    'DB_USER' => 'root',
    'DB_PWD' => 'dopool!@#',
    'DB_PORT' => 3306,
    'USER_AUTH_ON' => true,
    'URL_ROUTER_ON' => true,
    'TOKEN_ON' => false,
    'USER_AUTH_TYPE' => 1, // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTN_KEY' => 'admin', // 用户认证SESSION标记
    'ADMIN_AUTH_KEY' => 'administrator',
    'USER_AUTH_MODEL' => 'User', // 默认验证数据表模�?
    'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式
    'USER_AUTH_GATEWAY' => '/LoginPage/index', // 默认认证网关
    'NOT_AUTH_MODULE' => 'LoginPage,History', // 默认无需认证模块
    'REQUIRE_AUTH_MODULE' => '', // 默认需要认证模�?
    'NOT_AUTH_ACTION' => '', // 默认无需认证操作
    'REQUIRE_AUTH_ACTION' => '', // 默认需要认证操�?
    'GUEST_AUTH_ON' => false, // 是否开启游客授权访�?
    'GUEST_AUTH_ID' => 0, // 游客的用户ID
    'SESSION_EXPIRE' => '600',
    'DEFAULT_MODULE' => 'Home', // 默认模块名称
    'DEFAULT_CONTROLLER' => 'LoginPage',
    'DEFAULT_ACTION' => 'index', // 默认操作名称
//    "DEFAULT_THEME" => 'html',
    'DB_LIKE_FIELDS' => 'title|remark',
    'RBAC_ROLE_TABLE' => 'role',
    'RBAC_USER_TABLE' => 'role_user',
    'RBAC_ACCESS_TABLE' => 'access',
    'RBAC_NODE_TABLE' => 'node',
    'VAR_PAGE' => 'p',
    'DB_A3_DSN' => 'mysql://root:dopool!@#@211.100.56.166:3306/dopool_a3_data',
    'DB_DATA_DSN' => 'mysql://root:dopool!@#@211.100.56.166:3306/star_data',
);
#return array_merge($config,$array);
return $array;
