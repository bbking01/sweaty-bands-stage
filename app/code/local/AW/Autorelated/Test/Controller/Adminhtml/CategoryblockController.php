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


class AW_Autorelated_Test_Controller_Adminhtml_CategoryblockController extends EcomDev_PHPUnit_Test_Case_Controller {
    public function testNewAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->dispatch('awautorelated_admin/adminhtml_categoryblock/new');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_categoryblock/edit');
        }
    }

    public function testDeleteAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->dispatch('awautorelated_admin/adminhtml_categoryblock/delete');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/delete');
        }
    }

    public function testCategoriesJsonAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->dispatch('awautorelated_admin/adminhtml_categoryblock/categoriesJson');
            $this->assertResponseHttpCode(200);
            
            $this->reset();
            $helper->preparePost($this->getRequest());
            $this->getRequest()->setQuery(array(
                'id' => 'related_conditions_fieldset--1',
                'type' => 'catalogrule-rule_condition_product|custom_design_to',
                'form' => 'related_conditions_fieldset',
                'prefix' => 'related',
                'rule' => 'YXdhdXRvcmVsYXRlZC9ibG9ja3NfcHJvZHVjdF9ydWxlcmVsYXRlZA=='
            ));
            $this->dispatch('awautorelated_admin/adminhtml_categoryblock/newConditionHtml');
            $this->assertResponseHttpCode(200);
        }
    }

    public function testSaveAction() {
        $helper = new AW_Autorelated_Test_Helper();
        if($helper->prepareAdminUser()) {
            $this->getRequest()->setMethod('GET');
            $this->dispatch('awautorelated_admin/adminhtml_categoryblock/save');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_blocksgrid/list');
            
            $helper->preparePost($this->getRequest());
            $this->dispatch('awautorelated_admin/adminhtml_categoryblock/save');
            $this->assertRedirectTo('awautorelated_admin/adminhtml_categoryblock/edit', array('fswe' => 1));
        }
    }
}
