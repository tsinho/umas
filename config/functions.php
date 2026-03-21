<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 系统公共函数

// 导入PHPMailer命名空间
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 获取客户端IP
function get_client_ip() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '127.0.0.1';
    }
    return $ip;
}

// 记录日志
function add_log($type, $content, $ip) {
    execute('INSERT INTO logs (type, content, ip) VALUES (?, ?, ?)', array($type, $content, $ip));
    if ($type == 'security') {
        $config = get_one('SELECT * FROM config WHERE id = 1');
        if ($config && ($config['mail_reminder'] == 1 || $config['mail_reminder'] == 3)) {
            send_security_log_email($config, $content, $ip);
        }
    }
}

// 发送安全日志邮件
function send_security_log_email($config, $content, $ip) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    
    if (!filter_var($config['admin_mail'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
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
        
        $mail->setFrom($config['send_mail'], '无礁自动邮件系统');
        $mail->addAddress($config['admin_mail'], '管理员');
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Subject = '【安全提醒】系统安全日志';
        $mail->Body = '<h3>安全日志提醒</h3><p><strong>时间：</strong>' . date('Y-m-d H:i:s') . '</p><p><strong>内容：</strong>' . htmlspecialchars($content) . '</p><p><strong>IP地址：</strong>' . htmlspecialchars($ip) . '</p><p>此邮件由系统自动发送，请勿回复。</p>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        execute('INSERT INTO logs (type, content, ip) VALUES (?, ?, ?)', array('system', '安全日志邮件发送失败: ' . $e->getMessage(), '127.0.0.1'));
        return false;
    }
}

// 统一错误处理
function handle_error($message, $log_type = 'system', $ip = '') {
    add_log($log_type, $message, $ip);
    
    if (strpos($message, '数据库') !== false) {
        return '数据库操作失败，请稍后重试';
    } elseif (strpos($message, '邮箱') !== false) {
        return '邮箱格式错误，请检查输入';
    } elseif (strpos($message, '权限') !== false) {
        return '权限不足，无法执行此操作';
    } elseif (strpos($message, '网络') !== false || strpos($message, 'SMTP') !== false) {
        return '网络连接失败，请检查网络设置';
    } else {
        return '操作失败，请稍后重试';
    }
}

// 生成CSRF令牌
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 验证CSRF令牌
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 统一输入验证
function validate_input($input, $type = 'string', $max_length = 255) {
    $input = trim($input);
    if (strlen($input) > $max_length) {
        $input = substr($input, 0, $max_length);
    }
    
    switch ($type) {
        case 'email':
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return '';
            }
            break;
        case 'url':
            if (!filter_var($input, FILTER_VALIDATE_URL)) {
                return '';
            }
            break;
        case 'number':
            if (!is_numeric($input)) {
                return '';
            }
            break;
        case 'integer':
            if (!filter_var($input, FILTER_VALIDATE_INT)) {
                return '';
            }
            break;
        case 'string':
        default:
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            break;
    }
    
    return $input;
}

// 资源限制处理
function check_resource_limit($ip, $action, $limit = 10, $window = 3600) {
    $limit_key = md5($ip . '_' . $action);
    $limit_file = __DIR__ . '/../tmp/limits/' . $limit_key . '.txt';
    
    if (!is_dir(dirname($limit_file))) {
        mkdir(dirname($limit_file), 0755, true);
    }
    
    if (file_exists($limit_file)) {
        $data = json_decode(file_get_contents($limit_file), true);
        $current_time = time();
        
        if ($current_time - $data['timestamp'] < $window) {
            if ($data['count'] >= $limit) {
                return false;
            }
            $data['count']++;
        } else {
            $data = array(
                'timestamp' => $current_time,
                'count' => 1
            );
        }
    } else {
        $data = array(
            'timestamp' => time(),
            'count' => 1
        );
    }
    
    file_put_contents($limit_file, json_encode($data));
    return true;
}