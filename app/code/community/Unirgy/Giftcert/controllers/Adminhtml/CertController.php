<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Adminhtml_CertController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('customer/ugiftcert');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'));
        if(method_exists($this, '_title')) {
            $this->_title($this->__('Manage Gift Certificates'));
        }
        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_cert'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_certDetailsActionForm('Edit Certificate');
    }

    public function newAction()
    {
        $this->_certDetailsActionForm('Create Certificate');
    }

    protected function _initCertificate()
    {
        $model = Mage::getModel('ugiftcert/cert');
        Mage::register('giftcert_data', $model);
        if ($id = $this->getRequest()->getParam('id')) {
            if(is_numeric($id)) {
                $model->load($id);
            }

            if(!is_numeric($id) || !$model->getId()) {
                $model->load($id, 'cert_number');
            }
        }
    }

    protected function _certDetailsActionForm($title)
    {
        $this->loadLayout();
        $this->_initCertificate();

//        $this->_loadPdfJs();

        $this->_setActiveMenu('customer/ugiftcert');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'));
        if(method_exists($this, '_title')) {
            $this->_title($this->__($title));
        }

        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit'))
            ->_addLeft($this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit_tabs'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    public function saveAction()
    {
        $redirectBack   = $this->getRequest()->getParam('back', false);
        try {
            if ($id = $this->_saveCertificate() ) {
                if(is_array($id)) {
                    $msg = $this->__('Gift certificates were successfully saved');
                    $redirectBack = false;
                } else {
                    $msg = $this->__('Gift certificate %s was successfully saved', $id);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $id = $this->getRequest()->getParam('id');
            $redirectBack = true;
        }
        if ($redirectBack && $id) {
            $this->_redirect('*/*/edit', array(
                  'id'    => $id
             ));
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function savesendAction()
    {
        try {
            $ids = $this->_saveCertificate();
            if($ids) {
                $msg = 'Gift certificates %s were successfully saved';
                if(!is_array($ids)) {
                    $ids = array($ids);
                    $msg = 'Gift certificate %s was successfully saved';
                }
                $this->_getSession()->addSuccess($this->__($msg, implode(', ', $ids)));
                foreach($ids as $id) {
                    if($this->getRequest()->getParam('recipient_email', false)) {// emails are only sent if recipient is set
                        $email = Mage::helper('ugiftcert/email')->sendManualIdEmail($id);
                        if ($email->isLastSendSuccessful())
                            $this->_getSession()->addSuccess($this->__('Gift certificate %s notification email was successfully sent', $id));
                        else
                            $this->_getSession()->addError($this->__("Email sending failed. If you run the site in developer mode, full email information is logged in var/log/giftcert.log"));
                    }
                }
                $this->_redirect('*/*/');
                return;
            }
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }

    protected function _saveCertificate()
    {
        $r = $this->getRequest();
        $certId = false;
        if ($r->getPost()) {
            $id = $r->getParam('id');
            $new = !$id;
            $this->_initCertificate();

            /* @var $model Unirgy_Giftcert_Model_Cert */
//            $model = Mage::getModel('ugiftcert/cert')
            $model = Mage::registry('giftcert_data')
//                ->setId($id)
                ->setCertNumber($r->getParam('cert_number'))
                ->setBalance($r->getParam('balance'))
                ->setCurrencyCode($r->getParam('currency_code'))
                ->setStoreId($r->getParam('store_id'))
                ->setStatus($r->getParam('status1'))
                ->setExpireAt($r->getParam('expire_at'))
                ->setSendOn($r->getParam('send_on'))
                ->setCustomerGroups($r->getParam('customer_groups'))
                ->setData('disallow_coupons', $r->getParam('disallow_coupons'))
                ->setSenderName($r->getParam('sender_name'));
            if ($pin = $r->getParam('pin')) {
                $model->setPin($pin);
            }

            foreach (array('template', 'template_self', 'pdf_template_id') as $tpl) {
                $template = $r->getParam($tpl);
                if (is_numeric($template)) {
                    $model->setData($tpl, $template);
                } else {
                    $model->setData($tpl, null);
                }
            }

            $model->setRecipientName($r->getParam('recipient_name'))
                ->setRecipientEmail($r->getParam('recipient_email'))
                ->setRecipientAddress($r->getParam('recipient_address'))
                ->setRecipientMessage($r->getParam('recipient_message'));

            $this->_loadConditionData($model);
//            $model->setPdfSettings($this->_loadPdfSettings());

            $data = array(
                'user_id'       => Mage::getSingleton('admin/session')->getUser()->getId(),
                'username'      => Mage::getSingleton('admin/session')->getUser()->getUsername(),
                'ts'            => now(),
                'amount'        => $r->getParam('balance'),
                'currency_code' => $r->getParam('currency_code'),
                'status'        => $r->getParam('status1'),
                'comments'      => $r->getParam('comments'),
                'action_code'   => 'update',
            );

            if ($new) {
                $qty = (int)$r->getParam('qty');
                if ($qty < 1) {
                    $qty = 1;
                }

                $num = $model->getCertNumber();
                if (!Mage::helper('ugiftcert')->isPattern($num)) {
                    if ($new && $qty > 1) {
                        throw new Exception($this->__('Can not create multiple Gift Certificates with the same code.'));
                    }

                    $dup = Mage::getModel('ugiftcert/cert')->load($num, 'cert_number');
                    if ($dup->getId() && ($new || $dup->getId() != $model->getId())) {
                        throw new Exception($this->__('Duplicate Gift Certificate Code was found.'));
                    }
                }

                $data['action_code'] = 'create';
                if ($data['order_increment_id'] = $r->getParam('order_increment_id')) {
                    if ($order = Mage::getModel('sales/order')->loadByIncrementId($data['order_increment_id'])) {
                        $data['order_id'] = $order->getId();
                        $data['customer_id'] = $order->getCustomerId();
                        $data['customer_email'] = $order->getCustomerEmail();
                    }
                }
                $ids = array();
                for ($i = 0; $i < $qty; $i++) {
                    $clone = clone $model;
                    $clone->save();
                    $clone->addHistory($data);
                    $ids[] = $clone->getCertNumber();
                }
                $certId = (count($ids) == 1) ? $ids[0] : $ids;
            } else {
                $model->save();
                $model->addHistory($data);
                $certId = $model->getCertNumber();
            }
        }
        return $certId;
    }


    protected function _loadPdfSettings()
    {
        return Mage::helper('ugiftcert')->loadPdfSettings();
    }


    protected function _loadConditionData(Unirgy_Giftcert_Model_Cert $cert)
    {
        return Mage::helper('ugiftcert')->loadConditionData($cert);
    }

    protected function _loadPdfJs()
    {
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            /* @var $head Mage_Adminhtml_Block_Page_Head*/
            $head->setCanLoadExtJs(true);
            $head->setCanLoadRulesJs(true);
            $head->addCss('ugiftcert.css');
            $head->addItem('js_css', 'colorpicker/css/prototype_colorpicker.css');
            $head->addJs('colorpicker/colorpicker.js');
            $this->_addJs($this->getLayout()->createBlock('ugiftcert/adminhtml_pdf_js'));
        }
    }

    public function deleteAction()
    {
        if(($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                Mage::getModel('ugiftcert/cert')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift certificate was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/ugiftcert');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_grid')->toHtml()
        );
    }

    /**
     * Export certificates grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'giftcertificates.csv';
        $content    = $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export certificates grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'giftcertificates.xml';
        $content    = $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $certIds = $this->getRequest()->getParam('cert');
        if (!is_array($certIds)) {
            $this->_getSession()->addError($this->__('Please select gift certificates(s)'));
        }
        else {
            try {
                $cert = Mage::getSingleton('ugiftcert/cert');
                foreach ($certIds as $certId) {
                    $cert->setId($certId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($certIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $certIds = (array)$this->getRequest()->getParam('cert');
        $status     = (string)$this->getRequest()->getParam('status');

        try {
            $cert = Mage::getSingleton('ugiftcert/cert');
            foreach ($certIds as $certId) {
                $cert->setId($certId)->setStatus($status)->setIsMassAction(true)->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated', count($certIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession->addException($e, $this->__('There was an error while updating certificate(s) status'));
        }

        $this->_redirect('*/*/');
    }

    public function massEmailAction()
    {
        $certIds = $this->getRequest()->getParam('cert');
        if (!is_array($certIds)) {
            $this->_getSession()->addError($this->__('Please select gift certificates(s)'));
        }
        else {
            try {
                $ignoreSchedule = $this->getRequest()->getParam('email');
                $stats = Mage::helper('ugiftcert/email')->sendGiftcertEmails($certIds, $ignoreSchedule);

                if ($stats['old']) {
                    $this->_getSession()->addWarning(
                        $this->__('In current release you cannot send emails for certificates that were generated in admin (%d selected)', $stats['old'])
                    );
                } else if($stats['errors']) {
                    $msg = $this->__("%d emails could not be sent.", $stats['errors']);
                    if($stats['emails'] == 0) {
                        $msg  = $this->__("Email sending failed.");
                    }
                    $msg .= $this->__(" If you run the site in developer mode, full email information is logged in var/log/giftcert.log");
                    $this->_getSession()->addError($msg);
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d gift certificates(s) and %d emails were successfully sent', $stats['certs'], $stats['emails'])
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    public function historyGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ugiftcert/adminhtml_cert_edit_tab_history', 'admin.ugiftcert.history')
                ->setCertId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('ugiftcert/cert'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function printoutAction()
    {
        $this->_initCertificate();
        $cert = Mage::registry('giftcert_data');
        $id = $cert->getId();
        if($id) {
            try {
                $store = Mage::app()->getStore($cert->getStoreId());

                $data = new Varien_Object(array(
                            'store' => $store,
                            'email' => $cert->getRecipientEmail(),
                            'name' => $cert->getRecipientName(),
                            'sender_name' => $cert->getSenderName() ? $cert->getSenderName() : $store->getWebsite()->getName(),
                            'sender_firstname' => $cert->getSenderName() ? $cert->getSenderName() : $store->getWebsite()->getName(),
                            'gc' => $cert,
                        ));

                $printout = Mage::helper('ugiftcert')->outputPdfPrintout($data);
                $fileName = $cert->getCertNumber() . '.pdf';

                $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-type', 'application/pdf', true)
                    ->setHeader('Content-Length', strlen($printout))
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
                    ->setHeader('Last-Modified', date('r'))
                    ->setBody($printout);

                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/edit', array('id' => $id));
    }

    public function uploadAction()
    {
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('csv','txt','*'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $target = Mage::getConfig()->getVarDir('ugiftcert/import');
            Mage::getConfig()->createDirIfNotExists($target);
            $result = $uploader->save($target);

            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function importAction()
    {
        $file = $this->getRequest()->getParam('file');
        $session = $this->_getSession();
        if($file) {
            try {
                $start = microtime(true);

                $target = Mage::getConfig()->getVarDir('ugiftcert/import');
                $import_file = $target . DS . $file;
                if(!is_readable($import_file)) {
                    return "File " . $file . " cannot be found in " . $target;
                }
                /* @var $hlp Unirgy_Giftcert_Helper_Import */
                $hlp = Mage::helper('ugiftcert/import');
                $counter = $hlp->importFile($import_file);

                $end = microtime(true);

                $diff = $end - $start;

                $debug = 'Imported ' . $counter . ' certificate(s) for ' . round($diff, 3) . ' seconds. ';
                $diff = $counter / $diff;
                $debug .= round($diff, 3) . ' certificates per second';
                $session->addNotice($debug);
            } catch (Exception $e) {
                $session->addException($e, $hlp->__('An error occurred while importing:').' '.$e->getMessage());
            }
        }
        $this->_redirect('*/*/importing');
    }

    public function importingAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('customer/ugiftcert');
        $this->_addBreadcrumb($this->__('Gift Certificates'), $this->__('Gift Certificates'))
             ->_addBreadcrumb($this->__('Import'), $this->__('Import'));
        if(method_exists($this, '_title')) {
            $this->_title($this->__('Import Gift Certificates'));
        }
        $this->_addContent($this->getLayout()->createBlock('ugiftcert/adminhtml_import'));

        Mage::helper('ugiftcert')->addAdminhtmlVersion();

        $this->renderLayout();
    }

    public function saveimportAction()
    {
        $store = Mage::app()->getStore();
        $website = $store->getWebsite();
        $section = 'ugiftcert';
        $session = $this->_getSession();
        $request = $this->getRequest();
        $groups = array();
        $groups['import']['fields']['delimiter']['value'] = $request->getParam('delimiter');
        $groups['import']['fields']['enclosure']['value'] = $request->getParam('enclosure');
        $groups['import']['fields']['file']['value'] = $request->getParam('file');
        $groups['import']['fields']['action']['value'] = $request->getParam('action');
        try {
            Mage::getModel('adminhtml/config_data')
                ->setSection($section)
                ->setWebsite($website->getCode())
                ->setStore($store->getCode())
                ->setGroups($groups)
                ->save();

            Mage::getConfig()->reinit(); // reinit configuration

            $session->addSuccess(Mage::helper('adminhtml')->__('The configuration has been saved.'));

            if($request->getParam('doimport')) {
                $this->_forward('import');
                return;
            }
        }
        catch (Mage_Core_Exception $e) {
            foreach(explode("\n", $e->getMessage()) as $message) {
                $session->addError($message);
            }
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving this configuration:').' '.$e->getMessage());
        }
        $this->_redirect('*/*/importing');
    }

    public function reportAction()
    {
//        echo __METHOD__;
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Reports'))->_title($this->__('Gift Certificates'));
        }
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('ugiftcert')->__('Reports'), Mage::helper('ugiftcert')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('ugiftcert')->__('Gift Certificates'), Mage::helper('ugiftcert')->__('Gift Certificates'));

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    /**
     * Export reports grid to CSV format
     */
    public function exportCouponsCsvAction()
    {
        try {
            $this->doReportExport('csv');
        } catch (Exception $e) {
            $this->_getSession()->addError("Export failed.");
            Mage::logException($e);
            $this->_redirect("*/*/report");
        }
    }

    /**
     * Export reports grid to xls format
     */
    public function exportCouponsExcelAction()
    {
        try {
            $this->doReportExport('xls');
        } catch (Exception $e) {
            $this->_getSession()->addError("Export failed.");
            Mage::logException($e);
            $this->_redirect("*/*/report");
        }
    }

    protected function doReportExport($type)
    {
        $method = 'getCsvFile';
        $ext = 'csv';
        if($type == 'xls'){
            $method = 'getExcelFile';
            $ext = 'xml';
        }

        $fileName = 'giftcertificate_report.' . $ext;
        $block = $this->getLayout()->createBlock('ugiftcert/adminhtml_report_grid');
        $this->_initReportAction(array($block));
        $content = $block->$method();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    public function testitAction()
    {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        $config = Mage::getConfig();
        $moduleConfig = new DOMDocument("1.0");
        $moduleConfig->loadXML($config->getXmlString());
        $moduleConfig->formatOutput = true;
        $file = 'temp' . time() . '.xml';
        $fileurl = Mage::getBaseUrl('media') . $file;
        $moduleConfig->save(Mage::getBaseDir('media') . DS . $file);
        $colorpicker = Mage::getBaseUrl('js') . 'colorpicker';
        $prot = Mage::getBaseUrl('js') . 'prototype/prototype.js';
        $configt = <<<here
            <html>
                <head>
                    <title>Testing giftcert</title>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                    <script type="text/javascript" src="{$prot}"></script>
                    <link rel="stylesheet" type="text/css" href="{$colorpicker}/css/XMLDisplay.css"/>
                    <script type="text/javascript" src="{$colorpicker}/XMLDisplay.js"></script>
                </head>
                <body>
                <div class="exp-collapse">
                    <a href="#" onclick="CollapseAll()" title="Collapse all">Collapse All</a>
                    <a href="#" onclick="ExpandAll()" title="Expand all">Expand All</a>
                </div>
                <div id="config"></div>
                <div class="exp-collapse">
                    <a href="#" onclick="CollapseAll()" title="Collapse all">Collapse All</a>
                    <a href="#" onclick="ExpandAll()" title="Expand all">Expand All</a>
                </div>
                <script  type="text/javascript">
                    window.onload = function(){
                        LoadXML('config', '{$fileurl}');
                    };
                </script>
here;
        print($configt); //, $moduleConfig->saveXML($moduleConfig->documentElement));
        $tables = array(
            'ugiftcert_cert' => $res->getTableName('ugiftcert_cert'),
            'ugiftcert_history' => $res->getTableName('ugiftcert_history'),
            'sales_flat_quote' => $res->getTableName('sales_flat_quote'),
            'sales_flat_quote_address' => $res->getTableName('sales_flat_quote_address'),
            'sales_flat_order' => $res->getTableName('sales_flat_order'),
            'sales_flat_invoice' => $res->getTableName('sales_flat_invoice'),
            'sales_flat_creditmemo' => $res->getTableName('sales_flat_creditmemo'),
        );
        $conn_name = 'core_read';
        $connt = "<div style='font-family: sans-serif'>DB Connection - host: <strong>%s</strong>; dbname: <strong>%s</strong>; adapter: <strong>%s</strong></div><br/>";
        $connconfig = Mage::getConfig()->getResourceConnectionConfig($conn_name);
        printf($connt, $connconfig->host, $connconfig->dbname, $connconfig->type);
        $conn = $res->getConnection($conn_name);
        foreach ($tables as $code => $tname) {
            $data = $conn->describeTable($tname);
            $this->layTable($tname, $data);
        }
        print("</body></html>");
//        unlink(Mage::getBaseDir('media') . DS . $file);
    }

    private function layTable($tableName, $data)
    {
        $tablet = 'Table: "<strong>%s</strong>, %d columns"' . PHP_EOL;
        $t = "  ";
        printf($tablet, $tableName, count($data));
        echo '<style type="text/css">.odd{background-color: #eeffaa;} .even{ background-color: #e6e6e6;} .headings { background-color: #e46b00; color: #fff}</style>';
        echo '<table style="border: 1px; border-collapse: collapse; margin-bottom: 20px; width: 70%">' . PHP_EOL;
        $tdt = '    <td style="border: 1px dotted #f00; padding: 1px 2px">%s</td>' . PHP_EOL;
        $tht = '    <th style="font-weight: bold;border: 1px solid #000; font-size: 0.8em; padding: 1px 2px">%s</th>' . PHP_EOL;
        $trt = '  <tr class="%s">' . PHP_EOL;
        $i = 0;
        $cycle = "even";
        $full = in_array($tableName, array('ugiftcert_cert','ugiftcert_history'));
        $gcCols = 0;
        foreach($data as $name => $descr) {
            if($i === 0) {
                printf($trt, 'headings');
                $headings = array_keys($descr);
                printf($tht, 'Column');
                foreach($headings as $title) {
                    printf($tht, $title);
                }
                echo $t, '</tr>' . PHP_EOL;
                $i++;
            }
            if (!$full && strpos($name, 'giftcert') === false) {
                continue;
            }
            $gcCols++;
            printf($trt, $cycle);
            printf($tdt, $name);
            foreach($descr as $val) {
                printf($tdt, $val);
            }
            echo $t, '</tr>' . PHP_EOL;

            $cycle = ($cycle == 'even') ? 'odd' : 'even';
        }
        echo '</table>' . PHP_EOL;
        if (!$full) {
            printf('<p>%d giftcert columns</p>', $gcCols);
        }
    }
}
