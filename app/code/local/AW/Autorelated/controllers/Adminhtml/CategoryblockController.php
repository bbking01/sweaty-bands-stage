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

class AW_Autorelated_Adminhtml_CategoryblockController extends AW_Autorelated_Adminhtml_AbstractblockController
{
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
        $this->_initAction();
        if (!$this->getRequest()->getParam('fswe') || !Mage::helper('awautorelated/forms')->getFormData($this->getRequest()->getParam('id'))) {
            $_formData = Mage::getModel('awautorelated/blocks')->load($this->getRequest()->getParam('id'));
            if ($_formData->getData()) {
                Mage::helper('awautorelated/forms')->setFormData($_formData);
            }
            if (!$_formData->getData() && $this->getRequest()->getParam('id')) {
                $this->_getSession()->addError($this->__("Couldn't load block by given ID"));
                return $this->_redirect('*/*/list');
            }
            Mage::register('categoryblock_data', $_formData);
        }
        $this->_addContent($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_category_edit'))
            ->_addLeft($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs'));
        $this->_setTitle($this->getRequest()->getParam('id') ? 'Edit Category Block' : 'Add Category Block');
        $this->renderLayout();
    }

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs_currentlyviewed_categoriesgrid')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array();
            $data['name'] = $request->getParam('name');
            if (!$data['name']) {
                $this->_getSession()->addError($this->__("Name couldn't be empty"));
            }
            $data['status'] = $request->getParam('status');
            $data['customer_groups'] = $request->getParam('customer_groups');
            if (is_array($data['customer_groups']) && in_array(Mage_Customer_Model_Group::CUST_GROUP_ALL, $data['customer_groups'])) {
                $data['customer_groups'] = array(Mage_Customer_Model_Group::CUST_GROUP_ALL);
            }
            $data['store'] = $request->getParam('store');
            $data['priority'] = $request->getParam('priority');

            $data['date_from'] = $request->getParam('date_from') ? $request->getParam('date_from') : '';
            $data['date_to'] = $request->getParam('date_to') ? $request->getParam('date_to') : '';
            $data = $this->_filterDates($data, array('date_from', 'date_to'));
            $data['position'] = $request->getParam('position');
            $data['currently_viewed'] = $request->getParam('currently_viewed');
            if (!is_array($data['currently_viewed']))
                $data['currently_viewed'] = array();
            $data['currently_viewed']['category_ids'] = Mage::helper('awautorelated')->prepareArray($request->getParam('category_ids'));
            if (!isset($data['currently_viewed']['area']) || (($data['currently_viewed']['area'] == 2) && (!$data['currently_viewed']['category_ids']))) {
                $this->_getSession()->addError($this->__("Categories are not specified"));
            }
            $data['related_products'] = $request->getParam('related_products');
            if (!is_array($data['related_products']))
                $data['related_products'] = array();
            if (!isset($data['related_products']['count']) || intval($data['related_products']['count']) < 1) {
                $this->_getSession()->addError($this->__('Count of products should be an integer and greater than 0'));
            }
            $conditions = $request->getParam('rule');
            if (is_array($conditions) && isset($conditions['related'])) {
                $conditionsRelated = $conditions['related'];
                $conditionsRelated = Mage::helper('awautorelated')->updateChild($conditionsRelated, 'catalogrule/rule_condition_combine', 'awautorelated/catalogrule_rule_condition_combine');
                $conditions['related'] = $conditionsRelated;
            }
            $conditions = Mage::helper('awautorelated')->convertFlatToRecursive($conditions, array('related'));
            if (is_array($conditions) && isset($conditions['related']) && isset($conditions['related']['related_conditions'])) {
                $data['related_products']['conditions'] = $conditions['related']['related_conditions'];
            } else {
                $data['related_products']['conditions'] = array();
            }
            $data['type'] = AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK;

            $model = Mage::getModel('awautorelated/blocks')->load($request->getParam('id'));
            $model->setData($data);
            $id = ($this->getRequest()->getParam('saveasnew')) ? 0 : $request->getParam('id');
            if ($id) {
                $model->setId($id);
            }
            if ($this->_hasErrors()) {
                Mage::helper('awautorelated/forms')->setFormData($model->humanizeData());
                return $this->_redirect('*/*/edit', array('id' => $id, 'fswe' => 1));
            } else {
                $model->save();

                $this->_getSession()->addSuccess($this->__('Block has been succesfully saved'));
                if ($request->getParam('continue'))
                    return $this->_redirect('*/*/edit', array('id' => $model->getId(),
                        'continue_tab' => $request->getParam('continue_tab')));
                else
                    return $this->_redirect('*/adminhtml_blocksgrid/list');
            }
        }
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function deleteAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/delete', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }

    protected function indexAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function _isAllowed()
    {
        $helper = Mage::helper('awautorelated');
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
            case 'new':
            case 'save':
                return $helper->isEditAllowed();
                break;
            case 'categoriesJson':
            case 'edit':
            case 'index':
            case 'newConditionHtml':
                return $helper->isViewAllowed() || $helper->isEditAllowed();
                break;
            default:
                return false;
        }
    }
}
