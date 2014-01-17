<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Adminhtml_AffiliateController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAffiliate($idFieldName = 'id')
    {
        $affiliateId = (int)$this->getRequest()->getParam($idFieldName);
        $affiliate = Mage::getModel('awaffiliate/affiliate');

        if ($affiliateId) {
            $affiliate->load($affiliateId);
        }

        if ($data = $this->_getSession()->getAffiliateData()) {
            $affiliate->addData($data);
            $this->_getSession()->setAffiliateData(null);
        }

        Mage::register('current_affiliate', $affiliate);
        return $this;
    }

    protected function _initAction()
    {
        $this->_title($this->__("Magento Affiliate"))
            ->_title($this->__("Manage Affiliates"));
        $this->loadLayout()
            ->_setActiveMenu('awaffiliate');
        return $this;
    }

    protected function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    protected function newAction()
    {
        $this->_initAffiliate();
        $this->_initAction();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_title($this->__("New Affiliate"));
        $this->renderLayout();
    }

    protected function editAction()
    {
        $this->_initAffiliate();
        if (!Mage::registry('current_affiliate')->hasId()) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Affilate with this ID does not exist'));
            return $this->getRequest()->getServer('HTTP_REFERER') ? $this->_redirectReferer() : $this->_redirect('*/*');
        }
        $this->_initAction();
        $_affiliate = Mage::registry('current_affiliate');
        $this->_title($_affiliate->getFirstname() . ' ' . $_affiliate->getLastname());
        $this->renderLayout();
    }

    protected function deleteAction()
    {
        $affiliateId = (int)$this->getRequest()->getParam('id');
        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        if ($affiliate->getData()) {
            $affiliate->delete();
            $this->_getSession()->addSuccess($this->__("Affiliate has been successfully deleted."));
        } else {
            $this->_getSession()->addError($this->__("Can't load affiliate by given ID."));

        }
        $this->_redirect('*/*');
    }

    public function massDeleteAction()
    {
        $customerIds = $this->getRequest()->getParam('customer_ids');
        if (!is_array($customerIds)) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                $_doneCnt = 0;
                foreach ($customerIds as $customerId) {
                    $affiliate = Mage::getModel('awaffiliate/affiliate')->loadByCustomerId($customerId);
                    if ($affiliate->getId()) {
                        $affiliate->delete();
                        ++$_doneCnt;
                    }
                }
                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', $_doneCnt));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }

    public function massStatusAction()
    {
        $customerIds = $this->getRequest()->getParam('customer_ids');
        $status = $this->getRequest()->getParam('status');
        if (Mage::getModel('awaffiliate/source_affiliate_status')->getOptionLabel($status) === false) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__('Wrong status given'));
        } else {
            if (!is_array($customerIds)) {
                $this->_getSession()->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
            } else {
                try {
                    $_doneCnt = 0;
                    foreach ($customerIds as $customerId) {
                        $affiliate = Mage::getModel('awaffiliate/affiliate')->loadByCustomerId($customerId);
                        if ($affiliate->getId()) {
                            $affiliate->setData('status', $status);
                            $affiliate->save();
                            ++$_doneCnt;
                        }
                    }
                    $this->_getSession()->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d affiliate(s) were successfully updated', $_doneCnt)
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*');
    }

    protected function saveAction()
    {
        $formData = $this->getRequest()->getParams();
        if ($formData) {
            $redirectBack = $this->getRequest()->getParam('back', false);
            $this->_initAffiliate('affiliate_id');

            /* @var $campaign AW_Affiliate_Model_Affiliate */
            $affiliate = Mage::registry('current_affiliate');

            //pre save validate
            $_helper = Mage::helper('awaffiliate');
            if (!$_helper->affiliateEditPreSaveValidateData($formData)) {
                $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Invalid data for save'));
                $this->_getSession()->setAffiliateData($formData);
                $this->_redirect('*/*/edit', array('id' => $affiliate->getId(), '_current' => true));
                return;
            }
            if ($affiliate->isObjectNew()) {
                $affiliate->setCustomerId($formData['customer_id']);
            }
            $affiliate->setStatus($formData['status']);
            try {
                $affiliate->save();

                $this->_getSession()->addSuccess(Mage::helper('awaffiliate')->__('The affiliate has been saved.'));
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $affiliate->getId(), '_current' => true));
                    return;
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('awaffiliate')->__('An error occurred while saving the affiliate.'));
                $this->_getSession()->setAffiliateData($affiliate->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id' => $affiliate->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/adminhtml_affiliate'));
    }

    protected function customerViewAction()
    {
        $affiliateId = (int)$this->getRequest()->getParam('id');
        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        if ($affiliate->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($affiliate->getCustomerId());
            if ($customer->getId()) {
                Mage::helper('awaffiliate')->setBackUrl($this->getUrl('*/*/index'));
                return $this->_redirect(
                    'adminhtml/customer/edit',
                    array(
                        'id' => $customer->getId(),
                        AW_Affiliate_Helper_Data::USE_AW_BACKURL_FLAG => 1
                    )
                );
            }
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer has not been found'));
            return $this->_redirectReferer(Mage::getUrl('*/*/index'));
        }
        $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Affiliate has not been found'));
        return $this->_redirectReferer(Mage::getUrl('*/*/index'));
    }

    public function customerGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_customer_grid')->toHtml());
    }

    public function withdrawalsGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_withdrawals_grid')->toHtml());
    }

    public function profitsGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('awaffiliate/adminhtml_affiliate_edit_tab_balance_profits_grid')->toHtml());
    }

    public function jsonCustomerInfoAction()
    {
        $response = new Varien_Object();
        $id = $this->getRequest()->getParam('id');
        if (intval($id) > 0) {
            $customer = Mage::getModel('customer/customer')
                ->load($id);
            $group = Mage::getModel('customer/group')
                ->load($customer->getGroupId());
            $response->setId($id);
            $response->addData($customer->getData());
            $response->addData(array('group_code' => $group->getCode()));
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(Mage::helper('awaffiliate')->__('Unable to get the customer ID.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function jsonWithdrawalRequestSaveAction()
    {
        $response = new Varien_Object();
        $response->setError(0);
        $id = intval($this->getRequest()->getParam('id'));

        if ($id > 0) {
            $withdrawalRequest = Mage::getModel('awaffiliate/withdrawal_request');
            $withdrawalRequest->load($id);
            if (is_null($withdrawalRequest->getId())) {
                $response->setError(1);
                $response->setMessage(Mage::helper('awaffiliate')->__('Withdrawal request does not found'));
            }
        } else {
            $response->setError(1);
            $response->setMessage(Mage::helper('awaffiliate')->__('Unable to get the withdrawal request ID.'));
        }

        $__statusKey = $this->getRequest()->getParam('status');
        $__statusSource = Mage::getModel('awaffiliate/source_withdrawal_status')->toShortOptionArray();
        if (!array_key_exists($__statusKey, $__statusSource)) {
            $response->setError(1);
            $response->setMessage(Mage::helper('awaffiliate')->__('Incorrect status.'));
        }

        if ($response->getError() === 0) {
            $withdrawalRequest->addData($this->getRequest()->getPost());
            try {
                $withdrawalRequest->save();
                $response->setMessage(Mage::helper('awaffiliate')->__('Withdrawal request updated'));
                $affiliateId = $this->getRequest()->getParam('affiliate_id');
                if ($affiliateId > 0) {
                    $balance = $this->getBalance($affiliateId);
                    $response->setData('current', $balance['current_balance']);
                    $response->setData('withdrawn', $balance['total_withdrawn']);
                    $response->setData('total', $balance['total_affiliated']);
                }
            } catch (Exception $e) {
                $response->setError(1);
                $response->setMessage($e->getMessage());
            }
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function jsonProfitAddAction()
    {
        $response = new Varien_Object();
        $response->setError(0);
        $affiliateId = $this->getRequest()->getParam('affiliate_id');
        if (!(intval($affiliateId) > 0) && ($affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId))) {
            $response->setError(1);
            $response->setMessage(Mage::helper('awaffiliate')->__('Unable to get the affiliate ID.'));
        }
        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        Mage::register('current_affiliate', $affiliate);
        $__campaignId = intval($this->getRequest()->getParam('campaign_id'));
        $__campaignSource = Mage::getModel('awaffiliate/source_campaign')->toShortOptionArray();
        if (!array_key_exists($__campaignId, $__campaignSource)) {
            $response->setError(1);
            $response->setMessage(Mage::helper('awaffiliate')->__('Incorrect campaign.'));
        }

        $__amount = intval($this->getRequest()->getParam('amount'));
        if (!intval($__amount) > 0) {
            $response->setError(1);
            $response->setMessage(Mage::helper('awaffiliate')->__('Incorrect amount'));
        }

        if ($response->getError() === 0) {
            $profit = Mage::getModel('awaffiliate/transaction_profit');
            $profit->setData($this->getRequest()->getPost());
            $profit->setData('affiliate_id', $affiliateId);
            $profit->setData('type', AW_Affiliate_Model_Source_Transaction_Profit_Type::ADMIN);
            $profit->setData('rate', 1);
            $profit->setData('currency_code', Mage::helper('awaffiliate')->getDefaultCurrencyCode());
            $profit->setData('created_at', Mage::getModel('core/date')->gmtDate());
            try {
                $profit->save();
                $response->setMessage(Mage::helper('awaffiliate')->__('Profit has been added'));
                $balance = $this->getBalance($affiliateId);
                $response->setData('current', $balance['current_balance']);
                $response->setData('withdrawn', $balance['total_withdrawn']);
                $response->setData('total', $balance['total_affiliated']);
            } catch (Exception $e) {
                $response->setError(1);
                $response->setMessage($e->getMessage());
            }
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('awaffiliate')->isMageVersionGreathOrEqual('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }

    protected function _isAllowed()
    {
        $acl = 'awaffiliate/affiliates';
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

    protected function getBalance($affiliateId)
    {

        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        $requestWithdrawal = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
        $requestWithdrawal->addAffiliateFilter($affiliate->getId());
        $value = $affiliate->getCurrentBalance();
        foreach ($requestWithdrawal as $item) {
            if ($item['status'] == AW_Affiliate_Model_Source_Withdrawal_Status::PENDING) {
                $value =floatval($value- $item->getAmount());
            }
        }
        $balance = array('current_balance' => Mage::helper('core')->currency($value),
            'total_affiliated'=>Mage::helper('core')->currency($affiliate->getTotalAffiliated()),
            'total_withdrawn' => Mage::helper('core')->currency($affiliate->getTotalWithdrawn()));
        return $balance;
    }
}
