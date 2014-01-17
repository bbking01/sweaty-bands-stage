<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('qcprice')};
CREATE TABLE {$this->getTable('qcprice')} (
  `qcprice_id` int(11) unsigned NOT NULL auto_increment,
  `quantity_range_id` int(11) unsigned NOT NULL default '0',
  `colors_counter_id` int(11) unsigned NOT NULL default '0',
  `price` float(10,2) NOT NULL default '0.00',
  PRIMARY KEY (`qcprice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 