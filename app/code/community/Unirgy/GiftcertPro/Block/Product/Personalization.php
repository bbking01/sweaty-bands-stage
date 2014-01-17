<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-15
 * Time: 21:08
 */

class Unirgy_GiftcertPro_Block_Product_Personalization
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery
{
    public function getContentHtml()
    {
        $product     = $this->getDataObject();
        $personalizationSettings = $product->getData('ugiftcert_personalization');
        if(!is_array($personalizationSettings)){
            $personalizationSettings = Zend_Json::decode($personalizationSettings);
        }
        $this->setData('value', $personalizationSettings);
        $content = Mage::getSingleton('core/layout')
            ->createBlock('ugiftcertpro/adminhtml_product_personalization')
            ->setElement($this);
        return $content->toHtml();
    }

}
