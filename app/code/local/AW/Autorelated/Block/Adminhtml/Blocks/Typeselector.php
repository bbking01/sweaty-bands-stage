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


class AW_Autorelated_Block_Adminhtml_Blocks_Typeselector extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form(array(
            'id' => 'typeselector_form',
            'action' => $this->getUrl('*/*/selecttype'),
            'method' => 'post'
        ));

        $_fieldset = $_form->addFieldset('select_type', array(
            'legend' => $this->__('Select block type')
        ));

        $_fieldset->addField('block_type', 'select', array(
            'name' => 'block_type',
            'label' => $this->__('Block Type'),
            'values' => Mage::getModel('awautorelated/source_type')->toOptionArray()
        ));

        $_fieldset->addField('submit', 'submit', array(
            'name' => 'submit',
            'value' => $this->__('Continue'),
            'class' => 'button form-button'
        ));

        $_form->setUseContainer(true);
        $this->setForm($_form);
        parent::_prepareForm();
    }
}
