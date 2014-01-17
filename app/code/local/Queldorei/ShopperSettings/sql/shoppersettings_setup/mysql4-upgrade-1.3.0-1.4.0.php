<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();
$installer->setConfigData('shoppersettings/appearance/timeline', '#322c29');
$installer->setConfigData('shoppersettings/design/search_field', '0');
$installer->setConfigData('shoppersettings/design/below_logo', '0');
$installer->setConfigData('shoppersettings/navigation/use_wide_navigation', '0');
$installer->endSetup();