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


class AW_Autorelated_Block_Blocks_Product extends AW_Autorelated_Block_Blocks_Abstract
{
    protected $_canShow = null;
    protected $_currentProduct = null;
    protected $_joinedAttributes;
    protected $_nativeBlock = null;

    public function canShow()
    {
        if ($this->_canShow === null) {

            $currentProduct = $this->_getCurrentProduct();
            $model = Mage::getModel('awautorelated/blocks_product_ruleviewed');
            $model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
            $conditions = $this->getCurrentlyViewed()->getConditions();

            if (isset($conditions['viewed'])) {

                $model->getConditions()->loadArray($conditions, 'viewed');
                $match = $model->getMatchingProductIds();
                if (in_array($currentProduct->getId(), $match))
                    $this->_canShow = true;
                else
                    $this->_canShow = false;
            } else
                $this->_canShow = true;
        }
        return $this->_canShow;
    }

    protected function _getCurrentProduct()
    {
        if ($this->_currentProduct === null) {
            $productId = ($id = $this->getParent()->getData('product_id')) ? substr($id, strpos($id, '/') + 1) : $this->getRequest()->getParam('id');
            $product = Mage::getModel('catalog/product')->load($productId);
            $this->_currentProduct = $product;
        }
        return $this->_currentProduct;
    }

    protected function _setTemplate()
    {
        if (!$this->getTemplate()) {
            if (!Mage::helper('awautorelated')->checkVersion('1.9')) {
                switch ($this->getBlockPosition()) {
                    case AW_Autorelated_Model_Source_Position::INSTEAD_NATIVE_RELATED_BLOCK:
                    case AW_Autorelated_Model_Source_Position::UNDER_NATIVE_RELATED_BLOCK:
                        $this->setTemplate('aw_autorelated/blocks/product/product-sidebar.phtml');
                        break;
                    default:
                        $this->setTemplate('aw_autorelated/blocks/product/product.phtml');
                }
            } else {
                $this->setTemplate('aw_autorelated/blocks/product/product.phtml');
            }
        }
        return $this;
    }

    protected function _renderRelatedProductsFilters()
    {
        $currentProduct = $this->_getCurrentProduct();
        $model = Mage::getModel('awautorelated/blocks_product_rulerelated');
        $model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
        $conditions = $this->getRelatedProducts()->getRelated();
        $mIds = array();
        $gCondition = $this->getRelatedProducts()->getGeneral();
        $limit = $this->getRelatedProducts()->getProductQty();

        if (isset($conditions['conditions']['related'])) {
            $model->getConditions()->loadArray($conditions['conditions'], 'related');
            $mIds = $model->getMatchingProductIds();

            if (empty($mIds)) {
                unset($this->_collection);
                return $this;
            } else {
                $mIds = array_diff($mIds, array($currentProduct->getId()));
            }
        }

        if (!empty($gCondition)) {
            $filteredIds = $this->filterByAtts($currentProduct, $gCondition, $mIds);
        } elseif (!empty($mIds)) {
            $filteredIds = $mIds;
        } else {
            $filteredIds = $this->_collection->getAllIds();
        }

        if (!empty($filteredIds)) {
            $filteredIds = array_diff($filteredIds, array($currentProduct->getId()));
            $filteredIds = array_diff($filteredIds, Mage::helper('awautorelated')->getWishlistProductsIds());
            $filteredIds = array_diff($filteredIds, Mage::getSingleton('checkout/cart')->getProductIds());
            $filteredIds = array_intersect($filteredIds, $this->_collection->getAllIds());
            $itemsCount = count($filteredIds);
            if (!$itemsCount) {
                unset($this->_collection);
                return $this;
            }
            $this->_preorderIds($filteredIds);
            $this->_initCollectionForIds($filteredIds);
            $this->_collection->setPageSize($limit);
            $this->_collection->setCurPage(1);
            $this->_orderRelatedProductsCollection($this->_collection);
        } else {
            unset($this->_collection);
        }
        return $this;
    }

    /*
     * 
     * filter product by attributes valuesd
     * Mage_Catalog_Model_Product $currentProduct -main product
     * Array $atts - atts list for filter  
     * Array $ids - products id for filter
     */
    public function filterByAtts(Mage_Catalog_Model_Product $currentProduct, $atts, $ids = null)
    {

        $this->_joinedAttributes = array();
        $collection = $this->_collection;
        $rule = new AW_Autorelated_Model_Blocks_Rule();

        foreach ($atts as $at) {
            /*
            *  collect category ids related to product
            *  If category is anchor we should implode all of its subcategories as value
            *  If it's not we should get only its id
            *  If there is no category in product, get all categories product is in
            */
            if ($at['att'] == 'category_ids') {
                $category = $currentProduct->getCategory();
                if ($category instanceof Varien_Object) {
                    if ($category->getIsAnchor()) {
                        $value = $category->getAllChildren();
                    } else {
                        $value = $category->getId();
                    }
                } else {
                    $value = implode(',', $currentProduct->getCategoryIds());
                    $value = !empty($value) ? $value : null;
                }
            } /* */
            else {
                $value = $currentProduct->getData($at['att']);
            }
            if (!$value) {
                $collection = NULL;
                return false;
            }
            $sql = $rule->prepareSqlForAtt($at['att'], $this->_joinedAttributes, $collection, $at['condition'], $value);
            if ($sql) {
                $collection->getSelect()->where($sql);
            }
        }
        if ($ids) {
            $collection->getSelect()->where('e.entity_id IN(' . implode(',', $ids) . ')');
        }
        $collection->getSelect()->group('e.entity_id');

        return $collection->getAllIds();
    }

    public function showNativeBlock()
    {
        return Mage::getSingleton('awautorelated/blocks_product')->showNativeBlock();
    }

    public function iterateBlock()
    {
        Mage::getSingleton('awautorelated/blocks_product')->iterateBlock();
    }

    public function markAsShowed()
    {
        Mage::getSingleton('awautorelated/blocks_product')->markAsShowed();
    }
}
