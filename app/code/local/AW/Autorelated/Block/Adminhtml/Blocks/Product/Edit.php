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


class AW_Autorelated_Block_Adminhtml_Blocks_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'awautorelated';
        $this->_controller = 'adminhtml_blocks_product';

        $this->_updateButton('save', 'label', Mage::helper('awautorelated')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('awautorelated')->__('Delete Item'));
        $this->_updateButton('reset', 'label', Mage::helper('awautorelated')->__('Reset'));
        $this->_updateButton('back', 'label', Mage::helper('awautorelated')->__('Back'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        if ($this->getRequest()->getParam('id')) {
            $this->_addButton('saveasnew', array(
                'label' => Mage::helper('adminhtml')->__('Save As New'),
                'onclick' => 'saveAsNew()',
                'class' => 'scalable add',
            ), -100);

            $this->_formScripts[] = "
            function saveAsNew(){
                editForm.submit($('edit_form').action+'saveasnew/1/');
            }
        ";
        }
    }

    public function getHeaderText()
    {
        if (Mage::registry('productblock_data') && Mage::registry('productblock_data')->getId()) {
            return Mage::helper('awautorelated')->__("Edit Product Block #%s - '%s'", $this->htmlEscape(Mage::registry('productblock_data')->getId()), $this->htmlEscape(Mage::registry('productblock_data')->getName()));
        } else {
            return Mage::helper('awautorelated')->__('Add Product Block');
        }
    }
}
