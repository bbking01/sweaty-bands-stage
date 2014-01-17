<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


function getFbConfig($path) {

    $coll = Mage::getModel('core/config_data')->getCollection();
    $coll->getSelect()->where('path=?', $path);

    foreach ($coll as $i) {
        return $i->getValue();
    }
}

$key = getFbConfig('fbintegrator/facebook/api_key');
$secret = getFbConfig('fbintegrator/facebook/secret');

$installer = $this;
$installer->startSetup();

if ($key != '' && $secret != '') {
    $keyData = array(
        'scope' => 'default',
        'scope_id' => '0',
        'path' => 'fbintegrator/app/api_key',
        'value' => $key,
    );
    $secretData = array(
        'scope' => 'default',
        'scope_id' => '0',
        'path' => 'fbintegrator/app/secret',
        'value' => $secret,
    );

    $configData = Mage::getModel('core/config_data')->load(null);
    $configData->setData($keyData);
    $configData->save();

    $configData = Mage::getModel('core/config_data')->load(null);
    $configData->setData($secretData);
    $configData->save();
}


$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('fbintegrator/users')} (
      `id` bigint unsigned NOT NULL auto_increment,
      `fb_id` bigint unsigned NOT NULL,
      `fb_email` varchar(250) NOT NULL,
      `customer_id` bigint unsigned NOT NULL,
      `website_id` smallint(5) unsigned NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `fb_id` (`fb_id`,`website_id`),
      UNIQUE KEY `fb_email` (`fb_email`,`website_id`),
      UNIQUE KEY `customer_id` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
