<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Perm
*/
$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('amperm/login')};
CREATE TABLE {$this->getTable('amperm/login')} (
  `login_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login_hash` CHAR(32)  NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();