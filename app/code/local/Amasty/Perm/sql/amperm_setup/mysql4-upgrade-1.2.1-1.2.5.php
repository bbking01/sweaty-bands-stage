<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('amperm/message')};
CREATE TABLE {$this->getTable('amperm/message')} (
  `message_id` int(10) unsigned NOT NULL auto_increment,
  `from_id` mediumint(9) unsigned NOT NULL default '0',
  `order_id` int unsigned NOT NULL default '0',
  `to_id` int(10) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL,
  `txt` text NOT NULL default '',
  PRIMARY KEY  (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();