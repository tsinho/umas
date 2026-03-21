<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 数据库操作函数

// 包含公共函数
require_once __DIR__ . '/functions.php';

// 数据库连接配置
$db_config = array(
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'umas',
    'pass' => '123456',
    'dbname' => 'umas',
    'charset' => 'utf8mb4'
);

// 连接数据库
function connect_db() {
    global $db_config;
    try {
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('数据库连接失败：' . $e->getMessage());
    }
}

// 执行SQL查询
function query($sql, $params = array()) {
    $pdo = connect_db();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// 获取单条记录
function get_one($sql, $params = array()) {
    $stmt = query($sql, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取多条记录
function get_all($sql, $params = array()) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 执行增删改操作
function execute($sql, $params = array()) {
    $pdo = connect_db();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// 获取最后插入的ID
function last_insert_id() {
    $pdo = connect_db();
    return $pdo->lastInsertId();
}