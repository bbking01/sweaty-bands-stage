<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('customer/customer_group')}` ADD `crf_activation_status` INT( 10 ) NOT NULL DEFAULT '1';
");

$installer->endSetup();