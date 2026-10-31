CREATE TABLE IF NOT EXISTS `__PREFIX__mail_template` (
    `id` INT ( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` VARCHAR ( 50 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
    `title` VARCHAR ( 200 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮件标题',
    `content` text COLLATE utf8mb4_unicode_ci COMMENT '邮件内容',
    `status` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `remark` VARCHAR ( 255 ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `update_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '修改时间',
    `create_time` BIGINT ( 20 ) UNSIGNED DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '邮件模板管理';