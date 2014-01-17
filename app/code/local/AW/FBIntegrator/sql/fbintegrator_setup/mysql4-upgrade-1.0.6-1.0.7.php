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


function getStoreConfig($path) {

    $coll = Mage::getModel('core/config_data')->getCollection();
    $coll->getSelect()->where('path=?', $path);

    foreach ($coll as $i) {
        return $i->getValue();
    }
}

$text = getStoreConfig('fbintegrator/facebook/post_link_text');
$link = getStoreConfig('fbintegrator/facebook/post_link');
$template = $text . $link;

$installer = $this;
$installer->startSetup();

$template = $text . $link;

if ($template != '') {
    $upData = array(
        'scope' => 'default',
        'scope_id' => '0',
        'path' => 'fbintegrator/wall/post_link_template',
        'value' => $template,
    );

    $configData = Mage::getModel('core/config_data')->load('fbintegrator/wall/post_link_template', 'path');
    if ($configData->getConfigId())
        $configData->setValue($template);
    else
        $configData->setData($upData);
    $configData->save();
}

$installer->endSetup();