DROP DATABASE edy;
CREATE DATABASE edy;
USE edy;
-- ----------------------------
-- think_auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
DROP TABLE IF EXISTS `edy_auth_rule`;
CREATE TABLE `edy_auth_rule` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `name` char(80) NOT NULL DEFAULT '',
    `title` char(20) NOT NULL DEFAULT '',
    `type` tinyint(1) NOT NULL DEFAULT '1',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `condition` char(100) NOT NULL DEFAULT '',  # 规则附件条件,满足附加条件的规则,才认为是有效的规则
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group 用户组表，
-- id：主键， title:用户组中文名称， rules：用户组拥有的规则id， 多个规则","隔开，status 状态：为1正常，为0禁用
-- ----------------------------
DROP TABLE IF EXISTS `edy_auth_group`;
CREATE TABLE `edy_auth_group` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `title` char(100) NOT NULL DEFAULT '',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `rules` char(80) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group_access 用户组明细表
-- uid:用户id，group_id：用户组id
-- ----------------------------
DROP TABLE IF EXISTS `edy_auth_group_access`;
CREATE TABLE `edy_auth_group_access` (
    `uid` mediumint(8) unsigned NOT NULL,
    `group_id` mediumint(8) unsigned NOT NULL,
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
    KEY `uid` (`uid`),
    KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- ----------------------------
-- 管理员表
-- ----------------------------
DROP TABLE IF EXISTS `edy_auth_user`;
CREATE TABLE `edy_auth_user` (
`id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
`username` varchar(255) not null,
`password` varchar(255) not null
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `edy_auth_rule` (`id`, `name`, `title`, `type`, `status`, `condition`)
VALUES ('1', 'super', '超级权限', '1', '1', 'true'),
 ('2', '/index.php/home/group', '权限管理', '1', '1', 'true'),
 ('3', '/index.php/home/attendance', '考勤管理', '1', '1', 'true'),
 ('4', '/index.php/home/employee', '员工管理', '1', '1', 'true'),
 ('5', '/index.php/home/admin', '管理员设置', '1', '1', 'true')
;
INSERT INTO `edy_auth_group` (`id`, `title`, `status`, `rules`) VALUES (1, '超级管理员', '1', '1');
INSERT INTO `edy_auth_group_access` (`uid`, `group_id`) VALUES ('1', '1');
INSERT INTO `edy_auth_user` (`id`, `username`, `password`) VALUES
(1, 'gabin', 'a7866023ecac10e3df478ca9567fc64e');
-- ----------------------------------
-- 员工表
-- ----------------------------------
DROP TABLE IF EXISTS `edy_employee`;
CREATE TABLE `edy_employee` (
`id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
`real_name` VARCHAR(255) NOT NULL,
`attendance_cn` VARCHAR(255),
`department` VARCHAR(255))
ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- ----------------------------------
-- 考勤数据表
-- ----------------------------------
DROP TABLE IF EXISTS `edy_attendance`;
CREATE TABLE `edy_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `e_id` int(11) DEFAULT NULL,
  `work_date` varchar(255) DEFAULT NULL,
  `am_time` varchar(255) DEFAULT NULL,
  `pm_time` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `remark` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10820 DEFAULT CHARSET=utf8;
-- ----------------------------------
-- 异常情况表，目前设计用来存储外出、请假等
-- ----------------------------------
DROP TABLE IF EXISTS `edy_exception`;
CREATE TABLE `edy_exception` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `begin_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remark` varchar(255) DEFAULT NULL,
  `e_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
