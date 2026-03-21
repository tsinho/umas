<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 邮件发送API

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 检查API密钥
if (!isset($_GET['key'])) {
    add_log('security', 'API接口访问缺少密钥', get_client_ip());
    die('Access denied');
}

$api_key = $_GET['key'];
$ip = get_client_ip();

// 获取配置
$config = get_one('SELECT * FROM config WHERE api_key = ?', array($api_key));
if (!$config) {
    add_log('security', 'API密钥错误', $ip);
    die('Invalid API key');
}

// 检查IP白名单
if (!empty($config['api_whitelist'])) {
    $whitelist = explode(',', $config['api_whitelist']);
    $whitelist = array_map('trim', $whitelist);
    if (!in_array($ip, $whitelist)) {
        add_log('security', 'IP不在白名单内：' . $ip, $ip);
        die('IP not in whitelist');
    }
}

// 检查API访问限制
function check_api_limit($ip) {
    $time = time() - 3600;
    $count = get_one('SELECT COUNT(*) as count FROM logs WHERE type = ? AND ip = ? AND content LIKE ? AND time > FROM_UNIXTIME(?)', array('security', $ip, '%API密钥错误%', $time));
    return $count['count'] >= 5;
}

// 检查任务执行状态
function check_task_executed() {
    $time = time() - 3600;
    $count1 = get_one('SELECT COUNT(*) as count FROM logs WHERE type = ? AND content LIKE ? AND time > FROM_UNIXTIME(?)', array('system', '%发送任务执行完成%', $time));
    $count2 = get_one('SELECT COUNT(*) as count FROM logs WHERE type = ? AND content LIKE ? AND time > FROM_UNIXTIME(?)', array('system', '%发送邮件成功%', $time));
    $count3 = get_one('SELECT COUNT(*) as count FROM logs WHERE type = ? AND content LIKE ? AND time > FROM_UNIXTIME(?)', array('system', '%发送邮件失败%', $time));
    return $count1['count'] > 0 || $count2['count'] > 0 || $count3['count'] > 0;
}

// 检查API访问限制
if (check_api_limit($ip)) {
    add_log('security', 'API接口访问次数过多，已被限制', $ip);
    die('Access limited');
}

// 检查任务执行状态
if (check_task_executed()) {
    add_log('security', '发送任务已在一小时内执行过', $ip);
    die('Task already executed');
}

// 并发处理
$lock_file = __DIR__ . '/../tmp/lock/send_task.lock';

if (!is_dir(dirname($lock_file))) {
    mkdir(dirname($lock_file), 0755, true);
}

if (file_exists($lock_file)) {
    $lock_time = file_get_contents($lock_file);
    if (time() - $lock_time < 300) {
        add_log('security', '发送任务正在执行中', $ip);
        die('Task is running');
    } else {
        unlink($lock_file);
    }
}

file_put_contents($lock_file, time());

// 检查系统状态
if ($config['status'] == 1) {
    add_log('system', '系统已暂停运行，发送任务未执行', $ip);
    die('System is paused');
}

// 清理过期日志
function clean_logs($logs_keep) {
    $logs_keep_array = array();
    $logs_keep_parts = explode(';', $logs_keep);
    foreach ($logs_keep_parts as $part) {
        if (!empty($part)) {
            $part_data = explode(':', $part);
            if (count($part_data) == 2) {
                list($type, $time) = $part_data;
                if (is_numeric($time) && $time > 0) {
                    $logs_keep_array[$type] = $time;
                }
            }
        }
    }
    
    if (!isset($logs_keep_array['system']) || $logs_keep_array['system'] < 2592000) {
        $logs_keep_array['system'] = 2592000;
    }
    if (!isset($logs_keep_array['security']) || $logs_keep_array['security'] < 2592000) {
        $logs_keep_array['security'] = 2592000;
    }
    
    foreach ($logs_keep_array as $type => $time) {
        try {
            $expire_time = time() - $time;
            execute('DELETE FROM logs WHERE type = ? AND time < FROM_UNIXTIME(?)', array($type, $expire_time));
        } catch (Exception $e) {
            add_log('system', '日志清理失败: ' . $e->getMessage(), '127.0.0.1');
        }
    }
}

// 执行日志清理
clean_logs($config['logs_keep']);

// 获取当前日期
$current_date = date('Y-m-d');
$current_month_day = date('m-d');

// 查询需要发送的任务
$tasks = get_all('SELECT d.*, c.mail, c.name, c.birthday, c.status as contact_status, t.title, t.content FROM data d LEFT JOIN contacts c ON d.contact_id = c.id LEFT JOIN templates t ON d.template_id = t.id WHERE (d.is_recurring = 0 AND d.time = ?) OR (d.is_recurring = 1 AND d.time = ?)', array($current_date, $current_month_day));

// 发送邮件
function send_email($config, $to, $name, $subject, $body) {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'error' => '邮箱格式错误');
    }
    
    $name = str_replace(array("\r", "\n"), '', $name);
    $subject = str_replace(array("\r", "\n"), '', $subject);
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host = $config['send_smtp'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['send_mail'];
        $mail->Password = $config['send_key'];
        
        if ($config['send_port'] == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['send_port'];
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom($config['send_mail'], $config['send_name']);
        $mail->addAddress($to, $name);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return array('success' => true, 'error' => '');
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
}

// 替换模板变量
function replace_variables($content, $contact, $config) {
    $allowed_variables = array('{time}', '{send}', '{name}', '{age}', '{days}', '{day}');
    
    $replacements = array(
        '{time}' => date('Y-m-d H:i:s'),
        '{send}' => htmlspecialchars($config['send_name'], ENT_QUOTES, 'UTF-8'),
        '{name}' => htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8')
    );
    
    if (!empty($contact['birthday'])) {
        try {
            $birthday = new DateTime($contact['birthday']);
            $today = new DateTime();
            $age = $today->diff($birthday)->y;
            $days = $today->diff($birthday)->days;
            $replacements['{age}'] = (int)$age;
            $replacements['{days}'] = (int)$days;
            $replacements['{day}'] = (int)$days;
        } catch (Exception $e) {
            $replacements['{age}'] = '未知';
            $replacements['{days}'] = '未知';
            $replacements['{day}'] = '未知';
        }
    }
    
    foreach ($allowed_variables as $var) {
        if (isset($replacements[$var])) {
            $content = str_replace($var, $replacements[$var], $content);
        }
    }
    
    return $content;
}

// 执行发送任务
$success_count = 0;
$fail_count = 0;
$total_count = count($tasks);

foreach ($tasks as $task) {
    if ($task['contact_status'] == 1) {
        add_log('system', '联系人 ' . $task['name'] . ' 已停用，跳过发送', '127.0.0.1');
        $fail_count++;
        continue;
    }
    
    $subject = replace_variables($task['title'], $task, $config);
    $body = replace_variables($task['content'], $task, $config);
    
    $result = send_email($config, $task['mail'], $task['name'], $subject, $body);
    if ($result['success']) {
        add_log('system', '无礁自动邮件系统 - 发送邮件成功：' . $task['name'] . ' (' . $task['mail'] . ') - ' . $task['event_name'], '127.0.0.1');
        $success_count++;
        
        if ($task['is_recurring'] == 0) {
            execute('DELETE FROM data WHERE id = ?', array($task['id']));
            add_log('system', '无礁自动邮件系统 - 删除一次性任务：' . $task['event_name'], '127.0.0.1');
        }
    } else {
        add_log('system', '无礁自动邮件系统 - 发送邮件失败：' . $task['name'] . ' (' . $task['mail'] . ') - ' . $task['event_name'] . '，错误原因：' . $result['error'], '127.0.0.1');
        $fail_count++;
    }
}

// 记录总报告
$report = '发送任务执行完成，共 ' . $total_count . ' 个任务，成功 ' . $success_count . ' 个，失败 ' . $fail_count . ' 个';
add_log('system', $report, '127.0.0.1');

// 发送邮件提醒
if ($config['mail_reminder'] >= 2) {
    $original_send_name = $config['send_name'];
    $config['send_name'] = '无礁自动邮件系统';
    $reminder_result = send_email($config, $config['admin_mail'], '管理员', '系统发送任务报告', $report);
    if (!$reminder_result['success']) {
        add_log('system', '发送邮件提醒失败，错误原因：' . $reminder_result['error'], '127.0.0.1');
    }
    $config['send_name'] = $original_send_name;
}

// 删除锁文件
if (file_exists($lock_file)) {
    unlink($lock_file);
}

echo $report;