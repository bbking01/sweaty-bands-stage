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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Adminhtml_Transaction_Add extends Mage_Adminhtml_Block_Widget {

    public function getHeaderText() {
        return Mage::helper('points')->__('Add Transaction');
    }

    protected function _prepareLayout() {
        $this->setChild('back_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('points')->__('Back'),
                            'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                            'class' => 'back'
                        ))
        );

        $this->setChild('save_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('points')->__('Save Transaction'),
                            'onclick' => "transactionAddForm.submit();",
                            'class' => 'save'
                        ))
        );

        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml() {
        return $this->getChildHtml('save_button');
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save');
    }

    public function getForm() {
        return $this->getLayout()
                        ->createBlock('points/adminhtml_transaction_add_form')
                        ->toHtml();
    }

    public function getCustomersGrid() {
        return $this->getLayout()
                        ->createBlock('points/adminhtml_customer_grid')
                        ->toHtml();
    }

}