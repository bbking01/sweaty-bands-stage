<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('qrange')};
CREATE TABLE {$this->getTable('qrange')} (
  `qrange_id` int(11) unsigned NOT NULL auto_increment,
  `quantity_range_from` int(11) NOT NULL default '0',
  `quantity_range_to` int(11) NOT NULL default '0',
  PRIMARY KEY (`qrange_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 