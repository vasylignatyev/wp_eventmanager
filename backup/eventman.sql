-- phpMyAdmin SQL Dump
-- version 4.0.10.20
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 14, 2018 at 08:02 PM
-- Server version: 5.5.60-0+deb8u1-log
-- PHP Version: 5.4.45

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `eventman`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`u_eventman`@`localhost` FUNCTION `getOptionValue`(optionStr varchar(4096), optionName varchar(255)) RETURNS varchar(255) CHARSET utf8
BEGIN
  DECLARE xpathStr varchar(255);

  SET xpathStr = CONCAT('/', optionName, '[1]');

  RETURN IFNULL(EXTRACTVALUE(optionStr, xpathStr), '');

END$$

CREATE DEFINER=`u_eventman`@`localhost` FUNCTION `updateOptionValue`(optionStr VARCHAR(4096), optionName VARCHAR(255), optionVal VARCHAR(255)) RETURNS varbinary(4096)
BEGIN
  DECLARE result varchar (4096);
  DECLARE xpathStr, xml varchar (255);

  set xpathStr = CONCAT( '/', optionName, '[1]');

  set optionStr = IF( ExtractValue( optionStr, xpathStr) IS NULL , '',  optionStr);

  set xml = CONCAT('<', optionName, '>', optionVal ,'</', optionName,'>');

  IF(ExtractValue( optionStr, CONCAT('count(', xpathStr, ')')) > 0) THEN
    set result =  UPDATEXML( optionStr ,xpathStr, xml );
  ELSE
    set result = CONCAT(optionStr, xml);
  END IF;

RETURN result;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `i_company` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `iisue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`i_company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE IF NOT EXISTS `customer` (
  `i_customer` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `password` binary(16) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `second_name` varchar(255) NOT NULL,
  `token` binary(16) DEFAULT NULL,
  `options` varchar(1024) DEFAULT NULL,
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `i_company` int(11) DEFAULT NULL,
  PRIMARY KEY (`i_customer`),
  UNIQUE KEY `UK_customer_email` (`email`),
  UNIQUE KEY `UK_uuid` (`token`),
  KEY `FK_customer_company_i_company` (`i_company`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=16384 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`i_customer`, `email`, `password`, `first_name`, `second_name`, `token`, `options`, `issue_date`, `i_company`) VALUES
(1, 'vasyl.ignatyev@gmail.com', '+�� �H�', 'Василий', 'Игнатьев', '��k�)=�', '<LAST_LOGIN>2017-01-26 10:13:12</LAST_LOGIN>', '2017-01-26 04:18:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE IF NOT EXISTS `event` (
  `i_event` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `short_desc` varchar(2047) DEFAULT NULL,
  `full_desc` varchar(16385) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `duration` varbinary(45) DEFAULT NULL COMMENT '30 days',
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `i_project` int(11) DEFAULT NULL,
  `i_group` int(11) DEFAULT NULL,
  PRIMARY KEY (`i_event`),
  KEY `FK_event_group_i_group` (`i_group`),
  KEY `FK_event_project_i_project` (`i_project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=16384 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`i_event`, `title`, `short_desc`, `full_desc`, `options`, `duration`, `issue_date`, `i_project`, `i_group`) VALUES
(1, 'Ведення випадку й оцінка потреб дитини та її сім''ї', '<span style="color: rgb(0, 0, 0); font-family: Arial;">Ведення випадку та оцінка потреб - інноваційні технології соціальної роботи, розуміння та вміння застосовувати які покликана розвивати ця навчальна програма. Заняття будуть цікаві для спеціалістів соціальної сфери та студентів ВНЗ. Партнерством “Кожній дитині” також здійснюєтсья підготовка тренерів за цією програмою.</span>', '<p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">Знання:</strong></p><ul style="color: rgb(0, 0, 0); font-family: Arial;"><li>алгоритму ведення випадку, його процедурам, принципам та механізмам;</li><li>теоретичних та концептуальних засад оцінки потреб дитини та її сім’ї;</li><li>особливостей планування та документування випадку відповідно до рівня складності;</li><li>форм та методів соціальної роботи, які можуть бути використані в процесі здійснення оцінки, доцільності цих форм та методів у різних ситуаціях.</li></ul><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">Навички:</strong></p><ul style="color: rgb(0, 0, 0); font-family: Arial;"><li>контактної взаємодії та подолання опору, налагодження співпраці та мотивування отримувача послуги до активної участі у подоланні СЖО;</li><li>виявлення та активізації сильних сторін отримувача послуг і потенціалу його середовища для подолання СЖО;</li><li>застосування діагностичного інструментарію в процесі здійснення оцінки та планування послуг;</li><li>командної роботи в процесі ведення випадку та прийняття рішень в найкращих інтересах дитини.</li></ul><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"></p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><span style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent; color: rgb(40, 151, 163);"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">КОМУ БУДЕ ЦІКАВО ПОВЧИТИСЯ?</strong></span></p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">&nbsp;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">Програма буде цікавою&nbsp;<strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">для широкого кола фахівців соціальної сфери</strong>, а саме:</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; фахівців та спеціалістів центрів соціальних служб для сім’ї, дітей та молоді;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; соціальних педагогів шкіл;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; спеціалістів служб у справах дітей;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; спеціалістів закладів соціального обслуговування та соціального захисту;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; методистів;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; медиків;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; представників громадських організацій, які здійснюють соціальну роботу з вразливими сім’ями, дітьми та молоддю.</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">&nbsp;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">Вона також буде цікавою та корисною&nbsp;<strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">для студентів вищих навчальних закладів</strong>, які проходять підготовку за спеціальностями “соціальний педагог”, “соціальний працівник”, “соціальне управління”, “психолог” та ін.</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;">&nbsp;</p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">Партнерство “Кожній дитині” також здійснює підготовку тренерів за цією програмою.</strong></p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;"><br style="margin: 0px; padding: 0px; min-height: 14px;"></strong></p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;"><br style="margin: 0px; padding: 0px; min-height: 14px;"></strong></p><p style="margin-bottom: 0px; padding: 0px; border: 0px; outline: 0px; background-image: initial; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; min-height: 14px; color: rgb(0, 0, 0); font-family: Arial;"><span style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent; color: rgb(40, 151, 163);"><strong style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;">ЗМІСТ ПРОГРАМИ</strong></span></p>', NULL, '\0�', '2017-01-26 16:15:50', NULL, NULL),
(2, 'test1', '<b>test1</b>', '<i>test1</i>', NULL, '\0�', '2017-02-07 10:07:56', NULL, NULL),
(5, 'title', 'test', 'test', NULL, '\0�@�', '2017-02-21 16:29:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE IF NOT EXISTS `group` (
  `i_group` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `issue_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`i_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `i_project` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`i_project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `i_schedule` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` datetime DEFAULT NULL,
  `options` varchar(4095) DEFAULT NULL,
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `i_event` int(11) NOT NULL,
  PRIMARY KEY (`i_schedule`),
  KEY `FK_schedule_event_i_event` (`i_event`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`i_schedule`, `start_date`, `options`, `issue_date`, `i_event`) VALUES
(2, '2017-01-27 12:32:38', NULL, '2017-01-27 10:32:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE IF NOT EXISTS `subscription` (
  `i_subscription` int(11) NOT NULL AUTO_INCREMENT,
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uuid` binary(16) NOT NULL,
  `active` enum('FALSE','TRUE)') NOT NULL DEFAULT 'FALSE',
  `bolcked` enum('FALSE','TRUE') NOT NULL DEFAULT 'FALSE',
  `options` varchar(255) DEFAULT NULL,
  `i_customer` int(11) NOT NULL,
  `i_ticket` int(11) NOT NULL,
  PRIMARY KEY (`i_subscription`),
  UNIQUE KEY `UK_subscription` (`i_ticket`,`i_customer`),
  UNIQUE KEY `UK_token` (`uuid`),
  KEY `FK_subscription_customer_i_customer` (`i_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE IF NOT EXISTS `ticket` (
  `i_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `options` varchar(255) DEFAULT NULL,
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `i_schedule` int(11) NOT NULL,
  PRIMARY KEY (`i_ticket`),
  KEY `FK_ticket_schedule_i_schedule` (`i_schedule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `FK_customer_company_i_company` FOREIGN KEY (`i_company`) REFERENCES `company` (`i_company`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FK_event_group_i_group` FOREIGN KEY (`i_group`) REFERENCES `group` (`i_group`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_event_project_i_project` FOREIGN KEY (`i_project`) REFERENCES `project` (`i_project`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `FK_schedule_event_i_event` FOREIGN KEY (`i_event`) REFERENCES `event` (`i_event`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `subscription`
--
ALTER TABLE `subscription`
  ADD CONSTRAINT `FK_subscription_customer_i_customer` FOREIGN KEY (`i_customer`) REFERENCES `customer` (`i_customer`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_subscription_ticket_i_ticket` FOREIGN KEY (`i_ticket`) REFERENCES `ticket` (`i_ticket`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `FK_ticket_schedule_i_schedule` FOREIGN KEY (`i_schedule`) REFERENCES `schedule` (`i_schedule`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
