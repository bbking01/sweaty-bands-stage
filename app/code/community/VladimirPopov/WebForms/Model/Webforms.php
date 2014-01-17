<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2013 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Webforms
    extends VladimirPopov_WebForms_Model_Abstract
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_fields_to_fieldsets = array();
    protected $_hidden = array();
    protected $_logic_target = array();

    public function _getFieldsToFieldsets()
    {
        return $this->_fields_to_fieldsets;
    }

    public function _setLogicTarget($logic_target)
    {
        $this->_logic_target = $logic_target;
        return $this;
    }

    public function _getLogicTarget()
    {
        return $this->_logic_target;
    }

    public function _setFieldsToFieldsets($fields_to_fieldsets)
    {
        $this->_fields_to_fieldsets = $fields_to_fieldsets;
        return $this;
    }

    public function _getHidden()
    {
        return $this->_hidden;
    }

    public function _setHidden($hidden)
    {
        $this->_hidden = $hidden;
        return $this;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/webforms');
    }

    public function getAvailableStatuses()
    {
        $statuses = new Varien_Object(array(
            self::STATUS_ENABLED => Mage::helper('webforms')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('webforms')->__('Disabled'),
        ));

        Mage::dispatchEvent('webforms_statuses', array('statuses' => $statuses));

        return $statuses->getData();

    }

    public function toOptionArray()
    {
        $collection = $this->getCollection()->addFilter('is_active', self::STATUS_ENABLED)->addOrder('name', 'asc');
        $option_array = array();
        foreach ($collection as $webform)
            $option_array[] = array('value' => $webform->getId(), 'label' => $webform->getName());
        return $option_array;
    }

    public function getFieldsetsOptionsArray()
    {
        $collection = Mage::getModel('webforms/fieldsets')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getId());
        $collection->getSelect()->order('position asc');
        $options = array(0 => '...');
        foreach ($collection as $o) {
            $options[$o->getId()] = $o->getName();
        }
        return $options;
    }

    public function getTemplatesOptions()
    {
        $options = array(0 => Mage::helper('webforms')->__('Default'));
        $templates = Mage::getResourceSingleton('core/email_template_collection');
        foreach ($templates as $template) {
            $options[$template->getTemplateId()] = $template->getTemplateCode();
        }
        return $options;
    }

    public function getEmailSettings()
    {
        $settings["email_enable"] = $this->getSendEmail();
        $settings["email"] = Mage::getStoreConfig('webforms/email/email');
        if ($this->getEmail())
            $settings["email"] = $this->getEmail();
        return $settings;
    }

    public function getFieldsToFieldsets($all = false)
    {
        $logic_rules = $this->getLogic();

        //get form fieldsets
        $fieldsets = Mage::getModel('webforms/fieldsets')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getId());

        if (!$all)
            $fieldsets->addFilter('is_active', self::STATUS_ENABLED);

        $fieldsets->getSelect()->order('position asc');

        //get form fields
        $fields = Mage::getModel('webforms/fields')
            ->setStoreId($this->getStoreId())
            ->getCollection()
            ->addFilter('webform_id', $this->getId());

        if (!$all) {
            $fields->addFilter('is_active', self::STATUS_ENABLED);
        }

        $fields->getSelect()->order('position asc');

        //fields to fieldsets
        //make zero fieldset
        $fields_to_fieldsets = array();
        $hidden = array();
        $required_fields = array();
        $default_data = array();

        foreach ($fields as $field) {
            // set default data
            if (strstr($field->getType(), 'select')) {
                $options = $field->getOptionsArray();
                $checked_options = array();
                foreach ($options as $o) {
                    if ($o['checked']) {
                        $checked_options[] = $o['value'];
                    }
                }
                if (count($checked_options)) {
                    $default_data[$field->getId()] = $checked_options;
                }
            }

            //set default visibility
            $field->setData('logic_visibility', VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE);

            if ($field->getFieldsetId() == 0) {
                if ($all || $field->getType() != 'hidden') {
                    if ($field->getRequired()) $required_fields[] = 'field_' . $field->getId();
                    if ($all || $field->getIsActive())
                        $fields_to_fieldsets[0]['fields'][] = $field;
                } elseif ($field->getType() == 'hidden') {
                    $hidden[] = $field;
                }
            }
        }


        foreach ($fieldsets as $fieldset) {
            foreach ($fields as $field) {
                if ($field->getFieldsetId() == $fieldset->getId()) {
                    if ($all || $field->getType() != 'hidden') {
                        if ($all || $field->getIsActive())
                            $fields_to_fieldsets[$fieldset->getId()]['fields'][] = $field;
                    } elseif ($field->getType() == 'hidden') {
                        if ($all || $field->getIsActive())
                            $hidden[] = $field;
                    }
                }
            }
            if (!empty($fields_to_fieldsets[$fieldset->getId()]['fields'])) {
                $fields_to_fieldsets[$fieldset->getId()]['name'] = $fieldset->getName();
                $fields_to_fieldsets[$fieldset->getId()]['result_display'] = $fieldset->getResultDisplay();
            }
        }

        // set logic attributes
        $logic_target = array();
        $hidden_targets = array();
        $logicModel = Mage::getModel('webforms/logic');
        $target = array();
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $fields_to_fieldsets[$fieldset_id]['logic_visibility'] = VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE;
            if (count($logic_rules))
                foreach ($logic_rules as $logic) {
                    if ($logic->getAction() == VladimirPopov_WebForms_Model_Logic_Action::ACTION_SHOW && $logic->getIsActive()) {

                        // check fieldset visibility
                        if (in_array('fieldset_' . $fieldset_id, $logic->getTarget())) {
                            $fields_to_fieldsets[$fieldset_id]['logic_visibility'] = VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN;
                            $hidden_targets[] = "fieldset_" . $fieldset_id;
                        }

                        // check fields visibility
                        foreach ($fieldset['fields'] as $field) {
                            if (in_array('field_' . $field->getId(), $logic->getTarget())) {
                                $field->setData('logic_visibility', VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN);
                                $hidden_targets[] = "field_" . $field->getId();
                            }
                        }
                    }
                }
        }

        // check default values and assign visibility
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            $target['id'] = 'fieldset_' . $fieldset_id;
            $target['logic_visibility'] = $fieldset['logic_visibility'];
            $visibility = $logicModel->getTargetVisibility($target, $logic_rules, $default_data);
            $fields_to_fieldsets[$fieldset_id]['logic_visibility'] = $visibility ?
                VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE :
                VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN;

            // check fields visibility
            foreach ($fieldset['fields'] as $field) {
                $target['id'] = 'field_' . $field->getId();
                $target['logic_visibility'] = $field->getData('logic_visibility');
                $visibility = $logicModel->getTargetVisibility($target, $logic_rules, $default_data);
                $field->setData('logic_visibility', $visibility ?
                    VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE :
                    VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN);
            }

        }
        // set logic target
        foreach ($logic_rules as $logic)
            if ($logic->getIsActive())
                foreach ($logic->getTarget() as $target) {
                    $required = false;
                    if (in_array($target, $required_fields)) $required = true;
                    if (!in_array($target, $logic_target))
                        $logic_target[] = array(
                            "id" => $target,
                            "logic_visibility" =>
                            in_array($target, $hidden_targets) ?
                                VladimirPopov_WebForms_Model_Logic::VISIBILITY_HIDDEN :
                                VladimirPopov_WebForms_Model_Logic::VISIBILITY_VISIBLE,
                            "required" => $required
                        );
                }

        $this->_setLogicTarget($logic_target);
        $this->_setFieldsToFieldsets($fields_to_fieldsets);
        $this->_setHidden($hidden);

        return $fields_to_fieldsets;

    }

    public function useCaptcha()
    {
        $useCaptcha = true;
        if ($this->getCaptchaMode() != 'default') {
            $captcha_mode = $this->getCaptchaMode();
        } else {
            $captcha_mode = Mage::getStoreConfig('webforms/captcha/mode');
        }
        if ($captcha_mode == "off" || !Mage::helper('webforms')->captchaAvailable())
            $useCaptcha = false;
        if (Mage::getSingleton('customer/session')->getCustomerId() && $captcha_mode == "auto")
            $useCaptcha = false;
        if ($this->getData('disable_captcha'))
            $useCaptcha = false;

        return $useCaptcha;
    }

    public function duplicate()
    {
        // duplicate form
        $form = Mage::getModel('webforms/webforms')
            ->setData($this->getData())
            ->setId(null)
            ->setName($this->getName() . ' ' . Mage::helper('webforms')->__('(new copy)'))
            ->setIsActive(false)
            ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
            ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
            ->save();

        // duplicate store data
        $stores = Mage::getModel('webforms/store')
            ->getCollection()
            ->addFilter('entity_id', $this->getId())
            ->addFilter('entity_type', $this->getEntityType());

        foreach ($stores as $store) {
            Mage::getModel('webforms/store')
                ->setData($store->getData())
                ->setId(null)
                ->setEntityId($form->getId())
                ->save();
        }

        $fieldset_update = array();
        $field_update = array();

        // duplicate fieldsets and fields
        $fields_to_fieldsets = $this->getFieldsToFieldsets(true);
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {
            if ($fieldset_id) {
                $fs = Mage::getModel('webforms/fieldsets')->load($fieldset_id);
                $new_fieldset = Mage::getModel('webforms/fieldsets')
                    ->setData($fs->getData())
                    ->setId(null)
                    ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setWebformId($form->getId())
                    ->save();
                $new_fieldset_id = $new_fieldset->getId();

                $fieldset_update[$fieldset_id] = $new_fieldset_id;

                // duplicate store data
                $stores = Mage::getModel('webforms/store')
                    ->getCollection()
                    ->addFilter('entity_id', $fs->getId())
                    ->addFilter('entity_type', $fs->getEntityType());

                foreach ($stores as $store) {
                    Mage::getModel('webforms/store')
                        ->setData($store->getData())
                        ->setId(null)
                        ->setEntityId($new_fieldset_id)
                        ->save();
                }
            } else {
                $new_fieldset_id = 0;
            }
            foreach ($fieldset['fields'] as $field) {
                $new_field = Mage::getModel('webforms/fields')
                    ->setData($field->getData())
                    ->setId(null)
                    ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setWebformId($form->getId())
                    ->setFieldsetId($new_fieldset_id)
                    ->save();

                $field_update[$field->getId()] = $new_field->getId();

                // duplicate store data
                $stores = Mage::getModel('webforms/store')
                    ->getCollection()
                    ->addFilter('entity_id', $field->getId())
                    ->addFilter('entity_type', $field->getEntityType());

                foreach ($stores as $store) {
                    Mage::getModel('webforms/store')
                        ->setData($store->getData())
                        ->setId(null)
                        ->setEntityId($new_field->getId())
                        ->save();
                }
            }
        }

        // duplicate logic
        $logic_rules = $this->getLogic();
        foreach ($logic_rules as $logic) {
            $new_field_id = $field_update[$logic->getFieldId()];
            $new_target = array();
            foreach ($logic->getTarget() as $target) {
                foreach ($fieldset_update as $old_id => $new_id) {
                    if ($target == 'fieldset_' . $old_id)
                        $new_target[] = 'fieldset_' . $new_id;
                }
                foreach ($field_update as $old_id => $new_id) {
                    if ($target == 'field_' . $old_id)
                        $new_target[] = 'field_' . $new_id;
                }
            }
            $new_logic = Mage::getModel('webforms/logic')
                ->setData($logic->getData())
                ->setId(null)
                ->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())
                ->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                ->setFieldId($new_field_id)
                ->setTarget($new_target)
                ->save();

            // duplicate store data
            $stores = Mage::getModel('webforms/store')
                ->getCollection()
                ->addFilter('entity_id', $logic->getId())
                ->addFilter('entity_type', $logic->getEntityType());

            foreach ($stores as $store) {
                $new_target = array();
                $store_data = $store->getStoreData();
                if (!empty($store_data['target']))
                    foreach ($store_data['target'] as $target) {
                        foreach ($fieldset_update as $old_id => $new_id) {
                            if ($target == 'fieldset_' . $old_id)
                                $new_target[] = 'fieldset_' . $new_id;
                        }
                        foreach ($field_update as $old_id => $new_id) {
                            if ($target == 'field_' . $old_id)
                                $new_target[] = 'field_' . $new_id;
                        }
                    }
                $store->setData('target', $new_target);
                Mage::getModel('webforms/store')
                    ->setData($store->getData())
                    ->setId(null)
                    ->setEntityId($new_logic->getId())
                    ->save();
            }
        }

        return $form;
    }

    protected function getAjaxFiles()
    {
        $files = array();

        $postData = Mage::app()->getRequest()->getPost();

        foreach ($postData as $field_name => $value) {
            if (strstr($field_name, 'file_')) {
                $name = explode('/', $value);
                if (!empty($name[0]) && !empty($name[1])) {
                    $tmp_name = Mage::getBaseDir('base') . DS . 'js' . DS . 'webforms' . DS . 'upload' . DS . 'files' . DS . $name[0] . DS . $name[1];
                    $size = filesize($tmp_name);
                    $files[$field_name] = array(
                        'name' => $name[1],
                        'tmp_name' => $tmp_name,
                        'size' => $size
                    );
                }
            }
        }

        return $files;
    }

    protected function getUploadedFiles()
    {
        return array_merge($_FILES, $this->getAjaxFiles());
    }

    public function validatePostResult()
    {
        $postData = $this->getPostData();

        if (Mage::registry('webforms_errors_flag_' . $this->getId())) return Mage::registry('webforms_errors_' . $this->getId());

        $errors = array();

        // check captcha
        if ($this->useCaptcha()) {
            if (Mage::app()->getRequest()->getPost('recaptcha_response_field')) {
                $verify = Mage::helper('webforms')->getCaptcha()->verify(Mage::app()->getRequest()->getPost('recaptcha_challenge_field'), Mage::app()->getRequest()->getPost('recaptcha_response_field'));
                if (!$verify->isValid()) {
                    $errors[] = Mage::helper('webforms')->__('Verification code was not correct. Please try again.');
                }
            } else {
                $errors[] = Mage::helper('webforms')->__('Verification code was not correct. Please try again.');
            }
        }

        // check honeypot captcha
        if (Mage::getStoreConfig('webforms/honeypot/enable')){
            if(Mage::app()->getRequest()->getPost('message')){
                $errors[] = Mage::helper('webforms')->__('Spam bot detected. Honeypot field should be empty.');
            }
        }

        // check custom validation
        $logic_rules = $this->getLogic();
        $fields_to_fieldsets = $this->getFieldsToFieldsets();
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset)
            foreach ($fieldset['fields'] as $field) {
                if ($field->getIsActive() && $field->getValidateRegex() && $field->getRequired()) {
                    $pattern = $field->getValidateRegex();
                    $status = @preg_match($pattern, "Test");
                    if (false === $status) {
                        $pattern = "/" . $pattern . "/";
                    }
                    $validate = new Zend_Validate_Regex($pattern);
                    foreach ($this->getPostData() as $key => $value) {
                        if ($key == $field->getId() && !$validate->isValid($value)) {
                            $errors[] = $field->getName() . ": " . $field->getValidateMessage();
                        }
                    }
                }

                $hint = htmlspecialchars(trim($field->getHint()));
                if ($field->getRequired() && is_array($this->getPostData())) {
                    foreach ($this->getPostData() as $key => $value) {
                        if (
                            $key == $field->getId()
                            &&
                            $field->getType() != 'select/checkbox'
                            &&
                            (trim($value) == $hint || trim($value) == '')
                        ) {
                            // check logic visibility
                            $target_field = array("id" => 'field_' . $field->getId(), 'logic_visibility' => $field->getData('logic_visibility'));
                            $target_fieldset = array("id" => 'fieldset_' . $fieldset_id, 'logic_visibility' => $fieldset['logic_visibility']);

                            if (
                                $this->getLogicTargetVisibility($target_field, $logic_rules, $this->getPostData()) &&
                                $this->getLogicTargetVisibility($target_fieldset, $logic_rules, $this->getPostData())
                            )
                                $errors[] = Mage::helper('webforms')->__('%s is required', $field->getName());
                        }
                    }
                }
                // check e-mail stoplist
                if ($field->getIsActive() && $field->getType() == 'email') {
                    if (!empty($postData[$field->getId()])) {
                        if (stristr(Mage::getStoreConfig('webforms/email/stoplist'), $postData[$field->getId()])) {
                            $errors[] = Mage::helper('webforms')->__('E-mail address is blocked: %s', $postData[$field->getId()]);
                        }
                    }
                }
            }

        // check files
        $files = $this->getUploadedFiles();
        foreach ($files as $field_name => $file) {
            if (isset($file['name']) && file_exists($file['tmp_name'])) {
                $field_id = str_replace('file_', '', $field_name);
                $postData['field'][$field_id] = Varien_File_Uploader::getCorrectFileName($file['name']);
                $field = Mage::getModel('webforms/fields')
                    ->setStoreId($this->getStoreId())
                    ->load($field_id);
                $filesize = round($file['size'] / 1024);
                $images_upload_limit = Mage::getStoreConfig('webforms/images/upload_limit');
                if ($this->getImagesUploadLimit() > 0) {
                    $images_upload_limit = $this->getImagesUploadLimit();
                }
                $files_upload_limit = Mage::getStoreConfig('webforms/files/upload_limit');
                if ($this->getFilesUploadLimit() > 0) {
                    $files_upload_limit = $this->getFilesUploadLimit();
                }
                if ($field->getType() == 'image') {
                    // check file size
                    if ($filesize > $images_upload_limit && $images_upload_limit > 0) {
                        $errors[] = Mage::helper('webforms')->__('Uploaded image %s (%s kB) exceeds allowed limit: %s kB', $file['name'], $filesize, $images_upload_limit);
                    }

                    // check that file is valid image
                    if (!@getimagesize($file['tmp_name'])) {
                        $errors[] = Mage::helper('webforms')->__('Unsupported image compression: %s', $file['name']);
                    }
                } else {
                    // check file size
                    if ($filesize > $files_upload_limit && $files_upload_limit > 0) {
                        $errors[] = Mage::helper('webforms')->__('Uploaded file %s (%s kB) exceeds allowed limit: %s kB', $file['name'], $filesize, $files_upload_limit);
                    }

                }
                $allowed_extensions = $field->getAllowedExtensions();
                // check for allowed extensions
                if (count($allowed_extensions)) {
                    preg_match("/\.([^\.]+)$/", $file['name'], $matches);
                    $file_ext = strtolower($matches[1]);
                    // check file extension
                    if (!in_array($file_ext, $allowed_extensions)) {
                        $errors[] = Mage::helper('webforms')->__('Uploaded file %s has none of allowed extensions: %s', $file['name'], implode(', ', $allowed_extensions));
                    }
                }
                // check for valid filename
                if (Mage::getStoreConfig('webforms/files/validate_filename') && !preg_match("/^[a-zA-Z0-9_\s-\.]+$/", $file['name'])) {
                    $errors[] = Mage::helper('webforms')->__('Uploaded file %s has non-latin characters in the name', $file['name']);
                }
            }
        }

        $validate = new Varien_Object(array('errors' => $errors));

        Mage::dispatchEvent('webforms_validate_post_result', array('webform' => $this, 'validate' => $validate));

        Mage::register('webforms_errors_flag_' . $this->getId(), true);
        Mage::register('webforms_errors_' . $this->getId(), $validate->getData('errors'));

        return $validate->getData('errors');
    }


    public function savePostResult($config = array())
    {
        try {
            $postData = Mage::app()->getRequest()->getPost();
            if (!empty($config['prefix'])) {
                $postData = Mage::app()->getRequest()->getPost($config['prefix']);
            }
            $result = Mage::getModel('webforms/results');
            $new_result = true;
            if (!empty($postData['result_id'])) {
                $new_result = false;
                $result->load($postData['result_id'])->addFieldArray();
            }

            $this->setData('post_data', $postData['field']);

            $errors = $this->validatePostResult();

            if (count($errors)) {
                foreach ($errors as $error) {
                    Mage::getSingleton('core/session')->addError($error);
                    Mage::getSingleton('core/session')->setData('webform_result_tmp_' . $this->getId(), $postData);
                }
                return false;
            }

            Mage::getSingleton('core/session')->setData('webform_result_tmp_' . $this->getId(), false);

            $iplong = ip2long(Mage::helper('webforms')->getRealIp());

            $files = $this->getUploadedFiles();
            foreach ($files as $field_name => $file) {
                $field_id = str_replace('file_', '', $field_name);
                if ($file['name']) {
                    $postData['field'][$field_id] = Varien_File_Uploader::getCorrectFileName($file['name']);
                }
                if (!empty($postData['delete_file_' . $field_id])) {
                    if ($result->getData('field_' . $field_id)) {
                        //delete the file
                        @unlink($result->getFileFullPath($field_id, $result->getData('field_' . $field_id)));
                    }
                    $postData['field'][$field_id] = '';
                }
            }

            $result->setData('field', $postData['field'])
                ->setWebformId($this->getId())
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                ->setCustomerIp($iplong)
                ->save();

            $fields = Mage::getModel('webforms/fields')
                ->setStoreId($this->getStoreId())
                ->getCollection()
                ->addFilter('webform_id', $this->getId());

            // upload files from $_FILE array
            foreach ($_FILES as $field_name => $file) {
                $field_id = str_replace('file_', '', $field_name);

                // check that field belongs to the form
                foreach ($fields as $field) {
                    if ($field_id == $field->getId()) {
                        if (isset($file['name']) && file_exists($file['tmp_name'])) {
                            try {
                                $uploader = new Varien_File_Uploader($field_name);
                                $uploader->setAllowRenameFiles(false);
                                $uploader->setFilesDispersion(false);
                                $path = $result->getFilePath($field_id);
                                $uploader->save($path, $file['name']);
                            } catch (Exception $e) {

                            }
                        }
                    }
                }
            }
            // upload Ajax files
            $ajax_files = $this->getAjaxFiles();
            foreach ($ajax_files as $field_name => $file) {
                $field_id = str_replace('file_', '', $field_name);
                if (isset($file['name']) && file_exists($file['tmp_name'])) {
                    $path = $result->getFilePath($field_id);
                    @mkdir($path, 0777, true);
                    rename($file['tmp_name'], $path . Varien_File_Uploader::getCorrectFileName($file['name']));
                }
            }

            Mage::dispatchEvent('webforms_result_submit', array('result' => $result, 'webform' => $this));

            // send e-mail

            if ($new_result) {

                $emailSettings = $this->getEmailSettings();

                $result = Mage::getModel('webforms/results')->load($result->getId());

                // send admin notification
                if ($emailSettings['email_enable']) {
                    $result->sendEmail();
                }

                // send customer notification
                if ($this->getDuplicateEmail()) {
                    $result->sendEmail('customer');
                }

                // email contact
                foreach ($fields as $field) {
                    foreach ($result->getData() as $key => $value) {
                        if ($key == 'field_' . $field->getId() && $value && $field->getType() == 'select/contact') {
                            $result->sendEmail('contact', $field->getContactArray($value));
                        }

                        if ($key == 'field_' . $field->getId() && $value && $field->getType() == 'subscribe') {
                            // subscribe to newsletter
                            $customer_email = $result->getCustomerEmail();
                            foreach ($customer_email as $email)
                                Mage::getModel('newsletter/subscriber')->subscribe($email);
                        }
                    }
                }

            }


            return $result->getId();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            return false;
        }
    }

    public function getLogic()
    {
        $collection = Mage::getModel('webforms/logic')->setStoreId($this->getStoreId())->getCollection()->addWebformFilter($this->getId());
        return $collection;
    }

    public function getLogicTargetVisibility($target, $logic_rules, $data)
    {
        $logic = Mage::getModel('webforms/logic');
        return $logic->getTargetVisibility($target, $logic_rules, $data);
    }

    public function getSubmitButtonText()
    {
        $submit_button_text = trim($this->getData('submit_button_text'));
        if (strlen($submit_button_text) == 0)
            $submit_button_text = 'Submit';
        return $submit_button_text;
    }
}