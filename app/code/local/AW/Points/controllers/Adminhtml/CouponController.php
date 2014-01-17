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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Adminhtml_CouponController extends Mage_Adminhtml_Controller_Action {

    protected function displayTitle() {
        if (!Mage::helper('points')->magentoLess14())
            $this->_title($this->__('Rewards'))->_title($this->__('Coupons'));
        return $this;
    }

    public function indexAction() {

        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_coupon'))
                ->renderLayout();
    }

    public function transactionsAction() {
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('points/adminhtml_coupon_edit_tab_transactions')->toHtml()
        );
    }

    public function newAction() {

        $this->_forward('edit');
    }

    public function editAction() {
        $model = Mage::getModel('points/coupon');
        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }
        Mage::register('points_coupon_data', $model);

        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_coupon_edit'))
                ->_addLeft($this->getLayout()->createBlock('points/adminhtml_coupon_edit_tabs'))
                ->renderLayout();
    }

    public function saveAction() {

        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                /*  check unique coupon_code  */
                $testCoupon = Mage::getModel('points/coupon')->loadByCouponCode($data['coupon_code']);

                if ($testCoupon->getId() && ($testCoupon->getId() !== $data['coupon_id'])) {
                    unset($testCoupon);
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Coupon with this code is already exists'));
                    $this->_redirect('*/*/edit', array('id' => $data['coupon_id']));
                    return $this;
                }
                unset($testCoupon);

                $coupon = Mage::getModel('points/coupon');

                /* dates from locale to db format */
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                if (isset($data['from_date']) && $data['from_date'] instanceof Zend_Date) {
                    $data['from_date'] = $data['from_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
                }
                if (isset($data['to_date']) && $data['to_date'] instanceof Zend_Date) {
                    $data['to_date'] = $data['to_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
                }

                if (!empty($data['from_date']) && !empty($data['to_date'])) {
                    $from_date = new Zend_Date($data['from_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);
                    $to_date = new Zend_Date($data['to_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);

                    if ($from_date->compare($to_date) === 1) {
                        throw new Exception($this->__("'To Date' must be equal or more than 'From Date'"));
                    }
                }

                foreach ($data as $key => $value) {
                    $coupon->setData($key, $data[$key]);
                }


                $coupon->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setCouponData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCouponData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('coupon_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('points/coupon')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('points')->__('Coupon was successfully deleted'));
                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('points')->__('Unable to find a coupon to delete'));
        return $this->_redirect('*/*/');
    }

    public function massDeleteAction() {

        try {
            $couponsToDelete = $this->getRequest()->getParam('coupon_ids');
            foreach ($couponsToDelete as $couponToDelete) {
                Mage::getModel('points/coupon')->load($couponToDelete)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('points')->__('Total of %d record(s) were successfully removed', count($couponsToDelete))
            );
        } catch (Exception $exc) {
            Mage::getSingleton('adminhtml/session')->addError($exc->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('promo/points/coupons');
    }

}