<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 发件管理

require_once 'auth.php';

// 获取任务列表
$tasks = get_all('SELECT d.*, c.name as contact_name, c.mail as contact_mail, t.name as template_name FROM data d LEFT JOIN contacts c ON d.contact_id = c.id LEFT JOIN templates t ON d.template_id = t.id ORDER BY d.id DESC');

// 获取要编辑的任务
$edit_task = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_task = get_one('SELECT * FROM data WHERE id = ?', array($edit_id));
}

// 获取所有联系人和模板，用于下拉选择
$contacts = get_all('SELECT id, name, mail FROM contacts WHERE status = 0');
$templates = get_all('SELECT id, name FROM templates');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发件管理 - 无礁节日自动邮件祝福系统</title>
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
                    <a href="data.php" class="sidebar-link active">
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
                <div id="message-container">
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- 页面标题 -->
                <h1 class="page-title mb-4">发件管理</h1>
                
                <!-- 新建任务按钮 -->
                <div class="mb-4">
                    <button class="btn btn-primary" onclick="showModal('addTaskModal')">
                        <i class="fas fa-plus mr-2"></i>新建发送任务
                    </button>
                </div>
                
                <!-- 任务列表 -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            发送任务列表
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="tasks-list">
                            <?php if (empty($tasks)): ?>
                                <p class="text-center text-muted">暂无发送任务</p>
                            <?php else: ?>
                                <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>任务ID</th>
                                                <th>联系人</th>
                                                <th>模板</th>
                                                <th>事件名称</th>
                                                <th>发送时间</th>
                                                <th>任务类型</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tasks as $task): ?>
                                                <tr class="slide-in">
                                                    <td><?php echo $task['id']; ?></td>
                                                    <td><?php echo $task['contact_name']; ?><br><small class="text-muted"><?php echo $task['contact_mail']; ?></small></td>
                                                    <td><?php echo $task['template_name']; ?></td>
                                                    <td><?php echo $task['event_name']; ?></td>
                                                    <td><?php echo $task['time']; ?></td>
                                                    <td>
                                                        <span class="status-tag <?php echo $task['is_recurring'] == 0 ? 'status-success' : 'status-warning'; ?>">
                                                            <?php echo $task['is_recurring'] == 0 ? '一次性' : '周期性'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-2" onclick="editTask(<?php echo $task['id']; ?>)">
                                                            <i class="fas fa-edit mr-1"></i>编辑
                                                        </button>
                                                        <form method="POST" action="api.php" style="display: inline-block;" name="delete_task_form_<?php echo $task['id']; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                                            <input type="hidden" name="delete_task" value="1">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
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
    
    <!-- 新建任务模态框 -->
    <div id="addTaskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">新建发送任务</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="api.php" id="addTaskForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="contact_id">联系人</label>
                        <select class="form-control" id="contact_id" name="contact_id" required>
                            <option value="">请选择联系人</option>
                            <?php foreach ($contacts as $contact): ?>
                                <option value="<?php echo $contact['id']; ?>"><?php echo $contact['name']; ?> (<?php echo $contact['mail']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="template_id">模板</label>
                        <select class="form-control" id="template_id" name="template_id" required>
                            <option value="">请选择模板</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo $template['id']; ?>"><?php echo $template['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event_type">事件类型</label>
                        <select class="form-control" id="event_type" name="event_type" onchange="handleEventTypeChange()">
                            <option value="custom">自定义</option>
                            <option value="birthday">生日</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event_name">事件名称</label>
                        <input type="text" class="form-control" id="event_name" name="event_name" required placeholder="请输入事件名称">
                    </div>
                    <div class="form-group">
                        <label for="time">发送时间</label>
                        <input type="date" class="form-control" id="time" name="time" required>
                    </div>
                    <div class="form-group">
                        <label for="is_recurring">任务类型</label>
                        <select class="form-control" id="is_recurring" name="is_recurring">
                            <option value="0">一次性任务</option>
                            <option value="1">周期性任务（每年）</option>
                        </select>
                    </div>
                    <input type="hidden" name="add_task" value="1">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('addTaskModal')">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 编辑任务模态框 -->
    <div id="editTaskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">编辑发送任务</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="api.php" id="updateTaskForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_contact_id">联系人</label>
                        <select class="form-control" id="edit_contact_id" name="contact_id" required>
                            <option value="">请选择联系人</option>
                            <?php foreach ($contacts as $contact): ?>
                                <option value="<?php echo $contact['id']; ?>"><?php echo $contact['name']; ?> (<?php echo $contact['mail']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_template_id">模板</label>
                        <select class="form-control" id="edit_template_id" name="template_id" required>
                            <option value="">请选择模板</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo $template['id']; ?>"><?php echo $template['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_event_name">事件名称</label>
                        <input type="text" class="form-control" id="edit_event_name" name="event_name" required placeholder="请输入事件名称">
                    </div>
                    <div class="form-group">
                        <label for="edit_time">发送时间</label>
                        <input type="date" class="form-control" id="edit_time" name="time" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_recurring">任务类型</label>
                        <select class="form-control" id="edit_is_recurring" name="is_recurring">
                            <option value="0">一次性任务</option>
                            <option value="1">周期性任务（每年）</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_task" value="1">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('editTaskModal')">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
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
        
        // 加载任务列表
        function loadTasks() {
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'load_tasks', csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const tasks = response.data;
                        let html = '';
                        if (tasks.length === 0) {
                            html = '<p class="text-center text-muted">暂无发送任务</p>';
                        } else {
                            html = '<div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">' +
                                '<table class="table">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th>任务ID</th>' +
                                            '<th>联系人</th>' +
                                            '<th>模板</th>' +
                                            '<th>事件名称</th>' +
                                            '<th>发送时间</th>' +
                                            '<th>任务类型</th>' +
                                            '<th>操作</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>';
                            tasks.forEach(function(task) {
                                html += '<tr class="slide-in">' +
                                    '<td>' + task.id + '</td>' +
                                    '<td>' + task.contact_name + '<br><small class="text-muted">' + task.contact_mail + '</small></td>' +
                                    '<td>' + task.template_name + '</td>' +
                                    '<td>' + task.event_name + '</td>' +
                                    '<td>' + task.time + '</td>' +
                                    '<td>' +
                                        '<span class="status-tag ' + (task.is_recurring == 0 ? 'status-success' : 'status-warning') + '">' +
                                            (task.is_recurring == 0 ? '一次性' : '周期性') +
                                        '</span>' +
                                    '</td>' +
                                    '<td>' +
                                        '<button class="btn btn-sm btn-primary me-2" onclick="editTask(' + task.id + '); return false;">' +
                                            '<i class="fas fa-edit mr-1"></i>编辑' +
                                        '</button>' +
                                        '<form method="POST" action="api.php" style="display: inline-block;" name="delete_task_form_' + task.id + '">' +
                                            '<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">' +
                                            '<input type="hidden" name="id" value="' + task.id + '">' +
                                            '<input type="hidden" name="delete_task" value="1">' +
                                            '<button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">' +
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
                        $('#tasks-list').html(html);
                        // 重新绑定删除表单事件
                        bindDeleteEvents();
                    }
                }
            });
        }
        
        // 绑定删除事件
        function bindDeleteEvents() {
            $('form[name^="delete_task_form_"]').off('submit').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                const deleteButton = $(this).find('button[type="submit"]');
                
                // 保存原始按钮内容
                $(form).data('originalButtonHtml', deleteButton.html());
                
                if (confirm('确定要删除这个发送任务吗？')) {
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage(response.message, 'success');
                                // 立即刷新任务列表
                                loadTasks();
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
            
            // 表单提交处理
            $('#addTaskForm').on('submit', function(e) {
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
                            hideModal('addTaskModal');
                            // 立即刷新任务列表
                            loadTasks();
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
            });
            
            $('#updateTaskForm').on('submit', function(e) {
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
                            hideModal('editTaskModal');
                            // 立即刷新任务列表
                            loadTasks();
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
            });
        });
        
        // 处理事件类型变更
        function handleEventTypeChange() {
            const eventType = document.getElementById('event_type').value;
            const eventNameInput = document.getElementById('event_name');
            const contactId = document.getElementById('contact_id').value;
            
            if (eventType === 'birthday') {
                if (!contactId) {
                    showMessage('请先选择联系人', 'warning');
                    document.getElementById('event_type').value = 'custom';
                    return;
                }
                
                // 通过API获取联系人的生日
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    data: {action: 'get_contact', id: contactId, csrf_token: '<?php echo generate_csrf_token(); ?>'},
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const contact = response.data;
                            if (contact.birthday) {
                                eventNameInput.value = '生日';
                                // 设置发送时间为生日
                                document.getElementById('time').value = contact.birthday;
                            } else {
                                showMessage('该联系人没有设置生日', 'warning');
                                document.getElementById('event_type').value = 'custom';
                            }
                        } else {
                            showMessage('获取联系人数据失败', 'error');
                            document.getElementById('event_type').value = 'custom';
                        }
                    },
                    error: function() {
                        showMessage('操作失败，请稍后重试', 'error');
                        document.getElementById('event_type').value = 'custom';
                    }
                });
            } else {
                eventNameInput.placeholder = '请输入事件名称';
            }
        }
        
        // 编辑任务
        function editTask(id) {
            // 通过API获取任务数据
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'get_task', id: id, csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const task = response.data;
                        document.getElementById('edit_id').value = task.id;
                        document.getElementById('edit_contact_id').value = task.contact_id;
                        document.getElementById('edit_template_id').value = task.template_id;
                        document.getElementById('edit_event_name').value = task.event_name;
                        document.getElementById('edit_time').value = task.time;
                        document.getElementById('edit_is_recurring').value = task.is_recurring;
                        showModal('editTaskModal');
                    } else {
                        showMessage('获取任务数据失败', 'error');
                    }
                },
                error: function() {
                    showMessage('操作失败，请稍后重试', 'error');
                }
            });
        }
    </script>
</body>
</html>