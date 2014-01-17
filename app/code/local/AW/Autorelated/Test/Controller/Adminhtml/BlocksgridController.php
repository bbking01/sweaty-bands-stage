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


class AW_Autorelated_Test_Controller_Adminhtml_BlocksgridController extends EcomDev_PHPUnit_Test_Case_Controller {
    /**
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testMassStatusAction($id, $status) {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->getRequest()->setQuery(array(
                'blocks' => array($id),
                'status' => $status
            ));
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/massStatus');
            $block = Mage::getModel('awautorelated/blocks')->load($id);
            $this->assertEquals(
                $status,
                $block->getData('status')
            );
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/list');
        }
    }

    /**
     * @loadFixture
     */
    public function testEditAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/edit');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/list');
            
            $this->getRequest()->setQuery(array('id' => 1));
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/edit');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_productblock/edit', array('id' => 1));
            
            $this->getRequest()->setQuery(array('id' => 2));
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/edit');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_categoryblock/edit', array('id' => 2));
        }
    }

    public function testSimpleActions() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/index');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/list');
            
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/new');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/selecttype');
        }
    }

    public function testSelecttypeAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/selecttype');
            $this->assertLayoutBlockRendered('aw.autorelated.adminhtml.blocks.typeselector');
        }
    }

    public function testSelecttypePostAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $helper->preparePost($this->getRequest());
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/selecttype');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/selecttype');
            
            $helper->preparePost($this->getRequest());
            $this->getRequest()->setParam('block_type', AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK);
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/selecttype');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_productblock/new');
            
            $helper->preparePost($this->getRequest());
            $this->getRequest()->setParam('block_type', AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK);
            $this->dispatch('awautorelated_admin/adminhtml_blocksgrid/selecttype');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_categoryblock/new');
        }
    }

}
