<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-7-1
 * Time: 14:07
 */

class Unirgy_Giftcert_Adminhtml_PdfController
    extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();


        $this->_setActiveMenu('customer/ugiftcert/ugiftcert_pdf');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'));
        $this->_addBreadcrumb($this->__("PDF Settings"), $this->__("PDF Settings"));
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Gift Certificates'))->_title($this->__('PDF Settings'));
        }
        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    public function editAction()
    {
        return $this->_pdfTemplateEdit();
    }

    public function newAction()
    {
        return $this->_pdfTemplateEdit(true);
    }

    protected function _pdfTemplateEdit($isNew = false)
    {
        $this->loadLayout();
        $this->_initPdfTemplate();

        $this->_loadPdfJs();

        $title = $isNew ? $this->__("Add PDF Template") : $this->__("Edit PDF Template");

        $this->_setActiveMenu('customer/ugiftcert/ugiftcert_pdf');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'));
        if (method_exists($this, '_title')) {
            $this->_title($this->__($title));
        }

        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_edit'))
            ->_addLeft($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_edit_tabs'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    /**
     * @return false|Unirgy_Giftcert_Model_Pdf_Model
     */
    protected function _initPdfTemplate()
    {
        $model = Mage::getModel('ugiftcert/pdf_model');
        Mage::register('giftcert_pdf_tpl', $model);
        if ($id = $this->getRequest()->getParam('id')) {
            if (is_numeric($id)) {
                $model->load($id);
            }

            if (!is_numeric($id) || !$model->getId()) {
                $model->load($id, 'hash'); // option to load pdf by its hash
            }
        }
        return $model;
    }

    protected function _loadPdfJs()
    {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            /* @var $head Mage_Adminhtml_Block_Page_Head*/
            $head->addCss('ugiftcert.css');
            $head->addItem('js_css', 'colorpicker/css/prototype_colorpicker.css');
            $head->addJs('colorpicker/colorpicker.js');
            $this->_addJs($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_js'));
        }
    }

    public function saveAction()
    {
        $model = $this->_initPdfTemplate();
        $req   = $this->getRequest();
        $id    = $this->getRequest()->getParam('id');
        /* @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $user    = Mage::getSingleton('admin/session')->getUser();
        try {
            $settings = Mage::helper('ugiftcert')->loadPdfSettings();
            $hash     = sha1($settings);
            $author = $user ? sprintf('%s (%s)', $user->getName(), $user->getUsername()) : 'N/A';
            $model->setData('title', $req->getParam('title'))
                ->setData('hash', $hash)
                ->setData('settings', $settings)
                ->setData('modified_by', $author);
            if (!$model->getId()) {
                // probably new
                $model->setData('added_by', $author)
                    ->setData('added_at', date('Y-m-d H:i:s'));
            } else {
                $model->unsetData('modified_at'); // unset this so that db can auto update it
            }

            $model->save();
            $session->addSuccess(Mage::helper('adminhtml')->__('The configuration has been saved.'));
        } catch (Mage_Core_Exception $e) {
            foreach (explode("\n", $e->getMessage()) as $message) {
                $session->addError($message);
            }
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving this configuration:') . ' ' . $e->getMessage());
            $this->_redirect('*/*/edit', array(
                'id'    => $id
            ));
        }
        if ($this->getRequest()->getParam('back', false) && $id) {
            $this->_redirect('*/*/edit', array(
                'id'    => $id
            ));
        } else {
            $this->_redirect('*/*/', array('_current' => array('section', 'website', 'store')));
        }
    }

    protected function _correctPath($val)
    {
        $url          = $val['value'];
        $base_url     = Mage::getBaseUrl('media') . 'unirgy/giftcert/pdf/';
        $val['value'] = str_ireplace($base_url, '', $url);
        return $val;
    }

    public function printoutAction()
    {
        $pdf = $this->_initPdfTemplate();
        $id  = $pdf->getId();
        if ($id) {
            try {
                $store = Mage::app()->getStore();
                $settings = $pdf->getData('settings');
                if(!is_array($settings)){
                    $settings = Zend_Json::decode($settings);
                }
                $data = new Varien_Object(array(
                    'store'            => $store,
                    'email'            => 'john@doe.com',
                    'name'             => 'John Doe',
                    'sender_name'      => $store->getWebsite()->getName(),
                    'sender_firstname' => $store->getWebsite()->getName(),
                    'gc'               => new Varien_Object(array(
                        'cert_id'           => '1',
                        'cert_number'       => 'TEST-CODE',
                        'balance'           => '100.1234',
                        'pin'               => '0000',
                        'status'            => 'A',
                        'currency_code'     => 'USD',
                        'expire_at'         => '2112-01-31 12:00:00',
                        'recipient_name'    => "John Doe",
                        'recipient_email'   => 'john@doe.com',
                        'recipient_address' => '123 Unknown Street',
                        'recipient_message' => 'Sample message',
                        'store_id'          => '0',
                        'sender_name'       => $store->getWebsite()->getName(),
                        'pdf_settings'      => $settings,
                    )),
                ));

                $printout = Mage::helper('ugiftcert')->outputPdfPrintout($data);
                if(!$printout){
                    throw new Exception($this->__("Printout could not be generated, check if PDF is enabled in main settings."));
                }
                $fileName = $id . '_preview.pdf';

                $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-type', 'application/pdf', true)
                    ->setHeader('Content-Length', strlen($printout))
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                    ->setHeader('Last-Modified', date('r'))
                    ->setBody($printout);

                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/edit', array('id' => $id));
    }

    public function deleteAction()
    {
        try {
            $pdf = $this->_initPdfTemplate();
            if ($pdf) {
                $this->deletePdf($pdf);
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        try {
            $ids = $this->getRequest()->getParam('ids');
            Mage::getModel('ugiftcert/pdf_model')
                ->getCollection()
                ->addFieldToFilter('template_id', array('in' => $ids))
                ->walk(array($this, 'deletePdf'));

            $this->_getSession()->addNotice($this->__("%d PDF templates were deleted. ", count($ids)));
        } catch(Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }

        $this->_redirect('*/*/');
    }

    public function deletePdf($pdfs)
    {
        if(!is_array($pdfs)){
            $pdfs = array($pdfs);
        }

        foreach ($pdfs as $pdf) {
            $title = $pdf->getData('title');
            $pdf->delete();
            $this->_getSession()->addNotice($this->__("PDF template '%s' deleted.", $title));
        }
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_grid')->toHtml()
        );
    }
}
