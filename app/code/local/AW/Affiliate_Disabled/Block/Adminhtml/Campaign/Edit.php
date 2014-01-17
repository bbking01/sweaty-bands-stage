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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Block_Adminhtml_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_campaign';
        $this->_objectId = 'id';
        $this->_blockGroup = 'awaffiliate';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Campaign'));
        $this->_updateButton('delete', 'label', $this->__('Delete Campaign'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_campaign')->getId()) {
            return $this->__('Edit Campaign');
        }
        else {
            return $this->__('New Campaign');
        }
    }

    protected function _prepareLayout()
    {
        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('customer')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
            'class' => 'save'
        ), 10);
        $this->_formScripts[] = "function saveAndContinueEdit(url){"
            . "    var tabId = awaffiliate_campaign_tabsJsTabs.activeTab.getAttribute('name');"
            . "    url = url.replace(/{{tab_id}}/, tabId);"
            . "    editForm.submit(url)"
            . " }";
        return parent::_prepareLayout();
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true,
            'back' => 'edit',
            'tab' => '{{tab_id}}'
        ));
    }
}
