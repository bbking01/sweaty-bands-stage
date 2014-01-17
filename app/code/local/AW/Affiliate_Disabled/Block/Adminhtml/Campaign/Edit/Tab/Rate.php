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


class AW_Affiliate_Block_Adminhtml_Campaign_Edit_Tab_Rate
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
        $campaign = Mage::registry('current_campaign');
        $profit = $campaign->getProfitModel();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rate_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('awaffiliate')->__('Rate Information'))
        );

        $fieldset->addField('type', 'select', array(
            'name' => 'rate_type',
            'label' => Mage::helper('awaffiliate')->__('Type'),
            'title' => Mage::helper('awaffiliate')->__('Type'),
            'values' => Mage::getModel('awaffiliate/source_profit_type')->toOptionArray(),
            'required' => true,
        ));

        $fieldset->addField('rate_calculation_type', 'select', array(
            'name' => 'rate_settings[rate_calculation_type]',
            'label' => Mage::helper('awaffiliate')->__('Calculation type'),
            'title' => Mage::helper('awaffiliate')->__('Calculation type'),
            'values' => Mage::getModel('awaffiliate/source_profit_calculation_type')->toOptionArray(),
            'required' => true,
        ));

        $fieldset->addField('profit_rate', 'text', array(
            'name' => 'profit_rate',
            'label' => Mage::helper('awaffiliate')->__('Amount, %'),
            'title' => Mage::helper('awaffiliate')->__('Amount, %'),
            'required' => true,
        ));

        $_tiers = $this->_getTiersData($profit);
        if ($profit->getType() == AW_Affiliate_Model_Source_Profit_Type::TIER) {
            $_tiersData = $_tiers;
        }
        else {
            $_tiersData = array();
        }

        $fieldset->addField('tier_price', 'text', array(
            'name' => 'tier_price',
            'label' => Mage::helper('awaffiliate')->__('Amount'),
            'required' => true,
            'value' => $_tiersData,
        ));

        $fieldset->addField('profit_rate_cur', 'text', array(
            'name' => 'profit_rate_cur',
            'label' => Mage::helper('awaffiliate')->__('Amount'),
            'title' => Mage::helper('awaffiliate')->__('Amount'),
            'required' => true,
        ));

        if ($profit->getType() == AW_Affiliate_Model_Source_Profit_Type::TIER_CUR) {
            $_tiersCurData = $_tiers;
        }
        else {
            $_tiersCurData = array();
        }

        $fieldset->addField('tier_price_cur', 'text', array(
            'name' => 'tier_price_cur',
            'label' => Mage::helper('awaffiliate')->__('Amount'),
            'required' => true,
            'value' => $_tiersCurData,
        ));

        $form->getElement('tier_price')->setRenderer(
            $this->getLayout()->createBlock('awaffiliate/adminhtml_campaign_edit_tab_rate_tier')
        );

        $form->getElement('tier_price_cur')->setRenderer(
            $this->getLayout()->createBlock('awaffiliate/adminhtml_campaign_edit_tab_rate_tiercur')
        );

        $form->addValues(array('type' => $profit->getType()))
            ->addValues($profit->getRateSettings());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return Mage::helper('awaffiliate')->__('Rate');
    }


    public function getTabTitle()
    {
        return Mage::helper('awaffiliate')->__('Rate Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _getTiersData($profit)
    {
        if ($profit->hasTierPrice())
            return $profit->getTierPrice();
        $profitId = $profit->getId();
        $_tiersCollection = Mage::getModel('awaffiliate/profit_tier_rate')->loadByProfitId($profitId);
        $_tiersData = array();
        foreach ($_tiersCollection->getItems() as $item) {
            $_data = array(
                'cust_group' => $item->getAffiliateGroupId(),
                'amount' => $item->getProfitAmount(),
                'rate' => $item->getProfitRate(),
            );
            $_tiersData[] = $_data;
        }
        return $_tiersData;
    }
}
