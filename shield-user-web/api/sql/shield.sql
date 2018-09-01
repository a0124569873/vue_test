/*
Navicat MySQL Data Transfer

Source Server         : 192.168.9.100
Source Server Version : 50637
Source Host           : 192.168.9.100:3306
Source Database       : Shield

Target Server Type    : MYSQL
Target Server Version : 50637
File Encoding         : 65001

Date: 2018-03-01 14:52:17
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for manage_list
-- ----------------------------
DROP TABLE IF EXISTS `manage_list`;
CREATE TABLE `manage_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_domain` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `b_url` varchar(255) DEFAULT NULL,
  `b_ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of manage_list
-- ----------------------------

-- ----------------------------
-- Table structure for servers
-- ----------------------------
DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '服务器id',
  `group_id` int(11) DEFAULT NULL COMMENT '服务器组号',
  `server_ip` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '服务器ip',
  `thr_in` bigint(15) DEFAULT NULL COMMENT '输入阈值',
  `thr_out` bigint(15) DEFAULT NULL COMMENT '输出阈值',
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`server_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of servers
-- ----------------------------

-- ----------------------------
-- Table structure for udomains
-- ----------------------------
DROP TABLE IF EXISTS `udomains`;
CREATE TABLE `udomains` (
  `id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `u_id` int(11) unsigned zerofill DEFAULT NULL,
  `u_domain` varchar(255) DEFAULT NULL,
  `status` int(1) unsigned zerofill DEFAULT NULL,
  `rip` varchar(255) DEFAULT NULL,
  `text_code` varchar(255) DEFAULT NULL,
  `v_domain` varchar(255) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `registered_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `speed_status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of udomains
-- ----------------------------

-- ----------------------------
-- Table structure for user_server
-- ----------------------------
DROP TABLE IF EXISTS `user_server`;
CREATE TABLE `user_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `server_id` int(11) DEFAULT NULL COMMENT '服务器id',
  `stat` tinyint(1) DEFAULT NULL COMMENT '服务器联系状态1未审核2正常',
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of user_server
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_qq` varchar(20) DEFAULT NULL,
  `u_email` varchar(40) DEFAULT NULL,
  `u_phone` varchar(40) DEFAULT NULL,
  `u_pass` varchar(100) DEFAULT NULL,
  `last_login_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_login_ip` varchar(30) DEFAULT NULL,
  `login_count` int(11) DEFAULT '0',
  `registered_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`u_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of users
-- ----------------------------
