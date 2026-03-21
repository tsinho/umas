/* 
  * 无礁节日祝福邮件自动发送系统 
  * 作者：无礁 
  * QQ：1722791510 
  * 邮箱：tsinho@qq.com 
  */

-- 创建config表
CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(255) NOT NULL COMMENT '定时任务接口key',
    admin_user VARCHAR(50) NOT NULL COMMENT '管理员账号',
    admin_password VARCHAR(255) NOT NULL COMMENT '管理员密码（bcrypt）',
    admin_mail VARCHAR(100) NOT NULL COMMENT '管理员收信邮箱',
    cookie VARCHAR(255) NOT NULL COMMENT '管理员cookie',
    expire_time INT NOT NULL COMMENT '令牌过期时间',
    logs_keep VARCHAR(255) NOT NULL DEFAULT 'system:86400;security:86400;operation:86400;' COMMENT '日志保留时间，单位秒',
    admin_ip VARCHAR(50) NOT NULL COMMENT '管理员用户上次登录IP',
    mail_reminder TINYINT NOT NULL DEFAULT 0 COMMENT '日志提醒，0不提醒，1仅提醒安全日志，2仅提醒系统日志，3都提醒',
    send_smtp VARCHAR(100) NOT NULL COMMENT '发信smtp服务器',
    send_port INT NOT NULL COMMENT '发信端口',
    send_name VARCHAR(50) NOT NULL COMMENT '发送人',
    send_mail VARCHAR(100) NOT NULL COMMENT '发信邮箱',
    send_key VARCHAR(255) NOT NULL COMMENT '发信授权码',
    status TINYINT NOT NULL DEFAULT 0 COMMENT '系统状态，0正常运行，1暂停运行',
    api_whitelist VARCHAR(255) DEFAULT '' COMMENT 'API白名单，多个IP用英文逗号隔开'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- 创建contacts表
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mail VARCHAR(100) NOT NULL COMMENT '接收邮箱',
    name VARCHAR(50) NOT NULL COMMENT '称呼',
    status TINYINT NOT NULL DEFAULT 0 COMMENT '状态，0启用，1停用不发送',
    birthday DATE DEFAULT NULL COMMENT '生日，此字段可为空',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='联系人表';

-- 创建templates表
CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT '模板名称',
    title VARCHAR(255) NOT NULL COMMENT '邮件标题，也支持调用自定义字段',
    content TEXT NOT NULL COMMENT '模板内容，保留换行和空格',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '模板添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邮件模板表';

-- 创建logs表
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('security', 'system', 'operation') NOT NULL COMMENT '日志类型',
    content TEXT NOT NULL COMMENT '日志内容',
    ip VARCHAR(50) NOT NULL COMMENT '操作用户IP',
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日志创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='日志表';

-- 创建data表
CREATE TABLE IF NOT EXISTS data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    is_recurring TINYINT NOT NULL DEFAULT 0 COMMENT '是否重复，0一次性邮件，1重复邮件',
    time VARCHAR(20) NOT NULL COMMENT '发送时间，一次性为年月日，重复为月日',
    contact_id INT NOT NULL COMMENT '关联联系人id',
    template_id INT NOT NULL COMMENT '关联模板id',
    event_name VARCHAR(100) NOT NULL COMMENT '备注事件名称',
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发送任务表';

-- 插入默认管理员数据
INSERT INTO config (api_key, admin_user, admin_password, admin_mail, cookie, expire_time, admin_ip, send_smtp, send_port, send_name, send_mail, send_key) 
VALUES (
    CONCAT(MD5(RAND())),
    'admin',
    '$2a$10$jaVGfQ.yAxclk84YMUs0LuHtIIHoELiBdDwjH/bG5KTijpn.Vf1I2', -- bcrypt加密的123456
    'admin@example.com',
    '',
    0,
    '',
    'smtp.qq.com',
    587,
    '无礁节日祝福系统',
    'your-email@qq.com',
    'your-auth-code'
);

-- 插入生日模板
INSERT INTO templates (name, title, content) VALUES (
    '生日',
    '生日快乐 - {name}',
    '<!DOCTYPE html> 
 <html lang="zh-CN"> 
 <head> 
     <meta charset="UTF-8"> 
     <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
     <title>生日快乐🎂🎂🎂</title> 
     <style type="text/css"> 
         /* 基础样式 */ 
         body { 
             margin: 0; 
             padding: 0; 
             font-family: "Microsoft YaHei", "微软雅黑", Arial, sans-serif; 
             background-color: #f9f3e9; 
             color: #333; 
         } 
         
         /* 主容器 */ 
         .email-container { 
             max-width: 600px; 
             margin: 0 auto; 
             background-color: #fff; 
             border-radius: 10px; 
             overflow: hidden; 
             box-shadow: 0 3px 10px rgba(0,0,0,0.1); 
         } 
         
         /* 头部样式 */ 
         .header { 
             background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%); 
             padding: 30px 20px; 
             text-align: center; 
             color: white; 
         } 
         
         /* 内容区域 */ 
         .content { 
             padding: 30px; 
             line-height: 1.6; 
         } 
         
         /* 分隔线 */ 
         .divider { 
             height: 3px; 
             background: linear-gradient(to right, transparent, #ff9a9e, transparent); 
             margin: 20px 0; 
         } 
         
         /* 祝福语样式 */ 
         .blessing { 
             font-size: 16px; 
             text-align: center; 
             margin: 20px 0; 
         } 
         
         /* 底部样式 */ 
         .footer { 
             background-color: #f5f5f5; 
             padding: 20px; 
             text-align: center; 
             font-size: 14px; 
             color: #666; 
         } 
         
         /* 装饰元素 */ 
         .decoration { 
             text-align: center; 
             margin: 20px 0; 
             font-size: 24px; 
         } 
     </style> 
 </head> 
 <body> 
     <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f9f3e9"> 
         <tr> 
             <td align="center" style="padding: 20px 0;"> 
                 <!-- 主容器 --> 
                 <table class="email-container" width="100%" cellpadding="0" cellspacing="0" border="0"> 
                     <!-- 头部 --> 
                     <tr style="color:white"> 
                         <td class="header"> 
                             <h1 style="margin: 0; font-size: 32px;">生日快乐，{name}<br>🎂🎂🎂</h1> 
                             <p style="margin: 10px 0 0; font-size: 18px;">这是您登录地球Online服务器的<br>第 <font style="color:#4d53e8;size:25">{day}</font> 天</p> 
                         </td> 
                     </tr> 
                     
                     <!-- 内容区域 --> 
                     <tr> 
                         <td class="content"> 
                             <!-- 装饰元素 --> 
                             <div class="decoration">✨ 🎉 🎈</div> 
                             
                             <!-- 祝福语 --> 
                             <div class="blessing"> 
                                 恭喜你成功解锁  <font style="color:red">{age}</font>  岁🎉🎉🎉<br><br>愿你在新的一岁里，邂逅更多未知的风景，收获满心的欢喜与热爱。愿你自由定义属于自己的精彩故事，永远是这个世界上独一无二、闪闪发光的存在。<br><br>天天开心，万事胜意<br><br>感谢登录地球Online服务器！ 
                             </div> 
                             
                             <!-- 分隔线 --> 
                             <div class="divider"></div> 
                             
                             <!-- 发件人信息 --> 
                             <table width="100%" cellpadding="0" cellspacing="0" border="0"> 
                                 <tr> 
                                     <td align="center"> 
                                         <p style="margin: 0; font-size: 14px; color: #666;"> 
                                             来自服务器好友：<strong>{send}</strong><br> 
                                             时间：{time} 
                                         </p> 
                                     </td> 
                                 </tr> 
                             </table> 
                         </td> 
                     </tr> 
                     
                     <!-- 底部 --> 
                     <tr> 
                         <td class="footer"> 
                             <p style="margin: 0;">愿你的每一天都充满阳光与欢笑 🌟</p> 
                         </td> 
                     </tr> 
                 </table> 
             </td> 
         </tr> 
     </table> 
 </body> 
 </html>'
);