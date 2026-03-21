<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 日志管理

require_once 'auth.php';

// 获取日志类型
$log_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// 获取日志列表
if ($log_type == 'all') {
    $logs = get_all('SELECT * FROM logs ORDER BY time DESC');
} else {
    $logs = get_all('SELECT * FROM logs WHERE type = ? ORDER BY time DESC', array($log_type));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日志管理 - 无礁节日自动邮件祝福系统</title>
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
                    <a href="setting.php" class="sidebar-link">
                        <i class="fas fa-cog sidebar-icon"></i>
                        <span class="sidebar-text">系统设置</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="logs.php" class="sidebar-link active">
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
                <div id="message-container">
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                </div>
                

                
                <!-- 日志分类标签 -->
                <div class="mb-4">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $log_type == 'all' ? 'active' : ''; ?>" href="logs.php?type=all">
                                <i class="fas fa-list mr-2"></i>全部日志
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $log_type == 'security' ? 'active' : ''; ?>" href="logs.php?type=security">
                                <i class="fas fa-shield-alt mr-2"></i>安全日志
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $log_type == 'system' ? 'active' : ''; ?>" href="logs.php?type=system">
                                <i class="fas fa-cog mr-2"></i>系统日志
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $log_type == 'operation' ? 'active' : ''; ?>" href="logs.php?type=operation">
                                <i class="fas fa-user-cog mr-2"></i>操作日志
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- 清空日志按钮 -->
                <div class="mb-4">
                    <form method="POST" action="api.php" style="display: inline-block;" id="clearLogsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="type" value="<?php echo $log_type; ?>">
                        <input type="hidden" name="clear_logs" value="1">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt mr-2"></i>清空<?php echo $log_type == 'all' ? '所有' : ($log_type == 'security' ? '安全' : ($log_type == 'system' ? '系统' : '操作')); ?>日志
                        </button>
                    </form>
                </div>
                
                <!-- 日志列表 -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            <?php echo $log_type == 'all' ? '全部日志' : ($log_type == 'security' ? '安全日志' : ($log_type == 'system' ? '系统日志' : '操作日志')); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="logs-list">
                            <?php if (empty($logs)): ?>
                                <p class="text-center text-muted">暂无日志</p>
                            <?php else: ?>
                                <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>时间</th>
                                                <th>类型</th>
                                                <th>内容</th>
                                                <th>IP</th>
                                                <th>操作</th>
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
                                                    <td>
                                                        <form method="POST" action="api.php" style="display: inline-block;" name="delete_log_form">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $log['id']; ?>">
                                                            <input type="hidden" name="delete_log" value="1">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash mr-1"></i>删除
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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
        
        // 加载日志列表
        function loadLogs() {
            const logType = '<?php echo $log_type; ?>';
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'load_logs', type: logType, csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const logs = response.data;
                        let html = '';
                        if (logs.length === 0) {
                            html = '<p class="text-center text-muted">暂无日志</p>';
                        } else {
                            html = '<div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">' +
                                '<table class="table">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th>时间</th>' +
                                            '<th>类型</th>' +
                                            '<th>内容</th>' +
                                            '<th>IP</th>' +
                                            '<th>操作</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>';
                            logs.forEach(function(log) {
                                html += '<tr class="slide-in">' +
                                    '<td>' + log.time + '</td>' +
                                    '<td>' +
                                        '<span class="status-tag ' + (log.type == 'security' ? 'status-danger' : (log.type == 'system' ? 'status-success' : 'status-warning')) + '">' +
                                            (log.type == 'security' ? '安全' : (log.type == 'system' ? '系统' : '操作')) +
                                        '</span>' +
                                    '</td>' +
                                    '<td>' + log.content + '</td>' +
                                    '<td>' + log.ip + '</td>' +
                                    '<td>' +
                                        '<form method="POST" action="api.php" style="display: inline-block;" name="delete_log_form">' +
                                            '<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">' +
                                            '<input type="hidden" name="id" value="' + log.id + '">' +
                                            '<input type="hidden" name="delete_log" value="1">' +
                                            '<button type="submit" class="btn btn-sm btn-danger">' +
                                                '<i class="fas fa-trash mr-1"></i>删除' +
                                            '</button>' +
                                        '</form>' +
                                    '</td>' +
                                '</tr>';
                            });
                            html += '</tbody>' +
                                '</table>' +
                            '</div>';
                        }
                        $('#logs-list').html(html);
                        // 重新绑定删除表单事件
                        bindDeleteEvents();
                    }
                }
            });
        }
        
        // 绑定删除事件
        function bindDeleteEvents() {
            $('form[name="delete_log_form"]').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                if (confirm('确定要删除这条日志吗？')) {
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage(response.message, 'success');
                                // 立即刷新日志列表
                                loadLogs();
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
                } else {
                    // 重置表单提交状态
                    resetFormSubmit(form);
                }
            });
        }
        
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
            
            // 绑定初始删除事件
            bindDeleteEvents();
            
            // 清空日志
            $('#clearLogsForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                if (confirm('确定要清空所有<?php echo $log_type == 'all' ? '' : ($log_type == 'security' ? '安全' : ($log_type == 'system' ? '系统' : '操作')); ?>日志吗？')) {
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage(response.message, 'success');
                                // 立即刷新日志列表
                                loadLogs();
                            } else {
                                showMessage(response.message, 'error');
                            }
                            // 重置表单提交状态
                            resetFormSubmit(form);
                        },
                        error: function() {
                            showMessage('操作失败，请稍后重试', 'error');
                            // 重置表单提交状态
                            resetFormSubmit(form);
                        }
                    });
                } else {
                    // 重置表单提交状态
                    resetFormSubmit(form);
                }
            });
        });
    </script>
</body>
</html>