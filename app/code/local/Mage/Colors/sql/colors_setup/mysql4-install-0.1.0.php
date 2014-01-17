<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('colors')};
CREATE TABLE {$this->getTable('colors')} (
  `colors_id` int(11) unsigned NOT NULL auto_increment,
  `colors_counter` int(11) NOT NULL default '0',
  PRIMARY KEY (`colors_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 