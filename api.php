<?php
/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

// 系统API接口

require_once 'auth.php';

// 处理表单提交
$response = array('success' => false, 'message' => '操作失败');
$ip = get_client_ip();

// 验证ID格式
function validate_id($id) {
    return is_numeric($id) && $id > 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $response['message'] = '无效的请求';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    if (empty($_SESSION['admin_login']) && !check_resource_limit($ip, 'api_request', 60, 3600)) {
        $response['message'] = '请求过于频繁，请稍后重试';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    // 加载联系人列表
    if (isset($_POST['action']) && $_POST['action'] == 'load_contacts') {
        try {
            $contacts = get_all('SELECT * FROM contacts ORDER BY time DESC');
            $response = array('success' => true, 'data' => $contacts);
        } catch (Exception $e) {
            $response['message'] = handle_error('加载联系人失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 获取单个联系人
    if (isset($_POST['action']) && $_POST['action'] == 'get_contact') {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的联系人ID';
            } else {
                $contact = get_one('SELECT * FROM contacts WHERE id = ?', array($id));
                if ($contact) {
                    $response = array('success' => true, 'data' => $contact);
                } else {
                    $response['message'] = '联系人不存在';
                }
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('获取联系人失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 联系人相关操作
    if (isset($_POST['add_contact'])) {
        try {
            $mail = validate_input($_POST['mail'], 'email');
            $name = validate_input($_POST['name']);
            $birthday = validate_input($_POST['birthday']);
            $birthday = empty($birthday) ? null : $birthday;
            
            if (empty($mail)) {
                $response['message'] = '邮箱格式错误';
            } else {
                execute('INSERT INTO contacts (mail, name, birthday) VALUES (?, ?, ?)', array($mail, $name, $birthday));
                add_log('operation', '添加了联系人：' . $name, $ip);
                $response = array('success' => true, 'message' => '联系人添加成功');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('添加联系人失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['update_contact'])) {
        try {
            $id = $_POST['id'];
            $mail = validate_input($_POST['mail'], 'email');
            $name = validate_input($_POST['name']);
            $birthday = validate_input($_POST['birthday']);
            $status = intval($_POST['status']);
            $birthday = empty($birthday) ? null : $birthday;
            
            if (!validate_id($id)) {
                $response['message'] = '无效的联系人ID';
            } elseif (empty($mail)) {
                $response['message'] = '邮箱格式错误';
            } elseif (!in_array($status, array(0, 1))) {
                $response['message'] = '无效的状态值';
            } else {
                execute('UPDATE contacts SET mail = ?, name = ?, birthday = ?, status = ? WHERE id = ?', array($mail, $name, $birthday, $status, $id));
                add_log('operation', '修改了联系人：' . $name, $ip);
                $response = array('success' => true, 'message' => '联系人更新成功');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('更新联系人失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['delete_contact'])) {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的联系人ID';
            } else {
                $contact = get_one('SELECT name FROM contacts WHERE id = ?', array($id));
                if ($contact) {
                    execute('DELETE FROM contacts WHERE id = ?', array($id));
                    add_log('operation', '删除了联系人：' . $contact['name'], $ip);
                    $response = array('success' => true, 'message' => '联系人删除成功');
                } else {
                    $response['message'] = '联系人不存在';
                }
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('删除联系人失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 加载模板列表
    if (isset($_POST['action']) && $_POST['action'] == 'load_templates') {
        try {
            $templates = get_all('SELECT * FROM templates ORDER BY time DESC');
            $response = array('success' => true, 'data' => $templates);
        } catch (Exception $e) {
            $response['message'] = handle_error('加载模板失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 获取单个模板
    if (isset($_POST['action']) && $_POST['action'] == 'get_template') {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的模板ID';
            } else {
                $template = get_one('SELECT * FROM templates WHERE id = ?', array($id));
                if ($template) {
                    $response = array('success' => true, 'data' => $template);
                } else {
                    $response['message'] = '模板不存在';
                }
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('获取模板失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 模板相关操作
    if (isset($_POST['add_template'])) {
        try {
            $name = validate_input($_POST['name']);
            $title = validate_input($_POST['title']);
            // 直接处理模板内容，不进行 HTML 转义
            $content = trim($_POST['content']);
            // 限制长度
            if (strlen($content) > 100000) {
                $content = substr($content, 0, 100000);
            }
            // 检查是否包含DOCTYPE
            $has_doctype = strpos(strtolower($content), '<!doctype') !== false;
            
            // 过滤危险标签
            $allowed_tags = '<html><head><meta><title><style><body><div><span><p><br><b><i><u><strong><em><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><tr><td><th><thead><tbody><font>';
            // 先移除现有的DOCTYPE声明，避免strip_tags处理
            if ($has_doctype) {
                $content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);
            }
            // 过滤危险标签
            $content = strip_tags($content, $allowed_tags);
            // 重新添加DOCTYPE声明（如果原本有）
            if ($has_doctype) {
                $content = '<!DOCTYPE html>' . $content;
            }
            
            execute('INSERT INTO templates (name, title, content) VALUES (?, ?, ?)', array($name, $title, $content));
            add_log('operation', '添加了模板：' . $name, $ip);
            $response = array('success' => true, 'message' => '模板添加成功');
        } catch (Exception $e) {
            $response['message'] = handle_error('添加模板失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['update_template'])) {
        try {
            $id = intval($_POST['id']);
            $name = validate_input($_POST['name']);
            $title = validate_input($_POST['title']);
            // 直接处理模板内容，不进行 HTML 转义
            $content = trim($_POST['content']);
            // 限制长度
            if (strlen($content) > 100000) {
                $content = substr($content, 0, 100000);
            }
            // 检查是否包含DOCTYPE
            $has_doctype = strpos(strtolower($content), '<!doctype') !== false;
            
            // 过滤危险标签
            $allowed_tags = '<html><head><meta><title><style><body><div><span><p><br><b><i><u><strong><em><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><tr><td><th><thead><tbody><font>';
            // 先移除现有的DOCTYPE声明，避免strip_tags处理
            if ($has_doctype) {
                $content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);
            }
            // 过滤危险标签
            $content = strip_tags($content, $allowed_tags);
            // 重新添加DOCTYPE声明（如果原本有）
            if ($has_doctype) {
                $content = '<!DOCTYPE html>' . $content;
            }
            
            if (!validate_id($id)) {
                $response['message'] = '无效的模板ID';
            } else {
                execute('UPDATE templates SET name = ?, title = ?, content = ? WHERE id = ?', array($name, $title, $content, $id));
                add_log('operation', '修改了模板：' . $name, $ip);
                $response = array('success' => true, 'message' => '模板更新成功');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('更新模板失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['delete_template'])) {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的模板ID';
            } else {
                $template = get_one('SELECT name FROM templates WHERE id = ?', array($id));
                if ($template) {
                    execute('DELETE FROM templates WHERE id = ?', array($id));
                    add_log('operation', '删除了模板：' . $template['name'], $ip);
                    $response = array('success' => true, 'message' => '模板删除成功');
                } else {
                    $response['message'] = '模板不存在';
                }
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('删除模板失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 加载日志列表
    if (isset($_POST['action']) && $_POST['action'] == 'load_logs') {
        try {
            $type = isset($_POST['type']) ? validate_input($_POST['type']) : 'all';
            if ($type == 'all') {
                $logs = get_all('SELECT * FROM logs ORDER BY time DESC');
            } else {
                $valid_types = array('security', 'system', 'operation');
                if (in_array($type, $valid_types)) {
                    $logs = get_all('SELECT * FROM logs WHERE type = ? ORDER BY time DESC', array($type));
                } else {
                    $logs = get_all('SELECT * FROM logs ORDER BY time DESC');
                }
            }
            $response = array('success' => true, 'data' => $logs);
        } catch (Exception $e) {
            $response['message'] = handle_error('加载日志失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 日志相关操作
    if (isset($_POST['delete_log'])) {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的日志ID';
            } else {
                execute('DELETE FROM logs WHERE id = ?', array($id));
                add_log('operation', '删除了一条日志', $ip);
                $response = array('success' => true, 'message' => '日志删除成功');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('删除日志失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['clear_logs'])) {
        try {
            $type = validate_input($_POST['type']);
            if ($type == 'all') {
                execute('DELETE FROM logs');
                add_log('operation', '清空了所有日志', $ip);
            } else {
                $valid_types = array('security', 'system', 'operation');
                if (in_array($type, $valid_types)) {
                    execute('DELETE FROM logs WHERE type = ?', array($type));
                    add_log('operation', '清空了' . ($type == 'security' ? '安全' : ($type == 'system' ? '系统' : '操作')) . '日志', $ip);
                } else {
                    $response['message'] = '无效的日志类型';
                    throw new Exception('无效的日志类型');
                }
            }
            $response = array('success' => true, 'message' => '日志清空成功');
        } catch (Exception $e) {
            $response['message'] = handle_error('清空日志失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 系统设置相关操作
    if (isset($_POST['system_settings'])) {
        try {
            $admin_user = validate_input($_POST['admin_user']);
            $admin_mail = validate_input($_POST['admin_mail'], 'email');
            $send_name = validate_input($_POST['send_name']);
            $status = intval($_POST['status']);
            
            if (empty($admin_mail)) {
                $response['message'] = '管理员邮箱格式错误';
            } elseif (!in_array($status, array(0, 1))) {
                $response['message'] = '无效的系统状态值';
            } else {
                if (!empty($_POST['admin_password'])) {
                    $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
                    execute('UPDATE config SET admin_user = ?, admin_password = ?, admin_mail = ?, send_name = ?, status = ? WHERE id = ?', array($admin_user, $admin_password, $admin_mail, $send_name, $status, $config['id']));
                    add_log('operation', '修改了管理员密码和系统设置', $ip);
                } else {
                    execute('UPDATE config SET admin_user = ?, admin_mail = ?, send_name = ?, status = ? WHERE id = ?', array($admin_user, $admin_mail, $send_name, $status, $config['id']));
                    add_log('operation', '修改了系统设置', $ip);
                }
                $response = array('success' => true, 'message' => '系统设置已更新');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('更新系统设置失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['log_settings'])) {
        try {
            $system_logs = intval($_POST['system_logs']);
            $security_logs = intval($_POST['security_logs']);
            $operation_logs = intval($_POST['operation_logs']);
            $mail_reminder = intval($_POST['mail_reminder']);
            
            if ($system_logs < 1 || $security_logs < 1 || $operation_logs < 1) {
                $response['message'] = '日志保留天数必须大于0';
            } elseif (!in_array($mail_reminder, array(0, 1, 2, 3))) {
                $response['message'] = '无效的日志提醒方式';
            } else {
                $logs_keep = 'system:' . $system_logs . ';security:' . $security_logs . ';operation:' . $operation_logs . ';';
                execute('UPDATE config SET logs_keep = ?, mail_reminder = ? WHERE id = ?', array($logs_keep, $mail_reminder, $config['id']));
                add_log('operation', '修改了日志设置', $ip);
                $response = array('success' => true, 'message' => '日志设置已更新');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('更新日志设置失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['smtp_settings'])) {
        try {
            $send_smtp = validate_input($_POST['send_smtp']);
            $send_port = intval($_POST['send_port']);
            $send_mail = validate_input($_POST['send_mail'], 'email');
            $send_key = validate_input($_POST['send_key']);
            
            if (empty($send_mail)) {
                $response['message'] = '发件人邮箱格式错误';
            } elseif ($send_port < 1 || $send_port > 65535) {
                $response['message'] = '无效的SMTP端口';
            } else {
                execute('UPDATE config SET send_smtp = ?, send_port = ?, send_mail = ?, send_key = ? WHERE id = ?', array($send_smtp, $send_port, $send_mail, $send_key, $config['id']));
                add_log('operation', '修改了SMTP设置', $ip);
                $response = array('success' => true, 'message' => 'SMTP设置已更新');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('更新SMTP设置失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['reset_api_key'])) {
        try {
            $new_api_key = md5(uniqid(rand(), true));
            execute('UPDATE config SET api_key = ? WHERE id = ?', array($new_api_key, $config['id']));
            add_log('operation', '重置了API密钥', $ip);
            $response = array('success' => true, 'message' => 'API密钥已重置');
        } catch (Exception $e) {
            $response['message'] = handle_error('重置API密钥失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['cron_settings'])) {
        try {
            $api_whitelist = validate_input($_POST['api_whitelist']);
            execute('UPDATE config SET api_whitelist = ? WHERE id = ?', array($api_whitelist, $config['id']));
            add_log('operation', '修改了API白名单设置', $ip);
            $response = array('success' => true, 'message' => '定时任务设置已更新');
        } catch (Exception $e) {
            $response['message'] = handle_error('更新定时任务设置失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 加载任务列表
    if (isset($_POST['action']) && $_POST['action'] == 'load_tasks') {
        try {
            $tasks = get_all('SELECT d.*, c.name as contact_name, c.mail as contact_mail, t.name as template_name FROM data d LEFT JOIN contacts c ON d.contact_id = c.id LEFT JOIN templates t ON d.template_id = t.id ORDER BY d.id DESC');
            $response = array('success' => true, 'data' => $tasks);
        } catch (Exception $e) {
            $response['message'] = handle_error('加载任务失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 获取单个任务
    if (isset($_POST['action']) && $_POST['action'] == 'get_task') {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的任务ID';
            } else {
                $task = get_one('SELECT * FROM data WHERE id = ?', array($id));
                if ($task) {
                    $response = array('success' => true, 'data' => $task);
                } else {
                    $response['message'] = '任务不存在';
                }
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('获取任务失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    // 任务相关操作
    if (isset($_POST['add_task'])) {
        try {
            $contact_id = intval($_POST['contact_id']);
            $template_id = intval($_POST['template_id']);
            $event_name = validate_input($_POST['event_name']);
            $time = validate_input($_POST['time']);
            $is_recurring = intval($_POST['is_recurring']);
            
            if ($is_recurring == 1 && !empty($time)) {
                $time = date('m-d', strtotime($time));
            }
            
            if (!validate_id($contact_id)) {
                $response['message'] = '无效的联系人ID';
            } elseif (!validate_id($template_id)) {
                $response['message'] = '无效的模板ID';
            } elseif (empty($event_name)) {
                $response['message'] = '事件名称不能为空';
            } elseif (empty($time)) {
                $response['message'] = '发送时间不能为空';
            } elseif (!in_array($is_recurring, array(0, 1))) {
                $response['message'] = '无效的任务类型';
            } else {
                execute('INSERT INTO data (contact_id, template_id, event_name, time, is_recurring) VALUES (?, ?, ?, ?, ?)', array($contact_id, $template_id, $event_name, $time, $is_recurring));
                add_log('operation', '添加了发送任务：' . $event_name, $ip);
                $response = array('success' => true, 'message' => '任务添加成功');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('添加任务失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['update_task'])) {
        try {
            $id = intval($_POST['id']);
            $contact_id = intval($_POST['contact_id']);
            $template_id = intval($_POST['template_id']);
            $event_name = validate_input($_POST['event_name']);
            $time = validate_input($_POST['time']);
            $is_recurring = intval($_POST['is_recurring']);
            
            if ($is_recurring == 1 && !empty($time)) {
                $time = date('m-d', strtotime($time));
            }
            
            if (!validate_id($id)) {
                $response['message'] = '无效的任务ID';
            } elseif (!validate_id($contact_id)) {
                $response['message'] = '无效的联系人ID';
            } elseif (!validate_id($template_id)) {
                $response['message'] = '无效的模板ID';
            } elseif (empty($event_name)) {
                $response['message'] = '事件名称不能为空';
            } elseif (empty($time)) {
                $response['message'] = '发送时间不能为空';
            } elseif (!in_array($is_recurring, array(0, 1))) {
                $response['message'] = '无效的任务类型';
            } else {
                execute('UPDATE data SET contact_id = ?, template_id = ?, event_name = ?, time = ?, is_recurring = ? WHERE id = ?', array($contact_id, $template_id, $event_name, $time, $is_recurring, $id));
                add_log('operation', '修改了发送任务：' . $event_name, $ip);
                $response = array('success' => true, 'message' => '任务更新成功');
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('更新任务失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
    
    if (isset($_POST['delete_task'])) {
        try {
            $id = intval($_POST['id']);
            if (!validate_id($id)) {
                $response['message'] = '无效的任务ID';
            } else {
                $task = get_one('SELECT event_name FROM data WHERE id = ?', array($id));
                if ($task) {
                    execute('DELETE FROM data WHERE id = ?', array($id));
                    add_log('operation', '删除了发送任务：' . $task['event_name'], $ip);
                    $response = array('success' => true, 'message' => '任务删除成功');
                } else {
                    $response['message'] = '任务不存在';
                }
            }
        } catch (Exception $e) {
            $response['message'] = handle_error('删除任务失败: ' . $e->getMessage(), 'system', $ip);
        }
    }
}

// 返回JSON响应
header('Content-Type: application/json');
echo json_encode($response);
?>