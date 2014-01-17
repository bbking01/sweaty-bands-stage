<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('customer/customer_group')}` ADD `webform_id` INT( 10 ) NOT NULL DEFAULT '0';
");

$installer->endSetup();