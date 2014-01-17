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


class AW_Points_Adminhtml_RuleController extends Mage_Adminhtml_Controller_Action {

    protected function displayTitle() {
        if (!Mage::helper('points')->magentoLess14())
            $this->_title($this->__('Rewards'))->_title($this->__('Rules'));
        return $this;
    }

    public function indexAction() {
        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_rule'))
                ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('points/rule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('points')->__('This rule no longer exists'));
                return $this->_redirect('*/*');
            }
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        Mage::register('points_rule_data', $model);

        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo');

        $block = $this->getLayout()->createBlock('points/adminhtml_rule_edit')
                ->setData('action', $this->getUrl('*/points_rule/save'));

        $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true)
                ->setCanLoadRulesJs(true);

        $this
                ->_addContent($block)
                ->_addLeft($this->getLayout()->createBlock('points/adminhtml_rule_edit_tabs'))
                ->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
                if (!Mage::helper('points')->magentoLess14()) {
                    //_filterDates convert dates in array from localized to internal format only for magento > 1.4
                    $data = $this->_filterDates($data, array('from_date', 'to_date'));
                    if (isset($data['from_date']) && $data['from_date'] instanceof Zend_Date) {
                        $data['from_date'] = $data['from_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
                    }
                    if (isset($data['to_date']) && $data['to_date'] instanceof Zend_Date) {
                        $data['to_date'] = $data['to_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
                    }
                }


                if (!empty($data['from_date']) && !empty($data['to_date'])) {
                    $from_date = new Zend_Date($data['from_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);
                    $to_date = new Zend_Date($data['to_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);

                    if ($from_date->compare($to_date) === 1) {
                        throw new Exception($this->__("'To Date' must be equal or more than 'From Date'"));
                    }
                }

                $model = Mage::getModel('points/rule');
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);

                if ($this->getRequest()->getParam('_save_as_flag'))
                    $model->setId(null);

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('points')->__('Rule was successfully saved'));
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id' => $model->getId(),
                        '_current' => true
                    ));
                    return;
                }
                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function relatedGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('points/adminhtml_rule_edit_tab_related')->toHtml());
    }

    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('points/rule')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalogrule')->__('Rule was successfully deleted'));
                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalogrule')->__('Unable to find a page to delete'));
        return $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        try {
            $rulesToDelete = $this->getRequest()->getParam('rule');
            foreach ($rulesToDelete as $ruleToDelete) {
                Mage::getModel('points/rule')->load($ruleToDelete)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Successfully deleted'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancednewsletter')->__('Unable to find item to save'));
        }
        $this->_redirect('*/*/index');
    }

    public function massActivateAction() {
        try {
            $rulesToDelete = $this->getRequest()->getParam('rule');
            foreach ($rulesToDelete as $ruleToDelete) {
                Mage::getModel('points/rule')->load($ruleToDelete)->setData('is_active', 1)->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Successfully deleted'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancednewsletter')->__('Unable to find item to save'));
        }
        $this->_redirect('*/*/index');
    }

    public function massDeactivateAction() {
        try {
            $rulesToDelete = $this->getRequest()->getParam('rule');
            foreach ($rulesToDelete as $ruleToDelete) {
                Mage::getModel('points/rule')->load($ruleToDelete)->setData('is_active', 0)->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Successfully deleted'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancednewsletter')->__('Unable to find item to save'));
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('promo/points/reward_rules');
    }

}
