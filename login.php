<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 管理员登录

require_once 'config/database.php';

// 开始会话
session_start();

// 生成验证码
function generate_captcha() {
    header('Content-type: image/png');
    $im = imagecreatetruecolor(120, 40);
    $bg = imagecolorallocate($im, 255, 255, 255);
    imagefill($im, 0, 0, $bg);
    $text_color = imagecolorallocate($im, 26, 109, 243);
    
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $captcha = '';
    for ($i = 0; $i < 4; $i++) {
        $captcha .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    $_SESSION['captcha'] = $captcha;
    
    for ($i = 0; $i < 5; $i++) {
        $color = imagecolorallocate($im, rand(0, 150), rand(0, 150), rand(0, 150));
        imageline($im, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $color);
    }
    
    for ($i = 0; $i < 50; $i++) {
        $color = imagecolorallocate($im, rand(0, 150), rand(0, 150), rand(0, 150));
        imagesetpixel($im, rand(0, 120), rand(0, 40), $color);
    }
    
    $font = 5;
    $x = 20;
    $y = 15;
    for ($i = 0; $i < 4; $i++) {
        imagestring($im, $font, $x, $y + rand(-5, 5), $captcha[$i], $text_color);
        $x += 25;
    }
    
    imagepng($im);
    imagedestroy($im);
    exit;
}

// 检查登录次数限制
function check_login_limit($ip) {
    $time = time() - 3600;
    $count = get_one('SELECT COUNT(*) as count FROM logs WHERE type = ? AND ip = ? AND (content LIKE ? OR content LIKE ?) AND time > FROM_UNIXTIME(?)', array('security', $ip, '%密码错误%', '%验证码错误%', $time));
    return $count['count'] >= 5;
}

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captcha = strtolower($_POST['captcha']);
    $ip = get_client_ip();
    
    if (strtolower($captcha) != strtolower($_SESSION['captcha'])) {
        add_log('security', '验证码错误', $ip);
        echo '<script>alert("验证码错误");history.back();</script>';
        exit;
    }
    
    if (check_login_limit($ip)) {
        add_log('security', '登录次数过多，已被限制', $ip);
        echo '<script>alert("登录次数过多，已被限制");history.back();</script>';
        exit;
    }
    
    $admin = get_one('SELECT * FROM config WHERE admin_user = ?', array($username));
    if (!$admin) {
        add_log('security', '用户名不存在', $ip);
        echo '<script>alert("用户名或密码错误");history.back();</script>';
        exit;
    }
    
    if (!password_verify($password, $admin['admin_password'])) {
        add_log('security', '密码错误', $ip);
        echo '<script>alert("用户名或密码错误");history.back();</script>';
        exit;
    }
    
    $cookie = md5(uniqid(rand(), true));
    $expire_time = time() + 86400;
    
    execute('UPDATE config SET cookie = ?, expire_time = ?, admin_ip = ? WHERE id = ?', array($cookie, $expire_time, $ip, $admin['id']));
    setcookie('admin_cookie', $cookie, $expire_time, '/', '', true, true);
    
    add_log('security', '登录成功', $ip);
    header('Location: index.php');
    exit;
}

// 生成验证码
if (isset($_GET['captcha'])) {
    generate_captcha();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 无礁节日自动邮件祝福系统</title>
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e6f0ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Microsoft YaHei', 'PingFang SC', Arial, sans-serif;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
            transition: all 0.3s ease;
        }
        .login-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
        }
        .login-icon:hover {
            transform: scale(1.1);
        }
        .login-title {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .login-subtitle {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
        }
        .form-control::placeholder {
            text-align: left;
            color: #999;
            padding: 0;
            margin: 0;
        }
        .form-control:focus {
            border-color: #0455EA;
            box-shadow: 0 0 0 3px rgba(4, 85, 234, 0.1);
            outline: none;
        }
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .captcha-input {
            flex: 1;
            padding: 12px 15px;
            height: 40px;
        }
        .captcha-image {
            cursor: pointer;
            width: 120px;
            height: 40px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }
        /* 确保所有输入框圆角一致 */
        input[type="text"],
        input[type="password"] {
            border-radius: 8px !important;
        }
        .captcha-image:hover {
            transform: scale(1.05);
            border-color: #0455EA;
        }
        .btn-primary {
            background-color: #0455EA;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            color: white;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #0344c4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(4, 85, 234, 0.3);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .form-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .form-footer p {
            margin: 0;
        }
        @media (max-width: 768px) {
            .login-container {
                padding: 30px 20px;
                margin: 0 20px;
            }
            .login-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            .login-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <img src="assets/img/logo.png" alt="无礁网络" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
            </div>
            <h1 class="login-title">无礁自动节日邮件祝福系统</h1>
            <p class="login-subtitle">管理员登录</p>
        </div>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username" class="form-label">用户名</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="username" name="username" required placeholder="请输入用户名">
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">密码</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required placeholder="请输入密码">
                </div>
            </div>
            <div class="form-group">
                <label for="captcha" class="form-label">验证码</label>
                <div class="captcha-container">
                    <input type="text" class="form-control captcha-input" id="captcha" name="captcha" required placeholder="请输入验证码">
                    <img src="login.php?captcha" class="captcha-image" alt="验证码" onclick="this.src='login.php?captcha&' + Math.random()">
                </div>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt mr-2"></i>登录
            </button>
            <div class="form-footer">
                <p>© 2026 无礁节日祝福系统</p>
            </div>
        </form>
    </div>
</body>
</html>