<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 模板管理

require_once 'auth.php';

// 获取模板列表
$templates = get_all('SELECT * FROM templates ORDER BY time DESC');

// 获取要编辑的模板
$edit_template = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_template = get_one('SELECT * FROM templates WHERE id = ?', array($edit_id));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>模板管理 - 无礁节日自动邮件祝福系统</title>
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
                    <a href="template.php" class="sidebar-link active">
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
                <h1 class="page-title mb-4">模板管理</h1>
                

                
                <!-- 自定义字段说明 -->
                <div class="card mb-4 fade-in">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            可调用的自定义字段
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="custom-fields">
                            <div class="custom-field-item" onclick="insertField('{time}')">{time} - 当前时间</div>
                            <div class="custom-field-item" onclick="insertField('{send}')">{send} - 发送人昵称</div>
                            <div class="custom-field-item" onclick="insertField('{name}')">{name} - 收件人称呼</div>
                            <div class="custom-field-item" onclick="insertField('{age}')">{age} - 收件人年龄（生日邮件）</div>
                            <div class="custom-field-item" onclick="insertField('{days}')">{days} - 收件人已活天数（生日邮件）</div>
                        </div>
                    </div>
                </div>
                
                <!-- 模板列表 -->
                <div class="card fade-in">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i>
                                模板列表
                            </h3>
                            <button style="margin-left:10px" class="btn btn-primary" onclick="showModal('addTemplateModal')">
                                <i class="fas fa-plus mr-2"></i>新建模板
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="templates-list">
                            <?php if (empty($templates)): ?>
                                <p class="text-center text-muted">暂无模板</p>
                            <?php else: ?>
                                <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>模板名称</th>
                                                <th>邮件标题</th>
                                                <th>添加时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($templates as $template): ?>
                                                <tr class="slide-in">
                                                    <td><?php echo $template['name']; ?></td>
                                                    <td><?php echo $template['title']; ?></td>
                                                    <td><?php echo $template['time']; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary me-2" onclick="editTemplate(<?php echo $template['id']; ?>)">
                                                            <i class="fas fa-edit mr-1"></i>编辑
                                                        </button>
                                                        <button class="btn btn-sm btn-info me-2 text-white" onclick="previewTemplate(<?php echo $template['id']; ?>)">
                                                            <i class="fas fa-eye mr-1 text-white"></i>预览
                                                        </button>
                                                        <form method="POST" action="api.php" style="display: inline-block;" name="delete_template_form">
                                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                            <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                                            <input type="hidden" name="delete_template" value="1">
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
    
    <!-- 新建模板模态框 -->
    <div id="addTemplateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">新建模板</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="api.php" id="addTemplateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="form-group">
                        <label for="name">模板名称</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="title">邮件标题</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="content">邮件内容</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    <input type="hidden" name="add_template" value="1">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('addTemplateModal')">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 编辑模板模态框 -->
    <div id="editTemplateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">编辑模板</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="api.php" id="updateTemplateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_name">模板名称</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_title">邮件标题</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_content">邮件内容</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="10" required></textarea>
                    </div>
                    <input type="hidden" name="update_template" value="1">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('editTemplateModal')">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 预览模板模态框 -->
    <div id="previewTemplateModal" class="modal">
        <div class="modal-content" style="width: 80%; max-width: 900px; max-height: 90vh;">
            <div class="modal-header">
                <h4 class="modal-title">模板预览</h4>
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <label class="form-label mr-2">屏幕尺寸：</label>
                        <select id="screenSize" class="form-control form-control-sm" onchange="adjustPreviewSize()">
                            <option value="320">手机 (320px)</option>
                            <option value="768">平板 (768px)</option>
                            <option value="1024" selected>桌面 (1024px)</option>
                            <option value="1440">大屏 (1440px)</option>
                        </select>
                    </div>
                    <button class="modal-close">&times;</button>
                </div>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div id="previewContainer" style="width: 100%; min-height: 600px; overflow: auto; border: 1px solid #e0e0e0; background-color: #f5f5f5;">
                    <iframe id="previewIframe" style="width: 1024px; height: 1000px; border: none; margin: 0 auto; display: block; background-color: white;"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideModal('previewTemplateModal')">关闭</button>
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
        
        // 加载模板列表
        function loadTemplates() {
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'load_templates', csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const templates = response.data;
                        let html = '';
                        if (templates.length === 0) {
                            html = '<p class="text-center text-muted">暂无模板</p>';
                        } else {
                            html = '<div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">' +
                                '<table class="table">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th>模板名称</th>' +
                                            '<th>邮件标题</th>' +
                                            '<th>添加时间</th>' +
                                            '<th>操作</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>';
                            templates.forEach(function(template) {
                                html += '<tr class="slide-in">' +
                                    '<td>' + template.name + '</td>' +
                                    '<td>' + template.title + '</td>' +
                                    '<td>' + template.time + '</td>' +
                                    '<td>' +
                                        '<button class="btn btn-sm btn-primary me-2" onclick="editTemplate(' + template.id + ')">' +
                                            '<i class="fas fa-edit mr-1"></i>编辑' +
                                        '</button>' +
                                        '<button class="btn btn-sm btn-info me-2 text-white" onclick="previewTemplate(' + template.id + ')">' +
                                            '<i class="fas fa-eye mr-1 text-white"></i>预览' +
                                        '</button>' +
                                        '<form method="POST" action="api.php" style="display: inline-block;" name="delete_template_form">' +
                                            '<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">' +
                                            '<input type="hidden" name="id" value="' + template.id + '">' +
                                            '<input type="hidden" name="delete_template" value="1">' +
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
                        $('#templates-list').html(html);
                        // 重新绑定删除表单事件
                        bindDeleteEvents();
                    }
                }
            });
        }
        
        // 绑定删除事件
        function bindDeleteEvents() {
            $('form[name="delete_template_form"]').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                
                if (confirm('确定要删除这个模板吗？')) {
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage(response.message, 'success');
                                // 立即刷新模板列表
                                loadTemplates();
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
            $('#addTemplateForm').on('submit', function(e) {
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
                            hideModal('addTemplateModal');
                            // 立即刷新模板列表
                            loadTemplates();
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
            
            $('#updateTemplateForm').on('submit', function(e) {
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
                            hideModal('editTemplateModal');
                            // 立即刷新模板列表
                            loadTemplates();
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
        
        // 编辑模板
        function editTemplate(id) {
            // 通过API获取模板数据
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'get_template', id: id, csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const template = response.data;
                        document.getElementById('edit_id').value = template.id;
                        document.getElementById('edit_name').value = template.name;
                        document.getElementById('edit_title').value = template.title;
                        document.getElementById('edit_content').value = template.content;
                        showModal('editTemplateModal');
                    } else {
                        showMessage('获取模板数据失败', 'error');
                    }
                },
                error: function() {
                    showMessage('操作失败，请稍后重试', 'error');
                }
            });
        }
        
        // 插入自定义字段到内容编辑器
        function insertField(field) {
            // 检查当前打开的模态框
            if (document.getElementById('addTemplateModal').classList.contains('show')) {
                var content = document.getElementById('content');
                var startPos = content.selectionStart;
                var endPos = content.selectionEnd;
                var textBefore = content.value.substring(0, startPos);
                var textAfter = content.value.substring(endPos, content.value.length);
                content.value = textBefore + field + textAfter;
                content.focus();
                content.setSelectionRange(startPos + field.length, startPos + field.length);
            } else if (document.getElementById('editTemplateModal').classList.contains('show')) {
                var content = document.getElementById('edit_content');
                var startPos = content.selectionStart;
                var endPos = content.selectionEnd;
                var textBefore = content.value.substring(0, startPos);
                var textAfter = content.value.substring(endPos, content.value.length);
                content.value = textBefore + field + textAfter;
                content.focus();
                content.setSelectionRange(startPos + field.length, startPos + field.length);
            }
        }
        
        // 预览模板
        function previewTemplate(id) {
            // 通过API获取模板数据
            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {action: 'get_template', id: id, csrf_token: '<?php echo generate_csrf_token(); ?>'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const template = response.data;
                        // 使用iframe加载模板内容
                        const iframe = document.getElementById('previewIframe');
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write(template.content);
                        iframeDoc.close();
                        // 显示预览模态框
                        showModal('previewTemplateModal');
                    } else {
                        showMessage('获取模板数据失败', 'error');
                    }
                },
                error: function() {
                    showMessage('操作失败，请稍后重试', 'error');
                }
            });
        }
        
        // 调整预览尺寸
        function adjustPreviewSize() {
            const screenSize = document.getElementById('screenSize').value;
            const iframe = document.getElementById('previewIframe');
            iframe.style.width = screenSize + 'px';
        }
    </script>
</body>
</html>