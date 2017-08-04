/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : crab_studio

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-08-04 09:34:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for global_company
-- ----------------------------
DROP TABLE IF EXISTS `global_company`;
CREATE TABLE `global_company` (
  `objectid` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '企业ID',
  `companyname` varchar(50) NOT NULL COMMENT '企业名称',
  `companycode` char(48) DEFAULT NULL COMMENT '企业编码',
  `parentid` bigint(20) NOT NULL DEFAULT '0' COMMENT '父节点ID',
  `levelcode` varchar(50) DEFAULT NULL COMMENT '层级编码',
  `address` varchar(200) DEFAULT NULL COMMENT '地址',
  `flag` tinyint(4) DEFAULT '0' COMMENT '状态:1新建，2编辑过，3删除',
  `createtime` datetime DEFAULT '2016-01-01 00:00:00' COMMENT '创建时间',
  `createmanid` bigint(20) DEFAULT '0' COMMENT '创建人',
  `modifytime` datetime DEFAULT '2016-01-01 00:00:00' COMMENT '修改时间',
  `modifymanid` bigint(20) DEFAULT '0' COMMENT '修改人',
  PRIMARY KEY (`objectid`),
  KEY `parentid` (`parentid`),
  KEY `companycode` (`companycode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='企业信息表';

-- ----------------------------
-- Records of global_company
-- ----------------------------
INSERT INTO `global_company` VALUES ('-1', '', '', '0', null, null, '0', '2016-01-01 00:00:00', '1', '2016-01-01 00:00:00', '1');
INSERT INTO `global_company` VALUES ('1', 'CrabStudio', '', '-1', '00001', '华侨大学', '1', '2016-01-01 00:00:00', '1', '2016-01-01 00:00:00', '1');
INSERT INTO `global_company` VALUES ('2', 'crab1', null, '1', '0000100001', '西街', '1', '2016-01-01 00:00:00', '1', '2016-01-01 00:00:00', '1');

-- ----------------------------
-- Table structure for global_ms
-- ----------------------------
DROP TABLE IF EXISTS `global_ms`;
CREATE TABLE `global_ms` (
  `uid` int(10) NOT NULL COMMENT '唯一id标识',
  `companyid` bigint(20) NOT NULL COMMENT '所属企业id',
  `mstype` int(5) NOT NULL COMMENT '终端类型',
  `alias` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '别名',
  `addr` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '地址',
  `modifymanid` bigint(20) NOT NULL COMMENT '创建人',
  `modifytime` datetime NOT NULL COMMENT '修改时间',
  `createmanid` bigint(20) NOT NULL COMMENT '创建人',
  `createtime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of global_ms
-- ----------------------------
INSERT INTO `global_ms` VALUES ('11', '2', '0', '测试1', '华侨大学', '1', '2017-07-29 20:59:42', '0', '0000-00-00 00:00:00');
INSERT INTO `global_ms` VALUES ('30', '4', '0', '测试2', '西街', '1', '2017-07-29 20:59:42', '0', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for global_typecode
-- ----------------------------
DROP TABLE IF EXISTS `global_typecode`;
CREATE TABLE `global_typecode` (
  `objectid` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '唯一标识',
  `typeid` int(5) NOT NULL COMMENT '类型id，不递增',
  `typename` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '类型名称',
  `devicetype` tinyint(4) NOT NULL COMMENT '1代表终端，2代表中继，3代表基站',
  `companyid` bigint(20) NOT NULL COMMENT '所属企业',
  `modifymanid` bigint(20) NOT NULL COMMENT '修改人',
  `modifytime` datetime NOT NULL DEFAULT '2016-01-01 00:00:00' COMMENT '修改时间',
  `createmanid` bigint(20) NOT NULL COMMENT '创建人',
  `createtime` datetime NOT NULL DEFAULT '2016-01-01 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`objectid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of global_typecode
-- ----------------------------
INSERT INTO `global_typecode` VALUES ('1', '0', '未知', '1', '1', '1', '2016-01-01 00:00:00', '1', '2016-01-01 00:00:00');
INSERT INTO `global_typecode` VALUES ('2', '1', '测试门禁', '1', '1', '1', '2017-08-01 21:52:40', '1', '2016-01-01 00:00:00');

-- ----------------------------
-- Table structure for global_user
-- ----------------------------
DROP TABLE IF EXISTS `global_user`;
CREATE TABLE `global_user` (
  `objectid` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `companyid` bigint(20) NOT NULL COMMENT '所属企业id',
  `password` varchar(64) NOT NULL,
  `linkphone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `lastlogintime` datetime DEFAULT '2016-01-01 00:00:00' COMMENT '最后登录时间',
  `createtime` datetime DEFAULT '2016-01-01 00:00:00' COMMENT '创建时间',
  `createmanid` bigint(20) DEFAULT NULL COMMENT '创建人',
  `modifytime` datetime DEFAULT '2016-01-01 00:00:00' COMMENT '修改时间',
  `modifymanid` bigint(20) DEFAULT NULL COMMENT '修改人',
  PRIMARY KEY (`objectid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of global_user
-- ----------------------------
INSERT INTO `global_user` VALUES ('1', 'superman', '1', '19951119', '18661250718', 'CrabTeamStudio@163.com', '2016-01-01 00:00:00', '2016-01-01 00:00:00', '1', '2016-01-01 00:00:00', '1');
INSERT INTO `global_user` VALUES ('2', 'second', '2', '123456', '18661250718', '1463659386@qq.com', '2016-01-01 00:00:00', '2017-08-03 22:18:08', '1', '2017-08-03 22:39:58', '1');

-- ----------------------------
-- Table structure for latest_ms_info
-- ----------------------------
DROP TABLE IF EXISTS `latest_ms_info`;
CREATE TABLE `latest_ms_info` (
  `uid` int(10) NOT NULL,
  `datatype` int(5) NOT NULL,
  `rawdata` varchar(100) CHARACTER SET utf8 NOT NULL,
  `uptime` datetime NOT NULL,
  KEY `fk_ms_data_latest_5_global_ms_1` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of latest_ms_info
-- ----------------------------
