<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
	ALTER TABLE `{$this->getTable('admin/user')}` ADD `description` TEXT DEFAULT NULL;
");

$this->endSetup();	