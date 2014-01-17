<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */

class Unirgy_GiftcertPro_Block_Adminhtml_Product_Personalization
    extends Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Abstract
{
    /**
     * @var array
     */
    protected $pdfTemplates;
    /**
     * @var array
     */
    protected $emailTemplates;

    public function __construct()
    {
        $this->setTemplate('ugiftcertpro/product/personalization.phtml');
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
            'label'     => Mage::helper('catalog')->__('Add Option'),
            'onclick'   => 'return optionsControl.addItem()',
            'class'     => 'add'
        ));
        $button->setName('add_option_item_button');

        $this->setChild('add_button', $button);
        return $this;
    }

    public function getPdfTemplates()
    {
        if (empty($this->pdfTemplates)) {
            $options            = Mage::getSingleton('ugiftcert/source_pdf')
                ->setPath('ugiftcert/email/pdf_template');
            $this->pdfTemplates = $options->toOptionHash();
        }
        return $this->pdfTemplates;
    }

    public function getEmailTemplates()
    {
        if (empty($this->emailTemplates)) {
            $options              = Mage::getSingleton('ugiftcert/source_template');
            $this->emailTemplates = $options->toOptionHash();
        }
        return $this->emailTemplates;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortData($a, $b)
    {
        return 0;
    }
}
