/*
Navicat MySQL Data Transfer

Source Server         : 本地local
Source Server Version : 50714
Source Host           : localhost:3306
Source Database       : ybzcdb

Target Server Type    : MYSQL
Target Server Version : 50714
File Encoding         : 65001

Date: 2017-11-27 10:21:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'user id',
  `account` varchar(20) NOT NULL COMMENT 'user name',
  `password` varchar(255) NOT NULL COMMENT 'password',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'admin', '$2y$10$uw/IHpFz5kcBqoN57Riej.0RreCHbV9afO5CWDoJThq49luu7VGsu', '2017-11-27 09:48:46');

-- ----------------------------
-- Table structure for hide_ip_pool
-- ----------------------------
DROP TABLE IF EXISTS `hide_ip_pool`;
CREATE TABLE `hide_ip_pool` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(100) NOT NULL COMMENT '伪装原型ip',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='伪装原型ip池';

-- ----------------------------
-- Records of hide_ip_pool
-- ----------------------------

-- ----------------------------
-- Table structure for user_info
-- ----------------------------
DROP TABLE IF EXISTS `user_info`;
CREATE TABLE `user_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `p_ip` varchar(100) NOT NULL COMMENT '私有ip',
  `uid` int(11) DEFAULT NULL COMMENT '线路id',
  `hide_ip` varchar(100) DEFAULT NULL COMMENT '伪装原型ip',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户信息表';

-- ----------------------------
-- Records of user_info
-- ----------------------------

-- ----------------------------
-- Table structure for vpn_info
-- ----------------------------
DROP TABLE IF EXISTS `vpn_info`;
CREATE TABLE `vpn_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `v_vpn` varchar(100) NOT NULL COMMENT '虚拟vpn',
  `r_vpn` varchar(100) NOT NULL COMMENT '真实vpn',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='vpn信息表';

-- ----------------------------
-- Records of vpn_info
-- ----------------------------
