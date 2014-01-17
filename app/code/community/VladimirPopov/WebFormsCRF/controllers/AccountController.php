<?php
class VladimirPopov_WebFormsCRF_AccountController
    extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $webform = Mage::getModel('webforms/webforms')->setStoreId(Mage::app()->getStore()->getId())->load($this->getRequest()->getParam('id'));

        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        if ($webform->getData('crf_account') && $webform->getData('crf_account_frontend') && in_array($groupId, $webform->getData('crf_account_group'))) {
            $this->getLayout()->getBlock('customer_account_navigation')->setActive('webformscrf/account/index/id/' . $webform->getId());
            $this->getLayout()->getBlock('webformscrf.account')->setData('webform_id', $webform->getId());
        }

        $this->renderLayout();
    }
}