<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-15
 * Time: 21:08
 */

class Unirgy_Giftcert_Block_Product_Pdf
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery
{
    public function getContentHtml()
    {
        if(Mage::getStoreConfig('ugiftcert/email/pdf_template')){
            return '';
        } else if (Mage::getStoreConfig('ugiftcert/pdf/enabled')) {
            // kept this for some very unlikely backwards compatibility
            $product     = $this->getDataObject();
            $pdfSettings = $product->getData('ugiftcert_pdf_settings');
            if (!Mage::registry('giftcert_data')) {
                Mage::register('giftcert_data', Mage::getModel('ugiftcert/cert'));
            }
            Mage::registry('giftcert_data')->setPdfSettings($pdfSettings);
            $content = Mage::getSingleton('core/layout')->createBlock('ugiftcert/adminhtml_cert_edit_tab_pdf');
            return $content->toHtml();
        }
        return '';
    }

}
