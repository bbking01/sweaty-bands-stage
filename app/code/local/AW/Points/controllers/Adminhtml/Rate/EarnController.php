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


class AW_Points_Adminhtml_Rate_EarnController extends Mage_Adminhtml_Controller_Action {

    protected function displayTitle() {
        if (!Mage::helper('points')->magentoLess14())
            $this->_title($this->__('Rewards'))->_title($this->__('Earn Rates'));
        return $this;
    }

    public function indexAction() {
        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_rate_earn'))
                ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('points/rate');

        if ($id)
            $model->load($id);
        Mage::register('points_rate_data', $model);

        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_rate_earn_edit'))
                ->renderLayout();
    }

    public function saveAction() {
        $request = $this->getRequest();
        try {
            $model = Mage::getModel('points/rate');
            if ($request->getParam('id'))
                $model->load($request->getParam('id'));
            $model
                    ->addData($request->getPost())
                    ->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('points')->__('Rate was successfully saved'));

            return $this->_redirect('*/*/');
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('points')->__('Cannot save rate. Check if the same rate for this website and customer group exists'));
            return $this->_redirect('*/*/edit');
        }
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('points/rate')->load($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rate was successfully deleted'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        return $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('promo/points/points_rates/earn_rate');
    }

}