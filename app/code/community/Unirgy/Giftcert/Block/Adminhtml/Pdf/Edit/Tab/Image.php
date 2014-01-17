<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-11
 * Time: 20:31
 */
 
class Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Image
    extends Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Abstract
{
    public function __construct()
    {
        $this->setTemplate('ugiftcert/pdf/image.phtml');
    }

    protected function _sortData($a, $b)
    {
        return 0;
    }

    /**
     * Prepare global layout
     * Add "Add Row" button to layout
     *
     * @return Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Text
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                           'label'     => Mage::helper('catalog')->__('Add Row'),
                           'onclick'   => 'return imageControl.addItem()',
                           'class'     => 'add'
                      ));
        $button->setName('add_image_item_button');

        $this->setChild('add_button', $button);
        return $this;
    }

    public function getBaseUrl() {
        if (!$this->_baseUrl) {
            $this->_baseUrl = Mage::getBaseUrl('media');
        }
        return $this->_baseUrl;
    }
    
    public function getValues()
    {
        $values = parent::getValues();
        foreach ($values as &$_item) {
            $_item['url'] = isset($_item['url']) ? $_item['url']: '';
            $_item['value'] = isset($_item['value']) ? $_item['value']: '';
            $_item['width'] = isset($_item['width']) ? $_item['width']: 0;
            $_item['height'] = isset($_item['height']) ? $_item['height']: 0;
            $_item['x_pos'] = isset($_item['x_pos']) ? $_item['x_pos']: 0;
            $_item['y_pos'] = isset($_item['y_pos']) ? $_item['y_pos']: 0;
        }
        return $values;
    }
}
