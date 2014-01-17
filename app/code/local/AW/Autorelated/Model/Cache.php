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


class AW_Autorelated_Model_Cache extends Mage_Core_Model_Abstract
{
    const CACHE_ENABLED = false;
    const LOCK = 'awarpcronlock';
    const LOCKTIME = 300; // 5 minutes
    const CACHE_KEY_CATEGORY = 'awarpcategoryblock';
    const CACHE_KEY_CATEGORY_BLOCK = 'awarpcategoryblockcache';
    const CACHE_TIME = 600; // 10 minutes

    public static function run()
    {
        if (self::checkLock()) {
            self::cacheCategoryBlocksData();
            Mage::app()->removeCache(self::LOCK);
        }
    }

    public static function checkLock()
    {
        if ($time = Mage::app()->loadCache(self::LOCK)) {
            if ((time() - $time) < self::LOCKTIME)
                return false;
        }
        Mage::app()->saveCache(time(), self::LOCK, array(), self::LOCKTIME);
        return true;
    }

    public static function getCategoryBlocksCollection($id = null)
    {
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->addStatusFilter()
            ->addDateFilter()
            ->addCategoryBlockTypeFilter();
        if ($id)
            $collection->addIdFilter($id);
        return $collection;
    }

    public static function getCategoryBlockMatchingConditionIds($categoryBlock, $mode = null)
    {
        $relatedProducts = $categoryBlock->getData('related_products') ? $categoryBlock->getData('related_products') : null;
        $rule = Mage::getModel('awautorelated/blocks_category_rule');
        if ($mode != null)
            $rule->setReturnMode($mode);
        if ($relatedProducts && is_array($relatedProducts->getData('conditions'))) {
            $stores = array_keys(Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(FALSE, TRUE));
            $conditions = $relatedProducts->getData('conditions');
            $rule->getConditions()->loadArray($conditions, 'related');
            // Getting matching product IDs
            $rule->setWebsiteIds(implode(',', $stores));
            return $rule->getMatchingProductIds();
        }
        return null;
    }

    public static function cacheCategoryBlocksData($id = null)
    {
        $collection = self::getCategoryBlocksCollection($id);
        foreach ($collection as $categoryBlock) {
            $categoryBlock->afterLoad();
            $matchingIds = self::getCategoryBlockMatchingConditionIds($categoryBlock, AW_Autorelated_Model_Blocks_Rule::ALL_IDS_ON_NO_CONDITIONS);
            if (self::CACHE_ENABLED)
                Mage::app()->saveCache(serialize($matchingIds), self::CACHE_KEY_CATEGORY . $categoryBlock->getId(), array(), self::CACHE_TIME);
        }
        if ($id)
            return $matchingIds;
    }

    public static function getCategoryBlockMatchedIds($blockId)
    {
        if ($blockId) {
            $matchedIds = @unserialize(Mage::app()->loadCache(self::CACHE_KEY_CATEGORY . $blockId));
            if (!is_array($matchedIds))
                $matchedIds = self::cacheCategoryBlocksData($blockId);
            return $matchedIds;
        }
        return null;
    }
}
