<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('sales/order_grid')}` ADD `uid` INT NOT NULL; 
    UPDATE `{$this->getTable('sales/order_grid')}` AS o, {$this->getTable('amperm/perm')} AS p SET o.uid=p.uid WHERE o.customer_id=p.cid;
");

$this->endSetup();