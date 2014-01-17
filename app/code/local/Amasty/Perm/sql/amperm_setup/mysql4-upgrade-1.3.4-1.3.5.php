<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Perm
*/
$this->startSetup();

$this->run("
	ALTER TABLE `{$this->getTable('admin/user')}` MODIFY COLUMN `customer_group_id` VARCHAR(1024) NOT NULL DEFAULT '';
"); 

$this->endSetup();