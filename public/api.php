<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/7
 * Time: 3:22 PM
 */

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 隐藏url路由模块,绑定到index模块
define('BIND_MODULE', 'api');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';