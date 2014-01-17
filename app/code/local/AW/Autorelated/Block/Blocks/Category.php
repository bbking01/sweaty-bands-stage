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


class AW_Autorelated_Block_Blocks_Category extends AW_Autorelated_Block_Blocks_Abstract
{
    const CACHE_TIME = 300;

    protected $_canShow = null;
    protected $_currentCategory = null;

    public function canShow()
    {
        if ($this->_canShow === null) {
            $currentlyViewed = $this->_getCurrentlyViewed();
            $currentCategory = $this->_getCurrentCategory();
            if ($currentlyViewed && $currentlyViewed instanceof Varien_Object
                && (($currentCategory && Mage::registry('current_product') === null) || $this->getBlockPosition() == AW_Autorelated_Model_Source_Position::CUSTOM)
            ) {
                if ($currentlyViewed->getData('area') == 1) {
                    // Categories = ALL
                    $this->_canShow = true;
                } elseif ($currentCategory) {
                    if ($currentlyViewed->getData('category_ids')) {
                        // Block has category IDs
                        if (is_string($currentlyViewed->getData('category_ids')))
                            $currentlyViewed->setData('category_ids', explode(',', $currentlyViewed->getData('category_ids')));
                        if (is_array($currentlyViewed->getData('category_ids')) && in_array($currentCategory->getId(), $currentlyViewed->getData('category_ids'))) {
                            $this->_canShow = true;
                        } else {
                            $this->_canShow = false;
                        }
                    } else {
                        // No category IDs specified
                        $this->_canShow = false;
                    }
                } else {
                    // Custom categories and no current category
                    $this->_canShow = false;
                }
            } else {
                $this->_canShow = false;
            }
        }
        return $this->_canShow;
    }

    protected function _getCurrentCategory()
    {
        if ($this->_currentCategory === null) {
            if ($this->getParent() && $this->getParent()->getData('category_id')) {
                if (preg_match("/^category\/([0-9]*)$/", $this->getParent()->getData('category_id'), $matches)) {
                    $categoryId = isset($matches[1]) ? intval($matches[1]) : null;
                    if ($categoryId) {
                        $category = Mage::getModel('catalog/category')->load($categoryId);
                        if ($category->getData()) {
                            $this->_currentCategory = $category;
                        }
                    }
                }
            } else {
                $this->_currentCategory = Mage::registry('current_category');
            }
        }
        return $this->_currentCategory;
    }

    protected function _setTemplate()
    {
        if (!$this->getTemplate()) {
            if (Mage::helper('awautorelated')->checkVersion('1.4')) {
                $this->setTemplate('aw_autorelated/blocks/category/category.phtml');
            } else {
                // Magento 1.3.x
                $this->setTemplate('aw_autorelated/blocks/category/category_13.phtml');
            }
        }
        return $this;
    }

    public function getMatchingIds()
    {
        return AW_Autorelated_Model_Cache::getCategoryBlockMatchedIds($this->getId());
    }

    protected function _getCacheKey()
    {
        $cacheKey = AW_Autorelated_Model_Cache::CACHE_KEY_CATEGORY . '-' . $this->getId() . '-' . Mage::app()->getStore()->getId();
        if ($this->_getRelatedProducts() && $this->_getRelatedProducts()->getData('include') != AW_Autorelated_Model_Source_Block_Category_Include::ALL && $this->_getCurrentCategory()) {
            $cacheKey .= '-' . $this->_getCurrentCategory()->getId();
        }
        return $cacheKey;
    }

    protected function _getRelatedIds()
    {
        $relatedIds = @unserialize(Mage::app()->loadCache($this->_getCacheKey()));
        if (!is_array($relatedIds)) {
            $relatedProducts = $this->_getRelatedProducts();
            $currentCategory = $this->_getCurrentCategory();
            $filteredIds = $this->getMatchingIds();
            $currentIds = $this->_collection->getAllIds();
            $intersectedArray = array_intersect($currentIds, $filteredIds);
            if ($intersectedArray) {
                $this->_initCollectionForIds($intersectedArray);
                // Setting include filter
                if ($relatedProducts->getData('include') != AW_Autorelated_Model_Source_Block_Category_Include::ALL && $currentCategory) {
                    $this->_collection->addCategoriesFilter(
                        $currentCategory->getId(), $relatedProducts->getData('include') == AW_Autorelated_Model_Source_Block_Category_Include::CURRENT_CATEGORY ? false : true
                    );
                }
                if ($relatedProducts->getData('include') != AW_Autorelated_Model_Source_Block_Category_Include::ALL && !$currentCategory) {
                    $this->_collection = null;
                }
                if ($this->_collection) {
                    $relatedIds = $this->_collection->getAllIds();
                } else {
                    $relatedIds = array();
                }
            } else {
                $relatedIds = array();
            }
            if ($relatedIds) {
                $relatedIds = array_diff($relatedIds, Mage::helper('awautorelated')->getWishlistProductsIds());
                $relatedIds = array_diff($relatedIds, Mage::getSingleton('checkout/cart')->getProductIds());
            }
            if (AW_Autorelated_Model_Cache::CACHE_ENABLED)
                Mage::app()->saveCache(serialize($relatedIds), $this->_getCacheKey(), array(), self::CACHE_TIME);
        }
        return $relatedIds;
    }

    protected function _renderRelatedProductsFilters()
    {
        $limit = $this->_getRelatedProducts()->getData('count');
        $relatedIds = $this->_getRelatedIds();
        if ($relatedIds) {
            $relatedIds = $this->_preorderIds($relatedIds);
            $this->_initCollectionForIds($relatedIds);
            $this->_collection->setPageSize($limit);
            $this->_collection->setCurPage(1);
            $this->_orderRelatedProductsCollection($this->_collection);
        } else {
            $this->_collection = null;
        }
        return $this;
    }
}
