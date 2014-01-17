<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-15
 * Time: 17:46
 */
 
class Unirgy_Giftcert_Block_Adminhtml_Cert_Edit_Tab_Pdf
    extends Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        /* @var $mainFieldset Varien_Data_Form_Element_Fieldset */
        $mainFieldset = $form->getElement('main_settings_fs');
        $mainFieldset->removeField('enabled');
        $config = array(
            'name' => 'use_default_pdf',
            'label' => $this->__("Use default settings"),
            'note'  => $this->__('<strong>If this is checked all changes made to PDF settings will be ignored.</strong>')
        );
        $data = $this->getSettingsData();
        if (isset($data['use_default_pdf']) && $data['use_default_pdf']) {
            $config['checked'] = true;
        }
        $mainFieldset->addField('use_default_pdf', 'checkbox', $config, '^');
        return $this;
    }

    protected function getSettingsData()
    {
        $data = parent::getSettingsData(); // get default pdf settings
        if($model = Mage::registry('giftcert_data')) { // check for registered cert model, if one and it has pdf settings, use them.
            $pdfSettings = $model->getPdfSettings();
            $useDefault = isset($pdfSettings['use_default_pdf']) ? true : false;
            unset($pdfSettings['use_default_pdf']);
            /* @var $model Unirgy_Giftcert_Model_Cert */
            if($model && !empty($pdfSettings)) {
                $data = $pdfSettings;
            }

            if($useDefault) {
                $data['use_default_pdf'] = 1;
            }
        }
        return $data;
    }
}
