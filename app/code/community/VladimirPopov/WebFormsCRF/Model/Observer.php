<?php
class VladimirPopov_WebFormsCRF_Model_Observer
{
    public function customerAfterSave($observer)
    {
        $customer = $observer->getCustomer();
        if (Mage::getSingleton('customer/session')->getData('webformscrf_result_id')) {
            if (!Mage::registry('webformscrf_customer_after_save')) {
                Mage::getModel('webforms/results')
                    ->load(Mage::getSingleton('customer/session')->getData('webformscrf_result_id'))
                    ->setCustomerId($customer->getId())
                    ->save();
                Mage::register('webformscrf_customer_after_save', true);
                Mage::getSingleton('customer/session')->setData('webformscrf_result_id', false);
            }
        } else {
            $webformId = Mage::app()->getRequest()->getPost('webform_id');

            if ($webformId && !Mage::registry('webformscrf_customer_after_save')) {
                $webform = Mage::getModel('webforms/webforms')->load($webformId);
                $webform->setData('disable_captcha', true);

                $resultId = $webform->savePostResult();
                if ($resultId) {
                    $result = Mage::getModel('webforms/results')->load($resultId);
                    $result->setCustomerId($customer->getId())->save();

                    Mage::dispatchEvent('webformscrf_customer_after_save', array('result' => $result, 'webform' => $webform, 'customer' => $customer));
                    Mage::register('webformscrf_customer_after_save', true);

                    // update customer group
                    $group_collection = Mage::getModel('customer/group')->getCollection()->addFilter('webform_id', $webform->getId())->load();
                    $group = $group_collection->getFirstItem();
                    if ($group->getCustomerGroupId()) {
                        $customer->setGroupId($group->getCustomerGroupId())->save();
                        if(Mage::helper('webformscrf')->customerActivationEnabled() && !Mage::app()->getRequest()->getPost('result_id')){
                            $customer->setCustomerActivated($group->getCrfActivationStatus())->save();
                        }
                    }
                }
            }
        }
    }

    public function adminhtmlCustomerAfterSave($observer)
    {
        $customer = $observer->getCustomer();

        // save account form
        $postData = Mage::app()->getRequest()->getPost('account');
        $webformId = $postData['webform_id'];
        if ($webformId) {
            $webform = Mage::getModel('webforms/webforms')->load($webformId);
            $webform->setData('disable_captcha', true);

            $resultId = $webform->savePostResult(
                array(
                    'prefix' => 'account'
                )
            );
            if ($resultId) {
                Mage::getModel('webforms/results')->load($resultId)
                    ->setCustomerId($customer->getId())
                    ->setStoreId($customer->getStoreId())
                    ->save();
            }
        }

        // save custom forms
        $collection = Mage::getModel('webforms/webforms')
            ->setStoreId($customer->getStoreId())
            ->getCollection()
            ->addFilter('crf_account', 1)
            ->addFilter('is_active', 1);

        foreach ($collection as $webform) {
            $postData = Mage::app()->getRequest()->getPost('crf_account_form_' . $webform->getId());
            $webform->setData('disable_captcha', true);

            if (!empty($postData['webform_id'])) {
                $resultId = $webform->savePostResult(
                    array(
                        'prefix' => 'crf_account_form_' . $webform->getId()
                    )
                );
                if ($resultId) {
                    Mage::getModel('webforms/results')->load($resultId)
                        ->setCustomerId($customer->getId())
                        ->setStoreId($customer->getStoreId())
                        ->save();
                }
            }
        }
    }

    public function formAfterSave($observer)
    {
        // avoid infinite loop
        if (Mage::registry('crf_form_after_save')) return false;
        Mage::register('crf_form_after_save', true);

        $webform = $observer->getWebform();

        if ($webform->getId())
            $webform->setData('crf_account_group_serialized', serialize($webform->getData('crf_account_group')))->save();

    }

    public function formAfterLoad($observer)
    {
        $webform = $observer->getWebform();
        $crf_account_group = $webform->getData('crf_account_group');
        if (empty($crf_account_group))
            $webform->setData('crf_account_group', unserialize($webform->getData('crf_account_group_serialized')));
    }

    public function customerAfterLoad($observer)
    {
        $customer = $observer->getCustomer();

        if (!Mage::registry('webformscrf_load_customer_' . $customer->getId())) {

            $result = $this->getCustomerResult($customer)->addFieldArray();

            if ($result->getId()) {
                $customer->setData('result_id', $result->getId());
                $customer->setData('webform_id', $result->getWebformId());

                $data = $result->getData('field');

                foreach ($data as $field_id => $value) {
                    $field = Mage::getModel('webforms/fields')->load($field_id);
                    switch ($field->getType()) {
                        case 'file':
                        case 'image':
                            $value = Varien_File_Uploader::getCorrectFileName($value);
                            $customer->setData('field_' . $field_id . '_url', $result->getDownloadLink($field_id, $value));
                            break;
                    }
                    $customer->setData('field_' . $field_id, $value);
                    if ($field->getCode()) {
                        $customer->setData($field->getCode(), $value);
                    }
                }
            }
        }
    }

    protected function getCustomerResult($customer = false, $webformId = false)
    {

        Mage::register('webformscrf_load_customer_' . $customer->getId(), 1);

        if (!$customer) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }

        if (!$webformId) {

            $webformId = Mage::getStoreConfig('webformscrf/registration/form', $customer->getStoreId());

            // get webform id by customer group
            $group = Mage::getModel('customer/group')->load($customer->getGroupId());
            if ($group->getWebformId()) {
                $webformId = $group->getWebformId();
            }
        }

        $collection = Mage::getModel('webforms/results')->getCollection()
            ->addFilter('webform_id', $webformId)
            ->addFilter('customer_id', $customer->getEntityId());

        $collection->getSelect()->order('created_time desc')->limit('1');
        $collection->load();

        Mage::unregister('webformscrf_load_customer_' . $customer->getId());

        return $collection->getFirstItem();

    }

    public function isDirectAvailable($observer)
    {

        $available = $observer->getData('available');
        $form_data = $observer->getData('form_data');

        if (Mage::getStoreConfig('webformscrf/registration/enable') && Mage::getStoreConfig('webformscrf/registration/form') == $form_data['webform_id']) {
            $available->setData('status', false);
            return false;
        }

        $webform = Mage::getModel('webforms/webforms')->load($form_data['webform_id']);
        if ($webform->getData('crf_account')) {
            $available->setData('status', false);
        }

        $groups = Mage::getModel('customer/group')->getCollection()->addFilter('webform_id',$form_data['webform_id'])->load();
        $group = $groups->getFirstItem();
        if($group->getId())
            $available->setData('status', false);

        return false;
    }

    public function groupSave($observer)
    {

        if (Mage::registry('webformscrf_group_save')) return false;
        Mage::register('webformscrf_group_save', true);

        $group = $observer->getObject();
        $group->setWebformId((int)Mage::app()->getRequest()->getParam('webform_id'));
        $group->setCrfActivationStatus((int)Mage::app()->getRequest()->getParam('crf_activation_status'));
        $group->save();
    }

    public function addAssets($observer)
    {
        $layout = $observer->getLayout();
        $update = $observer->getLayout()->getUpdate();

        if (in_array('cms_page', $update->getHandles())) {

            $pageId = Mage::app()->getRequest()
                ->getParam('page_id', Mage::app()->getRequest()->getParam('id', false));

            $page = Mage::getModel('cms/page')->load($pageId);

            if (stristr($page->getContent(), 'webformscrf/widget')) {
                Mage::helper('webforms')->addAssets($layout);
                $layout->getBlock('head')->addCss('webformscrf/form.css');
            }
        }

        // add frontend customer account navigation
        if (in_array('customer_account', $update->getHandles())) {

            $menu = $layout->getBlock('customer_account_navigation');

            $collection = Mage::getModel('webforms/webforms')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->getCollection()
                ->addFilter('crf_account', 1)
                ->addFilter('crf_account_frontend', 1)
                ->addFilter('is_active', 1);

            $collection->getSelect()->order('crf_account_position asc');

            if ($menu) {
                $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                foreach ($collection as $webform) {
                    $form = Mage::getModel('webforms/webforms')->load($webform->getId());
                    $groups = $form->getData('crf_account_group');

                    if (is_array($groups)) {
                        $group = Mage::getModel('customer/group')->load($groupId);
                        if (in_array($groupId, $groups) && $group->getData('webform_id') != $webform->getId()) {
                            $menu->addLink('webform_' . $webform->getId(), 'webformscrf/account/index/id/' . $webform->getId(), $webform->getName());
                        }
                    }
                }
            }

        }

        // add backend customer account navigation
        if (in_array('adminhtml_customer_edit', $update->getHandles())) {
            $menu = $layout->getBlock('customer_edit_tabs');

            $collection = Mage::getModel('webforms/webforms')
                ->setStoreId(Mage::registry('current_customer')->getStoreId())
                ->getCollection()
                ->addFilter('crf_account', 1)
                ->addFilter('is_active', 1);

            $collection->getSelect()->order('crf_account_position asc');

            if ($menu) {
                foreach ($collection as $webform) {
                    $groups = unserialize($webform->getData('crf_account_group_serialized'));
                    if (is_array($groups)) {
                        $group = Mage::getModel('customer/group')->load(Mage::registry('current_customer')->getGroupId());
                        if (in_array($group->getId(), $groups) && $group->getData('webform_id') != $webform->getId()) {
                            $tab = $layout->createBlock('webformscrf/adminhtml_tab', 'crf_tab_' . $webform->getId(), array('webform_id' => $webform->getId()));
                            $menu->addTab('crf_tab_' . $webform->getId(), array(
                                'label' => $webform->getName(),
                                'title' => $webform->getName(),
                                'content' => $tab->toHtml(),
                                'after' => 'account',
                            ));
                        }
                    }
                }
            }

        }

    }

    public function addSettings($observer)
    {
        $form = $observer->getForm();

        $fieldset = $form->addFieldset('webformscrf_setting', array('legend' => Mage::helper('webformscrf')->__('Customer Account')));

        $fieldset->addField('crf_account', 'select', array
        (
            'label' => Mage::helper('webformscrf')->__('Add form to customer account'),
            'title' => Mage::helper('webformscrf')->__('Add form to customer account'),
            'name' => 'crf_account',
            'required' => false,
            'note' => Mage::helper('webformscrf')->__('Store additional customer account information'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('crf_account_position', 'text', array
        (
            'label' => Mage::helper('webformscrf')->__('Position'),
            'title' => Mage::helper('webformscrf')->__('Position'),
            'name' => 'crf_account_position',
            'required' => false,
            'note' => Mage::helper('webformscrf')->__('Form position relative to other account web-forms'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('crf_account_frontend', 'select', array
        (
            'label' => Mage::helper('webformscrf')->__('Show in frontend'),
            'title' => Mage::helper('webformscrf')->__('Show in frontend account area'),
            'name' => 'crf_account_frontend',
            'required' => false,
            'note' => Mage::helper('webformscrf')->__('Show this form in frontend account area'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('crf_account_group', 'multiselect', array
        (
            'label' => Mage::helper('webformscrf')->__('Customer groups'),
            'title' => Mage::helper('webformscrf')->__('Customer groups'),
            'name' => 'crf_account_group',
            'required' => false,
            'note' => Mage::helper('webformscrf')->__('Only for selected customer groups'),
            'values' => $this->getGroupOptions(),
        ));
    }

    public function getGroupOptions()
    {
        $options = array();
        $collection = Mage::getModel('customer/group')->getCollection();

        foreach ($collection as $group) {
            if ($group->getCustomerGroupId())
                $options[] = array('value' => $group->getCustomerGroupId(), 'label' => $group->getCustomerGroupCode());
        }

        return $options;
    }
}
