<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 系统设置

require_once 'auth.php';

// 获取当前标签
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'system';

// 解析日志保留时间
$logs_keep_array = array();
$logs_keep_parts = explode(';', $config['logs_keep']);
foreach ($logs_keep_parts as $part) {
    if (!empty($part)) {
        list($type, $time) = explode(':', $part);
        $logs_keep_array[$type] = $time;
    }
}

// 生成定时任务URL（使用GET请求）
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'];
$currentPath = dirname($_SERVER['REQUEST_URI']);
// 处理路径，确保没有多余的斜杠
$currentPath = str_replace('\\', '/', $currentPath);
if ($currentPath == '.') {
    $currentPath = '';
}
$api_url = $protocol . '://' . $domain . rtrim($currentPath, '/') . '/api/send.php';
$api_key = $config['api_key'];
$api_whitelist = isset($config['api_whitelist']) ? $config['api_whitelist'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - 无礁节日自动邮件祝福系统</title>
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="app-container">
        <!-- 侧边栏 -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">无礁自动邮件祝福系统</div>
            </div>
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="index.php" class="sidebar-link">
                        <i class="fas fa-tachometer-alt sidebar-icon"></i>
                        <span class="sidebar-text">仪表盘</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="contact.php" class="sidebar-link">
                        <i class="fas fa-users sidebar-icon"></i>
                        <span class="sidebar-text">联系人管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="template.php" class="sidebar-link">
                        <i class="fas fa-file-alt sidebar-icon"></i>
                        <span class="sidebar-text">模板管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="data.php" class="sidebar-link">
                        <i class="fas fa-envelope sidebar-icon"></i>
                        <span class="sidebar-text">发件管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="setting.php" class="sidebar-link active">
                        <i class="fas fa-cog sidebar-icon"></i>
                        <span class="sidebar-text">系统设置</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="logs.php" class="sidebar-link">
                        <i class="fas fa-history sidebar-icon"></i>
                        <span class="sidebar-text">日志管理</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="logout.php" class="sidebar-link">
                        <i class="fas fa-sign-out-alt sidebar-icon"></i>
                        <span class="sidebar-text">退出登录</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- 顶部导航栏 -->
        <nav class="top-nav">
            <div class="nav-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar();">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span>管理员</span>
                    <div class="user-menu">
                        <a href="setting.php" class="user-menu-item">
                            <i class="fas fa-cog"></i>系统设置
                        </a>
                        <a href="logout.php" class="user-menu-item">
                            <i class="fas fa-sign-out-alt"></i>退出登录
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- 主内容区 -->
        <main class="main-content">
            <div class="page-content">
                <!-- 消息容器 -->
                <div id="message-container"></div>
                
                <!-- 页面标题 -->
                <h1 class="page-title mb-4">系统设置</h1>
                
                <!-- 设置分类标签 -->
                <div class="mb-4">
                    <ul class="nav nav-tabs" id="settingsTabs">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab == 'system' ? 'active' : ''; ?>" href="setting.php?tab=system">
                                <i class="fas fa-cogs mr-2"></i>系统设置
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab == 'logs' ? 'active' : ''; ?>" href="setting.php?tab=logs">
                                <i class="fas fa-file-lines mr-2"></i>日志设置
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab == 'smtp' ? 'active' : ''; ?>" href="setting.php?tab=smtp">
                                <i class="fas fa-envelope mr-2"></i>SMTP设置
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_tab == 'cron' ? 'active' : ''; ?>" href="setting.php?tab=cron">
                                <i class="fas fa-clock mr-2"></i>定时任务设置
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- 设置内容 -->
                <div class="tab-content">
                    <!-- 系统设置 -->
                    <div class="tab-pane <?php echo $current_tab == 'system' ? 'active' : ''; ?>" id="system">
                        <div class="card fade-in">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cogs"></i>
                                    系统设置
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="api.php" id="systemSettingsForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <div class="form-group">
                                        <label for="admin_user" class="form-label">管理员账号</label>
                                        <input type="text" class="form-control" id="admin_user" name="admin_user" value="<?php echo $config['admin_user']; ?>" required placeholder="请输入管理员账号">
                                    </div>
                                    <div class="form-group">
                                        <label for="admin_password" class="form-label">管理员密码（留空不修改）</label>
                                        <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="请输入管理员密码">
                                    </div>
                                    <div class="form-group">
                                        <label for="admin_mail" class="form-label">管理员收信邮箱</label>
                                        <input type="email" class="form-control" id="admin_mail" name="admin_mail" value="<?php echo $config['admin_mail']; ?>" required placeholder="请输入管理员邮箱">
                                    </div>
                                    <div class="form-group">
                                        <label for="send_name" class="form-label">发送人昵称</label>
                                        <input type="text" class="form-control" id="send_name" name="send_name" value="<?php echo $config['send_name']; ?>" required placeholder="请输入发送人昵称">
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="form-label">系统状态</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="0" <?php echo $config['status'] == 0 ? 'selected' : ''; ?>>正常运行</option>
                                            <option value="1" <?php echo $config['status'] == 1 ? 'selected' : ''; ?>>暂停运行</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="system_settings" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>保存设置
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 日志设置 -->
                    <div class="tab-pane <?php echo $current_tab == 'logs' ? 'active' : ''; ?>" id="logs">
                        <div class="card fade-in">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-lines"></i>
                                    日志设置
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="api.php" id="logSettingsForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <div class="form-group">
                                        <label for="system_logs" class="form-label">系统日志保留时间（秒）</label>
                                        <input type="number" class="form-control" id="system_logs" name="system_logs" value="<?php echo $logs_keep_array['system']; ?>" required placeholder="请输入系统日志保留时间">
                                    </div>
                                    <div class="form-group">
                                        <label for="security_logs" class="form-label">安全日志保留时间（秒）</label>
                                        <input type="number" class="form-control" id="security_logs" name="security_logs" value="<?php echo $logs_keep_array['security']; ?>" required placeholder="请输入安全日志保留时间">
                                    </div>
                                    <div class="form-group">
                                        <label for="operation_logs" class="form-label">操作日志保留时间（秒）</label>
                                        <input type="number" class="form-control" id="operation_logs" name="operation_logs" value="<?php echo $logs_keep_array['operation']; ?>" required placeholder="请输入操作日志保留时间">
                                    </div>
                                    <div class="form-group">
                                        <label for="mail_reminder" class="form-label">日志提醒方式</label>
                                        <select class="form-control" id="mail_reminder" name="mail_reminder">
                                            <option value="0" <?php echo $config['mail_reminder'] == 0 ? 'selected' : ''; ?>>不提醒</option>
                                            <option value="1" <?php echo $config['mail_reminder'] == 1 ? 'selected' : ''; ?>>仅提醒安全日志</option>
                                            <option value="2" <?php echo $config['mail_reminder'] == 2 ? 'selected' : ''; ?>>仅提醒系统日志</option>
                                            <option value="3" <?php echo $config['mail_reminder'] == 3 ? 'selected' : ''; ?>>都提醒</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="log_settings" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>保存设置
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SMTP设置 -->
                    <div class="tab-pane <?php echo $current_tab == 'smtp' ? 'active' : ''; ?>" id="smtp">
                        <div class="card fade-in">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-envelope"></i>
                                    SMTP设置
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="api.php" id="smtpSettingsForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <div class="form-group">
                                        <label for="send_smtp" class="form-label">SMTP服务器</label>
                                        <input type="text" class="form-control" id="send_smtp" name="send_smtp" value="<?php echo $config['send_smtp']; ?>" required placeholder="请输入SMTP服务器地址">
                                    </div>
                                    <div class="form-group">
                                        <label for="send_port" class="form-label">SMTP端口</label>
                                        <input type="number" class="form-control" id="send_port" name="send_port" value="<?php echo $config['send_port']; ?>" required placeholder="请输入SMTP端口">
                                    </div>
                                    <div class="form-group">
                                        <label for="send_mail" class="form-label">发信邮箱</label>
                                        <input type="email" class="form-control" id="send_mail" name="send_mail" value="<?php echo $config['send_mail']; ?>" required placeholder="请输入发信邮箱">
                                    </div>
                                    <div class="form-group">
                                        <label for="send_key" class="form-label">发信授权码</label>
                                        <input type="password" class="form-control" id="send_key" name="send_key" value="<?php echo $config['send_key']; ?>" required placeholder="请输入发信授权码">
                                    </div>
                                    <input type="hidden" name="smtp_settings" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>保存设置
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 定时任务设置 -->
                    <div class="tab-pane <?php echo $current_tab == 'cron' ? 'active' : ''; ?>" id="cron">
                        <div class="card fade-in">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clock"></i>
                                    定时任务设置
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="api.php" id="cronSettingsForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <div class="form-group">
                                        <label class="form-label">定时任务API密钥</label>
                                        <input type="text" class="form-control" value="<?php echo $config['api_key']; ?>" readonly onclick="this.select();">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-warning" id="resetApiKeyBtn">
                                                <i class="fas fa-sync-alt mr-2"></i>重置API密钥
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">完整定时任务URL</label>
                                        <input type="text" class="form-control" value="<?php echo $api_url; ?>?key=<?php echo $api_key; ?>" readonly onclick="this.select();">
                                    </div>
                                    <div class="form-group">
                                        <label for="api_whitelist" class="form-label">API白名单（多个IP用英文逗号隔开）</label>
                                        <input type="text" class="form-control" id="api_whitelist" name="api_whitelist" value="<?php echo $api_whitelist; ?>" placeholder="例如：127.0.0.1,192.168.1.1">
                                        <small class="form-text text-muted">留空表示不限制IP</small>
                                    </div>
                                    <input type="hidden" name="cron_settings" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>保存设置
                                    </button>
                                </form>
                                
                                <!-- 重置API密钥表单 -->
                                <form method="POST" action="api.php" id="resetApiKeyForm" style="display: none;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="reset_api_key" value="1">
                                </form>
                                <div class="info-box mt-4" style="background-color: #e6f7ff; border: 1px solid #91d5ff; border-radius: 8px; padding: 16px; position: relative;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                        <h5 style="color: #1890ff; margin: 0; display: flex; align-items: center;">
                                            <i class="fas fa-info-circle mr-2"></i>设置说明
                                        </h5>
                                        <button type="button" onclick="this.parentElement.parentElement.style.display='none';" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #999;">
                                            &times;
                                        </button>
                                    </div>
                                    <div style="color: #333; line-height: 1.6;">
                                        <p style="margin: 8px 0;">1. 请在宝塔面板中设置计划任务</p>
                                        <p style="margin: 8px 0;">2. 任务类型：访问URL-GET</p>
                                        <p style="margin: 8px 0;">3. 执行周期：每天</p>
                                        <p style="margin: 8px 0;">4. 执行时间：00:00</p>
                                        <p style="margin: 8px 0;">5. URL地址：</p>
                                        <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;"><?php echo $api_url; ?>?key=<?php echo $api_key; ?></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modern.js"></script>
    <script>
        // 判断是否为移动设备布局（基于宽高比）
        function isMobileLayout() {
            return window.innerHeight > window.innerWidth;
        }
        
        // 切换侧边栏
        function toggleSidebar() {
            if (isMobileLayout()) {
                // 移动端：切换show类
                $('.sidebar').toggleClass('show');
            } else {
                // 电脑端：切换collapsed类
                $('.sidebar').toggleClass('collapsed');
            }
        }
        
        // 点击非侧边栏区域关闭侧边栏（移动端）
        $(document).on('click', function(e) {
            if (isMobileLayout() && $('.sidebar').hasClass('show') && !$(e.target).closest('.sidebar').length && !$(e.target).closest('.sidebar-toggle-btn').length) {
                $('.sidebar').removeClass('show');
            }
        });
        
        // 初始化
        $(document).ready(function() {
            // 根据宽高比设置初始侧边栏状态
            if (isMobileLayout()) {
                // 移动端默认不展开侧边栏
                if ($('.sidebar').hasClass('show')) {
                    $('.sidebar').removeClass('show');
                }
            } else {
                // 电脑端默认展开侧边栏
                if ($('.sidebar').hasClass('collapsed')) {
                    $('.sidebar').removeClass('collapsed');
                }
            }
            
            // 系统设置表单提交
            $('#systemSettingsForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.message, 'success');
                            // 刷新页面
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showMessage(response.message, 'error');
                            // 重置表单提交状态
                            resetFormSubmit(form);
                        }
                    },
                    error: function() {
                        showMessage('操作失败，请稍后重试', 'error');
                        // 重置表单提交状态
                        resetFormSubmit(form);
                    }
                });
            });
            
            // 日志设置表单提交
            $('#logSettingsForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.message, 'success');
                            // 刷新页面
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showMessage(response.message, 'error');
                            // 重置表单提交状态
                            resetFormSubmit(form);
                        }
                    },
                    error: function() {
                        showMessage('操作失败，请稍后重试', 'error');
                        // 重置表单提交状态
                        resetFormSubmit(form);
                    }
                });
            });
            
            // SMTP设置表单提交
            $('#smtpSettingsForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.message, 'success');
                            // 刷新页面
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showMessage(response.message, 'error');
                            // 重置表单提交状态
                            resetFormSubmit(form);
                        }
                    },
                    error: function() {
                        showMessage('操作失败，请稍后重试', 'error');
                        // 重置表单提交状态
                        resetFormSubmit(form);
                    }
                });
            });
            
            // 重置API密钥按钮点击事件
            $('#resetApiKeyBtn').on('click', function() {
                if (confirm('确定要重置API密钥吗？')) {
                    const form = $('#resetApiKeyForm')[0];
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: $(form).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage(response.message, 'success');
                                // 刷新页面
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                showMessage(response.message, 'error');
                            }
                        },
                        error: function() {
                            showMessage('操作失败，请稍后重试', 'error');
                        }
                    });
                }
            });
            
            // 定时任务设置表单提交
            $('#cronSettingsForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.message, 'success');
                            // 刷新页面
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showMessage(response.message, 'error');
                            // 重置表单提交状态
                            resetFormSubmit(form);
                        }
                    },
                    error: function() {
                        showMessage('操作失败，请稍后重试', 'error');
                        // 重置表单提交状态
                        resetFormSubmit(form);
                    }
                });
            });
        });
    </script>
</body>
</html>