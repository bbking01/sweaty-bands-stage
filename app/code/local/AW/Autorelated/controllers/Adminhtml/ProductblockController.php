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

class AW_Autorelated_Adminhtml_ProductblockController extends AW_Autorelated_Adminhtml_AbstractblockController
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
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('awautorelated/blocks')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            //$model = Mage::getModel('awautorelated/rule');


            Mage::register('productblock_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('catalog/awautorelated');
            $this->_setTitle($id ? 'Edit Product Block' : 'Add Product Block');
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)->setCanLoadRulesJs(true);

            $this->_addContent($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_product_edit'))
                ->_addLeft($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_product_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('awautorelated')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {

        if ($this->getRequest()->isPost()) {
            $id = ($this->getRequest()->getParam('saveasnew')) ? 0 : (int)$this->getRequest()->getParam('id');
            $block = Mage::getModel('awautorelated/blocks')->load($id);
            try {

                $data = $this->getRequest()->getPost();

                $data = $this->_filterDates($data, array('date_from', 'date_to'));

                foreach ($data as $name => $param) {
                    if (!empty($param) || (in_array($name, array('date_from', 'date_to', 'priority')) && $id))
                        $block->setData($name, $param);
                }
                $block->setData('position', (int)$this->getRequest()->getParam('position'));
                $block->setData('status', (int)$this->getRequest()->getParam('status'));
                $block->setType(AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK);

                $block->setStore($block->getStore());

                $request = $this->getRequest();
                $data = array();

                $rule = $request->getParam('rule');
                $rule['viewed'] = Mage::helper('awautorelated')->updateChild($rule['viewed'], 'catalogrule/rule_condition_combine', 'awautorelated/catalogrule_rule_condition_combine');
                $rule['related'] = Mage::helper('awautorelated')->updateChild($rule['related'], 'catalogrule/rule_condition_combine', 'awautorelated/catalogrule_rule_condition_combine');

                $conditions = Mage::helper('awautorelated')->convertFlatToRecursive($rule, array('viewed', 'related'));

                if (is_array($conditions) && isset($conditions['viewed']) && isset($conditions['viewed']['viewed_conditions_fieldset']))
                    $data['currently_viewed']['conditions'] = $conditions['viewed']['viewed_conditions_fieldset'];
                else
                    $data['currently_viewed']['conditions'] = array();

                if (is_array($conditions) && isset($conditions['related']) && isset($conditions['related']['related_conditions_fieldset']))
                    $data['related_products']['conditions'] = $conditions['related']['related_conditions_fieldset'];
                else
                    $data['related_products']['conditions'] = array();

                $block->setCurrentlyViewed($data['currently_viewed']);
                //save tab as array
                $general = $block->getGeneral();
                $filtered = array();
                foreach ($general as $row) {
                    if (!empty($row['att']) && !empty($row['condition']))
                        $filtered[] = $row;
                }

                $relatedPostData = $this->getRequest()->getParam('related_products');

                $related_data = array(
                    'general' => $filtered,
                    'related' => $data['related_products'],
                    'product_qty' => $block->getProductQty(),
                    'show_out_of_stock' => isset($relatedPostData['show_out_of_stock']) ? $relatedPostData['show_out_of_stock'] : 0,
                    'order' => isset($relatedPostData['order']) ? $relatedPostData['order'] : array()
                );

                $block->setRelatedProducts($related_data);

                if ($id) {
                    $block->setId($id);
                }

                $block->save();
                Mage::getSingleton('adminhtml/session')->addSuccess("Block successfully saved");
            } catch (Exception $E) {
                Mage::getSingleton('adminhtml/session')->addError($E->getMessage());
            }
            if ($back = $this->getRequest()->getParam('back'))
                return $this->_redirect('*/*/' . $back, array('id' => $block->getId()));
            else
                return $this->_redirect('*/*');
        } else
            return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    public function indexAction()
    {
        $this->_redirect('admin_awautorelated/adminhtml_blocksgrid/list');
    }

    protected function _isAllowed()
    {
        $helper = Mage::helper('awautorelated');
        switch ($this->getRequest()->getActionName()) {
            case 'new':
            case 'save':
            case 'delete':
                return $helper->isEditAllowed();
                break;
            case 'edit':
            case 'index':
            case 'newConditionHtml':
                return $helper->isViewAllowed() || $helper->isEditAllowed();
                break;
            default:
                return false;
        }
    }

    protected function deleteAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/delete', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }
}
