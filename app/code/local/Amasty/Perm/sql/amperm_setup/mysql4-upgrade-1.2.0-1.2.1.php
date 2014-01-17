<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('admin/user')}` ADD `customer_group_id` INT NOT NULL; 
");

$this->endSetup();