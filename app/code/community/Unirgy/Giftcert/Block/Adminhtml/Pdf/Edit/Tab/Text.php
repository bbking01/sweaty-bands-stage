<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-11
 * Time: 20:31
 */
 
class Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Text
    extends Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Abstract
{
    protected $defaultFontSize = 12;
    protected $defaultColor = '000000';
    protected $defaultFontVariant = 'r';
    protected $fontVariants;
    public $fieldTypes;

    public function __construct()
    {
        $this->setTemplate('ugiftcert/pdf/text.phtml');
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
                           'onclick'   => 'return textControl.addItem()',
                           'class'     => 'add'
                      ));
        $button->setName('add_text_item_button');

        $this->setChild('add_button', $button);
        return $this;
    }

    public function getFontVariants()
    {
        if(empty($this->fontVariants)) {
            $options = Mage::getSingleton('ugiftcert/source_pdf')->setPath('font_variants');
            $this->fontVariants = $options->toOptionHash();
        }
        return $this->fontVariants;
    }

    public function getFieldTypes()
    {
        if(empty($this->fieldTypes)) {
            $options = Mage::getSingleton('ugiftcert/source_pdf')->setPath('fields');
            $this->fieldTypes = $options->toOptionHash();
        }
        return $this->fieldTypes;
    }

    public function getDefaultFontSize()
    {
        return $this->defaultFontSize;
    }

    public function getDefaultFontVariant()
    {
        return $this->defaultFontVariant;
    }

    public function getDefaultColor()
    {
        return $this->defaultColor;
    }

    public static function isFieldDate($field)
    {
        $dateFields = array(
            'expire_at',
        );
        return in_array($field, $dateFields);
    }
}
