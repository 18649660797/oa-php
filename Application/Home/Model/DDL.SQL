CREATE TABLE `EDY_ATTENDANCE`(
`id` INT PRIMARY KEY AUTO_INCREMENT,
`name` VARCHAR(255) NOT NULL,
`department` VARCHAR(255),
`work_date` TIMESTAMP,
`am_time` TIMESTAMP,
`pm_time` TIMESTAMP)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- think_auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
DROP TABLE IF EXISTS `edy_auth_rule`;
DROP TABLE IF EXISTS `edy_auth_group`;
DROP TABLE IF EXISTS `edy_auth_group_access`;
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

CREATE TABLE `edy_auth_group_access` (
    `uid` mediumint(8) unsigned NOT NULL,
    `group_id` mediumint(8) unsigned NOT NULL,
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
    KEY `uid` (`uid`),
    KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `edy_auth_rule` (`id`, `name`, `title`, `type`, `status`, `condition`) VALUES ('1', 'super', '超级权限', '1', '1', 'true');
INSERT INTO `edy_auth_group` (`id`, `title`, `status`, `rules`) VALUES (NULL, '超级管理员', '1', '1');
INSERT INTO `edy_auth_group_access` (`uid`, `group_id`) VALUES ('1', '1');

CREATE TABLE `edy_auth_user` (
id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
username varchar(255) not null,
passwod varchar(255) not null
);
INSERT INTO `edy_auth_user` (`id`, `username`, `password`) VALUES
(1, 'gabin', 'a7866023ecac10e3df478ca9567fc64e');


