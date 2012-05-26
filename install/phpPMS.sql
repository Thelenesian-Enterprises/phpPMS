-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 24-05-2012 a las 14:53:43
-- Versión del servidor: 5.1.61
-- Versión de PHP: 5.3.3-7+squeeze8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `phppms`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `intAccountId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `intUGroupFId` tinyint(3) unsigned NOT NULL,
  `intUserFId` tinyint(3) unsigned NOT NULL,
  `intUEditFId` tinyint(3) unsigned NOT NULL,
  `vacCliente` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `vacName` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `intCategoryFid` tinyint(3) unsigned NOT NULL,
  `vacLogin` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `vacUrl` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `vacPassword` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `vacMd5Password` varchar(32) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `vacInitialValue` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `txtNotice` text COLLATE utf8_spanish_ci,
  `intCountView` int(10) unsigned NOT NULL DEFAULT '0',
  `intCountDecrypt` int(10) unsigned NOT NULL DEFAULT '0',
  `datAdded` datetime NOT NULL,
  `datChanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intAccountId`),
  KEY `vacCliente` (`vacCliente`),
  FULLTEXT KEY `vacName` (`vacName`),
  FULLTEXT KEY `vacLogin` (`vacLogin`),
  FULLTEXT KEY `vacUrl` (`vacUrl`,`txtNotice`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_history`
--

CREATE TABLE IF NOT EXISTS `acc_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intAccountId` smallint(5) unsigned NOT NULL,
  `intUGroupFId` tinyint(3) unsigned NOT NULL,
  `intUserFId` tinyint(3) unsigned NOT NULL,
  `intUEditFId` tinyint(3) unsigned NOT NULL,
  `vacCliente` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `vacName` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `intCategoryFid` tinyint(3) unsigned NOT NULL,
  `vacLogin` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `vacUrl` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `vacPassword` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `vacMd5Password` varchar(32) COLLATE utf8_spanish_ci NOT NULL DEFAULT '0',
  `vacInitialValue` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `txtNotice` text COLLATE utf8_spanish_ci,
  `intCountView` int(10) unsigned NOT NULL DEFAULT '0',
  `intCountDecrypt` int(10) unsigned NOT NULL DEFAULT '0',
  `datAdded` datetime NOT NULL,
  `datChanged` datetime NOT NULL,
  `blnModificada` tinyint(1) DEFAULT NULL,
  `blnEliminada` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vacName` (`vacName`),
  KEY `vacCliente` (`vacCliente`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_usergroups`
--

CREATE TABLE IF NOT EXISTS `acc_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `intAccId` int(10) unsigned NOT NULL,
  `intUGroupId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `intAccId` (`intAccId`,`intUGroupId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `intCategoryId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `vacCategoryName` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`intCategoryId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `vacParameter` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `vacValue` varchar(128) COLLATE utf8_spanish_ci NOT NULL,
  UNIQUE KEY `vacParameter` (`vacParameter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `intId` int(11) NOT NULL AUTO_INCREMENT,
  `intAccountId` smallint(5) unsigned NOT NULL,
  `vacName` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `vacType` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `intSize` int(11) NOT NULL,
  `blobContent` mediumblob NOT NULL,
  `vacExtension` varchar(10) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`intId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datLog` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `vacLogin` varchar(25) COLLATE utf8_spanish_ci NOT NULL,
  `intUserId` tinyint(3) unsigned NOT NULL,
  `vacAccion` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `txtDescripcion` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usergroups`
--

CREATE TABLE IF NOT EXISTS `usergroups` (
  `intUGroupId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `vacUGroupName` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `vacUGroupDesc` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`intUGroupId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `intUserId` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `vacUName` varchar(80) COLLATE utf8_spanish_ci NOT NULL,
  `intUGroupFid` tinyint(3) unsigned NOT NULL,
  `intUSGroupFid` tinyint(3) unsigned DEFAULT NULL,
  `vacULogin` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `vacUPassword` varchar(32) COLLATE utf8_spanish_ci NOT NULL,
  `vacUserMPwd` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `vacUserMIv` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `vacUEmail` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `txtUNotes` text COLLATE utf8_spanish_ci,
  `intUCount` int(10) unsigned NOT NULL DEFAULT '0',
  `intUProfile` tinyint(4) NOT NULL,
  `datULastLogin` datetime NOT NULL,
  `datULastUpdate` datetime NOT NULL,
  `datUserLastUpdateMPass` datetime DEFAULT NULL,
  `blnIsAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `blnFromLdap` tinyint(1) NOT NULL DEFAULT '0',
  `blnDisabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`intUserId`),
  UNIQUE KEY `vacULogin` (`vacULogin`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;