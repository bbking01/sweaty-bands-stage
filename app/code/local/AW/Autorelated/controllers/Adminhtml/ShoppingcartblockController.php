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
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


require_once 'AbstractblockController.php';

class AW_Autorelated_Adminhtml_ShoppingcartblockController extends AW_Autorelated_Adminhtml_AbstractblockController
{
    const BLOCK_REGISTRY_KEY = 'aw_arp2_scb';

    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('catalog/awautorelated');
    }

    protected function newAction()
    {
        return $this->_redirect('*/*/edit');
    }

    protected function editAction()
    {
        /** @var $helperForms AW_Autorelated_Helper_Forms */
        $helperForms = Mage::helper('awautorelated/forms');
        $this->_initAction();
        $blockId = $this->getRequest()->getParam('id');
        if (!($block = $helperForms->getFormData($blockId))) {
            $block = Mage::getModel('awautorelated/blocks')->load($blockId);
            if (!$block->getId() && $blockId) {
                $this->_getSession()->addError($this->__("Couldn't load block by given ID"));
                return $this->_redirect('*/adminhtml_blocksgrid/list');
            }
        }
        Mage::register(self::BLOCK_REGISTRY_KEY, $block);
        $this->_setTitle($this->getRequest()->getParam('id') ? 'Edit Shopping Cart Block' : 'Add Shopping Cart Block');
        $this->_addContent($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit'))
            ->_addLeft($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit_tabs'));
        $this->renderLayout();
    }

    protected function saveAction()
    {
        /** @var $helperForms AW_Autorelated_Helper_Forms */
        $helperForms = Mage::helper('awautorelated/forms');
        $request = $this->getRequest();

        $data = array();
        if ($request->getParam('name')) {
            $data['name'] = $request->getParam('name');
        } else {
            $this->_getSession()->addError($this->__("Name couldn't be empty"));
        }
        $data['status'] = $request->getParam('status');
        $data['store'] = $request->getParam('store');
        $data['customer_groups'] = $request->getParam('customer_groups');
        if (is_array($data['customer_groups']) && in_array(Mage_Customer_Model_Group::CUST_GROUP_ALL, $data['customer_groups'])) {
            $data['customer_groups'] = array(Mage_Customer_Model_Group::CUST_GROUP_ALL);
        }
        $data['priority'] = $request->getParam('priority');

        $data['date_from'] = $request->getParam('date_from') ? $request->getParam('date_from') : '';
        $data['date_to'] = $request->getParam('date_to') ? $request->getParam('date_to') : '';
        $data = $this->_filterDates($data, array('date_from', 'date_to'));
        $data['position'] = $request->getParam('position');
        $data['related_products'] = $request->getParam('related_products');
        $conditions = $request->getParam('rule');
        if (is_array($conditions)) {
            if (isset($conditions['viewed'])) {
                $viewedConditions = $conditions['viewed'];
                $viewedConditions = Mage::helper('awautorelated')->updateChild(
                    $viewedConditions,
                    'salesrule/rule_condition_combine',
                    'awautorelated/salesrule_rule_condition_combine'
                );
                $conditions['viewed'] = $viewedConditions;
                unset($viewedConditions);
            }
            if (isset($conditions['related'])) {
                $relatedConditions = $conditions['related'];
                $relatedConditions = Mage::helper('awautorelated')->updateChild(
                    $relatedConditions,
                    'catalogrule/rule_condition_combine',
                    'awautorelated/catalogrule_rule_condition_combine'
                );
                $conditions['related'] = $relatedConditions;
                unset($relatedConditions);
            }
            $conditions = Mage::helper('awautorelated')->convertFlatToRecursive($conditions, array('viewed', 'related'));

            if (isset($conditions['viewed']) && isset($conditions['viewed']['viewed_conditions'])) {
                $data['currently_viewed']['conditions'] = $conditions['viewed']['viewed_conditions'];
            } else {
                $data['currently_viewed']['conditions'] = array();
            }
            if (isset($conditions['related']) && isset($conditions['related']['related_conditions'])) {
                $data['related_products']['conditions'] = $conditions['related']['related_conditions'];
            } else {
                $data['related_products']['conditions'] = array();
            }
        }
        $data['type'] = AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK;
        if (isset($data['related_products'])
            && isset($data['related_products']['options'])
            && is_array($data['related_products']['options'])
        ) {
            $_options = array();
            foreach ($data['related_products']['options'] as $_option) {
                if (!isset($_option['delete']) || !$_option['delete']) {
                    unset($_option['delete']);
                    $_options[] = $_option;
                }
            }
            $data['related_products']['options'] = $_options;
            unset($_options);
        }
        $model = Mage::getModel('awautorelated/blocks')->load($request->getParam('id'));
        $model->setData($data);
        $id = ($request->getParam('saveasnew')) ? null : $request->getParam('id');
        if ($id && !$request->getParam('saveasnew')) {
            $model->setId($id);
        }
        if ($this->_hasErrors()) {
            $helperForms->setFormData($model->humanizeData());
            return $this->_redirect('*/*/edit', array('id' => $id));
        } else {
            $model->save();
            $helperForms->unsetFormData($model->getId());
            $this->_getSession()->addSuccess($this->__('Block has been succesfully saved'));
            if ($request->getParam('continue') || $request->getParam('saveasnew')) {
                return $this->_redirect('*/*/edit', array('id' => $model->getId(),
                    'continue_tab' => $request->getParam('continue_tab')));
            } else {
                return $this->_redirect('*/adminhtml_blocksgrid/list');
            }
        }
    }

    protected function indexAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function deleteAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/delete', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }
}
