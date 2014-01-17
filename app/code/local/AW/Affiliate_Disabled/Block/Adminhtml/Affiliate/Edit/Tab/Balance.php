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


class AW_Affiliate_Block_Adminhtml_Affiliate_Edit_Tab_Balance
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        /* @var $model AW_Affiliate_Model_Campaign */
        $affiliate = Mage::registry('current_affiliate');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('balance_');

        //current balance fieldset
        if ($affiliate->hasId()) {
            $balanceFieldset = $form->addFieldset('balance_fieldset',
                array('legend' => Mage::helper('awaffiliate')->__('Current Balance'))
            );

            $requestWithdrawal = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
            $requestWithdrawal->addAffiliateFilter(Mage::registry('current_affiliate')->getId());
            $value = $affiliate->getCurrentBalance();
            foreach ($requestWithdrawal as $item) {
                if($item['status']==AW_Affiliate_Model_Source_Withdrawal_Status::PENDING) {
                $value -= $item->getAmount();}
            }
            $balanceFieldset->addField('current_balance', 'note', array(
                'html_id' => 'current_balance',
                'text' => Mage::helper('core')->currency($value),
                'label' => Mage::helper('awaffiliate')->__('Available balance'),
                'title' => Mage::helper('awaffiliate')->__('Available balance'),
            ));

            $value = $affiliate->getTotalWithdrawn();
            $balanceFieldset->addField('total_withdrawn', 'note', array(
                'html_id' => 'total_withdrawn',
                'text' => Mage::helper('core')->currency($value),
                'label' => Mage::helper('awaffiliate')->__('Total Withdrawn'),
                'title' => Mage::helper('awaffiliate')->__('Total Withdrawn'),
            ));
        }
        if ($affiliate->hasId()) {
            $addingProfitFieldset = $form->addFieldset('add_profit_fieldset',
                array('legend' => Mage::helper('awaffiliate')->__('Adding profit'))
            );

            $profitForm = $this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_profits_form');
            $htmlContent = '';
            $htmlContent .= $this->_getMessagesBlockHtml('profit-messages');
            $htmlContent .= $this->_getProfitFormButtons();
            $htmlContent .= $profitForm->toHtml();
            $htmlContent .= $this->_getScriptInitialization();
            $addingProfitFieldset->setData('html_content', $htmlContent);

            //profit list fieldset
            $profitsListFieldset = $form->addFieldset('list_profit_fieldset',
                array('legend' => Mage::helper('awaffiliate')->__('Transactions'))
            );
            $profitGrid = $this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_profits_grid');
            $profitsListFieldset->setData('html_content', $profitGrid->toHtml());

            //withdrawals fieldset
            $withdrawalFieldset = $form->addFieldset('withdrawal_fieldset',
                array('legend' => Mage::helper('awaffiliate')->__('Withdrawals'))
            );
            $withdrawalGrid = $this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_withdrawals_grid');
            $withdrawalForm = $this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_withdrawals_form');
            $htmlContent = '';
            $htmlContent .= $this->_getMessagesBlockHtml('withdrawal-messages');
            $htmlContent .= $this->_getWithdrawalFormButtons();
            $htmlContent .= $withdrawalForm->toHtml();
            $htmlContent .= $withdrawalGrid->toHtml();
            $withdrawalFieldset->setData('html_content', $htmlContent);
        }

        $form->setValues($affiliate->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return Mage::helper('awaffiliate')->__('Balance');
    }

    public function getTabTitle()
    {
        return Mage::helper('awaffiliate')->__('Balance Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }


    public function getBalanceBlock() {

    }

    protected function _getMessagesBlockHtml($elementId)
    {
        $html = '';
        $html .= '<div id="' . $elementId . '">';
        $html .= '<ul class="messages">';
        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    protected function _getWithdrawalFormButtons()
    {
        $html = '';
        $html .= '<div id="withdrawal-head" class="content-header">';
        $html .= '<h3 class="icon-head">' . Mage::helper('awaffiliate')->__('Manage Withdrawal Request') . '</h3>';
        $html .= '<p class="form-buttons">';
        $html .= '<button type="button" class="scalable back" id="back_to_withdrawal_list"><span>' . Mage::helper('awaffiliate')->__('Back') . '</span></button>';
        $html .= '<button type="button" class="scalable save" id="withdrawal_save"><span>' . Mage::helper('awaffiliate')->__('Save') . '</span></button>';
        $html .= '</p>';
        $html .= '</div>';
        return $html;
    }

    protected function _getProfitFormButtons()
    {
        $html = '';
        $html .= '<div id="profit-head" class="content-header">';
        $html .= '<h3 class="icon-head">' . Mage::helper('awaffiliate')->__('Add commissions') . '</h3>';
        $html .= '<p class="form-buttons">';
        $html .= '<button type="button" class="scalable save" id="profit_add"><span>' . Mage::helper('awaffiliate')->__('Add') . '</span></button>';
        $html .= '</p>';
        $html .= '</div>';
        return $html;
    }

    protected function _getScriptInitialization()
    {
        $affiliate = Mage::registry('current_affiliate');
        $html = "";
        $html .= "<script type='text/javascript'>";
        $html .= " var awAffiliateProfitConfig = new varienForm('profit_form', ''); ";
        $html .= " awAffiliateProfitConfig.ajaxJsErrorMsg = '" . Mage::helper('awaffiliate')->__('Incorrect response') . "' ; ";
        $html .= " awAffiliateProfitConfig.incorrectValidation = '" . Mage::helper('awaffiliate')->__('Incorrect validation') . "' ; ";
        $html .= " awAffiliateProfitConfig.affiliate_id ='".$affiliate->getId()."' ; ";
        $html .= " awAffiliateProfitConfig.requestUrl = '" . Mage::getUrl('awaffiliate/adminhtml_affiliate/jsonProfitAdd', array('affiliate_id' => $affiliate->getId())) . "' ; ";
        $html .= "</script>";
        return $html;
    }
}
