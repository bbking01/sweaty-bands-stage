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


class AW_Autorelated_Block_Adminhtml_Blocks_Shoppingcart_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_blocks_shoppingcart';
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'awautorelated';

        $this->_addButton('saveandcontinueedit', array(
            'label' => $this->__('Save And Continue Edit'),
            'onclick' => 'awarpSaveAndContinueEdit()',
            'class' => 'save',
            'id' => 'awarp-save-and-continue'
        ), -200);

        $this->_formScripts[] = <<<JS
function awarpSaveAndContinueEdit()
{
    if($('edit_form').action.indexOf('continue/1/')<0)
        $('edit_form').action += 'continue/1/';
    if($('edit_form').action.indexOf('continue_tab/')<0)
        $('edit_form').action += 'continue_tab/'+awautorelated_tabsJsTabs.activeTab.name+'/';
    editForm.submit();
}
JS;
        if ($this->getRequest()->getParam('id')) {
            $this->_addButton('saveasnew', array(
                'label' => $this->__('Save As New'),
                'onclick' => 'awarpSaveAsNew()',
                'class' => 'scalable add',
            ), -100);

            $this->_formScripts[] = <<<JS
function awarpSaveAsNew(){
    editForm.submit($('edit_form').action+'saveasnew/1/');
}
JS;
        }
    }

    public function getHeaderText()
    {
        if (($block = Mage::registry(AW_Autorelated_Adminhtml_ShoppingcartblockController::BLOCK_REGISTRY_KEY))
            && $block->getId()
        ) {
            return $this->__('Edit Shopping Cart Block #%s - %s', $block->getId(), $this->escapeHtml($block->getData('name')));
        } else {
            return $this->__('Add Shopping Cart Block');
        }
    }
}
