<?php

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('amperm/perm')};
CREATE TABLE {$this->getTable('amperm/perm')} (
  `perm_id` int(10) unsigned NOT NULL auto_increment,
  `uid` mediumint(9) unsigned NOT NULL default '0',
  `cid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`perm_id`),
  CONSTRAINT `FK_AMPERM_CUSTOMER` FOREIGN KEY (`cid`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_AMPERM_USER` FOREIGN KEY (`uid`) REFERENCES `{$this->getTable('admin_user')}` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup(); 