<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
	ALTER TABLE `{$this->getTable('admin/user')}` ADD `emails` TEXT DEFAULT NULL;
");

$this->endSetup();	