<?php
/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2013 Vladimir Popov
 */

class VladimirPopov_WebForms_Adminhtml_ResultsController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webforms/webforms');
        if ((float)substr(Mage::getVersion(), 0, 3) > 1.3)
            $this->_title($this->__('Web-forms'))->_title($this->__('Results'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_initAction();
        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('webforms/adminhtml_results_edit', 'edit')
        );
        $this->renderLayout();
    }

    public function saveAction()
    {

        $postData = Mage::app()->getRequest()->getPost('result');
        $saveandcontinue = $postData["saveandcontinue"];

        $webformId = $postData['webform_id'];

        if ($webformId) {

            $webform = Mage::getModel('webforms/webforms')->load($webformId);
            $webform->setData('disable_captcha', true);
            $storeId = Mage::getModel('webforms/results')->load($postData['result_id'])->getStoreId();
            $resultId = $webform->savePostResult(
                array(
                    'prefix' => 'result'
                )
            );

            // if we get validation error
            if(!$resultId){
                if($postData['result_id']){
                    $resultId = $postData['result_id'];
                    $this->_redirect('*/adminhtml_results/edit', array('id' => $resultId));
                    return;
                }
                $this->_redirect('*/adminhtml_results/index', array('webform_id' => $webformId));
                return;
            }

            // recover store id
            Mage::getModel('webforms/results')->load($resultId)->setStoreId($storeId)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('webforms')->__('Result was successfully saved'));

            if ($saveandcontinue) {
                $this->_redirect('*/adminhtml_results/edit', array('id' => $resultId));
            } else {
                $this->_redirect('*/adminhtml_results/index', array('webform_id' => $webformId));
            }
        }
    }

    public function replyAction()
    {
        $this->_initAction();
        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('webforms/adminhtml_reply', 'reply')
        );
        $this->renderLayout();
    }

    public function saveMessageAction()
    {
        $post = $this->getRequest()->getPost('reply');
        $Ids = unserialize($post['result_id']);

        $user = Mage::getModel('admin/user')->load(Mage::helper('adminhtml')->getCurrentUserId());
        $i = 0;

        $filter = Mage::helper('cms')->getPageTemplateProcessor();

        foreach ($Ids as $id) {
            $result = Mage::getModel('webforms/results')->load($id);

            // add template processing
            $filter->setStoreId($result->getStoreId());
            $message = $filter->filter($post['message']);

            if (Mage::getStoreConfig('webforms/message/nl2br', $result->getStoreId())) {
                $message = str_replace("</p><br />", "</p>", nl2br($post['message'], true));
            }

            $message = Mage::getModel('webforms/message')
                ->setMessage($message)
                ->setAuthor($user->getName())
                ->setUserId($user->getId())
                ->setResultId($id)
                ->save();


            if ($post['email']) {

                if ($result->getCustomerEmail()) {

                    $success = $message->sendEmail();

                    if ($success) {
                        $i++;
                        $message->setIsCustomerEmailed(1)->save();
                    }
                }

            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d reply(s) has been saved.', count($Ids)));

        if ($i) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d reply(s) has been emailed.', $i));
        }

        if ($post['email'] && $i < count($Ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Total of %d result(s) has no reply-to e-mail address.', count($Ids) - $i));
        }

        $this->_redirect('*/*/', array('webform_id' => $post['webform_id']));
    }

    public function gridAction()
    {
        $this->loadLayout();
        if (!Mage::registry('webform_data')) {
            $webform = Mage::getModel('webforms/webforms')
                ->setStoreId($this->getRequest()->getParam('store'))
                ->load($this->getRequest()->getParam('webform_id'));
            Mage::register('webform_data', $webform);
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('webforms/adminhtml_results_grid')->toHtml()
        );
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $result = Mage::getModel('webforms/results')->load($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Result was successfully deleted'));
                $this->_redirect('*/*/', array('webform_id' => $result->getWebformId()));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        if (!Mage::registry('webform_data')) {
            $webform = Mage::getModel('webforms/webforms')->load($this->getRequest()->getParam('webform_id'));
            Mage::register('webform_data', $webform);
        }
        $fileName = 'results.csv';
        $content = $this->getLayout()->createBlock('webforms/adminhtml_results_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        if (!Mage::registry('webform_data')) {
            $webform = Mage::getModel('webforms/webforms')->load($this->getRequest()->getParam('webform_id'));
            Mage::register('webform_data', $webform);
        }
        $fileName = 'results.xml';
        $content = $this->getLayout()->createBlock('webforms/adminhtml_results_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massEmailAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        try {
            $k = 0;
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/results')->load($id);
                $success = $result->sendEmail();
                if ($success) $k++;
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d result(s) have been emailed.', count($k))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred during operation.'));
        }

        $this->_redirect('*/*/', array('webform_id' => $this->getRequest()->getParam('webform_id')));

    }

    public function massDeleteAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');

        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/results')->load($id);
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

        $this->_redirect('*/*/', array('webform_id' => $this->getRequest()->getParam('webform_id')));

    }

    public function massApproveAction($approveStatus = 1)
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        try {
            foreach ($Ids as $id) {
                $result = Mage::getModel('webforms/results')->load($id);
                $result->setApproved(intval($approveStatus));
                $result->save();
                Mage::dispatchEvent('webforms_result_approve', array('result' => $result));
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d result(s) have been updated.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred during operation.'));
        }

        $this->_redirect('*/*/', array('webform_id' => $this->getRequest()->getParam('webform_id')));

    }

    public function massDisapproveAction()
    {
        $this->massApproveAction(0);
    }

}

?>
