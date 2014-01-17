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


class AW_Affiliate_Block_Report_Create extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/report/create.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    public function getActionUrl()
    {
        return Mage::getUrL('awaffiliate/customer_affiliate/getReportAsJson');
    }

    public function getReportTypeField()
    {
        $select = new Varien_Data_Form_Element_Select(array(
            'label' => $this->__('Report type'),
            'html_id' => 'report-type',
            'name' => 'report_type',
            'no_span' => true,
            'values' => $this->getReportTypes(),
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('report_type'))) {
            $select->setData('value', $_defaultValue);
        }
        $select->setForm(new Varien_Object());
        return $select->getHtml();
    }

    public function getReportDatePeriodField()
    {
        $select = new Varien_Data_Form_Element_Select(array(
            'label' => $this->__('Date period'),
            'html_id' => 'date-period',
            'name' => 'date_period',
            'no_span' => true,
            'values' => $this->getDefaultDatePeriods(),
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('date_period'))) {
            $select->setData('value', $_defaultValue);
        }
        $select->setForm(new Varien_Object());
        return $select->getHtml();
    }

    public function getPeriodFromField()
    {
        $label = new Varien_Data_Form_Element_Label(array(
            'html_id' => 'period-from',
            'no_span' => true,
            'label' => $this->__('From'),
        ));
        $label->setForm(new Varien_Object());

        $date = Mage::getSingleton('core/layout')->createBlock('core/html_date');
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $date->setData(array(
            'id' => 'period-from',
            'name' => 'period_from',
            'no_span' => true,
            'image' => $this->getSkinUrl('images/calendar.gif'),
            'format' => $format,
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('period_from'))) {
            $date->setData('value', $_defaultValue);
        } else {
            $dateDefault = Mage::helper('awaffiliate/report')
                ->getPeriodRange(AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD_DEFAULT);
            $date->setData('value', $dateDefault['from']);
        }
        $html = $label->getLabelHtml() . $date->getHtml();
        return $html;
    }

    public function getPeriodToField()
    {
        $label = new Varien_Data_Form_Element_Label(array(
            'html_id' => 'period-to',
            'no_span' => true,
            'label' => $this->__('To'),
        ));
        $label->setForm(new Varien_Object());

        $date = Mage::getSingleton('core/layout')->createBlock('core/html_date');
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $date->setData(array(
            'id' => 'period-to',
            'name' => 'period_to',
            'no_span' => true,
            'image' => $this->getSkinUrl('images/calendar.gif'),
            'format' => $format,
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('period_to'))) {
            $date->setData('value', $_defaultValue);
        } else {
            $dateDefault = Mage::helper('awaffiliate/report')
                ->getPeriodRange(AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD_DEFAULT);
            $date->setData('value', $dateDefault['to']);
        }
        $html = $label->getLabelHtml() . $date->getHtml();
        return $html;
    }

    public function getDetalizationsField()
    {
        $select = new Varien_Data_Form_Element_Select(array(
            'label' => $this->__('Graph by'),
            'html_id' => 'detalization',
            'name' => 'detalization',
            'no_span' => true,
            'values' => $this->getDetalizations(),
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('detalization'))) {
            $select->setData('value', $_defaultValue);
        }
        $select->setForm(new Varien_Object());
        return $select->getHtml();
    }

    public function getCampaignsField()
    {
        $multiselect = new Varien_Data_Form_Element_Multiselect(array(
            'label' => $this->__('Include Campaigns'),
            'html_id' => 'campaigns',
            'name' => 'campaigns',
            'no_span' => true,
            'values' => $this->getCampaigns(),
            'select_all' => $this->__('Select All'),
            'deselect_all' => $this->__('Deselect All'),
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('campaigns'))) {
            $multiselect->setData('value', $_defaultValue);
        } else {
            $multiselect->setData('value', array_keys($this->getCampaigns(true)));
        }
        $multiselect->setForm(new Varien_Object());
        return $multiselect->getHtml();
    }

    public function isFormSpecified()
    {
        $_session = Mage::getSingleton('customer/session');
        return !is_null($_session->getCreateReportFormData());
    }

    protected function getReportTypes()
    {
        $types = Mage::getModel('awaffiliate/source_report_type')->toOptionArray();
        return $types;
    }

    protected function getDefaultDatePeriods()
    {
        $periods = Mage::getModel('awaffiliate/source_report_period')->toOptionArray();
        return $periods;
    }

    protected function getDetalizations()
    {
        return Mage::getModel('awaffiliate/source_report_detalization')->toOptionArray();
    }

    protected function getCampaigns($short = false)
    {
        $campaigns = Mage::getModel('awaffiliate/source_campaign');

        return $short ? $campaigns->toShortOptionArray() : $campaigns->toOptionArray();
    }

    private function __getDefaultValue($key)
    {
        $_session = Mage::getSingleton('customer/session');
        $formData = $_session->getCreateReportFormData();
        return isset($formData[$key]) ? $formData[$key] : null;
    }
}
