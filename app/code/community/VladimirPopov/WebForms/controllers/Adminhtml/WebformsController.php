<?php
/**
 * Feel free to contact me via Facebook
 * http://www.facebook.com/rebimol
 *
 *
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2011 Vladimir Popov
 */

class VladimirPopov_WebForms_Adminhtml_WebFormsController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webforms/webforms');
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3)
            $this->_title($this->__('Web-forms'))->_title($this->__('Manage Forms'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_webforms_grid')->toHtml()
        );
    }

    public function logicAction()
    {
        $webformsId = $this->getRequest()->getParam('webform_id');
        $webformsModel = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getRequest()->getParam('store'))
            ->load($webformsId);
        if ($webformsModel->getId() || $webformsId == 0) {
            Mage::register('webforms_data', $webformsModel);
            $this->loadLayout();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tab_logic')->toHtml()
            );
        }
    }

    public function editAction()
    {
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 || Mage::helper('webforms')->getMageEdition() == 'EE')
            $this->_title($this->__('Web-forms'))->_title($this->__('Edit Form'));

        $webformsId = $this->getRequest()->getParam('id');
        $webformsModel = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getRequest()->getParam('store'))
            ->load($webformsId);

        if ($webformsModel->getId() || $webformsId == 0) {
            Mage::register('webforms_data', $webformsModel);
            $this->loadLayout();
            $this->_setActiveMenu('webforms/webforms');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Web-forms'), Mage::helper('adminhtml')->__('Web-forms'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('webforms/adminhtml_webforms_edit'))
                ->_addLeft($this->getLayout()->createBlock('webforms/adminhtml_webforms_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('webforms')->__('Web-form does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $id = $this->getRequest()->getParam('id');
                $postData = $this->getRequest()->getPost('form');

                $form = Mage::getModel('webforms/webforms')->setId($id);

                $store = Mage::app()->getRequest()->getParam('store');
                if ($store)
                    $form->saveStoreData($store, $postData);
                else
                    $form->setData($postData)->setId($id)->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())->save();

                if ($this->getRequest()->getParam('id') <= 0)
                    $form->setCreatedTime(Mage::getSingleton('core/date')->gmtDate())->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Web-form was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setWebFormsData(false);

                // check if 'Save and Continue'
                $redirectBack = $this->getRequest()->getParam('back', false);
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $form->getId(), 'store' => $store, 'active_tab' => $this->getRequest()->getParam('active_tab')));
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setWebFormsData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'store' => $store));
                return;
            }

        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $webformsModel = Mage::getModel('webforms/webforms');
                $webformsModel->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Web-form was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $form = Mage::getModel('webforms/webforms')->load($id)->duplicate();
        if ($form->getId()) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Web-form was successfully duplicated'));
            $this->_redirect('*/*/edit', array('id' => $form->getId()));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Error duplicating web-form'));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }
    }


    public function massStatusAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        $status = (int)$this->getRequest()->getParam('status');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/webforms')->load($id);
                $result->setData('is_active', $status);
                $result->save();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }

        $this->_redirect('*/*/index');


    }

    public function massDuplicateAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $webform = Mage::getModel('webforms/webforms')->load($id);
                $webform->duplicate();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been duplicated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while duplicating records.'));
        }

        $this->_redirect('*/*/');

    }

    public function massDeleteAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/webforms')->load($id);
                $result->delete();
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }

        $this->_redirect('*/*/');

    }
}

?>
