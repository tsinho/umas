<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 仪表盘

require_once 'auth.php';

// 获取联系人数量
$contact_count = get_one('SELECT COUNT(*) as count FROM contacts');
$contact_count = $contact_count['count'];

// 获取模板数量
$template_count = get_one('SELECT COUNT(*) as count FROM templates');
$template_count = $template_count['count'];

// 获取所有日志数量（用于统计卡片）
$log_count = get_one('SELECT COUNT(*) as count FROM logs');
$log_count = $log_count['count'];

// 获取发件队列数量
$task_count = get_one('SELECT COUNT(*) as count FROM data');
$task_count = $task_count['count'];

// 获取最近的日志（只获取前5条）
$logs = get_all('SELECT * FROM logs ORDER BY time DESC LIMIT 5');

// 获取服务器信息
$server_info = array(
    'PHP版本' => PHP_VERSION,
    '服务器软件' => $_SERVER['SERVER_SOFTWARE'],
    '操作系统' => PHP_OS,
    '最大执行时间' => ini_get('max_execution_time') . '秒',
    '内存限制' => ini_get('memory_limit')
);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>仪表盘 - 无礁节日自动邮件祝福系统</title>
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
                    <a href="index.php" class="sidebar-link active">
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
                    <a href="setting.php" class="sidebar-link">
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
                <h1 class="page-title mb-4">仪表盘</h1>
                
                <!-- 统计卡片 -->
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card fade-in">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo $contact_count; ?></div>
                            <div class="stat-label">联系人数量</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-number"><?php echo $template_count; ?></div>
                            <div class="stat-label">模板数量</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                            <div class="stat-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="stat-number"><?php echo $log_count; ?></div>
                            <div class="stat-label">日志数量</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                            <div class="stat-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="stat-number"><?php echo $task_count; ?></div>
                            <div class="stat-label">发件队列</div>
                        </div>
                    </div>
                </div>
                
                <!-- 服务器信息 -->
                <div class="card mt-4 fade-in">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-server"></i>
                            服务器信息
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($server_info as $key => $value): ?>
                                <div class="col-md-6 mb-3">
                                    <strong><?php echo $key; ?>:</strong> <?php echo $value; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-md-6 mb-3">
                                <strong>服务器时间:</strong> <span class="time-display"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 日志信息和开发者信息并排显示 -->
                <div class="row mt-4" style="display: flex; flex-wrap: wrap;">
                    <!-- 最近日志 -->
                    <div class="col-md-6" style="display: flex; flex: 1; flex-direction: column;">
                        <div class="card fade-in" style="flex: 1;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history"></i>
                                    最近日志
                                </h3>
                            </div>
                            <div class="card-body" style="min-height: 200px;">
                                <?php if (empty($logs)): ?>
                                    <p class="text-center text-muted">暂无日志</p>
                                <?php else: ?>
                                    <div class="table-container">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>时间</th>
                                                    <th>类型</th>
                                                    <th>内容</th>
                                                    <th>IP</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($logs as $log): ?>
                                                    <tr class="slide-in">
                                                        <td><?php echo $log['time']; ?></td>
                                                        <td>
                                                            <span class="status-tag <?php echo $log['type'] == 'security' ? 'status-danger' : ($log['type'] == 'system' ? 'status-success' : 'status-warning'); ?>">
                                                                <?php echo $log['type'] == 'security' ? '安全' : ($log['type'] == 'system' ? '系统' : '操作'); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $log['content']; ?></td>
                                                        <td><?php echo $log['ip']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 开发者信息 -->
                    <div class="col-md-6" style="display: flex; flex: 1; flex-direction: column;">
                        <div class="card fade-in" style="flex: 1;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle"></i>
                                    开发者信息
                                </h3>
                            </div>
                            <div class="card-body" style="min-height: 200px;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p><strong>系统名称：</strong>无礁节日祝福邮件自动发送系统</p>
                                        <p><strong>作者：</strong>无礁</p>
                                        <p><strong>QQ：</strong>1722791510</p>
                                        <p><strong>邮箱：</strong>tsinho@qq.com</p>
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
        });
    </script>
</body>
</html>