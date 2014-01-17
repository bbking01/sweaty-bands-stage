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


require_once 'AbstractController.php';

class AW_Affiliate_Adminhtml_WithdrawalController extends AW_Affiliate_Adminhtml_AbstractController
{
    public function indexAction()
    {
        $this->_initAction()
            ->_setTitle('Magento Affiliate')
            ->_setTitle('Withdrawal');
        $this->renderLayout();
    }

    public function viewaffiliateAction()
    {
        $affiliateId = $this->getRequest()->getParam('id');
        if ($affiliateId) {
            Mage::helper('awaffiliate')->setBackUrl($this->getUrl('*/*/index'));
            return $this->_redirect(
                '*/adminhtml_affiliate/edit',
                array(
                    'id' => $affiliateId,
                    AW_Affiliate_Helper_Data::USE_AW_BACKURL_FLAG => 1
                )
            );
        }
        return $this->_redirectReferer();
    }

    /**
     * Render grid AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
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
            }
            catch (Exception $e) {
                $response->setError(1);
                $response->setMessage($e->getMessage());
            }
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function massstatusAction()
    {
        $withdrawalIds = $this->getRequest()->getParam('withdrawals');
        if ($withdrawalIds) {
            $collection = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
            $collection->addFieldToFilter('id', array('in' => $withdrawalIds));
            $status = $this->getRequest()->getParam('status');
            if (Mage::getModel('awaffiliate/source_withdrawal_status')->getOptionLabel($status)) {
                foreach ($collection as $withdrawal) {
                    $withdrawal->setData('status', $status);
                    $withdrawal->save();
                }
                if ($collection->getSize() == 1) {
                    $this->_getSession()->addSuccess($this->__('Withdrawal has been updated'));
                } else {
                    $this->_getSession()->addSuccess($this->__('%s withdrawals has been updated', $collection->getSize()));
                }
            } else {
                $this->_getSession()->addError($this->__('Wrong withdrawal status given'));
            }
        } else {
            $this->_getSession()->addError($this->__('There is no withdrawals has been selected'));
        }
        return $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        $acl = 'awaffiliate/withdrawal';
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
