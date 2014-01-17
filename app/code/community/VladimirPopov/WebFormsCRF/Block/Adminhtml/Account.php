<?php
class VladimirPopov_WebFormsCRF_Block_Adminhtml_Account
    extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{
    public function initForm()
    {
        parent::initForm();

        $form = $this->getForm();

        $customer = Mage::registry('current_customer');
        $group = Mage::getModel('customer/group')->load($customer->getGroupId());
        $customerStoreId = null;

        if ($customer->getId()) {
            $customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
        }

        $webformId = Mage::getStoreConfig('webformscrf/registration/form', $customerStoreId);
        if ($group->getWebformId()) {
            $webformId = $group->getWebformId();
        }
        if (!Mage::getStoreConfig('webformscrf/registration/enable', $customerStoreId) && $webformId == Mage::getStoreConfig('webformscrf/registration/form', $customerStoreId)) return $this;

        $webform = Mage::getModel('webforms/webforms')->load($webformId);
        $result = Mage::getModel('webforms/results');
        if ($customer->getData('result_id')) {
            $result->load($customer->getData('result_id'));
        }

        Mage::dispatchEvent('webformscrf_block_adminhtml_init_before', array('block' => $this, 'webform' => $webform));

        $editor_type = 'textarea';
        $editor_config = '';
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE') {

            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array('tab_id' => $this->getTabId())
            );

            $wysiwygConfig["files_browser_window_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
            $wysiwygConfig["directives_url"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
            $wysiwygConfig["directives_url_quoted"] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');

            $wysiwygConfig["add_widgets"] = false;
            $wysiwygConfig["add_variables"] = false;
            $wysiwygConfig["widget_plugin_src"] = false;
            $wysiwygConfig->setData("plugins", array());

            $editor_type = 'editor';
            $editor_config = $wysiwygConfig;
        }

        $fields_to_fieldsets = $webform->getFieldsToFieldsets(true);

        foreach ($fields_to_fieldsets as $fs_id => $fs_data) {
            $legend = "";
            if (!empty($fs_data['name'])) $legend = $fs_data['name'];

            // check logic visibility
            $fieldset = $form->addFieldset('fs_' . $fs_id, array(
                'legend' => $legend,
                'fieldset_container_id' => 'fieldset_' . $fs_id . '_container'
            ));

            foreach ($fs_data['fields'] as $field) {
                $type = 'text';
                $config = array
                (
                    'name' => 'field[' . $field->getId() . ']',
                    'label' => $field->getName(),
                    'container_id' => 'field_' . $field->getId() . '_container'
                );

                $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $dateTimeFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

                switch ($field->getType()) {
                    case 'textarea':
                    case 'hidden':
                        $type = 'textarea';
                        break;
                    case 'wysiwyg':
                        $type = $editor_type;
                        $config['config'] = $editor_config;
                        break;
                    case 'date':
                        $type = 'date';
                        $config['format'] = $dateFormatIso;
                        $config['image'] = $this->getSkinUrl('images/grid-cal.gif');
                        break;

                    case 'datetime':
                        $type = 'date';
                        $config['time'] = true;
                        $config['format'] = $dateTimeFormatIso;
                        $config['image'] = $this->getSkinUrl('images/grid-cal.gif');
                        break;

                    case 'select/radio':
                        $type = 'select';
                        $config['required'] = false;
                        $config['values'] = $field->getOptionsArray();
                        break;

                    case 'select/checkbox':
                        $type = 'checkboxes';
                        $value = explode("\n", $result->getData('field_' . $field->getId()));
                        $result->setData('field_' . $field->getId(), $value);
                        $config['options'] = $field->getSelectOptions();
                        $config['name'] = 'field[' . $field->getId() . '][]';
                        break;

                    case 'select':
                        $type = 'select';
                        $config['options'] = $field->getSelectOptions();
                        break;

                    case 'select/contact':
                        $type = 'select';
                        $config['options'] = $field->getSelectOptions(false);
                        break;

                    case 'stars':
                        $type = 'select';
                        $config['options'] = $field->getStarsOptions();
                        break;

                    case 'file':
                        $type = 'file';
                        $config['field_id'] = $field->getId();
                        $config['result_id'] = $result->getId();
                        $config['url'] = $result->getFilePath($field->getId());
                        $config['name'] = 'file_' . $field->getId();
                        $fieldset->addType('file', Mage::getConfig()->getBlockClassName('webforms/adminhtml_element_file'));
                        break;

                    case 'image':
                        $type = 'image';
                        $config['field_id'] = $field->getId();
                        $config['result_id'] = $result->getId();
                        $config['url'] = $result->getFilePath($field->getId());
                        $config['name'] = 'file_' . $field->getId();
                        $fieldset->addType('image', Mage::getConfig()->getBlockClassName('webforms/adminhtml_element_image'));
                        break;

                    case 'html':
                        $type = 'label';
                        $config['label'] = false;
                        $config['after_element_html'] = $field->getValue();
                        break;
                }
                // check logic visibility
                $fieldset->addField('field_' . $field->getId(), $type, $config);
            }
        }

        if ($customer->getData('result_id')) {
            $form->addField('result_id', 'hidden', array('name' => 'result_id'));
        }

        $form->addValues($customer->getData());

        $form->addField('webform_id', 'hidden', array('name' => 'webform_id', 'value' => $webformId));

        $this->setForm($form);

        return $this;
    }
}

?>
