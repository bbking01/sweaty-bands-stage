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


class AW_Autorelated_Model_Api
{
    public function getRelatedProductsForShoppingCartRule($blockId, $quoteId)
    {
        $relatedIds = array();
        /** @var $block AW_Autorelated_Model_Blocks */
        $block = Mage::getModel('awautorelated/blocks')->load($blockId);
        if ($block->getId()) {
            /** @var $layoutBlock AW_Autorelated_Block_Blocks_Shoppingcart */
            $layoutBlock = Mage::getSingleton('core/layout')->createBlock('awautorelated/blocks_shoppingcart');
            $layoutBlock->setData($block->getData());
            $layoutBlock->setData('_quote_id', $quoteId);
            $relatedCollection = $layoutBlock->getCollection();
            $relatedIds = $relatedCollection ? $relatedCollection->getColumnValues('entity_id') : array();
        }
        return $relatedIds;
    }
}
