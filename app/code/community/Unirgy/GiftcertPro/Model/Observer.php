<?php

class Unirgy_GiftcertPro_Model_Observer
{

    public function catalog_product_prepare_save($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() !== 'ugiftcert') {
            return;
        }
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp                    = Mage::helper('ugiftcertpro');
        $personalizationOptions = $hlp->preparePersonalizeSettings();
        $product->setData('ugiftcert_personalization', $personalizationOptions);
    }
}
