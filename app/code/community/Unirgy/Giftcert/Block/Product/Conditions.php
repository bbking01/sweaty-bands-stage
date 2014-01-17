<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-15
 * Time: 21:08
 */

class Unirgy_Giftcert_Block_Product_Conditions
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery
{
    public function getContentHtml()
    {
        if (Mage::getStoreConfig('ugiftcert/default/use_conditions')) {
            $head = Mage::getSingleton('core/layout')->getBlock('head');
            if ($head) {
                if (!Mage::registry('giftcert_data')) {
                    Mage::register('giftcert_data', Mage::getModel('ugiftcert/cert'));
                }
                $product = $this->getDataObject();
                $conditions = $product->getData('ugiftcert_conditions');
                if ($conditions && !is_array($conditions)) {
                    $conditions = unserialize($conditions);
                }
                if ($conditions) {
                    Mage::registry('giftcert_data')->getConditions()->setConditions(array())->loadArray($conditions);
                }
                $head->setCanLoadExtJs(true);
                $head->setCanLoadRulesJs(true);
                $content = Mage::getSingleton('core/layout')->createBlock(
                    'ugiftcert/adminhtml_cert_edit_tab_conditions'
                );
                return $content->toHtml();
            }
        }
        return ''; // if we cannot load js, no point to show the rest
    }

}
