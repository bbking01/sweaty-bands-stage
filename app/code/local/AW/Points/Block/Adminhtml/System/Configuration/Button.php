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


class AW_Points_Block_Adminhtml_System_Configuration_Button extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        $url = $this->getUrl("points_admin/adminhtml_transaction/resetTransactions");

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('scalable')
                ->setLabel(Mage::helper('points')->__('Reset all transactions'))
                ->setOnClick("return conformation ();")
                ->toHtml();

        $html .="<p class='note'>";
        $html .="<span style='color:#E02525;'>";
        $html .=Mage::helper('points')->__("This action is unrecoverable and will set all customers' points balance to 0");
        $html .="</span>";
        $html .="</p>";


        $html .= "<script  type='text/javascript'>
                            function conformation (){
                                if(confirm('" . Mage::helper('points')->__('Are you sure to reset all transactions?') . "')){
                                    setLocation('$url');
                                }
                            }
                       </script>";

        return $html;
    }

}
