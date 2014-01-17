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


class AW_Autorelated_Test_Block_Adminhtml_Blocks_Category_Edit_Tabs_Currentlyviewed_Categoriesgrid extends EcomDev_PHPUnit_Test_Case {
    /**
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testGetProduct($id, $categories) {
        $block = Mage::getModel('awautorelated/blocks')->load($id);
        $block->setData('currently_viewed', array('category_ids' => $categories));
        $block->save();
        $block->load();
        if($id % 2 == 0)
            $block->setCategoryIds($categories);
        $helper = Mage::helper('awautorelated/forms')->setFormData($block->getData());
        
        Mage::app()->getRequest()->setQuery(array('id' => $id));
        $blockCatGrid = Mage::getSingleton('core/layout')->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs_currentlyviewed_categoriesgrid');
        
        $product = $blockCatGrid->getProduct();
        $this->assertEquals(
            $categories,
            $product->getCategoryIds()
        );
    }
}
