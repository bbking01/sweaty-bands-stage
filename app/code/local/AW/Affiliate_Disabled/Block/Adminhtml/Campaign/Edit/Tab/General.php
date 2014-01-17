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


class AW_Affiliate_Block_Adminhtml_Campaign_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _prepareForm()
    {
        /* @var $model AW_Affiliate_Model_Campaign */
        $campaign = Mage::registry('current_campaign');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('awaffiliate')->__('General Information'))
        );

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('awaffiliate')->__('Name'),
            'title' => Mage::helper('awaffiliate')->__('Name'),
            'required' => true,
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'multiselect', array(
                'name' => 'store_ids[]',
                'title' => Mage::helper('awaffiliate')->__('Store'),
                'label' => Mage::helper('awaffiliate')->__('Store'),
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
                'required' => true,
            ));
        }

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $fieldset->addField('active_from', 'date', array(
            'name' => 'active_from',
            'label' => Mage::helper('awaffiliate')->__('Active From'),
            'title' => Mage::helper('awaffiliate')->__('Active From'),
            'format' => $dateFormat,
            'locale' => $locale,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));

        $fieldset->addField('active_to', 'date', array(
            'name' => 'active_to',
            'label' => Mage::helper('awaffiliate')->__('Active To'),
            'title' => Mage::helper('awaffiliate')->__('Active To'),
            'format' => $dateFormat,
            'locale' => $locale,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => Mage::helper('awaffiliate')->__('Status'),
            'title' => Mage::helper('awaffiliate')->__('Status'),
            'values' => Mage::getModel('awaffiliate/source_campaign_status')->toShortOptionArray(),
            'required' => true,
        ));

        $fieldset->addField('allowed_groups', 'multiselect', array(
            'name' => 'allowed_groups[]',
            'label' => Mage::helper('awaffiliate')->__('Customer Groups'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'values' => Mage::getModel('awaffiliate/source_customer_groups')->toOptionArray(),
            'required' => true,
            'note' => Mage::helper('awaffiliate')->__('Customer groups of affiliates who can access this campaign'),
        ));

        $adminhhtmlUrlModel = Mage::getSingleton('adminhtml/url');
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $wysiwygConfig->addData(array(
            'files_browser_window_url' => $adminhhtmlUrlModel->getUrl('adminhtml/cms_wysiwyg_images/index'),
            'directives_url' => $adminhhtmlUrlModel->getUrl('adminhtml/cms_wysiwyg/directive')
        ));

        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => $this->__('Description'),
            'config' => $wysiwygConfig
        ));

        $form->setValues($campaign->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return Mage::helper('awaffiliate')->__('General');
    }


    public function getTabTitle()
    {
        return Mage::helper('awaffiliate')->__('General Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
