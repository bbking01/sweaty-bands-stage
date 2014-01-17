<?php

$installer = $this;

$installer->startSetup();


$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('magestore_designidea_category')}` (
  `album_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url_rewrite_id` int(11) NOT NULL,
  `url_key` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('magestore_designidea')}` (
  `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `designdata` longtext NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`gallery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$installer->endSetup(); 