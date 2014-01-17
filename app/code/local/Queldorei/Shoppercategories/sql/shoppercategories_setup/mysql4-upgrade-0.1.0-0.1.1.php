<?php
/**
 * @version   1.0 06.08.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('shoppercategories/scheme'), 'menu_text_color', 'char(7) NOT NULL after `header_bg`');
$installer->endSetup();