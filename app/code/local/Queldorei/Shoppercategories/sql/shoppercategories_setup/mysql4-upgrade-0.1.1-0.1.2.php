<?php
/**
 * @version   1.0 06.08.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('shoppercategories/scheme'), 'content_bg_img', 'varchar(255) NOT NULL after `content_bg`');
$installer->getConnection()->addColumn($installer->getTable('shoppercategories/scheme'), 'content_bg_img_mode', 'varchar(8) NOT NULL after `content_bg_img`');
$installer->endSetup();