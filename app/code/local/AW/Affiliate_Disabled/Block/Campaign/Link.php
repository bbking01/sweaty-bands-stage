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


class AW_Affiliate_Block_Campaign_Link extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/campaign/generate_link.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    public function getUrlToRequest()
    {
        $campaignId = Mage::registry('current_campaign')->getId();
        $affiliateId = Mage::registry('current_affiliate')->getId();
        $params = array(
            'campaign_id' => $campaignId,
            'affiliate_id' => $affiliateId
        );
        return Mage::getUrl('awaffiliate/customer_affiliate/generateLink', $params);
    }

    public function getInputLinkField()
    {
        $input = new Varien_Data_Form_Element_Text(array(
            'label' => $this->__('Website Link'),
            'html_id' => 'link-to-generate',
            'name' => 'link_to_generate',
            'no_span' => true,
            'required' => true,
            'after_element_html' => $this->__('Direct link visitor will be redirected to. Full web page URL required.')
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('link_to_generate'))) {
            $input->setData('value', $_defaultValue);
        }
        else {
            $_defaultValue = Mage::app()->getStore()->getBaseUrl();
            $input->setData('value', $_defaultValue);
        }

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getTrafficSourceLinkField()
    {
        $input = new Varien_Data_Form_Element_Text(array(
            'label' => $this->__('Traffic Source'),
            'html_id' => 'traffic-source-generate',
            'name' => 'traffic_source_generate',
            'no_span' => true,
            'after_element_html' => $this->__('Custom value to group data in reports.')
        ));

        $input->setForm(new Varien_Object());
        return $input->getHtml();
    }

    public function getResultLinkField()
    {
        $textarea = new Varien_Data_Form_Element_Textarea(array(
            'label' => $this->__('Tracking Link'),
            'html_id' => 'result',
            'name' => 'result',
            'no_span' => true,
        ));
        if (!is_null($_defaultValue = $this->__getDefaultValue('result'))) {
            $textarea->setData('value', $_defaultValue);
        }
        $textarea->setForm(new Varien_Object());
        return $textarea->getHtml();
    }

    private function __getDefaultValue($key)
    {
        $_session = Mage::getSingleton('customer/session');
        $formData = $_session->getGenerateLinkFormData();
        return isset($formData[$key]) ? $formData[$key] : null;
    }
}
