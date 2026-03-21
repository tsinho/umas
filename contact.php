<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 联系人管理

require_once 'auth.php';

// 获取联系人列表
$contacts = get_all('SELECT * FROM contacts ORDER BY time DESC');

// 获取要编辑的联系人
$edit_contact = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_contact = get_one('SELECT * FROM contacts WHERE id = ?', array($edit_id));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>联系人管理 - 无礁节日自动邮件祝福系统</title>
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
                    <a href="contact.php" class="sidebar-link active">
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
                <div id="message-container">
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- 页面标题 -->
                <h1 class="page-title mb-4">联系人管理</h1>
                

                
                <!-- 联系人列表 -->
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i>
                                联系人列表
                            </h3>
                            <button style="margin-left:10px" class="btn btn-primary" onclick="showModal('addContactModal')">
                                <i class="fas fa-plus mr-2"></i>新建联系人
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="contacts-list">
                            <?php if (empty($contacts)): ?>
                                <p class="text-center text-muted">暂无联系人</p>
                            <?php else: ?>
                                <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>称呼</th>
                                                <th>邮箱</th>
                                                <th>生日</th>
                                                <th>状态</th>
                                                <th>添加时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contacts as $contact): ?>
                                                <tr class="slide-in">
                                                    <td><?php echo $contact['name']; ?></td>
                                                    <td><?php echo $contact['mail']; ?></td>
                                                    <td><?php echo $contact['birthday'] ? $contact['birthday'] : '未设置'; ?></td>
                                                    <td>
                                                        <span class="status-tag <?php echo $contact['status'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                                                            <?php echo $contact['status'] == 0 ? '启用' : '停用'; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $contact['time']; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-2" onclick="editContact(<?php echo $contact['id']; ?>)">
                                                            <i class="fas fa-edit mr-1"></i>编辑
                                                        </button>
                                                        <form method="POST" action="api.php" style="display: inline-block;" name="delete_contact_form">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                                            <input type="hidden" name="delete_contact" value="1">
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
    
    <!-- 新建联系人模态框 -->
    <div id="addContactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">新建联系人</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="api.php" id="addContactForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="name">称呼</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="请输入称呼">
                    </div>
                    <div class="form-group">
                        <label for="mail">邮箱</label>
                        <input type="email" class="form-control" id="mail" name="mail" required placeholder="请输入邮箱">
                    </div>
                    <div class="form-group">
                        <label for="birthday">生日（可选，用于生日邮件）</label>
                        <input type="date" class="form-control" id="birthday" name="birthday">
                    </div>
                    <input type="hidden" name="add_contact" value="1">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('addContactModal')">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 编辑联系人模态框 -->
    <div id="editContactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">编辑联系人</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="api.php" id="updateContactForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_name">称呼</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required placeholder="请输入称呼">
                    </div>
                    <div class="form-group">
                        <label for="edit_mail">邮箱</label>
                        <input type="email" class="form-control" id="edit_mail" name="mail" required placeholder="请输入邮箱">
                    </div>
                    <div class="form-group">
                        <label for="edit_birthday">生日（可选，用于生日邮件）</label>
                        <input type="date" class="form-control" id="edit_birthday" name="birthday">
                    </div>
                    <div class="form-group">
                        <label for="edit_status">状态</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="0">启用</option>
                            <option value="1">停用</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_contact" value="1">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('editContactModal')">取消</button>
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
        
        // 加载联系人列表
        function loadContacts() {
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'load_contacts', csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const contacts = response.data;
                        let html = '';
                        if (contacts.length === 0) {
                            html = '<p class="text-center text-muted">暂无联系人</p>';
                        } else {
                            html = '<div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">' +
                                '<table class="table">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th>称呼</th>' +
                                            '<th>邮箱</th>' +
                                            '<th>生日</th>' +
                                            '<th>状态</th>' +
                                            '<th>添加时间</th>' +
                                            '<th>操作</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>';
                            contacts.forEach(function(contact) {
                                html += '<tr class="slide-in">' +
                                    '<td>' + contact.name + '</td>' +
                                    '<td>' + contact.mail + '</td>' +
                                    '<td>' + (contact.birthday ? contact.birthday : '未设置') + '</td>' +
                                    '<td>' +
                                        '<span class="status-tag ' + (contact.status == 0 ? 'status-active' : 'status-inactive') + '">' +
                                            (contact.status == 0 ? '启用' : '停用') +
                                        '</span>' +
                                    '</td>' +
                                    '<td>' + contact.time + '</td>' +
                                    '<td>' +
                                        '<button class="btn btn-sm btn-primary me-2" onclick="editContact(' + contact.id + ')">' +
                                            '<i class="fas fa-edit mr-1"></i>编辑' +
                                        '</button>' +
                                        '<form method="POST" action="api.php" style="display: inline-block;" name="delete_contact_form">' +
                                            '<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">' +
                                            '<input type="hidden" name="id" value="' + contact.id + '">' +
                                            '<input type="hidden" name="delete_contact" value="1">' +
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
                        $('#contacts-list').html(html);
                        // 重新绑定删除表单事件
                        bindDeleteEvents();
                    }
                }
            });
        }
        
        // 绑定删除事件
        function bindDeleteEvents() {
            $('form[name="delete_contact_form"]').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                if (confirm('确定要删除这个联系人吗？')) {
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage(response.message, 'success');
                                // 立即刷新联系人列表
                                loadContacts();
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
            $('#addContactForm').on('submit', function(e) {
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
                            hideModal('addContactModal');
                            // 立即刷新联系人列表
                            loadContacts();
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
            
            $('#updateContactForm').on('submit', function(e) {
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
                            hideModal('editContactModal');
                            // 立即刷新联系人列表
                            loadContacts();
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
        
        // 编辑联系人
        function editContact(id) {
            // 通过API获取联系人数据
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'get_contact', id: id, csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const contact = response.data;
                        document.getElementById('edit_id').value = contact.id;
                        document.getElementById('edit_name').value = contact.name;
                        document.getElementById('edit_mail').value = contact.mail;
                        document.getElementById('edit_birthday').value = contact.birthday;
                        document.getElementById('edit_status').value = contact.status;
                        showModal('editContactModal');
                    } else {
                        showMessage('获取联系人数据失败', 'error');
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