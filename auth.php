<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 管理员认证

require_once 'config/database.php';

// 开始会话
session_start();

// 认证函数
function auth() {
    if (!isset($_COOKIE['admin_cookie'])) {
        header('Location: login.php');
        exit;
    }
    
    $cookie = $_COOKIE['admin_cookie'];
    if (!preg_match('/^[a-f0-9]{32}$/', $cookie)) {
        header('Location: login.php');
        exit;
    }
    
    $ip = get_client_ip();
    $config = get_one('SELECT * FROM config WHERE cookie = ?', array($cookie));
    
    if (!$config) {
        header('Location: login.php');
        exit;
    }
    
    if (time() > $config['expire_time']) {
        echo '<script>alert("登录信息已过期，请重新登录");location.href="login.php";</script>';
        exit;
    }
    
    if ($config['admin_ip'] != $ip) {
        echo '<script>alert("登录信息已过期，请重新登录");location.href="login.php";</script>';
        exit;
    }
    
    $_SESSION['admin_login'] = true;
    return $config;
}

// 执行认证
$config = auth();