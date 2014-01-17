<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_configPath = 'ugiftcert/pdf';
    public $_fieldsetId;

    /**
     * @var Mage_Adminhtml_Model_Config_Data
     */
    protected $_configDataObject;
    protected $_configData;

    protected function _construct()
    {
//        $this->setTemplate('ugiftcert/pdf/form.phtml');
        parent::_construct();
        $this->_configDataObject = Mage::getModel('adminhtml/config_data')
                ->setSection($this->getSectionCode())
                ->setWebsite($this->getWebsiteCode())
                ->setStore($this->getStoreCode());
    }

    protected function _prepareLayout()
    {
        $layout = $this->getLayout();
        $head = $layout->getBlock('head');
        if($head) {
            /* @var $head Mage_Adminhtml_Block_Page_Head*/
            $head->addCss('ugiftcert.css');
        }
    }

    protected function _prepareForm()
    {
        $data = $this->getSettingsData();
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $mainFieldset = $form->addFieldset('main_settings_fs',
                                           array('legend' => $this->__("Main PDF Settings"), 'class' => 'ugiftcert_pdf'));
        if ($this->getDataModel()->getId()) {
            $id = $this->getDataModel()->getId();
            $link = new Unirgy_Giftcert_Block_Link(array(
                'href'  => $this->getUrl('*/*/printout', array('id' => $id))
            ));
            $link->setId('printout');
            $mainFieldset->addElement($link, '^');
            $data['printout'] = $this->__("Get Preview");
        }
        $mainFieldset->addField('title','text',
                                array(
                                    'name' => 'title',
                                    'label' => $this->__("Template Title"),
                                    'required' => true,
                                ));
        $mainFieldset->addField('use_font','select',
                                array(
                                    'name' => 'settings[use_font]',
                                    'label' => $this->__("Font to use in PDF"),
                                    'values' => Mage::getModel('ugiftcert/source_pdf')->setPath('use_font')->toOptionArray(),
                                    'required' => true,
                                    'note' => $this->__("Using standard fonts will yield much smaller file size<br><strong>Attention:</strong> In Magento 1.7 there is a bug with Zend PDF when using bundled fonts."),
                                ));
        $mainFieldset->addField('units','select',
                                array(
                                     'name' => 'settings[units]',
                                     'label' => $this->__("Settings Units"),
                                     'values' => Mage::getModel('ugiftcert/source_pdf')->setPath('units')->toOptionArray(),
                                     'required' => true,
                                     'class' => 'weight-select'
                                ));

        $pageFieldset = $form->addFieldset('page_settings_fs',
                                           array('legend' => $this->__("Page Settings"), 'class' => 'ugiftcert_pdf'));
        $pageFieldset->addField('page_width','text',
                                array(
                                     'name' => 'settings[page_width]',
                                     'label' => $this->__("Page Width"),
                                     'required' => true,
                                     'class' => 'short-text validate-number'
                                ));
        $pageFieldset->addField('page_height','text',
                                array(
                                     'name' => 'settings[page_height]',
                                     'label' => $this->__("Page Height"),
                                     'required' => true,
                                     'class' => 'short-text validate-number'
                                ));

        $textFieldset = $form->addFieldset('text_settings_fs',
                                           array('legend' => $this->__("Text Settings"), 'class' => 'ugiftcert_pdf'));
        $textFieldset->addField('text_settings','text',
                                array(
                                    'name' => 'settings[text_settings]',
                                    'label' => $this->__("Text Settings"),
                                    'note'  => $this->__("You can use custom templates for all fields. Free text fields will be rendered literally.<br/>For certificate properties fields, you can use <a href='http://php.net/sprintf'>sprintf()</a> formatted template, field value will be the only passed argument.")
                                ));
        $form->getElement('text_settings')->setRenderer($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_edit_tab_text'));

        $imageFieldset = $form->addFieldset('image_settings_fs',
                                           array('legend' => $this->__("Images Settings"), 'class' => 'ugiftcert_pdf'));
        $imageFieldset->addField('image_settings','text',
                                 array(
                                     'name' => 'settings[image_settings]',
                                     'label' => $this->__("Images Settings"),
                                     'note' => $this->__("Prepare your images beforehand. Too much resizing in PDF will result in distorted images.<br/>Images functionality requires that PHP runs with <strong>GD</strong> library enabled. If you have troubles with image not displaying check for GD first.<br/>Accepted formats JPG, PNG and TIFF")
                                 ));
        $form->getElement('image_settings')->setRenderer($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_edit_tab_image'));

        $form->setValues($data);
        return parent::_prepareForm();
    }

    public function getSectionCode()
    {
        return $this->_configPath;
//        return 'ugiftcert';
    }

    public function getFieldsetId()
    {
        return $this->_fieldsetId;
    }

    public function setFieldsetId($fieldsetId)
    {
        $this->_fieldsetId = $fieldsetId;
    }

    protected function getSettingsData()
    {
        /* @var $model Unirgy_Giftcert_Model_Pdf_Model */
        $model = $this->getDataModel();
        if(!$model){
            return $this->getLegacyData();
        }
        if($model->getId()){
            $data = $model->getData();
            $settings = Zend_Json::decode($data['settings']);
            $data['settings'] = $settings;
            foreach ($settings as $id => $value) {
                $data[$id] = $value;
            }

            return $data;
        }
        return array();
    }

    protected function getLegacyData()
    {
        $data = $this->getConfigData();
        if (empty($data)){
            return $data;
        }
        if(!isset($data['text_settings'])) {
            $txt_settings = $this->_parseLegacyTextSettings($data);
            $data['text_settings'] = $txt_settings;
        } else  if (!empty($data['text_settings']) && is_string($data['text_settings'])) {
            $data['text_settings'] = @unserialize($data['text_settings']);
        }

        if(!isset($data['image_settings']) && isset($data['image'])) {
            $data['image_settings'] = $this->_parseLegacyImageSettings($data);
        } else  if (!empty($data['image_settings']) && is_string($data['image_settings'])){
            $data['image_settings'] = @unserialize($data["image_settings"]);
        }

        return $data;
    }

    /**
     * Get config data prepared for use
     * @return array
     */
    protected function getConfigData()
    {
        if(!isset($this->_configData)) {
            $data = $this->_configDataObject->load(); // load appropriate scope data
            foreach($data as $path => $value) {
                $key = substr($path, strlen($this->_configPath . DS)); // remove path prefix
                $this->_configData[$key] = $value;
            }
        }
        return $this->_configData;
    }

    protected function _parseLegacyImageSettings($data)
    {
        $img_settings = array(
            array(
                'url'    => 'unirgy/giftcert/pdf/' . $data['image'],
                'value'  => $data['image'],
                'width'  => isset($data['image_width']) ? $data['image_width'] : '',
                'height' => isset($data['image_height']) ? $data['image_height'] : '',
                'x_pos'  => isset($data['image_x']) ? $data['image_x'] : '',
                'y_pos'  => isset($data['image_y']) ? $data['image_y'] : '',
            )
        );
        return $img_settings;
    }

    protected function _parseLegacyTextSettings($data)
    {
        $settings = array(
            array('field'        => 'cert_number',
                  'template'     => '%s',
                  'x_pos'        => isset($data['cert_number_x']) ? $data['cert_number_x']: '',
                  'y_pos'        => isset($data['cert_number_y']) ? $data['cert_number_y']: '',
                  'font_size'    => isset($data['cert_number_font_size']) ? $data['cert_number_font_size']: '',
                  'font_variant' => isset($data['cert_number_font_weight']) ? $data['cert_number_font_weight']: '',
                  'color'        => isset($data['cert_number_font_color']) ? $data['cert_number_font_color']: ''),
            array('field'        => 'pin',
                  'template'     => '%s',
                  'x_pos'        => isset($data['pin_x']) ? $data['pin_x']: '',
                  'y_pos'        => isset($data['pin_y']) ? $data['pin_y']: '',
                  'font_size'    => isset($data['pin_font_size']) ? $data['pin_font_size']: '',
                  'font_variant' => isset($data['pin_font_weight']) ? $data['pin_font_weight']: '',
                  'color'        => isset($data['pin_font_color']) ? $data['pin_font_color']: ''),
            array('field'        => 'other',
                  'template'     => isset($data['field_1']) ? $data['field_1']: '',
                  'x_pos'        => isset($data['field_1_x']) ? $data['field_1_x']: '',
                  'y_pos'        => isset($data['field_1_y']) ? $data['field_1_y']: '',
                  'font_size'    => isset($data['field_1_font_size']) ? $data['field_1_font_size']: '',
                  'font_variant' => isset($data['field_1_font_weight']) ? $data['field_1_font_weight']: '',
                  'color'        => isset($data['field_1_font_color']) ? $data['field_1_font_color']: ''),
            array('field'        => 'other',
                  'template'     => isset($data['field_2']) ? $data['field_2']: '',
                  'x_pos'        => isset($data['field_2_x']) ? $data['field_2_x']: '',
                  'y_pos'        => isset($data['field_2_y']) ? $data['field_2_y']: '',
                  'font_size'    => isset($data['field_2_font_size']) ? $data['field_2_font_size']: '',
                  'font_variant' => isset($data['field_2_font_weight']) ? $data['field_2_font_weight']: '',
                  'color'        => isset($data['field_2_font_color']) ? $data['field_2_font_color']: ''),
            array('field'        => 'other',
                  'template'     => isset($data['field_3']) ? $data['field_3']: '',
                  'x_pos'        => isset($data['field_3_x']) ? $data['field_3_x']: '',
                  'y_pos'        => isset($data['field_3_y']) ? $data['field_3_y']: '',
                  'font_size'    => isset($data['field_3_font_size']) ? $data['field_3_font_size']: '',
                  'font_variant' => isset($data['field_3_font_weight']) ? $data['field_3_font_weight']: '',
                  'color'        => isset($data['field_3_font_color']) ? $data['field_3_font_color']: ''),
            array('field'        => 'other',
                  'template'     => isset($data['field_4']) ? $data['field_4']: '',
                  'x_pos'        => isset($data['field_4_x']) ? $data['field_4_x']: '',
                  'y_pos'        => isset($data['field_4_y']) ? $data['field_4_y']: '',
                  'font_size'    => isset($data['field_4_font_size']) ? $data['field_4_font_size']: '',
                  'font_variant' => isset($data['field_4_font_weight']) ? $data['field_4_font_weight']: '',
                  'color'        => isset($data['field_4_font_color']) ? $data['field_4_font_color']: ''),
            array('field'        => 'other',
                  'template'     => isset($data['field_5']) ? $data['field_5']: '',
                  'x_pos'        => isset($data['field_5_x']) ? $data['field_5_x']: '',
                  'y_pos'        => isset($data['field_5_y']) ? $data['field_5_y']: '',
                  'font_size'    => isset($data['field_5_font_size']) ? $data['field_5_font_size']: '',
                  'font_variant' => isset($data['field_5_font_weight']) ? $data['field_5_font_weight']: '',
                  'color'        => isset($data['field_5_font_color']) ? $data['field_5_font_color']: '')
        );

        return $settings;
    }

    private function getWebsiteCode()
    {
        return $this->getRequest()->getParam('website','');
    }

    private function getStoreCode()
    {
        return $this->getRequest()->getParam('store','');
    }

    /**
     * @return null|Unirgy_Giftcert_Model_Pdf_Model
     */
    private function getDataModel()
    {
        $model = Mage::registry('giftcert_pdf_tpl');
        return $model;
    }
}
