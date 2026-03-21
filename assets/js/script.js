/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 页面加载完成后执行
$(document).ready(function() {
    // 侧边栏导航激活状态
    var currentUrl = window.location.href;
    $('.sidebar a').each(function() {
        var linkUrl = $(this).attr('href');
        if (currentUrl.includes(linkUrl)) {
            $(this).addClass('active');
        }
    });
    
    // 自定义字段插入功能
    if (typeof insertField === 'undefined') {
        function insertField(field) {
            var content = document.getElementById('content');
            if (content) {
                var startPos = content.selectionStart;
                var endPos = content.selectionEnd;
                var textBefore = content.value.substring(0, startPos);
                var textAfter = content.value.substring(endPos, content.value.length);
                content.value = textBefore + field + textAfter;
                content.focus();
                content.setSelectionRange(startPos + field.length, startPos + field.length);
            }
        }
    }
    
    // 验证码刷新功能
    $('.captcha-image').on('click', function() {
        $(this).attr('src', 'login.php?captcha&' + Math.random());
    });
    
    // 表单提交确认
    $('form').on('submit', function() {
        var submitButton = $(this).find('button[type="submit"]');
        var originalText = submitButton.html();
        submitButton.html('<span class="loading"></span> 提交中...');
        submitButton.attr('disabled', true);
        
        // 防止重复提交
        $(this).data('submitted', true);
    });
    
    // 平滑滚动
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top
            }, 1000);
        }
    });
    
    // 响应式导航
    if ($(window).width() < 768) {
        $('.sidebar').addClass('mobile-sidebar');
    }
    
    $(window).resize(function() {
        if ($(window).width() < 768) {
            $('.sidebar').addClass('mobile-sidebar');
        } else {
            $('.sidebar').removeClass('mobile-sidebar');
        }
    });
    
    // 通知消息自动消失
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
    
    // 表格排序功能
    $('.table-sortable th').on('click', function() {
        var table = $(this).closest('table');
        var tbody = table.find('tbody');
        var rows = tbody.find('tr').toArray();
        var index = $(this).index();
        var isAsc = $(this).hasClass('sort-asc');
        
        // 移除其他列的排序类
        table.find('th').removeClass('sort-asc sort-desc');
        
        // 设置当前列的排序类
        $(this).addClass(isAsc ? 'sort-desc' : 'sort-asc');
        
        // 排序
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(index).text().trim();
            var bVal = $(b).find('td').eq(index).text().trim();
            
            if (!isAsc) {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });
        
        // 重新排列行
        tbody.empty();
        $.each(rows, function(i, row) {
            tbody.append(row);
        });
    });
    
    // 搜索功能
    $('#search-input').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.searchable-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // 日期选择器
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            minDate: 'today'
        });
    }
    
    // 模态框
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('input:first').focus();
    });
    
    // 标签页切换
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // 数字输入框限制
    $('.number-input').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // 邮箱格式验证
    $('.email-input').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});

// 显示加载动画
function showLoading() {
    if (!$('.loading-overlay').length) {
        $('body').append('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
    }
    $('.loading-overlay').show();
}

// 隐藏加载动画
function hideLoading() {
    $('.loading-overlay').hide();
}

// 显示消息
function showMessage(message, type) {
    var alertClass = 'alert-info';
    switch (type) {
        case 'success':
            alertClass = 'alert-success';
            break;
        case 'error':
            alertClass = 'alert-danger';
            break;
        case 'warning':
            alertClass = 'alert-warning';
            break;
    }
    
    var messageHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        message +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
    
    $('#message-container').html(messageHtml);
    setTimeout(function() {
        $('.alert').fadeOut('slow');
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