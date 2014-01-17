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


class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_Currentlyviewed_Categoriesgrid extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    private $_afpVO = null;

    protected function _beforeToHtml()
    {
        $this->setTemplate('aw_autorelated/catalog/categories/tree.phtml');
        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->_afpVO))
            $this->_afpVO = new Varien_Object();
        if (!$this->_afpVO->getCategoryIds()) {
            $_data = Mage::helper('awautorelated/forms')->getFormData($this->getRequest()->getParam('id'));
            if (!is_object($_data))
                $_data = new Varien_Object($_data);
            if ($_data->getCategoryIds()) {
                $this->_afpVO->setCategoryIds(is_array($_data->getCategoryIds()) ? $_data->getCategoryIds() : @explode(',', $_data->getCategoryIds()));
            } else {
                $_currentlyViewed = $_data->getData('currently_viewed');
                $cats = array();
                if ($_currentlyViewed && (is_array($_currentlyViewed) || is_object($_currentlyViewed))) {
                    if (is_array($_currentlyViewed) && isset($_currentlyViewed['category_ids'])) {
                        $cats = is_array($_currentlyViewed['category_ids']) ? $_currentlyViewed['category_ids'] : explode(',', $_currentlyViewed['category_ids']);
                    } else if (is_object($_currentlyViewed) && $_currentlyViewed->getData('category_ids')) {
                        $cats = is_array($_currentlyViewed->getData('category_ids')) ? $_currentlyViewed->getData('category_ids') : explode(',', $_currentlyViewed->getData('category_ids'));
                    }
                }
                $this->_afpVO->setCategoryIds($cats);
            }
        }
        return $this->_afpVO;
    }
}
