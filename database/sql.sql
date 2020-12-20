SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `tbl_comments`;
CREATE TABLE `tbl_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `i_user_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `st_comment` text NOT NULL,
  `i_auction_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET NAMES utf8mb4;

DROP TABLE IF EXISTS `tbl_files`;
CREATE TABLE `tbl_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `st_file_name` varchar(255) NOT NULL,
  `st_file_original_name` varchar(255) NOT NULL,
  `date_uploaded` datetime NOT NULL,
  `fl_size` float NOT NULL,
  `st_ext` varchar(45) CHARACTER SET utf8 NOT NULL,
  `i_tender_id` int(11) NOT NULL COMMENT 'the id of the auction the file related to',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tbl_finishing`;
CREATE TABLE `tbl_finishing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `st_name` varchar(45) NOT NULL,
  `i_model_id` int(11) NOT NULL,
  `i_is_active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tbl_log`;
CREATE TABLE `tbl_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `i_type` int(1) NOT NULL COMMENT '1-email,2-sms,3-other',
  `st_subject` varchar(255) NOT NULL,
  `st_message` text NOT NULL,
  `st_receiver` varchar(255) NOT NULL,
  `i_status` int(1) NOT NULL,
  `date_created` datetime NOT NULL,
  `i_tender_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
