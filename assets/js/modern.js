/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 页面加载完成后执行
$(document).ready(function() {
    // 侧边栏切换
    $('.sidebar-toggle').on('click', function() {
        $('.sidebar').toggleClass('collapsed');
    });
    
    // 移动端菜单切换
    $('.sidebar-toggle').on('click', function() {
        $('.sidebar').toggleClass('show');
    });
    
    // 实时时间显示
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleString('zh-CN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        $('.time-display').text(timeString);
    }
    
    // 初始更新时间
    updateTime();
    
    // 每秒更新时间
    setInterval(updateTime, 1000);
    
    // 侧边栏导航激活状态
    const currentUrl = window.location.href;
    $('.sidebar-link').each(function() {
        const linkUrl = $(this).attr('href');
        if (currentUrl.includes(linkUrl)) {
            $(this).addClass('active');
        }
    });
    
    // 表单提交处理
    $('form').on('submit', function(e) {
        const submitButton = $(this).find('button[type="submit"]');
        
        // 防止重复提交
        if ($(this).data('submitted')) {
            e.preventDefault();
            return;
        }
        
        // 保存原始按钮内容
        const originalButtonHtml = submitButton.html();
        $(this).data('originalButtonHtml', originalButtonHtml);
        
        // 显示加载状态
        submitButton.html('<span class="loading"></span> 提交中...');
        submitButton.attr('disabled', true);
        $(this).data('submitted', true);
    });
    
    // 重置表单提交状态
    window.resetFormSubmit = function(form) {
        const $form = $(form);
        const submitButton = $form.find('button[type="submit"]');
        const originalButtonHtml = $form.data('originalButtonHtml');
        
        if (originalButtonHtml) {
            submitButton.html(originalButtonHtml);
        } else {
            submitButton.html('保存');
        }
        
        submitButton.attr('disabled', false);
        $form.data('submitted', false);
    };
    
    // 验证码刷新
    $('.captcha-image').on('click', function() {
        $(this).attr('src', $(this).attr('src').split('?')[0] + '?' + Math.random());
    });
    
    // 模态框功能
    $('.modal').on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $(this).removeClass('show');
        }
    });
    
    $('.modal-close').on('click', function() {
        $(this).closest('.modal').removeClass('show');
    });
    
    // 平滑滚动
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top
            }, 500);
        }
    });
    
    // 表格行悬停效果
    $('.table tr').on('mouseenter', function() {
        $(this).addClass('bg-light');
    }).on('mouseleave', function() {
        $(this).removeClass('bg-light');
    });
    
    // 按钮悬停效果
    $('.btn').on('mouseenter', function() {
        $(this).css('transform', 'translateY(-2px)');
    }).on('mouseleave', function() {
        $(this).css('transform', 'translateY(0)');
    });
    
    // 卡片悬停效果
    $('.card').on('mouseenter', function() {
        $(this).css('transform', 'translateY(-3px)');
        $(this).css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.12)');
    }).on('mouseleave', function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', '0 2px 8px rgba(0, 0, 0, 0.08)');
    });
    
    // 统计卡片悬停效果
    $('.stat-card').on('mouseenter', function() {
        $(this).css('transform', 'translateY(-5px)');
        $(this).css('box-shadow', '0 6px 16px rgba(0, 0, 0, 0.12)');
    }).on('mouseleave', function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', '0 2px 8px rgba(0, 0, 0, 0.08)');
    });
    
    // 输入框聚焦效果
    $('.form-control').on('focus', function() {
        $(this).parent().addClass('input-focus');
    }).on('blur', function() {
        $(this).parent().removeClass('input-focus');
    });
    
    // 判断是否为移动设备布局（基于宽高比）
    function isMobileLayout() {
        return window.innerHeight > window.innerWidth;
    }
    
    // 响应式处理
    function handleResponsive() {
        if (isMobileLayout()) {
            $('.sidebar').removeClass('collapsed');
        }
    }
    
    // 初始响应式处理
    handleResponsive();
    
    // 窗口大小变化时处理
    $(window).resize(function() {
        handleResponsive();
    });
    
    // 消息提示自动消失
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
    
    // 自定义字段插入功能
    if (typeof insertField === 'undefined') {
        window.insertField = function(field) {
            const content = document.getElementById('content');
            if (content) {
                const startPos = content.selectionStart;
                const endPos = content.selectionEnd;
                const textBefore = content.value.substring(0, startPos);
                const textAfter = content.value.substring(endPos, content.value.length);
                content.value = textBefore + field + textAfter;
                content.focus();
                content.setSelectionRange(startPos + field.length, startPos + field.length);
            }
        };
    }
});

// 显示模态框
function showModal(modalId) {
    $('#' + modalId).addClass('show');
}

// 隐藏模态框
function hideModal(modalId) {
    $('#' + modalId).removeClass('show');
}

// 显示加载动画
function showLoading() {
    if (!$('.loading-overlay').length) {
        $('body').append('<div class="loading-overlay"><div class="loading-spinner"><div class="loading"></div></div></div>');
    }
    $('.loading-overlay').show();
}

// 隐藏加载动画
function hideLoading() {
    $('.loading-overlay').hide();
}

// 显示消息提示
function showMessage(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const messageHtml = '<div class="alert ' + alertClass + ' fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 10000; max-width: 400px;">' +
        '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-circle' : 
                         type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle') + '"></i> ' +
        message +
        '</div>';
    
    $('body').append(messageHtml);
    
    // 添加动画效果
    $('.alert').hide().fadeIn('fast');
    
    // 3秒后自动关闭
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

// 确认对话框
function confirmAction(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
        return true;
    }
    return false;
}

// AJAX请求
function ajaxRequest(url, data, successCallback, errorCallback) {
    showLoading();
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (typeof successCallback === 'function') {
                successCallback(response);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            if (typeof errorCallback === 'function') {
                errorCallback(error);
            } else {
                showMessage('请求失败，请稍后重试', 'error');
            }
        }
    });
}

// 数字输入框限制
function restrictNumberInput(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

// 邮箱格式验证
function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}
