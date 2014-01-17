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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Autorelated
 * @copyright  Copyright (c) 2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @var $this Mage_Core_Model_Resource_Setup
 */

$this->startSetup();

/** @var $collection AW_Autorelated_Model_Mysql4_Blocks_Collection */
$collection = Mage::getModel('awautorelated/blocks')->getCollection();
$pagesCount = ceil($collection->getSize() / 10);
for ($i = 1; $i <= $pagesCount; $i++) {
    $collection->clear();
    $collection->setPageSize(10);
    $collection->setCurPage($i);
    foreach ($collection as $block) {
        if ($block->getData('randomize')) {
            $block = Mage::getModel('awautorelated/blocks')->load($block->getId());
            $relatedProductData = $block->getData('related_products') ? $block->getData('related_products') : new Varien_Object();
            $relatedProductData->setData('order', array(
                'type' => AW_Autorelated_Model_Source_Block_Common_Order::RANDOM,
                'order_attribute' => null,
                'order_direction' => null
            ));
            $block->setData('related_products', $relatedProductData->toArray());
            $block->setData('currently_viewed', $block->getData('currently_viewed') ? $block->getData('currently_viewed')->toArray() : array());
            $block->save();
        }
    }
}
$this->getConnection()->dropColumn($this->getTable('awautorelated/blocks'), 'randomize');

$this->endSetup();
