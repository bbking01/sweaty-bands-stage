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


class AW_Points_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_blockGroup = 'points';
        $this->_controller = 'adminhtml_rule';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('points')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('points')->__('Delete Rule'));
        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('points')->__('Save And Continue Edit'),
            'onclick' => 'editForm.submit(\'' . $this->_getSaveAndContinueUrl() . '\')',
            'class' => 'save'
                ), 10);
        $this->_addButton('save_as', array(
            'label' => Mage::helper('points')->__('Save As'),
            'onclick' => 'saveAs()',
            'class' => 'save'
                ), 10);
    }

    protected function _getSaveAndContinueUrl() {
        return $this->getUrl('*/*/save', array(
                    'back' => 'edit',
                ));
    }

    public function getHeaderText() {
        $rule = Mage::registry('points_rule_data');
        if ($rule->getRuleId()) {
            return Mage::helper('points')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        } else {
            return Mage::helper('points')->__('New Rule');
        }
    }

}
