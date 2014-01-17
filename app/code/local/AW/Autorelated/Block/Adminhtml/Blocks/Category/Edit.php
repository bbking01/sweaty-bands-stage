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


class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_blocks_category';
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'awautorelated';

        if (!Mage::helper('awautorelated')->isEditAllowed()) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('reset');
        } else {
            $this->_updateButton('save', 'label', Mage::helper('awautorelated')->__('Save'));
            $this->_updateButton('delete', 'label', Mage::helper('awautorelated')->__('Delete'));
            $this->_updateButton('reset', 'label', Mage::helper('awautorelated')->__('Reset'));
            $this->_updateButton('back', 'label', Mage::helper('awautorelated')->__('Back'));

            $this->_addButton('saveandcontinueedit', array(
                'label' => $this->__('Save And Continue Edit'),
                'onclick' => 'awarpSaveAndContinueEdit()',
                'class' => 'save',
                'id' => 'awarp-save-and-continue'
            ), -200);

            $this->_formScripts[] = "function awarpSaveAndContinueEdit() {
            if($('edit_form').action.indexOf('continue/1/')<0)
                $('edit_form').action += 'continue/1/';
            if($('edit_form').action.indexOf('continue_tab/')<0)
                $('edit_form').action += 'continue_tab/'+awautorelated_tabsJsTabs.activeTab.name+'/';
            editForm.submit();
             }";
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
    }

    public function getHeaderText()
    {
        if (Mage::registry('categoryblock_data') && Mage::registry('categoryblock_data')->getId()) {
            return Mage::helper('awautorelated')
                ->__("Edit Category Block #%s - '%s'", $this->htmlEscape(Mage::registry('categoryblock_data')->getId()), $this->htmlEscape(Mage::registry('categoryblock_data')->getName())
            );
        } else {
            return Mage::helper('awautorelated')->__('Add Category Block');
        }
    }
}
