<?php
$this->startSetup();

$this->run("     
CREATE TABLE IF NOT EXISTS `printablecolor_management` (
  `color_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color_name` varchar(255) NOT NULL,
  `color_code` varchar(255) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '1',
  `store_id` varchar(10) DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;   
");

$this->run("INSERT INTO `printablecolor_management` (`color_id`, `color_name`, `color_code`, `status`, `created_time`, `update_time`) VALUES 
(1, 'Red', '#FF0000', 1, NULL, NULL),
(2, 'Pink', '#FF00FF', 1, NULL, NULL),
(3, 'Yellow', '#FFFF00', 1, NULL, NULL),
(4, 'Blue', '#0000FF', 1, NULL, NULL),
(5, 'Grey', '#C0C0C0', 1, NULL, NULL),
(6, 'Cyan', '#26B0FF', 1, '2012-10-31 13:31:12', '2012-10-31 13:31:12'),
(9, 'White', '#FFFFFF', 1, '2012-11-23 16:58:54', '2012-11-23 16:58:54');
");

$this->endSetup();
?>
	