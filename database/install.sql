/*
 Navicat Premium Dump SQL

 Source Server         : 本地root
 Source Server Type    : MySQL
 Source Server Version : 80031 (8.0.31)
 Source Host           : localhost:3306
 Source Schema         : ypay_entry_build

 Target Server Type    : MySQL
 Target Server Version : 80031 (8.0.31)
 File Encoding         : 65001

 Date: 12/12/2025 19:23:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for as_admin
-- ----------------------------
DROP TABLE IF EXISTS `as_admin`;
CREATE TABLE `as_admin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机',
  `login_failure` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录失败次数',
  `last_login_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '上次登录时间',
  `last_login_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `motto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '签名',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态:enable=启用,disable=禁用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_admin
-- ----------------------------
INSERT INTO `as_admin` VALUES (1, 'admin', 'Admin', '', 'admin@buildadmin.com', '18888888888', 0, 1765538564, '127.0.0.1', '$2y$10$40eETIGljcDfjzAfN4CnBuXsnHJHKfdkzRCskd4.BO.Z.fddl7Ena', '', '', 'enable', 1765538564, 1754925349);

-- ----------------------------
-- Table structure for as_admin_group
-- ----------------------------
DROP TABLE IF EXISTS `as_admin_group`;
CREATE TABLE `as_admin_group`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级分组',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '权限规则ID',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理分组表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_admin_group
-- ----------------------------
INSERT INTO `as_admin_group` VALUES (1, 0, '超级管理组', '*', 1, 1754925349, 1754925349);
INSERT INTO `as_admin_group` VALUES (2, 1, '一级管理员', '1,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,77,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,89', 1, 1754925349, 1754925349);
INSERT INTO `as_admin_group` VALUES (3, 2, '二级管理员', '21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43', 1, 1754925349, 1754925349);
INSERT INTO `as_admin_group` VALUES (4, 3, '三级管理员', '55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75', 1, 1754925349, 1754925349);

-- ----------------------------
-- Table structure for as_admin_group_access
-- ----------------------------
DROP TABLE IF EXISTS `as_admin_group_access`;
CREATE TABLE `as_admin_group_access`  (
  `uid` int UNSIGNED NOT NULL COMMENT '管理员ID',
  `group_id` int UNSIGNED NOT NULL COMMENT '分组ID',
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `group_id`(`group_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理分组映射表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_admin_group_access
-- ----------------------------
INSERT INTO `as_admin_group_access` VALUES (1, 1);

-- ----------------------------
-- Table structure for as_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `as_admin_log`;
CREATE TABLE `as_admin_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员用户名',
  `url` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作Url',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日志标题',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '请求数据',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_admin_log
-- ----------------------------

-- ----------------------------
-- Table structure for as_admin_rule
-- ----------------------------
DROP TABLE IF EXISTS `as_admin_rule`;
CREATE TABLE `as_admin_rule`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级菜单',
  `type` enum('menu_dir','menu','button') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:menu_dir=菜单目录,menu=菜单项,button=页面按钮',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `keepalive` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '缓存:0=关闭,1=开启',
  `extend` enum('none','add_rules_only','add_menu_only') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 243 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '菜单和权限规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_admin_rule
-- ----------------------------
INSERT INTO `as_admin_rule` VALUES (1, 0, 'menu', '控制台', 'dashboard', 'dashboard', 'fa fa-dashboard', 'tab', '', '/src/views/backend/dashboard.vue', 1, 'none', 'Remark lang', 1002, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (2, 0, 'menu_dir', '权限管理', 'auth', 'auth', 'fa fa-group', NULL, '', '', 0, 'none', '', 103, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (3, 2, 'menu', '角色组管理', 'auth/group', 'auth/group', 'fa fa-group', 'tab', '', '/src/views/backend/auth/group/index.vue', 1, 'none', 'Remark lang', 102, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (4, 3, 'button', '查看', 'auth/group/index', '', '', NULL, '', '', 0, 'none', '', -146, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (5, 3, 'button', '添加', 'auth/group/add', '', '', NULL, '', '', 0, 'none', '', -145, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (6, 3, 'button', '编辑', 'auth/group/edit', '', '', NULL, '', '', 0, 'none', '', -144, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (7, 3, 'button', '删除', 'auth/group/del', '', '', NULL, '', '', 0, 'none', '', -143, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (8, 2, 'menu', '管理员管理', 'auth/admin', 'auth/admin', 'el-icon-UserFilled', 'tab', '', '/src/views/backend/auth/admin/index.vue', 1, 'none', '', 101, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (9, 8, 'button', '查看', 'auth/admin/index', '', '', NULL, '', '', 0, 'none', '', -142, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (10, 8, 'button', '添加', 'auth/admin/add', '', '', NULL, '', '', 0, 'none', '', -141, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (11, 8, 'button', '编辑', 'auth/admin/edit', '', '', NULL, '', '', 0, 'none', '', -140, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (12, 8, 'button', '删除', 'auth/admin/del', '', '', NULL, '', '', 0, 'none', '', -139, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (13, 2, 'menu', '菜单规则管理', 'auth/rule', 'auth/rule', 'el-icon-Grid', 'tab', '', '/src/views/backend/auth/rule/index.vue', 1, 'none', '', 100, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (14, 13, 'button', '查看', 'auth/rule/index', '', '', NULL, '', '', 0, 'none', '', -138, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (15, 13, 'button', '添加', 'auth/rule/add', '', '', NULL, '', '', 0, 'none', '', -137, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (16, 13, 'button', '编辑', 'auth/rule/edit', '', '', NULL, '', '', 0, 'none', '', -136, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (17, 13, 'button', '删除', 'auth/rule/del', '', '', NULL, '', '', 0, 'none', '', -135, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (18, 13, 'button', '快速排序', 'auth/rule/sortable', '', '', NULL, '', '', 0, 'none', '', -134, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (19, 2, 'menu', '管理员日志管理', 'auth/adminLog', 'auth/adminLog', 'el-icon-List', 'tab', '', '/src/views/backend/auth/adminLog/index.vue', 1, 'none', '', 99, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (20, 19, 'button', '查看', 'auth/adminLog/index', '', '', NULL, '', '', 0, 'none', '', -133, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (21, 0, 'menu_dir', '会员管理', 'user', 'user', 'fa fa-drivers-license', NULL, '', '', 0, 'none', '', 98, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (22, 21, 'menu', '会员管理', 'user/user', 'user/user', 'fa fa-user', 'tab', '', '/src/views/backend/user/user/index.vue', 1, 'none', '', 97, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (23, 22, 'button', '查看', 'user/user/index', '', '', NULL, '', '', 0, 'none', '', -132, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (24, 22, 'button', '添加', 'user/user/add', '', '', NULL, '', '', 0, 'none', '', -131, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (25, 22, 'button', '编辑', 'user/user/edit', '', '', NULL, '', '', 0, 'none', '', -130, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (26, 22, 'button', '删除', 'user/user/del', '', '', NULL, '', '', 0, 'none', '', -129, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (27, 21, 'menu', '会员分组管理', 'user/group', 'user/group', 'fa fa-group', 'tab', '', '/src/views/backend/user/group/index.vue', 1, 'none', '', 96, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (28, 27, 'button', '查看', 'user/group/index', '', '', NULL, '', '', 0, 'none', '', -128, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (29, 27, 'button', '添加', 'user/group/add', '', '', NULL, '', '', 0, 'none', '', -127, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (30, 27, 'button', '编辑', 'user/group/edit', '', '', NULL, '', '', 0, 'none', '', -126, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (31, 27, 'button', '删除', 'user/group/del', '', '', NULL, '', '', 0, 'none', '', -125, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (32, 21, 'menu', '会员菜单管理', 'user/rule', 'user/rule', 'fa fa-th-list', 'tab', '', '/src/views/backend/user/rule/index.vue', 1, 'none', '', 95, 1, 1761745197, 1754925349);
INSERT INTO `as_admin_rule` VALUES (33, 32, 'button', '查看', 'user/rule/index', '', '', NULL, '', '', 0, 'none', '', -124, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (34, 32, 'button', '添加', 'user/rule/add', '', '', NULL, '', '', 0, 'none', '', -123, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (35, 32, 'button', '编辑', 'user/rule/edit', '', '', NULL, '', '', 0, 'none', '', -122, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (36, 32, 'button', '删除', 'user/rule/del', '', '', NULL, '', '', 0, 'none', '', -121, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (37, 32, 'button', '快速排序', 'user/rule/sortable', '', '', NULL, '', '', 0, 'none', '', -120, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (38, 21, 'menu', '会员余额管理', 'user/moneyLog', 'user/moneyLog', 'el-icon-Money', 'tab', '', '/src/views/backend/user/moneyLog/index.vue', 1, 'none', '', 94, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (39, 38, 'button', '查看', 'user/moneyLog/index', '', '', NULL, '', '', 0, 'none', '', -119, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (40, 38, 'button', '添加', 'user/moneyLog/add', '', '', NULL, '', '', 0, 'none', '', -118, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (41, 21, 'menu', '会员积分管理', 'user/scoreLog', 'user/scoreLog', 'el-icon-Discount', 'tab', '', '/src/views/backend/user/scoreLog/index.vue', 1, 'none', '', 93, 0, 1761748483, 1754925349);
INSERT INTO `as_admin_rule` VALUES (42, 41, 'button', '查看', 'user/scoreLog/index', '', '', NULL, '', '', 0, 'none', '', -117, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (43, 41, 'button', '添加', 'user/scoreLog/add', '', '', NULL, '', '', 0, 'none', '', -116, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (44, 0, 'menu_dir', '常规管理', 'routine', 'routine', 'fa fa-cogs', NULL, '', '', 0, 'none', '', 92, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (45, 44, 'menu', '系统配置', 'routine/config', 'routine/config', 'el-icon-Tools', 'tab', '', '/src/views/backend/routine/config/index.vue', 1, 'none', '', 91, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (46, 45, 'button', '查看', 'routine/config/index', '', '', NULL, '', '', 0, 'none', '', -115, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (47, 45, 'button', '编辑', 'routine/config/edit', '', '', NULL, '', '', 0, 'none', '', -114, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (48, 44, 'menu', '附件管理', 'routine/attachment', 'routine/attachment', 'fa fa-folder', 'tab', '', '/src/views/backend/routine/attachment/index.vue', 1, 'none', 'Remark lang', 90, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (49, 48, 'button', '查看', 'routine/attachment/index', '', '', NULL, '', '', 0, 'none', '', -113, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (50, 48, 'button', '编辑', 'routine/attachment/edit', '', '', NULL, '', '', 0, 'none', '', -112, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (51, 48, 'button', '删除', 'routine/attachment/del', '', '', NULL, '', '', 0, 'none', '', -111, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (52, 44, 'menu', '个人资料', 'routine/adminInfo', 'routine/adminInfo', 'fa fa-user', 'tab', '', '/src/views/backend/routine/adminInfo.vue', 1, 'none', '', 89, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (53, 52, 'button', '查看', 'routine/adminInfo/index', '', '', NULL, '', '', 0, 'none', '', -110, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (54, 52, 'button', '编辑', 'routine/adminInfo/edit', '', '', NULL, '', '', 0, 'none', '', -109, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (55, 0, 'menu_dir', '数据安全管理', 'security', 'security', 'fa fa-shield', NULL, '', '', 0, 'none', '', 88, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (56, 55, 'menu', '数据回收站', 'security/dataRecycleLog', 'security/dataRecycleLog', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycleLog/index.vue', 1, 'none', '', 87, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (57, 56, 'button', '查看', 'security/dataRecycleLog/index', '', '', NULL, '', '', 0, 'none', '', -108, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (58, 56, 'button', '删除', 'security/dataRecycleLog/del', '', '', NULL, '', '', 0, 'none', '', -107, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (59, 56, 'button', '还原', 'security/dataRecycleLog/restore', '', '', NULL, '', '', 0, 'none', '', -106, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (60, 56, 'button', '查看详情', 'security/dataRecycleLog/info', '', '', NULL, '', '', 0, 'none', '', -105, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (61, 55, 'menu', '敏感数据修改记录', 'security/sensitiveDataLog', 'security/sensitiveDataLog', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveDataLog/index.vue', 1, 'none', '', 86, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (62, 61, 'button', '查看', 'security/sensitiveDataLog/index', '', '', NULL, '', '', 0, 'none', '', -104, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (63, 61, 'button', '删除', 'security/sensitiveDataLog/del', '', '', NULL, '', '', 0, 'none', '', -103, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (64, 61, 'button', '回滚', 'security/sensitiveDataLog/rollback', '', '', NULL, '', '', 0, 'none', '', -102, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (65, 61, 'button', '查看详情', 'security/sensitiveDataLog/info', '', '', NULL, '', '', 0, 'none', '', -101, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (66, 55, 'menu', '数据回收规则管理', 'security/dataRecycle', 'security/dataRecycle', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycle/index.vue', 1, 'none', 'Remark lang', 85, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (67, 66, 'button', '查看', 'security/dataRecycle/index', '', '', NULL, '', '', 0, 'none', '', -100, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (68, 66, 'button', '添加', 'security/dataRecycle/add', '', '', NULL, '', '', 0, 'none', '', -99, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (69, 66, 'button', '编辑', 'security/dataRecycle/edit', '', '', NULL, '', '', 0, 'none', '', -98, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (70, 66, 'button', '删除', 'security/dataRecycle/del', '', '', NULL, '', '', 0, 'none', '', -97, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (71, 55, 'menu', '敏感字段规则管理', 'security/sensitiveData', 'security/sensitiveData', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveData/index.vue', 1, 'none', 'Remark lang', 84, 1, 1754925349, 1754925349);
INSERT INTO `as_admin_rule` VALUES (72, 71, 'button', '查看', 'security/sensitiveData/index', '', '', NULL, '', '', 0, 'none', '', -96, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (73, 71, 'button', '添加', 'security/sensitiveData/add', '', '', NULL, '', '', 0, 'none', '', -95, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (74, 71, 'button', '编辑', 'security/sensitiveData/edit', '', '', NULL, '', '', 0, 'none', '', -94, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (75, 71, 'button', '删除', 'security/sensitiveData/del', '', '', NULL, '', '', 0, 'none', '', -93, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (77, 45, 'button', '添加', 'routine/config/add', '', '', NULL, '', '', 0, 'none', '', -92, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (89, 1, 'button', '查看', 'dashboard/index', '', '', NULL, '', '', 0, 'none', '', -82, 1, 1761907593, 1754925349);
INSERT INTO `as_admin_rule` VALUES (103, 44, 'menu_dir', '邮件管理', 'mail', 'mail', 'el-icon-Message', 'tab', '', '', 0, 'none', '', -81, 1, 1761907593, 1754926404);
INSERT INTO `as_admin_rule` VALUES (104, 103, 'menu', '模板管理', 'mail/template', 'mail/template', 'el-icon-Document', 'tab', '', '/src/views/backend/mail/template/index.vue', 1, 'none', '', -80, 1, 1761907593, 1754926404);
INSERT INTO `as_admin_rule` VALUES (105, 104, 'button', '查看', 'mail/template/index', '', '', NULL, '', '', 0, 'none', '', -79, 1, 1761907593, 1754926404);
INSERT INTO `as_admin_rule` VALUES (106, 104, 'button', '添加', 'mail/template/add', '', '', NULL, '', '', 0, 'none', '', -78, 1, 1761907593, 1754926404);
INSERT INTO `as_admin_rule` VALUES (107, 104, 'button', '编辑', 'mail/template/edit', '', '', NULL, '', '', 0, 'none', '', -77, 1, 1761907593, 1754926404);
INSERT INTO `as_admin_rule` VALUES (108, 104, 'button', '删除', 'mail/template/del', '', '', NULL, '', '', 0, 'none', '', -76, 1, 1761907593, 1754926404);
INSERT INTO `as_admin_rule` VALUES (109, 0, 'menu_dir', '支付通道管理', 'channel', 'channel', 'fa fa-th-list', NULL, '', '', 0, 'none', '', -74, 1, 1765708627, 1754933526);
INSERT INTO `as_admin_rule` VALUES (116, 136, 'menu', '服务商管理', 'serviceProvider', 'serviceProvider', 'fa fa-user-secret', 'tab', '', '/src/views/backend/serviceProvider/index.vue', 1, 'none', '', -50, 1, 1763025185, 1755268720);
INSERT INTO `as_admin_rule` VALUES (117, 116, 'button', '查看', 'serviceProvider/index', '', '', NULL, '', '', 0, 'none', '', -66, 1, 1761907593, 1755268720);
INSERT INTO `as_admin_rule` VALUES (118, 116, 'button', '添加', 'serviceProvider/add', '', '', NULL, '', '', 0, 'none', '', -65, 1, 1761907593, 1755268720);
INSERT INTO `as_admin_rule` VALUES (119, 116, 'button', '编辑', 'serviceProvider/edit', '', '', NULL, '', '', 0, 'none', '', -64, 1, 1761907593, 1755268720);
INSERT INTO `as_admin_rule` VALUES (120, 116, 'button', '删除', 'serviceProvider/del', '', '', NULL, '', '', 0, 'none', '', -63, 1, 1761907593, 1755268720);
INSERT INTO `as_admin_rule` VALUES (121, 116, 'button', '快速排序', 'serviceProvider/sortable', '', '', NULL, '', '', 0, 'none', '', -62, 1, 1761907593, 1755268720);
INSERT INTO `as_admin_rule` VALUES (134, 116, 'menu', 'OAuth2配置', 'serviceProvider/config', 'serviceProvider/config', 'fa fa-circle-o', 'tab', '', '/src/views/backend/serviceProvider/config.vue', 1, 'add_rules_only', '', -61, 1, 1765351964, 1755329891);
INSERT INTO `as_admin_rule` VALUES (135, 134, 'menu', '编辑配置项', 'serviceProvider/config/:id', 'serviceProvider/config/:id', 'fa fa-circle-o', 'tab', '', '/src/views/backend/serviceProvider/config.vue', 1, 'none', '', -60, 1, 1761907593, 1755330081);
INSERT INTO `as_admin_rule` VALUES (136, 0, 'menu_dir', '进件管理', 'merchant', 'merchant', 'el-icon-Avatar', NULL, '', '', 0, 'none', '', -59, 1, 1763022826, 1755709731);
INSERT INTO `as_admin_rule` VALUES (137, 136, 'menu', '商户进件', 'merchant/merchantEntry', 'merchant/merchantEntry', 'fa fa-user-plus', 'tab', '', '/src/views/backend/merchant/merchantEntry/index.vue', 1, 'none', '', -51, 1, 1765357801, 1755709731);
INSERT INTO `as_admin_rule` VALUES (138, 137, 'button', '查看', 'merchant/merchantEntry/index', '', '', NULL, '', '', 0, 'none', '', -57, 1, 1761907593, 1755709731);
INSERT INTO `as_admin_rule` VALUES (139, 137, 'button', '添加', 'merchant/merchantEntry/add', '', '', NULL, '', '', 0, 'none', '', -56, 1, 1761907593, 1755709731);
INSERT INTO `as_admin_rule` VALUES (140, 137, 'button', '编辑', 'merchant/merchantEntry/edit', '', '', NULL, '', '', 0, 'none', '', -55, 1, 1761907593, 1755709731);
INSERT INTO `as_admin_rule` VALUES (141, 137, 'button', '删除', 'merchant/merchantEntry/del', '', '', NULL, '', '', 0, 'none', '', -54, 1, 1761907593, 1755709731);
INSERT INTO `as_admin_rule` VALUES (142, 137, 'button', '快速排序', 'merchant/merchantEntry/sortable', '', '', NULL, '', '', 0, 'none', '', -53, 1, 1761907593, 1755709731);
INSERT INTO `as_admin_rule` VALUES (143, 136, 'menu', '进件商户管理', 'merchant/merchant', 'merchant/merchant', 'el-icon-Avatar', 'tab', '', '/src/views/backend/merchant/merchant/index.vue', 0, 'none', '', -52, 0, 1765351906, 1755710214);
INSERT INTO `as_admin_rule` VALUES (144, 143, 'button', '查看', 'merchant/merchant/index', '', '', NULL, '', '', 0, 'none', '', -49, 1, 1761907593, 1755710214);
INSERT INTO `as_admin_rule` VALUES (145, 143, 'button', '添加', 'merchant/merchant/add', '', '', NULL, '', '', 0, 'none', '', -48, 1, 1761907593, 1755710214);
INSERT INTO `as_admin_rule` VALUES (146, 143, 'button', '编辑', 'merchant/merchant/edit', '', '', NULL, '', '', 0, 'none', '', -47, 1, 1761907593, 1755710214);
INSERT INTO `as_admin_rule` VALUES (147, 143, 'button', '删除', 'merchant/merchant/del', '', '', NULL, '', '', 0, 'none', '', -46, 1, 1761907593, 1755710214);
INSERT INTO `as_admin_rule` VALUES (148, 143, 'button', '快速排序', 'merchant/merchant/sortable', '', '', NULL, '', '', 0, 'none', '', -45, 1, 1761907593, 1755710214);
INSERT INTO `as_admin_rule` VALUES (149, 44, 'menu_dir', '短信管理', 'sms', 'sms', 'el-icon-ChatLineRound', 'tab', '', '', 0, 'none', '', -44, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (150, 149, 'menu', '短信配置', 'sms/config', 'sms/config', 'el-icon-Setting', 'tab', '', '/src/views/backend/sms/config.vue', 1, 'none', '', -43, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (151, 150, 'button', '查看', 'sms/config/getConfigKey', '', '', NULL, '', '', 0, 'none', '', -42, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (152, 150, 'button', '修改配置', 'sms/config/saveConfig', '', '', NULL, '', '', 0, 'none', '', -41, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (153, 149, 'menu', '模板变量管理', 'sms/variable', 'sms/variable', 'fa fa-asterisk', 'tab', '', '/src/views/backend/sms/variable/index.vue', 1, 'none', '', -40, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (154, 153, 'button', '查看', 'sms/variable/index', '', '', NULL, '', '', 0, 'none', '', -39, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (155, 153, 'button', '添加', 'sms/variable/add', '', '', NULL, '', '', 0, 'none', '', -38, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (156, 153, 'button', '编辑', 'sms/variable/edit', '', '', NULL, '', '', 0, 'none', '', -37, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (157, 153, 'button', '删除', 'sms/variable/del', '', '', NULL, '', '', 0, 'none', '', -36, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (158, 149, 'menu', '短信模板管理', 'sms/template', 'sms/template', 'el-icon-Document', 'tab', '', '/src/views/backend/sms/template/index.vue', 1, 'none', '不同服务商可能需要不同的模板ID，所以单个模板并不一定适用于所有服务商，若有轮询服务商发送的需求，多数情况需自行通过代码实现', -35, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (159, 158, 'button', '查看', 'sms/template/index', '', '', NULL, '', '', 0, 'none', '', -34, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (160, 158, 'button', '添加', 'sms/template/add', '', '', NULL, '', '', 0, 'none', '', -33, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (161, 158, 'button', '编辑', 'sms/template/edit', '', '', NULL, '', '', 0, 'none', '', -32, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (162, 158, 'button', '删除', 'sms/template/del', '', '', NULL, '', '', 0, 'none', '', -31, 1, 1761907593, 1755871428);
INSERT INTO `as_admin_rule` VALUES (163, 195, 'menu', '会员套餐', 'user/subscribe', 'user/subscribe', 'el-icon-IceCream', 'tab', '', '/src/views/backend/user/subscribe/index.vue', 1, 'none', '', -28, 1, 1765390811, 1756708306);
INSERT INTO `as_admin_rule` VALUES (164, 163, 'button', '查看', 'user/subscribe/index', '', 'fa fa-circle-o', NULL, '', '', 0, 'none', '', -27, 1, 1761987624, 1756708306);
INSERT INTO `as_admin_rule` VALUES (165, 163, 'button', '添加', 'user/subscribe/add', '', 'fa fa-circle-o', NULL, '', '', 0, 'none', '', -26, 1, 1761987631, 1756708306);
INSERT INTO `as_admin_rule` VALUES (166, 163, 'button', '编辑', 'user/subscribe/edit', '', 'fa fa-circle-o', NULL, '', '', 0, 'none', '', -25, 1, 1761987637, 1756708306);
INSERT INTO `as_admin_rule` VALUES (167, 163, 'button', '删除', 'user/subscribe/del', '', 'fa fa-circle-o', NULL, '', '', 0, 'none', '', -24, 1, 1761987642, 1756708306);
INSERT INTO `as_admin_rule` VALUES (168, 163, 'button', '快速排序', 'user/subscribe/sortable', '', 'fa fa-circle-o', NULL, '', '', 0, 'none', '', -23, 1, 1761987649, 1756708306);
INSERT INTO `as_admin_rule` VALUES (175, 195, 'menu', '订单管理', 'order', 'order', 'fa fa-th-list', 'tab', '', '/src/views/backend/order/index.vue', 1, 'none', '', -29, 1, 1765390811, 1757926735);
INSERT INTO `as_admin_rule` VALUES (176, 175, 'button', '查看', 'order/index', '', '', NULL, '', '', 0, 'none', '', -15, 1, 1761907593, 1757926735);
INSERT INTO `as_admin_rule` VALUES (177, 175, 'button', '添加', 'order/add', '', '', NULL, '', '', 0, 'none', '', -14, 1, 1761907593, 1757926735);
INSERT INTO `as_admin_rule` VALUES (178, 175, 'button', '编辑', 'order/edit', '', '', NULL, '', '', 0, 'none', '', -13, 1, 1761907593, 1757926735);
INSERT INTO `as_admin_rule` VALUES (179, 175, 'button', '删除', 'order/del', '', '', NULL, '', '', 0, 'none', '', -12, 1, 1761907593, 1757926735);
INSERT INTO `as_admin_rule` VALUES (180, 175, 'button', '快速排序', 'order/sortable', '', '', NULL, '', '', 0, 'none', '', -11, 1, 1761907593, 1757926735);
INSERT INTO `as_admin_rule` VALUES (182, 195, 'menu', '收款通道', 'sys/payChannel', 'sys/payChannel', 'fa fa-paypal', 'tab', '', '/src/views/backend/sys/payChannel/index.vue', 1, 'none', '', -10, 1, 1761907593, 1760772508);
INSERT INTO `as_admin_rule` VALUES (183, 182, 'button', '查看', 'sys/payChannel/index', '', '', NULL, '', '', 0, 'none', '', -9, 1, 1761907593, 1760772508);
INSERT INTO `as_admin_rule` VALUES (184, 182, 'button', '添加', 'sys/payChannel/add', '', '', NULL, '', '', 0, 'none', '', -8, 1, 1761907593, 1760772508);
INSERT INTO `as_admin_rule` VALUES (185, 182, 'button', '编辑', 'sys/payChannel/edit', '', '', NULL, '', '', 0, 'none', '', -7, 1, 1761907593, 1760772508);
INSERT INTO `as_admin_rule` VALUES (186, 182, 'button', '删除', 'sys/payChannel/del', '', '', NULL, '', '', 0, 'none', '', -6, 1, 1761907593, 1760772508);
INSERT INTO `as_admin_rule` VALUES (187, 182, 'button', '快速排序', 'sys/payChannel/sortable', '', '', NULL, '', '', 0, 'none', '', -5, 1, 1761907593, 1760772508);
INSERT INTO `as_admin_rule` VALUES (188, 195, 'menu', '充值订单', 'sys/order', 'sys/order', 'fa fa-list', 'tab', '', '/src/views/backend/sys/order/index.vue', 1, 'none', '', -4, 1, 1761907593, 1760789934);
INSERT INTO `as_admin_rule` VALUES (189, 188, 'button', '查看', 'sys/order/index', '', '', NULL, '', '', 0, 'none', '', -3, 1, 1761907593, 1760789934);
INSERT INTO `as_admin_rule` VALUES (190, 188, 'button', '添加', 'sys/order/add', '', '', NULL, '', '', 0, 'none', '', -2, 1, 1761907593, 1760789934);
INSERT INTO `as_admin_rule` VALUES (191, 188, 'button', '编辑', 'sys/order/edit', '', '', NULL, '', '', 0, 'none', '', -1, 1, 1761907593, 1760789934);
INSERT INTO `as_admin_rule` VALUES (192, 188, 'button', '删除', 'sys/order/del', '', '', NULL, '', '', 0, 'none', '', 0, 1, 1761907593, 1760789934);
INSERT INTO `as_admin_rule` VALUES (193, 188, 'button', '快速排序', 'sys/order/sortable', '', '', NULL, '', '', 0, 'none', '', 1, 1, 1761907593, 1760789934);
INSERT INTO `as_admin_rule` VALUES (194, 103, 'menu', '邮件配置', 'mail/config', 'mail/config', 'fa fa-gear', 'tab', '', '/src/views/backend/mail/config.vue', 0, 'none', '', 2, 1, 1761907593, 1761752583);
INSERT INTO `as_admin_rule` VALUES (195, 0, 'menu_dir', '商城管理', 'store', 'store', 'fa fa-shopping-cart', 'tab', '', '', 0, 'none', '', 3, 1, 1761755892, 1761755892);
INSERT INTO `as_admin_rule` VALUES (196, 137, 'menu', '进件资料', 'merchant/merchantEntry/edit', 'merchant/merchantEntry/edit/:id', 'fa fa-circle-o', 'tab', '', '/src/views/backend/merchant/merchantEntry/pageForm.vue', 1, 'add_rules_only', '', 3, 1, 1765351955, 1762954854);
INSERT INTO `as_admin_rule` VALUES (197, 0, 'menu_dir', '主题管理', 'theme', 'theme', 'el-icon-BrushFilled', 'tab', '', '', 0, 'none', '', 3, 1, 1763360726, 1763360726);
INSERT INTO `as_admin_rule` VALUES (198, 197, 'menu', '首页模板', 'theme/homeTemplate', 'theme/homeTemplate', 'fa fa-home', 'tab', '', '/src/views/backend/theme/homeTemplate/index.vue', 1, 'none', '', 3, 1, 1763360983, 1763360849);
INSERT INTO `as_admin_rule` VALUES (199, 44, 'menu_dir', '快捷登录管理', 'oauth', 'oauth', 'fa fa-coffee', NULL, '', '', 0, 'none', '', 3, 1, 1763452961, 1763452007);
INSERT INTO `as_admin_rule` VALUES (200, 199, 'menu', '快捷登录配置', 'oauth/config', 'oauth/config', 'fa fa-gear', 'tab', '', '/src/views/backend/oauth/config/index.vue', 1, 'none', '', 3, 1, 1763453026, 1763452007);
INSERT INTO `as_admin_rule` VALUES (201, 200, 'button', '查看', 'oauth/config/index', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452007, 1763452007);
INSERT INTO `as_admin_rule` VALUES (202, 200, 'button', '添加', 'oauth/config/add', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452007, 1763452007);
INSERT INTO `as_admin_rule` VALUES (203, 200, 'button', '编辑', 'oauth/config/edit', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452007, 1763452007);
INSERT INTO `as_admin_rule` VALUES (204, 200, 'button', '删除', 'oauth/config/del', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452007, 1763452007);
INSERT INTO `as_admin_rule` VALUES (205, 200, 'button', '快速排序', 'oauth/config/sortable', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452007, 1763452007);
INSERT INTO `as_admin_rule` VALUES (206, 199, 'menu', '快捷登录日志', 'oauth/log', 'oauth/log', 'fa fa-list-alt', 'tab', '', '/src/views/backend/oauth/log/index.vue', 1, 'none', '', 3, 1, 1763452984, 1763452665);
INSERT INTO `as_admin_rule` VALUES (207, 206, 'button', '查看', 'oauth/log/index', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452665, 1763452665);
INSERT INTO `as_admin_rule` VALUES (208, 206, 'button', '添加', 'oauth/log/add', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452665, 1763452665);
INSERT INTO `as_admin_rule` VALUES (209, 206, 'button', '编辑', 'oauth/log/edit', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452665, 1763452665);
INSERT INTO `as_admin_rule` VALUES (210, 206, 'button', '删除', 'oauth/log/del', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452665, 1763452665);
INSERT INTO `as_admin_rule` VALUES (211, 206, 'button', '快速排序', 'oauth/log/sortable', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1763452665, 1763452665);
INSERT INTO `as_admin_rule` VALUES (218, 109, 'menu', '通道支付类型', 'channel/payType', 'channel/payType', 'fa fa-th-large', 'tab', '', '/src/views/backend/channel/payType/index.vue', 1, 'none', '', 3, 1, 1764056615, 1764055078);
INSERT INTO `as_admin_rule` VALUES (219, 218, 'button', '查看', 'channel/payType/index', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055078, 1764055078);
INSERT INTO `as_admin_rule` VALUES (220, 218, 'button', '添加', 'channel/payType/add', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055078, 1764055078);
INSERT INTO `as_admin_rule` VALUES (221, 218, 'button', '编辑', 'channel/payType/edit', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055078, 1764055078);
INSERT INTO `as_admin_rule` VALUES (222, 218, 'button', '删除', 'channel/payType/del', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055078, 1764055078);
INSERT INTO `as_admin_rule` VALUES (223, 218, 'button', '快速排序', 'channel/payType/sortable', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055078, 1764055078);
INSERT INTO `as_admin_rule` VALUES (224, 109, 'menu', '通道类型', 'channel/type', 'channel/type', 'fa fa-th', 'tab', '', '/src/views/backend/channel/type/index.vue', 1, 'none', '', 3, 1, 1764056621, 1764055501);
INSERT INTO `as_admin_rule` VALUES (225, 224, 'button', '查看', 'channel/type/index', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055501, 1764055501);
INSERT INTO `as_admin_rule` VALUES (226, 224, 'button', '添加', 'channel/type/add', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055501, 1764055501);
INSERT INTO `as_admin_rule` VALUES (227, 224, 'button', '编辑', 'channel/type/edit', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055501, 1764055501);
INSERT INTO `as_admin_rule` VALUES (228, 224, 'button', '删除', 'channel/type/del', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055501, 1764055501);
INSERT INTO `as_admin_rule` VALUES (229, 224, 'button', '快速排序', 'channel/type/sortable', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764055501, 1764055501);
INSERT INTO `as_admin_rule` VALUES (230, 109, 'menu', '通道账户', 'channel/account', 'channel/account', 'el-icon-Avatar', 'tab', '', '/src/views/backend/channel/account/index.vue', 1, 'none', '', 3, 1, 1764056527, 1764056104);
INSERT INTO `as_admin_rule` VALUES (231, 230, 'button', '查看', 'channel/account/index', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764056104, 1764056104);
INSERT INTO `as_admin_rule` VALUES (232, 230, 'button', '添加', 'channel/account/add', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764056104, 1764056104);
INSERT INTO `as_admin_rule` VALUES (233, 230, 'button', '编辑', 'channel/account/edit', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764056104, 1764056104);
INSERT INTO `as_admin_rule` VALUES (234, 230, 'button', '删除', 'channel/account/del', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764056104, 1764056104);
INSERT INTO `as_admin_rule` VALUES (235, 230, 'button', '快速排序', 'channel/account/sortable', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1764056104, 1764056104);
INSERT INTO `as_admin_rule` VALUES (236, 0, 'menu', '在线更新', 'onlineUpdate', 'onlineUpdate', 'fa fa-cloud-upload', 'tab', '', '/src/views/backend/onlineUpdate.vue', 0, 'none', '', -75, 1, 1765708627, 1764939022);
INSERT INTO `as_admin_rule` VALUES (237, 195, 'menu', '退款记录', 'refundRecord', 'refundRecord', 'local-refund', 'tab', '', '/src/views/backend/refundRecord/index.vue', 1, 'none', '', -30, 1, 1765390806, 1765389890);
INSERT INTO `as_admin_rule` VALUES (238, 237, 'button', '查看', 'refundRecord/index', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1765389890, 1765389890);
INSERT INTO `as_admin_rule` VALUES (239, 237, 'button', '添加', 'refundRecord/add', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1765389890, 1765389890);
INSERT INTO `as_admin_rule` VALUES (240, 237, 'button', '编辑', 'refundRecord/edit', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1765389890, 1765389890);
INSERT INTO `as_admin_rule` VALUES (241, 237, 'button', '删除', 'refundRecord/del', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1765389890, 1765389890);
INSERT INTO `as_admin_rule` VALUES (242, 237, 'button', '快速排序', 'refundRecord/sortable', '', '', NULL, '', '', 0, 'none', '', 3, 1, 1765389890, 1765389890);
INSERT INTO `as_admin_rule` VALUES (243, 21, 'menu', '用户实名认证', 'user/realName', 'user/realName', 'fa fa-vcard', 'tab', '', '/src/views/backend/user/realName/index.vue', 1, 'none', '', 1, 1, 1765696903, 1765696851);
INSERT INTO `as_admin_rule` VALUES (244, 243, 'button', '查看', 'user/realName/index', '', '', NULL, '', '', 0, 'none', '', 1, 1, 1765696851, 1765696851);
INSERT INTO `as_admin_rule` VALUES (245, 243, 'button', '添加', 'user/realName/add', '', '', NULL, '', '', 0, 'none', '', 1, 1, 1765696851, 1765696851);
INSERT INTO `as_admin_rule` VALUES (246, 243, 'button', '编辑', 'user/realName/edit', '', '', NULL, '', '', 0, 'none', '', 1, 1, 1765696851, 1765696851);
INSERT INTO `as_admin_rule` VALUES (247, 243, 'button', '删除', 'user/realName/del', '', '', NULL, '', '', 0, 'none', '', 1, 1, 1765696851, 1765696851);
INSERT INTO `as_admin_rule` VALUES (248, 243, 'button', '快速排序', 'user/realName/sortable', '', '', NULL, '', '', 0, 'none', '', 1, 1, 1765696851, 1765696851);

-- ----------------------------
-- Table structure for as_area
-- ----------------------------
DROP TABLE IF EXISTS `as_area`;
CREATE TABLE `as_area`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int UNSIGNED NULL DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '简称',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '全称',
  `level` tinyint UNSIGNED NULL DEFAULT NULL COMMENT '层级:1=省,2=市,3=区/县',
  `pinyin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '长途区号',
  `zip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '首字母',
  `lng` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '经度',
  `lat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '省份地区表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_area
-- ----------------------------

-- ----------------------------
-- Table structure for as_attachment
-- ----------------------------
DROP TABLE IF EXISTS `as_attachment`;
CREATE TABLE `as_attachment`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `topic` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '细目',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '上传管理员ID',
  `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '上传用户ID',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物理路径',
  `media_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '媒体ID',
  `width` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '宽度',
  `height` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '高度',
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原始名称',
  `size` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '大小',
  `mimetype` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mime类型',
  `quote` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '上传(引用)次数',
  `storage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '存储方式',
  `sha1` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'sha1编码',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展信息',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `last_upload_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '最后上传时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '附件表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for as_captcha
-- ----------------------------
DROP TABLE IF EXISTS `as_captcha`;
CREATE TABLE `as_captcha`  (
  `key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证码Key',
  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证码(加密后)',
  `captcha` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '验证码数据',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `expire_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '验证码表' ROW_FORMAT = DYNAMIC;


-- ----------------------------
-- Table structure for as_channel_account
-- ----------------------------
DROP TABLE IF EXISTS `as_channel_account`;
CREATE TABLE `as_channel_account`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `pay_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付类型',
  `channel_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通道类型',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '通道配置',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `online_status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '在线状态:0=离线,1=在线',
  `pay_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '支付配置',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展内容',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '通道账户' ROW_FORMAT = DYNAMIC;


-- ----------------------------
-- Table structure for as_channel_pay_type
-- ----------------------------
DROP TABLE IF EXISTS `as_channel_pay_type`;
CREATE TABLE `as_channel_pay_type`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型标识',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展内容',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '通道支付类型' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_channel_pay_type
-- ----------------------------
INSERT INTO `as_channel_pay_type` VALUES (1, 'wxpay', '微信支付', 0, NULL, 1);
INSERT INTO `as_channel_pay_type` VALUES (2, 'alipay', '支付宝', 0, NULL, 1);
INSERT INTO `as_channel_pay_type` VALUES (3, 'qqpay', 'QQ支付', 0, NULL, 1);

-- ----------------------------
-- Table structure for as_channel_type
-- ----------------------------
DROP TABLE IF EXISTS `as_channel_type`;
CREATE TABLE `as_channel_type`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pay_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '支付类型',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通道类型',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通道名称',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '通道配置',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展内容',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '通道类型' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_channel_type
-- ----------------------------
INSERT INTO `as_channel_type` VALUES (1, 'alipay', 'alipay_personal_not_hang', '支付宝个人免挂', NULL, NULL, 0, '', 1);
INSERT INTO `as_channel_type` VALUES (2, 'wxpay', 'wxpay_official_special', '微信官方-特约商户', NULL, NULL, 0, '', 1);

-- ----------------------------
-- Table structure for as_cloud
-- ----------------------------
DROP TABLE IF EXISTS `as_cloud`;
CREATE TABLE `as_cloud`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `platform_code` enum('wxpay','qqpay','alipay') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'wxpay' COMMENT '平台类型:wxpay=微信,qqpay=QQ,alipay=支付宝',
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '云端类型',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `device_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '设备类型',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '云端地址',
  `proxy` enum('close','open') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'close' COMMENT '云端代理:close=关闭,open=开启',
  `proxy_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '云端代理配置',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int NULL DEFAULT NULL COMMENT '权重',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '云端管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_cloud
-- ----------------------------

-- ----------------------------
-- Table structure for as_config
-- ----------------------------
DROP TABLE IF EXISTS `as_config`;
CREATE TABLE `as_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量输入组件类型',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '变量值',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '字典数据',
  `rule` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展属性',
  `allow_del` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '允许删除:0=否,1=是',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 43 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_config
-- ----------------------------
INSERT INTO `as_config` VALUES (1, 'config_group', 'basics', 'Config group', '', 'array', '[{\"key\":\"basics\",\"value\":\"Basics\"},{\"key\":\"login_reg\",\"value\":\"\\u767b\\u5f55|\\u6ce8\\u518c\"},{\"key\":\"member\",\"value\":\"\\u4f1a\\u5458\\u914d\\u7f6e\"},{\"key\":\"security\",\"value\":\"\\u5b89\\u5168\\u914d\\u7f6e\"},{\"key\":\"extend\",\"value\":\"\\u529f\\u80fd\\u589e\\u5f3a\"}]', NULL, 'required', '', 0, -1);
INSERT INTO `as_config` VALUES (2, 'site_name', 'basics', '站点名称', '', 'string', 'YPayPlus', NULL, 'required', '', 0, 99);
INSERT INTO `as_config` VALUES (3, 'site_title', 'basics', '站点副标题', 'SEO标题', 'string', 'YPay-支付进件', NULL, '', '', 0, 98);
INSERT INTO `as_config` VALUES (4, 'site_keyword', 'basics', '站点关键词', 'SEO关键词', 'string', 'YPay,源支付,支付进件', NULL, '', '', 0, 97);
INSERT INTO `as_config` VALUES (5, 'site_description', 'basics', '站点描述', 'SEO描述', 'textarea', '工匠精神出品', NULL, '', '', 0, 96);
INSERT INTO `as_config` VALUES (6, 'site_ico', 'basics', 'Favicon', '', 'image', '/storage/default/20251212/iconab5056a8a9fec620b904d448a21c8de3cf6ac8ff.png', NULL, '', '', 0, 95);
INSERT INTO `as_config` VALUES (7, 'site_logo', 'basics', 'LOGO', '', 'image', '/storage/default/20251212/logo131a6e69ae168e055666a28913e28cbb173f70b5.png', NULL, '', '', 0, 94);
INSERT INTO `as_config` VALUES (8, 'footer_content', 'basics', '底部版权', '', 'editor', '<p>@c 8888888</p>', NULL, '', '', 0, 93);
INSERT INTO `as_config` VALUES (9, 'record_number', 'basics', 'Record number', '域名备案号', 'string', '渝ICP备8888888号-1', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (10, 'backend_entrance', 'basics', '自定义后台入口', '', 'string', '/admin', NULL, 'required', '', 0, 0);
INSERT INTO `as_config` VALUES (11, 'version', 'basics', 'Version number', '系统版本号', 'string', '0.8.0', NULL, 'required', '', 0, 0);
INSERT INTO `as_config` VALUES (12, 'time_zone', 'basics', 'time zone', '', 'string', 'Asia/Shanghai', NULL, 'required', '', 0, 0);
INSERT INTO `as_config` VALUES (13, 'no_access_ip', 'basics', 'No access ip', '禁止访问站点的ip列表,一行一个', 'textarea', '', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (14, 'smtp_server', 'mail', 'smtp server', '', 'string', 'smtp.qq.com', NULL, '', '', 0, 9);
INSERT INTO `as_config` VALUES (15, 'smtp_port', 'mail', 'smtp port', '', 'string', '', NULL, '', '', 0, 8);
INSERT INTO `as_config` VALUES (16, 'smtp_user', 'mail', 'smtp user', '', 'string', '', NULL, '', '', 0, 7);
INSERT INTO `as_config` VALUES (17, 'smtp_pass', 'mail', 'smtp pass', '', 'string', '', NULL, '', '', 0, 6);
INSERT INTO `as_config` VALUES (18, 'smtp_verification', 'mail', 'smtp verification', '', 'select', 'SSL', '{\"SSL\":\"SSL\",\"TLS\":\"TLS\"}', '', '', 0, 5);
INSERT INTO `as_config` VALUES (19, 'smtp_sender_mail', 'mail', 'smtp sender mail', '', 'string', '', NULL, 'email', '', 0, 4);
INSERT INTO `as_config` VALUES (20, 'config_quick_entrance', 'config_quick_entrance', 'Config Quick entrance', '', 'array', '[{\"key\":\"\\u6570\\u636e\\u56de\\u6536\\u89c4\\u5219\\u914d\\u7f6e\",\"value\":\"security\\/dataRecycle\"},{\"key\":\"\\u654f\\u611f\\u6570\\u636e\\u89c4\\u5219\\u914d\\u7f6e\",\"value\":\"security\\/sensitiveData\"},{\"key\":\"\\u77ed\\u4fe1\\u914d\\u7f6e\",\"value\":\"sms\\/config\"}]', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (21, 'login_method', 'login_reg', '登录方式', '', 'selects', '[\"username\",\"mobile\",\"email\"]', '{\"username\":\"\\u7528\\u6237\\u540d\",\"mobile\":\"\\u624b\\u673a\",\"email\":\"\\u90ae\\u7bb1\"}', '', '', 0, 0);
INSERT INTO `as_config` VALUES (22, 'reg_method', 'login_reg', '注册方式', '', 'selects', '[\"username\",\"mobile\",\"email\"]', '{\"username\":\"\\u7528\\u6237\\u540d\",\"mobile\":\"\\u624b\\u673a\",\"email\":\"\\u90ae\\u7bb1\"}', '', '', 0, 0);
INSERT INTO `as_config` VALUES (23, 'is_login_captcha', 'login_reg', '开启登录验证码', '', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (24, 'is_reg_captcha', 'login_reg', '开启注册验证码', '', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (25, 'force_bind_mobile', 'member', '强制绑定手机', '', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (26, 'force_bind_email', 'member', '强制绑定邮箱', '', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (27, 'is_send_captcha', 'security', '发信验证码', '在发送邮件或手机短信时验证', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (28, 'send_frequent_interval', 'security', '发送频繁间隔(秒)', '防止用户频繁发送', 'number', '60', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (29, 'user_login_retry', 'login_reg', '用户登录失败次数', '0为无限制，登录失败次数达到上限时需要1天后才可登录', 'number', '10', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (30, 'is_user_sso', 'login_reg', '用户单点登录', '当处登录它处失效，用户始终只能在一个地方登录', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (31, 'aliyun_ocr', 'extend', '阿里云OCR密钥配置', '', 'secretKey', '{\"id\":\"\",\"secret\":\"\"}', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (32, 'is_user_edit_profile', 'member', '允许用户编辑资料', '', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (36, 'login_page_img', 'login_reg', '登录/注册背景设置', '', 'loginPageImg', '{\"type\":\"default\",\"imageUrl\":\"\\/storage\\/default\\/20251117\\/2025-08-05_14-47f3f6ded665b7ce11927a8c51c7a836d75f9e30e.jpg\",\"text\":\"<p>\\u652f\\u4ed8\\u8fdb\\u4ef6<\\/p>\"}', NULL, '', '', 1, 0);
INSERT INTO `as_config` VALUES (37, 'home_template', 'hide', '首页模板', '', 'string', 'default', NULL, '', '', 1, 0);
INSERT INTO `as_config` VALUES (38, 'is_home', 'basics', '启用首页', '', 'radio', 'open', '{\"open\":\"\\u5f00\\u542f\",\"close\":\"\\u5173\\u95ed\"}', '', '{\"baInputExtend\":{\"border\":true}}', 1, 92);
INSERT INTO `as_config` VALUES (39, 'is_totp', 'security', '前台TOTP安全验证', '', 'radio', 'open', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 1, 0);
INSERT INTO `as_config` VALUES (40, 'entry_list_style', 'member', '进件列表样式', '', 'radio', 'style1', '{\"style1\":\"\\u6837\\u5f0f\\u4e00\",\"style2\":\"\\u6837\\u5f0f\\u4e8c\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);
INSERT INTO `as_config` VALUES (41, 'real_name_config', 'extend', '实名认证配置', '', 'realNameConfig', '{\"type\":\"close\"}', NULL, '', '', 0, 0);
INSERT INTO `as_config` VALUES (42, 'force_real_name', 'member', '强制实名认证', '', 'radio', 'close', '{\"close\":\"\\u5173\\u95ed\",\"open\":\"\\u5f00\\u542f\"}', '', '{\"baInputExtend\":{\"border\":true}}', 0, 0);

-- ----------------------------
-- Table structure for as_crud_log
-- ----------------------------
DROP TABLE IF EXISTS `as_crud_log`;
CREATE TABLE `as_crud_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `table_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表名',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '注释',
  `table` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '数据表数据',
  `fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '字段数据',
  `sync` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步记录',
  `status` enum('delete','success','error','start') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'start' COMMENT '状态:delete=已删除,success=成功,error=失败,start=生成中',
  `connection` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'CRUD记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_crud_log
-- ----------------------------

-- ----------------------------
-- Table structure for as_entry_channel
-- ----------------------------
DROP TABLE IF EXISTS `as_entry_channel`;
CREATE TABLE `as_entry_channel`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `sp_id` int UNSIGNED NOT NULL COMMENT '服务商ID',
  `platform_code` enum('wxpay','alipay') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'wxpay' COMMENT '平台代码:wxpay=微信支付,alipay=支付宝',
  `price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '进件价格',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'OAuth2配置',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '服务商平台配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_entry_channel
-- ----------------------------

-- ----------------------------
-- Table structure for as_mail_template
-- ----------------------------
DROP TABLE IF EXISTS `as_mail_template`;
CREATE TABLE `as_mail_template`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮件标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '邮件内容',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '邮件模板管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_mail_template
-- ----------------------------
INSERT INTO `as_mail_template` VALUES (1, 'user_email_login', '用户邮件登录', '登录验证码{code}', 1, '', 1755937317, 1755937317);
INSERT INTO `as_mail_template` VALUES (2, 'user_register', '用户注册', '欢迎加入，您的注册验证码为：{code}', 1, '', 1755972230, 1755972230);
INSERT INTO `as_mail_template` VALUES (3, 'user_retrieve_pwd', '用户找回密码', '找回密码验证码：{code}', 1, '', 1756026506, 1756026506);
INSERT INTO `as_mail_template` VALUES (4, 'user_change_email', '用户修改邮箱（绑定、换绑）', '你的验证码：{code}', 1, '', 1761843937, 1761843937);
INSERT INTO `as_mail_template` VALUES (5, 'user_email_verify', '验证邮箱账户', '您的验证码{code}', 1, '', 1761894028, 1761894028);

-- ----------------------------
-- Table structure for as_merchant
-- ----------------------------
DROP TABLE IF EXISTS `as_merchant`;
CREATE TABLE `as_merchant`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `sp_id` int UNSIGNED NOT NULL COMMENT '服务商ID',
  `entry_id` int UNSIGNED NULL DEFAULT NULL COMMENT '进件ID',
  `ec_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商户名称',
  `platform_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '平台代码',
  `merchant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商户号',
  `merchant_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '商户信息',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '商户配置',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '商户管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_merchant
-- ----------------------------

-- ----------------------------
-- Table structure for as_merchant_entry
-- ----------------------------
DROP TABLE IF EXISTS `as_merchant_entry`;
CREATE TABLE `as_merchant_entry`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `sp_id` int UNSIGNED NOT NULL COMMENT '服务商ID',
  `ec_id` int NOT NULL COMMENT '进件通道ID',
  `platform_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '进件平台代码:wxpay=微信官方,alipay=支付宝官方',
  `business_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '业务编号',
  `apply_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '申请单号',
  `apply_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '申请数据',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态:0=待提交,1=已提交,2=审核中,3=已驳回,4=待验证,5=待签约,6=开通权限中,7=已作废,8=已完成',
  `audit_result` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审核结果',
  `apply_result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '申请结果，存在申请结果的整个返回体',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '商户进件管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_merchant_entry
-- ----------------------------

-- ----------------------------
-- Table structure for as_migrations
-- ----------------------------
DROP TABLE IF EXISTS `as_migrations`;
CREATE TABLE `as_migrations`  (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_migrations
-- ----------------------------

-- ----------------------------
-- Table structure for as_oauth_config
-- ----------------------------
DROP TABLE IF EXISTS `as_oauth_config`;
CREATE TABLE `as_oauth_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置标题',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单字段类型',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '配置值',
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置分组',
  `weigh` int NULL DEFAULT NULL COMMENT '权重',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '配置项',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '快捷登录配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_oauth_config
-- ----------------------------
INSERT INTO `as_oauth_config` VALUES (1, 'api_url', 'API地址', 'string', '', 'aggregation', NULL, '');
INSERT INTO `as_oauth_config` VALUES (2, 'appid', '应用ID', 'string', '', 'aggregation', NULL, '');
INSERT INTO `as_oauth_config` VALUES (3, 'app_key', '应用KEY', 'string', '', 'aggregation', NULL, '');
INSERT INTO `as_oauth_config` VALUES (4, 'method', '登录方法', '', NULL, 'aggregation', NULL, '{\"qq\": \"ＱＱ\",\"wx\": \"微信\",\"alipay\": \"支付宝\",\"sina\": \"微博\",\"baidu\": \"百度\",\"douyin\": \"抖音\",\"huawei\": \"华为\",\"xiaomi\": \"小米\",\"google\": \"谷歌\",\"microsoft\": \"微软\",\"facebook\": \"Facebook\",\"twitter\": \"Twitter\",\"wework\": \"企业微信\",\"dingtalk\": \"钉钉\",\"gitee\": \"Gitee\",\"github\": \"GitHub\"}');
INSERT INTO `as_oauth_config` VALUES (5, 'login_method', '登录方式集合', '', NULL, 'config', NULL, '[]');
INSERT INTO `as_oauth_config` VALUES (6, 'appid', '应用ID', 'string', '', 'qq', NULL, '');
INSERT INTO `as_oauth_config` VALUES (7, 'app_key', '应用KEY', 'string', '', 'qq', NULL, '');
INSERT INTO `as_oauth_config` VALUES (8, 'method', '登录方法', '', NULL, 'qq', NULL, '{\"qq\":\"腾讯QQ\"}');

-- ----------------------------
-- Table structure for as_oauth_log
-- ----------------------------
DROP TABLE IF EXISTS `as_oauth_log`;
CREATE TABLE `as_oauth_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '用户ID',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字符串',
  `uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '平台身份标识',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '登录信息',
  `login_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '登录IP',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=失败,1=成功',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '快捷登录日志' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_oauth_log
-- ----------------------------

-- ----------------------------
-- Table structure for as_order
-- ----------------------------
DROP TABLE IF EXISTS `as_order`;
CREATE TABLE `as_order`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `channel_id` int UNSIGNED NULL DEFAULT NULL COMMENT '支付通道ID',
  `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '系统订单号',
  `out_trade_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商户订单号',
  `trade_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '第三方平台交易号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式',
  `notify_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '异步通知地址',
  `return_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '同步通知地址',
  `money` int NOT NULL COMMENT '订单金额',
  `pay_money` int NULL DEFAULT NULL COMMENT '支付金额',
  `pay_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '支付信息',
  `status` enum('0','1','2','3','4','5') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '订单状态:0=未支付,1=已支付,2=退款处理,3=支付失败,4=已关闭,5=已过期',
  `notify_status` enum('0','1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '通知状态:0=待通知,1=通知成功,2=通知失败',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '订单扩展内容',
  `client_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '用户IP',
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '设备类型',
  `callback` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '回调内容',
  `extend_param` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展参数,原样返回',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `pay_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '支付时间',
  `expire_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '到期时间',
  `sign_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MD5' COMMENT '签名类型：默认MD5',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '订单管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_order
-- ----------------------------

-- ----------------------------
-- Table structure for as_refund_record
-- ----------------------------
DROP TABLE IF EXISTS `as_refund_record`;
CREATE TABLE `as_refund_record`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `order_id` int UNSIGNED NOT NULL COMMENT '订单ID',
  `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订单号',
  `channel_id` int UNSIGNED NOT NULL COMMENT '退款通道ID',
  `refund_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '系统退款单号',
  `refund_trade_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '平台退款单号',
  `refund_amount` int NOT NULL DEFAULT 0 COMMENT '退款金额',
  `order_amount` int NOT NULL COMMENT '订单总金额',
  `refund_channel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '退款渠道',
  `refund_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '退款账户',
  `status` int NOT NULL DEFAULT 0 COMMENT '状态:0=处理中,1=退款成功,2=退款失败,3=取消退款,4=退款异常',
  `refund_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '退款原因',
  `callback` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '回调内容',
  `extend` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '扩展内容',
  `refund_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '退款时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '退款记录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_refund_record
-- ----------------------------

-- ----------------------------
-- Table structure for as_security_data_recycle
-- ----------------------------
DROP TABLE IF EXISTS `as_security_data_recycle`;
CREATE TABLE `as_security_data_recycle`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `controller` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器',
  `controller_as` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器别名',
  `data_table` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对应数据表',
  `connection` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '回收规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_security_data_recycle
-- ----------------------------

-- ----------------------------
-- Table structure for as_security_data_recycle_log
-- ----------------------------
DROP TABLE IF EXISTS `as_security_data_recycle_log`;
CREATE TABLE `as_security_data_recycle_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作管理员',
  `recycle_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '回收规则ID',
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '回收的数据',
  `data_table` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表',
  `connection` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `is_restore` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已还原:0=否,1=是',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作者IP',
  `useragent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '数据回收记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_security_data_recycle_log
-- ----------------------------

-- ----------------------------
-- Table structure for as_security_sensitive_data
-- ----------------------------
DROP TABLE IF EXISTS `as_security_sensitive_data`;
CREATE TABLE `as_security_sensitive_data`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `controller` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器',
  `controller_as` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '控制器别名',
  `data_table` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对应数据表',
  `connection` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `data_fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '敏感数据字段',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '敏感数据规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_security_sensitive_data
-- ----------------------------

-- ----------------------------
-- Table structure for as_security_sensitive_data_log
-- ----------------------------
DROP TABLE IF EXISTS `as_security_sensitive_data_log`;
CREATE TABLE `as_security_sensitive_data_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作管理员',
  `sensitive_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '敏感数据规则ID',
  `data_table` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表',
  `connection` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库连接配置标识',
  `primary_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据表主键',
  `data_field` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '被修改字段',
  `data_comment` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '被修改项',
  `id_value` int NOT NULL DEFAULT 0 COMMENT '被修改项主键值',
  `before` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '修改前',
  `after` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '修改后',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作者IP',
  `useragent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `is_rollback` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已回滚:0=否,1=是',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '敏感数据修改记录' ROW_FORMAT = DYNAMIC;


-- ----------------------------
-- Table structure for as_service_provider
-- ----------------------------
DROP TABLE IF EXISTS `as_service_provider`;
CREATE TABLE `as_service_provider`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '服务商名称',
  `real_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人手机号',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人邮箱',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '服务商 Logo',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '服务商管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_service_provider
-- ----------------------------

-- ----------------------------
-- Table structure for as_sms_template
-- ----------------------------
DROP TABLE IF EXISTS `as_sms_template`;
CREATE TABLE `as_sms_template`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '模板标题',
  `code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '唯一标识',
  `template` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '服务商模板ID',
  `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '短信内容',
  `variables` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '模板变量',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `updatetime` int UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `createtime` int UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '短信模板表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_sms_template
-- ----------------------------
INSERT INTO `as_sms_template` VALUES (1, '用户注册', 'user_register', '', '您的注册验证码：${code}', '1', 1, 1756026451, 1664260457);
INSERT INTO `as_sms_template` VALUES (2, '用户身份验证', 'user_mobile_verify', '', '', '', 1, 1664348583, 1664296092);
INSERT INTO `as_sms_template` VALUES (3, '用户验证新手机号', 'user_change_mobile', '', '', '', 1, 1664296339, 1664296212);
INSERT INTO `as_sms_template` VALUES (4, '用户找回密码', 'user_retrieve_pwd', '', '', '', 1, 1664296333, 1664296300);
INSERT INTO `as_sms_template` VALUES (5, '用户手机登录', 'user_mobile_login', '', '登录验证码${code}', '1', 1, 1755937254, 1755937239);

-- ----------------------------
-- Table structure for as_sms_variable
-- ----------------------------
DROP TABLE IF EXISTS `as_sms_variable`;
CREATE TABLE `as_sms_variable`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '变量名称',
  `value_source` enum('literal','func','sql') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'literal' COMMENT '变量值来源:literal=字面量,func=方法返回值,sql=sql查询结果',
  `value` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '变量值',
  `sql` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT 'SQL语句',
  `namespace` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '命名空间',
  `class` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '类名',
  `func` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '方法名',
  `param` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '传递的参数',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `updatetime` int UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `createtime` int UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '模板变量表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_sms_variable
-- ----------------------------
INSERT INTO `as_sms_variable` VALUES (1, '随机数字-6位', 'code', 'func', '', '', 'modules\\sms\\library', 'Helper', 'numeric', '6', 1, 1664349253, 1664255892);
INSERT INTO `as_sms_variable` VALUES (2, '随机字符-4位', 'alnum', 'func', '', '', 'modules\\sms\\library', 'Helper', 'alnum', '4', 1, 1664348499, 1664256828);
INSERT INTO `as_sms_variable` VALUES (3, '系统会员数量', 'user_count', 'sql', '', 'SELECT count(id) FROM as_user', '', '', '', '', 1, 1664257064, 1664257064);

-- ----------------------------
-- Table structure for as_sys_order
-- ----------------------------
DROP TABLE IF EXISTS `as_sys_order`;
CREATE TABLE `as_sys_order`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `channel_id` int UNSIGNED NULL DEFAULT NULL COMMENT '系统收款通道ID',
  `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `type` int NOT NULL DEFAULT 1 COMMENT '订单类型:1=余额充值',
  `order_amount` int NOT NULL COMMENT '订单金额',
  `discount_amount` int NOT NULL DEFAULT 0 COMMENT '优惠金额',
  `payment_amount` int NOT NULL COMMENT '支付金额',
  `pay_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '支付信息',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '订单内容',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '订单描述',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '订单结果',
  `status` int NULL DEFAULT 0 COMMENT '订单状态:0=待支付,1=已支付,2=已取消',
  `pay_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '支付时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统订单' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_sys_order
-- ----------------------------

-- ----------------------------
-- Table structure for as_sys_pay_channel
-- ----------------------------
DROP TABLE IF EXISTS `as_sys_pay_channel`;
CREATE TABLE `as_sys_pay_channel`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通道名称',
  `pay_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付类型',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通道类型',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通道配置',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
  `weigh` int NULL DEFAULT 0 COMMENT '权重',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统收款通道' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_sys_pay_channel
-- ----------------------------

-- ----------------------------
-- Table structure for as_test_build
-- ----------------------------
DROP TABLE IF EXISTS `as_test_build`;
CREATE TABLE `as_test_build`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `keyword_rows` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  `views` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `likes` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '有帮助数',
  `dislikes` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '无帮助数',
  `note_textarea` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '知识库表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_test_build
-- ----------------------------

-- ----------------------------
-- Table structure for as_token
-- ----------------------------
DROP TABLE IF EXISTS `as_token`;
CREATE TABLE `as_token`  (
  `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Token',
  `type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `expire_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`token`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户Token表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_token
-- ----------------------------

-- ----------------------------
-- Table structure for as_user
-- ----------------------------
DROP TABLE IF EXISTS `as_user`;
CREATE TABLE `as_user`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '分组ID',
  `subscribe_id` int NULL DEFAULT 0 COMMENT '会员套餐ID',
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别:0=未知,1=男,2=女',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户密钥',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `money` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '余额',
  `score` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分',
  `last_login_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '上次登录时间',
  `last_login_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `login_failure` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录失败次数',
  `join_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '加入IP',
  `join_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '加入时间',
  `motto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '签名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态:enable=启用,disable=禁用',
  `subscribe_expire_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '会员到期时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1001 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user
-- ----------------------------
INSERT INTO `as_user` VALUES (1000, 1, 1, 'user', 'User', '', '18888888888', '', 1, '', '2025-08-11', 0, 0, 1761470379, '127.0.0.1', 0, '', NULL, '', '$2y$10$k6psCKloLD/JQMA0z3oRy.ritGTzlhWvlZQpC9zP301eht7oqx.ji', 'enable', 0, 1765538425, 1754925349);

-- ----------------------------
-- Table structure for as_user_config
-- ----------------------------
DROP TABLE IF EXISTS `as_user_config`;
CREATE TABLE `as_user_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `google_auth` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '谷歌安全验证信息',
  `notify_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '通知配置',
  `channel_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '通道配置',
  `basic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '常规配置',
  PRIMARY KEY (`id`, `user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user_config
-- ----------------------------
INSERT INTO `as_user_config` VALUES (1, 1000, '[]', '[]', '[]', '[]');

-- ----------------------------
-- Table structure for as_user_group
-- ----------------------------
DROP TABLE IF EXISTS `as_user_group`;
CREATE TABLE `as_user_group`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '权限节点',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员组表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user_group
-- ----------------------------
INSERT INTO `as_user_group` VALUES (1, '默认分组', '*', 1, 1754925349, 1754925349);

-- ----------------------------
-- Table structure for as_user_money_log
-- ----------------------------
DROP TABLE IF EXISTS `as_user_money_log`;
CREATE TABLE `as_user_money_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日志类型',
  `money` int NOT NULL DEFAULT 0 COMMENT '变更余额',
  `before` int NOT NULL DEFAULT 0 COMMENT '变更前余额',
  `after` int NOT NULL DEFAULT 0 COMMENT '变更后余额',
  `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '相关内容',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员余额变动表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user_money_log
-- ----------------------------

-- ----------------------------
-- Table structure for as_user_real_name
-- ----------------------------
DROP TABLE IF EXISTS `as_user_real_name`;
CREATE TABLE `as_user_real_name`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '用户ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '实名名字',
  `type` enum('personal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'personal' COMMENT '实名类型:personal=个人',
  `id_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证号码',
  `business_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '实名业务ID',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=未通过,1=已认证,2=已提交',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `real_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '实名时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户实名认证' ROW_FORMAT = DYNAMIC;


-- ----------------------------
-- Table structure for as_user_rule
-- ----------------------------
DROP TABLE IF EXISTS `as_user_rule`;
CREATE TABLE `as_user_rule`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级菜单',
  `type` enum('route','menu_dir','menu','nav_user_menu','nav','button') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:route=路由,menu_dir=菜单目录,menu=菜单项,nav_user_menu=顶栏会员菜单下拉项,nav=顶栏菜单项,button=页面按钮',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `path` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `menu_type` enum('tab','link','iframe') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tab' COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
  `component` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
  `no_login_valid` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '未登录有效:0=否,1=是',
  `extend` enum('none','add_rules_only','add_menu_only') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员菜单权限规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user_rule
-- ----------------------------
INSERT INTO `as_user_rule` VALUES (2, 0, 'route', '分站管理', 'Station', '/Station', 'icon-dingdan', 'tab', '', '', 0, 'none', '', -9, 0, 1765731880, 1761750390);
INSERT INTO `as_user_rule` VALUES (3, 2, 'route', '分站配置', 'applySet', '/applySet', 'icon-dian', 'tab', '', '/src/views/Station/applySet.vue', 0, 'none', '', -8, 0, 1765731900, 1761750482);
INSERT INTO `as_user_rule` VALUES (4, 2, 'route', '余额明细', 'datastatistics', '/datastatistics', 'icon-dian', 'tab', '', '/src/views/Station/datastatistics.vue', 0, 'none', '', -7, 0, 1765731900, 1761750513);
INSERT INTO `as_user_rule` VALUES (5, 2, 'route', '下级用户', 'Subordinateusers', '/Subordinateusers', 'icon-dian', 'tab', '', '/src/views/Station/Subordinateusers.vue', 0, 'none', '', -6, 0, 1765731900, 1761750622);
INSERT INTO `as_user_rule` VALUES (6, 2, 'route', '提现', 'log', '/log', 'icon-dian', 'tab', '', '/src/views/Station/log.vue', 0, 'none', '', -5, 0, 1765731899, 1761750643);
INSERT INTO `as_user_rule` VALUES (7, 8, 'route', '商户进件', 'applyMentSform', '/applyMentSform', 'icon-dian', 'tab', '', '/src/views/Application/applyMentSform.vue', 0, 'none', '', 2, 1, 1762446499, 1761750667);
INSERT INTO `as_user_rule` VALUES (8, 0, 'route', '通道管理', 'place', '/place', 'icon-shuju', 'tab', '', '', 0, 'none', '', 10, 1, 1764332480, 1761750685);
INSERT INTO `as_user_rule` VALUES (9, 8, 'route', '免签管理', 'channelList', '/channelList', 'icon-dian', 'tab', '', '/src/views/channel/channelList.vue', 0, 'none', '', 0, 1, 1762446438, 1761750709);
INSERT INTO `as_user_rule` VALUES (10, 8, 'route', '通道配置', 'channelSet', '/channelSet', 'icon-dian', 'tab', '', '/src/views/channel/channelSet.vue', 0, 'none', '', -1, 1, 1762446514, 1761750727);
INSERT INTO `as_user_rule` VALUES (11, 8, 'route', '进件管理', 'Applicationlist', '/Applicationlist', 'icon-dian', 'tab', '', '/src/views/Application/Applicationlist.vue', 0, 'none', '', 1, 1, 1762446432, 1761750746);
INSERT INTO `as_user_rule` VALUES (12, 0, 'route', '总览', 'dashboard', '/dashboard', 'icon-shouye', 'tab', '', '', 0, 'none', '', 20, 1, 1762446181, 1761750784);
INSERT INTO `as_user_rule` VALUES (13, 12, 'route', '仪表盘', 'Analytics', '/analytics', 'icon-dian', 'tab', '', '/src/views/dashboard/analytics/index.vue', 0, 'none', '', 6, 1, 1762607924, 1761750803);
INSERT INTO `as_user_rule` VALUES (15, 0, 'route', 'API密钥', 'Api', '/Api', 'icon-renzheng', 'tab', '', '/src/views/indexapi/index.vue', 0, 'none', '', 8, 1, 1762446420, 1761750844);
INSERT INTO `as_user_rule` VALUES (16, 21, 'route', '在线充值', 'onlineRecharge', '/onlineRecharge', 'icon-dian', 'tab', '', '/src/views/onlineRecharge/index.vue', 0, 'none', '', 15, 1, 1762446476, 1761750866);
INSERT INTO `as_user_rule` VALUES (17, 0, 'route', '账户设置', 'indexSet', '/indexSet1', 'icon-haoyourenzheng', 'tab', '', '', 0, 'none', '', 19, 1, 1762614270, 1761750918);
INSERT INTO `as_user_rule` VALUES (18, 21, 'route', '套餐购买', 'pricing', '/pricing', 'icon-dian', 'tab', '', '/src/views/pricing/index.vue', 0, 'none', '', 14, 1, 1762446484, 1761750936);
INSERT INTO `as_user_rule` VALUES (19, 21, 'route', '订单日志', 'olderList', '/olderList', 'icon-dian', 'tab', '', '/src/views/olderList/index.vue', 0, 'none', '', 13, 1, 1762446491, 1761750952);
INSERT INTO `as_user_rule` VALUES (20, 0, 'route', '开通分站', 'apply', '/apply', 'icon-yue', 'tab', '', '/src/views/Station/apply.vue', 0, 'none', '', 7, 0, 1765731880, 1761756320);
INSERT INTO `as_user_rule` VALUES (21, 0, 'route', '财务管理', 'paylist', '/paylist', 'icon-yue', 'tab', '', '', 0, 'none', '', 18, 1, 1762609803, 1762446225);
INSERT INTO `as_user_rule` VALUES (22, 17, 'route', '账户详情', 'indexSetAccount', '/account', 'icon-dian', 'tab', '', '/src/views/indexSet/index.vue', 0, 'none', '', 27, 1, 1762615595, 1762612021);
INSERT INTO `as_user_rule` VALUES (23, 17, 'route', '安全设置', 'indexSetSecurity', '/indexSetSecurity', 'icon-dian', 'tab', '', '/src/views/indexSet/index.vue', 0, 'none', '', 26, 1, 1762615608, 1762612103);
INSERT INTO `as_user_rule` VALUES (24, 17, 'route', '通知设置', 'dellbolt', '/dellbolt', 'icon-dian', 'tab', '', '/src/views/indexSet/index.vue', 0, 'none', '', 25, 1, 1762615618, 1762615225);
INSERT INTO `as_user_rule` VALUES (25, 17, 'route', '账号绑定', 'tablerloginlock', '/tablerloginlock', 'icon-dian', 'tab', '', '/src/views/indexSet/index.vue', 0, 'none', '', 24, 1, 1762615625, 1762615263);
INSERT INTO `as_user_rule` VALUES (26, 17, 'route', '实名认证', 'tableridcard', '/tableridcard', 'icon-dian', 'tab', '', '/src/views/indexSet/index.vue', 0, 'none', '', 23, 1, 1762615625, 1762615296);
INSERT INTO `as_user_rule` VALUES (27, 8, 'route', '商户进件', 'Merchantonboarding', '/Merchantonboarding', 'icon-dian', 'tab', '', '/src/views/Application/Merchantonboarding.vue', 0, 'none', '', 28, 1, 1762965735, 1762965717);

-- ----------------------------
-- Table structure for as_user_score_log
-- ----------------------------
DROP TABLE IF EXISTS `as_user_score_log`;
CREATE TABLE `as_user_score_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `score` int NOT NULL DEFAULT 0 COMMENT '变更积分',
  `before` int NOT NULL DEFAULT 0 COMMENT '变更前积分',
  `after` int NOT NULL DEFAULT 0 COMMENT '变更后积分',
  `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员积分变动表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user_score_log
-- ----------------------------

-- ----------------------------
-- Table structure for as_user_subscribe
-- ----------------------------
DROP TABLE IF EXISTS `as_user_subscribe`;
CREATE TABLE `as_user_subscribe`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '套餐名称',
  `price` decimal(10, 2) NOT NULL COMMENT '价格',
  `duration` int NOT NULL DEFAULT 0 COMMENT '套餐时长',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '套餐内容',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '套餐描述',
  `style` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '套餐样式',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
  `weigh` int NULL DEFAULT NULL COMMENT '权重',
  `create_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员套餐' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of as_user_subscribe
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 补全表(与线上结构对齐;原 install.sql 仅含 42 表基线)
-- 以下 27 表在升级过程中由 migrations 或手工补建,新部署直接导入本文件即可全量建表
-- ============================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_admin_real_name` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int unsigned DEFAULT NULL COMMENT '管理员ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '实名名字',
  `type` enum('personal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'personal' COMMENT '实名类型:personal=个人',
  `id_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证号码',
  `business_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '实名业务ID',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:0=未通过,1=已认证,2=已提交',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '修改时间',
  `real_time` bigint unsigned DEFAULT NULL COMMENT '实名时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员实名认证';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `subscribe_id` int NOT NULL DEFAULT '0' COMMENT '订阅套餐ID',
  `invite_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邀请码',
  `api_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API ID',
  `api_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API密钥',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_subscribe` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '套餐名称',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '套餐代码',
  `status` int DEFAULT '1' COMMENT '状态',
  `is_top` int DEFAULT '0' COMMENT '是否顶级套餐',
  `price` int DEFAULT '0' COMMENT '价格',
  `rebate_ratio` int DEFAULT '0' COMMENT '返佣比例',
  `order_rate` int NOT NULL DEFAULT '0' COMMENT '订单费率',
  `entry_discount` int DEFAULT '0' COMMENT '进件折扣',
  `weigh` int DEFAULT '0' COMMENT '权重',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理套餐';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_subscribe_entry_discount` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `ec_id` int NOT NULL DEFAULT '0' COMMENT '进件通道ID',
  `subscribe_id` int NOT NULL DEFAULT '0' COMMENT '订阅套餐ID',
  `discount` int DEFAULT '0' COMMENT '进件折扣',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理套餐和进件通道的折扣关系表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_subscribe_entry_rate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `subscribe_id` int NOT NULL DEFAULT '0' COMMENT '代理套餐ID',
  `ec_id` int NOT NULL DEFAULT '0' COMMENT '进件通道ID',
  `rate` int NOT NULL DEFAULT '0' COMMENT '费率',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理套餐与进件通道的费率表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_user_entry` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `agent_id` int NOT NULL DEFAULT '0' COMMENT '代理ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `me_id` int NOT NULL DEFAULT '0' COMMENT '进件记录',
  `price` int DEFAULT '0' COMMENT '代理进件价格',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理给用户的进件记录';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_user_entry_discount` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `agent_id` int NOT NULL DEFAULT '0' COMMENT '代理ID',
  `ec_id` int NOT NULL DEFAULT '0' COMMENT '进件通道ID',
  `discount` int DEFAULT '0' COMMENT '进件折扣',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `api_price` int DEFAULT '0' COMMENT '下游接口进件价格',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理用户和进件通道的折扣关系表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_withdrawal_apply_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `agent_id` int NOT NULL DEFAULT '0' COMMENT '代理ID',
  `withdraw_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提现单号',
  `apply_amount` int DEFAULT '0' COMMENT '申请金额',
  `fee_amount` int DEFAULT '0' COMMENT '手续费金额',
  `final_amount` int DEFAULT '0' COMMENT '实际到账金额',
  `status` int DEFAULT '1' COMMENT '状态',
  `rejected` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '拒绝理由',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `pay_time` bigint unsigned DEFAULT NULL COMMENT '打款时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理商提现申请日志';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_agent_withdrawal_money_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `agent_id` int NOT NULL DEFAULT '0' COMMENT '代理ID',
  `money` int DEFAULT '0' COMMENT '金额',
  `before` int DEFAULT '0' COMMENT '变更前金额',
  `after` int DEFAULT '0' COMMENT '变更后金额',
  `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `type` int DEFAULT '0' COMMENT '类型',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '内容',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='代理可提现余额日志';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_channel_pool` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '池名称',
  `pay_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '支付方式',
  `polling_mode` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '轮询模式',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `weigh` int DEFAULT '0' COMMENT '权重',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='通道轮询池';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_channel_pool_account` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pool_id` int NOT NULL DEFAULT '0' COMMENT '池ID',
  `account_id` int NOT NULL DEFAULT '0' COMMENT '通道ID',
  `weigh` int DEFAULT '0' COMMENT '权重',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='通道轮询池通道表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_complaint` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `platform` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '平台代码',
  `complaint_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '投诉单号',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户ID|子商户ID',
  `ec_id` int NOT NULL DEFAULT '0' COMMENT '进件通道ID',
  `sp_id` int NOT NULL DEFAULT '0' COMMENT '服务商ID',
  `is_refund` int DEFAULT '0' COMMENT '是否申请退款,0=否,1=是',
  `complaint_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '投诉标题',
  `complaint_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '投诉内容',
  `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '订单号',
  `status` int DEFAULT '0' COMMENT '投诉状态',
  `wait_reply` int DEFAULT '0' COMMENT '是否待回复',
  `res_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '响应内容',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  `complaint_time` bigint unsigned DEFAULT NULL COMMENT '投诉时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='投诉管理';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_entry_merchant` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `me_id` int NOT NULL DEFAULT '0' COMMENT '进件记录ID',
  `mch_id` int NOT NULL DEFAULT '0' COMMENT '商户ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='商户与进件记录关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_notify_channel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `config` text,
  `status` tinyint NOT NULL DEFAULT '1',
  `weigh` int NOT NULL DEFAULT '0',
  `create_time` bigint DEFAULT NULL,
  `update_time` bigint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_notify_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL DEFAULT '0',
  `event` varchar(100) NOT NULL DEFAULT '',
  `channel` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `status` tinyint NOT NULL DEFAULT '0',
  `create_time` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_notify_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模板名称',
  `event` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件名称',
  `notify_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通知类型',
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '模板标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '模板内容',
  `vars` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '模板变量',
  `template_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '模板ID',
  `status` int NOT NULL DEFAULT '0' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='通知模板表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_plugin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '插件名称',
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '插件标识码',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '插件标题',
  `version` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '插件版本',
  `latest_version` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '最新版本',
  `status` int NOT NULL DEFAULT '0' COMMENT '状态：0禁用 1启用',
  `install_status` int NOT NULL DEFAULT '0' COMMENT '安装状态：0未安装 1已安装',
  `auth_status` int NOT NULL DEFAULT '0' COMMENT '授权状态：0未授权 1已授权',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '配置信息',
  `auth_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '授权信息',
  `path_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '文件路径信息',
  `install_time` bigint unsigned DEFAULT NULL COMMENT '安装时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='插件管理';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_supply_downstream` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `agent_id` int NOT NULL DEFAULT '0' COMMENT '代理ID',
  `domain` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '下游域名',
  `rejected` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '拒绝原因',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `status` int DEFAULT '1' COMMENT '状态',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='下游管理';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_supply_upstream` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上游名称',
  `api` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上游API地址',
  `api_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API ID',
  `api_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API密钥',
  `rejected` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '拒绝原因',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `status` int DEFAULT '1' COMMENT '状态',
  `api_status` int DEFAULT '0' COMMENT 'API接口状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='上游管理';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int DEFAULT '0' COMMENT '用户ID',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '操作URL',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '操作标题',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户代理User-Agent',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'IP',
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '操作数据',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户日志';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_rate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `rate_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order' COMMENT '费率类型',
  `rate` int NOT NULL DEFAULT '0' COMMENT '费率',
  `channel_id` int NOT NULL DEFAULT '0' COMMENT '通道ID',
  `action_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin' COMMENT '操作人类型',
  `action_id` int NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='多类型用户费率表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_subscribe_entry_rate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `subscribe_id` int NOT NULL DEFAULT '0' COMMENT '用户套餐ID',
  `ec_id` int NOT NULL DEFAULT '0' COMMENT '进件通道ID',
  `rate` int NOT NULL DEFAULT '0' COMMENT '费率',
  `status` int DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户套餐与进件通道的费率表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_subscribe_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '套餐名称',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '描述',
  `status` int DEFAULT '1' COMMENT '状态',
  `weigh` int DEFAULT '0' COMMENT '权重',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员套餐分组';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_subscribe_group_channel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int NOT NULL DEFAULT '0' COMMENT '套餐分组ID',
  `channel_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通道类型',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员套餐分组与支付通道类型关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_wallet` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `money` int NOT NULL DEFAULT '0' COMMENT '金额（分）',
  `withdrawal_money` int NOT NULL DEFAULT '0' COMMENT '可提现金额（分）',
  `freeze_money` int NOT NULL DEFAULT '0' COMMENT '冻结金额（分）',
  `total_income` int NOT NULL DEFAULT '0' COMMENT '累计收入金额（分）',
  `total_withdrawal` int NOT NULL DEFAULT '0' COMMENT '累计提现金额（分）',
  `pay_account` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '打款账户设置',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户钱包';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_wallet_money_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `money` int NOT NULL DEFAULT '0' COMMENT '变更金额（分）',
  `before_money` int NOT NULL DEFAULT '0' COMMENT '变动前金额（分）',
  `before_withdrawal_money` int NOT NULL DEFAULT '0' COMMENT '变动前提现金额（分）',
  `before_freeze_money` int NOT NULL DEFAULT '0' COMMENT '变动前冻结金额（分）',
  `after_money` int NOT NULL DEFAULT '0' COMMENT '变动后金额（分）',
  `after_withdrawal_money` int NOT NULL DEFAULT '0' COMMENT '变动后提现金额（分）',
  `after_freeze_money` int NOT NULL DEFAULT '0' COMMENT '变动后冻结金额（分）',
  `type` int NOT NULL DEFAULT '0' COMMENT '类型',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '来源标识',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '相关内容',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='钱包余额日志';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `as_user_withdrawal_apply_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int NOT NULL DEFAULT '0' COMMENT '用户ID',
  `withdraw_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提现申请单号',
  `apply_amount` int NOT NULL DEFAULT '0' COMMENT '申请提现金额（分）',
  `final_amount` int NOT NULL DEFAULT '0' COMMENT '最终提现金额（分）',
  `fee` int NOT NULL DEFAULT '0' COMMENT '手续费（分）',
  `status` int NOT NULL DEFAULT '0' COMMENT '状态',
  `reject_msg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '拒绝原因',
  `create_time` bigint unsigned DEFAULT NULL COMMENT '创建时间',
  `pay_time` bigint unsigned DEFAULT NULL COMMENT '打款时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户提现记录';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


-- [补建表结束]
