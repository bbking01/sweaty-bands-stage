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
 * @package    AW_Checkoutpromo
 * @version    1.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Checkoutpromo_Adminhtml_CheckoutpromoController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('promo/checkoutpromo')
                ->_addBreadcrumb($this->__('Checkout Promo Rules'), $this->__('Checkout Promo Rules'));

        return $this;
    }

    protected function _isAllowed() {

        return Mage::getSingleton('admin/session')->isAllowed('promo/checkoutpromo');
    }

    public function indexAction() {
        $this->_initAction()
                ->_setTitle("Manage Rules")
                ->renderLayout();
    }

    public function editAction() {
        $session = Mage::getSingleton('adminhtml/session');
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('checkoutpromo/rule')->load($id);

        if ($model->getId() || $id == 0) {
            // set entered data if was error when we do save
            $data = $model->getData();
            $sessionData = $session->getCPData(true);
            if (is_array($sessionData))
                $data = array_merge($data, $sessionData);
            $session->getCPData(false);

            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

            Mage::register('checkoutpromo_rule', $model);

            $block = $this->getLayout()->createBlock('checkoutpromo/adminhtml_checkoutpromo_edit')
                    ->setData('action', $this->getUrl('*/*/save'));
            $title = ($model->getId()) ? "Edit Rule" : "New Rule";
            $this
                    ->_initAction()
                    ->_setTitle($title)
                    ->getLayout()->getBlock('head')
                    ->setCanLoadExtJs(true)
                    ->setCanLoadRulesJs(true);

            $this
                    ->_addContent($block)
                    ->_addLeft($this->getLayout()->createBlock('checkoutpromo/adminhtml_checkoutpromo_edit_tabs'))
                    ->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setCPData(false);

        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('checkoutpromo/rule');

            if ($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
                if ($id != $model->getId()) {
                    $session->addError($this->__('The page you are trying to save no longer exists'));
                    $session->setCPData($data);
                    $this->_redirect('*/*/edit', array('page_id' => $this->getRequest()->getParam('page_id')));
                    return;
                }
            }
            $conditions = isset($data['rule']['conditions']) ? $data['rule']['conditions'] : $data['rule'];
            unset($data['rule']);
            $model->loadPost(array('conditions' => $conditions));
            $model->setData(array_merge($data, $model->getData()));

            try {
                // check if date was entered correctly
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

                if ($date = $model->getFromDate()) {
                    $date = Mage::app()->getLocale()->date($date, $format, null, false);
                    $model->setFromDate($date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
                }
                else
                    $model->setFromDate(null);

                if ($date = $model->getToDate()) {
                    $date = Mage::app()->getLocale()->date($date, $format, null, false);
                    $model->setToDate($date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
                }
                else
                    $model->setToDate(null);

                $dateError = (!is_null($model->getFromDate())
                        && !is_null($model->getToDate())
                        && strtotime($model->getFromDate()) > strtotime($model->getToDate())
                        ) ? $this->__('Start date can\'t be greater than end date') : false;

                if (is_null($model->getFromDate()))
                    $model->setFromDate(new Zend_Db_Expr('null'));
                if (is_null($model->getToDate()))
                    $model->setToDate(new Zend_Db_Expr('null'));

                if ($dateError) {
                    if ($dateError)
                        $session->addError($dateError);
                    $session->setCPData($data);
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'tab' => 'schedule'));
                    return;
                }
                $model->setCustomerGroupIds(implode(',', $model->getCustomerGroupIds()));
                $model->setWebsiteIds(implode(',', $model->getWebsiteIds()));

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule was successfully saved'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id'))
            try {
                Mage::getModel('checkoutpromo/rule')
                        ->setId($id)
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ruleIds = (array)$this->getRequest()->getParam('rule_ids');
        try {
            foreach ($ruleIds as $ruleId) {
                $rule = Mage::getModel('checkoutpromo/rule')->load($ruleId);
                $rule->delete();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($ruleIds))
            );
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $ruleIds = (array)$this->getRequest()->getParam('rule_ids');
        $status  = (int)$this->getRequest()->getParam('status');

        try {
            foreach ($ruleIds as $ruleId) {
                $rule = Mage::getModel('checkoutpromo/rule')->load($ruleId);
                $rule->setIsActive($status);
                $rule->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($ruleIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating the rule(s) status.'));
        }
        $this->_redirect('*/*/index');
    }

    public function newConditionHtmlAction() {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('checkoutpromo/rule'))
                ->setPrefix('conditions');

        if (!empty($typeArr[1]))
            $model->setAttribute($typeArr[1]);

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        }
        else
            $html = '';

        $this->getResponse()->setBody($html);
    }

    protected function _setTitle($action) {
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Promotions'))->_title($this->__('Checkout Promo'))->_title($this->__($action));
        }
        return $this;
    }

}