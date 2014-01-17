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


class AW_Autorelated_Adminhtml_BlocksgridController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('catalog/awautorelated');
    }

    protected function indexAction()
    {
        return $this->_redirect('*/*/list');
    }

    public function newAction()
    {
        return $this->_redirect('*/*/selecttype');
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $type = Mage::getModel('awautorelated/blocks')->getTypeById($id);

        switch ($type) {
            case AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK:
                return $this->_redirect('*/adminhtml_productblock/edit', array('id' => $id));
                break;
            case AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK:
                return $this->_redirect('*/adminhtml_categoryblock/edit', array('id' => $id));
                break;
            case AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK:
                return $this->_redirect('*/adminhtml_shoppingcartblock/edit', array('id' => $id));
                break;
        }
        return $this->_redirect('*/*/list');
    }

    public function selecttypeAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_initAction()->_setTitle('Select block type')->renderLayout();
        } else {
            $blockType = $this->getRequest()->getParam('block_type');
            $blockTypesModel = Mage::getModel('awautorelated/source_type');
            $_redirect = '*/*/selecttype';
            if ($blockType && $blockTypesModel->getOption($blockType) !== false) {
                switch ($blockType) {
                    case AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK:
                        $_redirect = '*/adminhtml_productblock/new';
                        break;
                    case AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK:
                        $_redirect = '*/adminhtml_categoryblock/new';
                        break;
                    case AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK:
                        $_redirect = '*/adminhtml_shoppingcartblock/new';
                        break;
                }
            }
            return $this->_redirect($_redirect);
        }
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (!is_array($id) && (int)$id) { //single item
            $block = Mage::getModel('awautorelated/blocks')->load($id);
            try {
                $block->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('awautorelated')->__('Block was successfully deleted'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } elseif (is_array($id) && count($id)) { //massDelete
            try {
                foreach ($id as $blockId) {
                    $block = Mage::getModel('awautorelated/blocks')->load((int)$blockId);
                    $block->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($id))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        }

        return $this->_redirect('*/*/list');
    }

    protected function listAction()
    {
        $this->_initAction()
            ->_setActiveMenu('catalog/awautorelated')
            ->_setTitle('Manage Blocks')
            ->_addContent($this->getLayout()->createBlock('awautorelated/adminhtml_blocks'))
            ->renderLayout();
    }

    public function massStatusAction()
    {
        $blocksId = $this->getRequest()->getParam('id');
        if (!is_array($blocksId)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $db = Mage::getSingleton('core/resource')->getConnection('core_write');
                $db->query(
                    'UPDATE `' . Mage::getSingleton('core/resource')->getTableName('awautorelated/blocks')
                        . '` SET `status` = ' . (int)$this->getRequest()->getParam('status')
                        . ' WHERE `id` IN (' . implode(',', $blocksId) . ')'
                );
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($blocksId))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/list');
    }

    protected function _isAllowed()
    {
        $helper = Mage::helper('awautorelated');
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
            case 'massStatus':
            case 'new':
            case 'selecttype':
                return $helper->isEditAllowed();
                break;
            case 'edit':
            case 'index':
            case 'list':
                return $helper->isViewAllowed() || $helper->isEditAllowed();
                break;
            default:
                return false;
        }
    }

    /**
     * Set title of page
     *
     * @return AW_Autorelated_Adminhtml_BlocksgridController
     */
    protected function _setTitle($action)
    {
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Automatic Related Products 2'))->_title($this->__($action));
        }
        return $this;
    }
}
