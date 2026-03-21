<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 退出登录

// 开始会话
session_start();

// 清除cookie
setcookie('admin_cookie', '', time() - 3600, '/');

// 清除会话
$_SESSION = array();
session_destroy();

// 跳转到登录页面
header('Location: login.php');
exit;