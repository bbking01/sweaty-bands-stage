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


class AW_Autorelated_Model_Observer
{
    protected function _getShoppingCartCrosssellsBlocks()
    {
        /** @var $collection AW_Autorelated_Model_Mysql4_Blocks_Collection */
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->addStoreFilter()
            ->addTypeFilter(AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK)
            ->addPositionFilter(AW_Autorelated_Model_Source_Position::REPLACE_CROSSSELS_BLOCK);
        return $collection->getSize() > 0;
    }

    public function replaceCrossselsBlock($observer)
    {
        if (!$this->_getShoppingCartCrosssellsBlocks()) {
            return;
        }
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::app()->getLayout();
        /** @var $helper AW_Autorelated_Helper_Data */
        $helper = Mage::helper('awautorelated');
        if (!$helper->getExtDisabled()) {
            /** @var $shoppingCartBlock Mage_Checkout_Block_Cart */
            $shoppingCartBlock = $layout->getBlock('checkout.cart');
            /** @var $arpBlock AW_Autorelated_Block_Blocks */
            $arpBlock = $layout->createBlock('awautorelated/blocks', 'aw.arp2.shc.crosssells');
            $shoppingCartBlock->setChild('crosssell', $arpBlock);
        }
    }
}
