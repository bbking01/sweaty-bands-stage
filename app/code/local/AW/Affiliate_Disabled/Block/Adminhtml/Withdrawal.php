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


class AW_Affiliate_Block_Adminhtml_Withdrawal extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_withdrawal';
        $this->_blockGroup = 'awaffiliate';
        $this->_headerText = $this->__('Withdrawals');
        parent::__construct();
        $this->_removeButton('add');
    }

    protected function _toHtml()
    {
        $withdrawalForm = $this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_withdrawals_form');
        $additionalHtml = $this->_getMessagesBlockHtml('withdrawal-messages');
        $additionalHtml .= $this->_getWithdrawalButtonsHtml();
        $additionalHtml .= $withdrawalForm->toHtml();
        $containerHtml = '<div id="' . $this->helper('awaffiliate')->getWithdrawalContainerId() . '">%s</div>';
        return $additionalHtml . sprintf($containerHtml, parent::_toHtml());
    }

    protected function _getWithdrawalButtonsHtml()
    {
        return <<<WBH
<div id="withdrawal-head" class="content-header">
    <h3 class="icon-head">{$this->__('Manage Withdrawal Request')}</h3>
    <p class="form-buttons">
        <button type="button" class="scalable back" id="back_to_withdrawal_list"><span>{$this->__('Back')}</span></button>
        <button type="button" class="scalable save" id="withdrawal_save"><span>{$this->__('Save')}</span></button>
    </p>
</div>
WBH;
    }

    protected function _getMessagesBlockHtml($elementId)
    {
        return <<<MBH
<div id="{$elementId}">
    <ul class="messages">
    </ul>
</div>
MBH;
    }
}
