<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-10-31
 * Time: 16:55
 */

class Unirgy_Giftcert_Helper_Email
    extends Mage_Core_Helper_Data
{
    protected $_sent_success;

    /**
     * @var Unirgy_Giftcert_Helper_Data
     */
    protected $_data_helper;
    public $_productCache;

    /**
     * @param $data
     * @return boolean
     */
    public function sendEmail($data)
    {
        if (is_array($data)) {
            $data = new Varien_Object($data);
        }
        $store = $data->getStore();
        /* @var $gc Unirgy_Giftcert_Model_Cert */
        $gc = $data->getGc();

        /*
         * check if certificate has scheduled sending and if not, skip sending
         */
        if ($this->_checkSchedule($gc) === false) {
            $this->setSentSuccess(false);
        } else {
            $currency = $this->getDataHelper()->getCurrency($gc->getCurrencyCode());
//        $self = !$gc->getRecipientName();

            list($product, $template) = $this->getTemplate($data);

            $identity = Mage::getStoreConfig('ugiftcert/email/identity', $store);
            $this->getDataHelper()->setDesignStore($store);

            $email = $this->getEmailTemplate();
            $this->addPdfPrintouts($email, $data);
            $amount = $data->hasData('amount') ? $data->getData('amount') : $gc->getAmount();
            $email->sendTransactional($template, $identity, $data->getEmail(), $data->getName(),
                                      array(
                                           'order'               => $data->getOrder(),
                                           'item'                => $data->getItem(),
                                           'product'             => $product,
                                           'gc'                  => $gc,
                                           'amount'              => $currency->format($amount),
                                           'sender_name'         => $data->getSenderName(),
                                           'sender_firstname'    => $data->getSenderFirstname(),
                                           'recipient_name'      => $data->getName(),
                                           'custom_message'      => $gc->getRecipientMessage(),
                                           'expire_on'           => $gc->getExpireAt() ? $this->formatDate($gc->getExpireAt(), 'long') : '',
                                           'certificate_numbers' => join('<br/>', $data->getGcNumbers()),
                                           'website_name'        => $store->getWebsite()->getName(),
                                           'group_name'          => $store->getGroup()->getName(),
                                           'store_name'          => $store->getFrontendName(),
                                      ), $gc->getData('store_id'));

            $this->setSentSuccess($email->getData('sent_success'));

            $this->debugEmail(var_export($email->debug(), true));
            $this->getDataHelper()->setDesignStore();
        }

        return $this->isLastSendSuccessful();
    }

    /**
     * Check if certifcate is scheduled
     * If it is scheduled, and today date is not matching schedule date, return false else return true.
     *
     * @param Unirgy_Giftcert_Model_Cert $cert
     * @return bool
     */
    protected function _checkSchedule($cert)
    {
        if ($cert->getData('send_on') && $cert->getData('send_on') != date('Y-m-d')) {
            Mage::log($this->__("GC '%s' is scheduled on '%s' and cannot be sent on '%s'.",
                                $cert->getData('cert_number'), $cert->getData('send_on'), date('Y-m-d')),
                      Zend_Log::WARN, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            return false;
        }
        return true;
    }

    /**
     * @return Mage_Core_Model_Email_Template
     */
    public function getEmailTemplate()
    {
        //todo change this to check if local object was instantiated instead of taking this from outside each time
        return Mage::getModel('core/email_template');
    }

    /**
     * @param Varien_Object $data
     * @return array
     */
    protected function getTemplate($data)
    {
        $product = null;
//        $self = !$data->getData('gc')->getRecipientName();
        // get email template id out of gift certificate
//        $template = $self ? $data->getData('gc')->getData('template_self') : $data->getData('gc')->getData('template');
        $template = $data->getData('gc')->getData('template');
        if ($data->hasData('item')) {
            if ($data->getData('item')->getProduct()) {
                $product = $data->getData('item')->getProduct();
                if (empty($this->_productCache[$product->getId()])) {
                    $this->_productCache[$product->getId()] = $product;
                }
            } else {
                $productId = $data->getData('item')->getProductId();
                if (empty($this->_productCache[$productId])) {
                    $this->_productCache[$productId] = Mage::getModel('catalog/product')->load($productId);
                }
                $product = $this->_productCache[$productId];
            }
//            if (!$template) {
            // if certificate does not have template assigned, try product attribute
//                $template = $self ? $product->getUgiftcertEmailTemplateSelf() : $product->getUgiftcertEmailTemplate();
//                $template = $product->getUgiftcertEmailTemplate();
//            }
        }

        if (!$template) {
            // if nor product or certificate have anything assigned to them for template, get default values.
            $store = $data->getData('store');
//            $template = Mage::getStoreConfig($self ? 'ugiftcert/email/template_self' : 'ugiftcert/email/template', $store);
            $template = Mage::getStoreConfig('ugiftcert/email/template', $store);
        }

        return array($product, $template);
    }

    public function sendManualEmail($gc)
    {
        $store = Mage::app()->getStore($gc->getStoreId());

        $emailEnabled = Mage::getStoreConfig('ugiftcert/email/enabled', $store);
        if (!$gc->getRecipientEmail()) {
            $this->debugEmail("No recipient email in " . $gc->getCertNumber());

            return $this;
        } else if (!$emailEnabled) {
            $this->debugEmail("Email sending disabled.");

            return $this;
        }

        $usePin    = Mage::getStoreConfig('ugiftcert/default/use_pin', $store);
        $pinFormat = Mage::getStoreConfig('ugiftcert/email/pin_format', $store);

        $this->addCertHistory($gc, $gc->getRecipientEmail());
        $this->sendEmail(array(
                              'store'            => $store,
                              'email'            => $gc->getRecipientEmail(),
                              'name'             => $gc->getRecipientName(),
                              'sender_name'      => $gc->getSenderName() ? $gc->getSenderName() : $store->getWebsite()
                                  ->getName(),
                              'sender_firstname' => $gc->getSenderName() ? $gc->getSenderName() : $store->getWebsite()
                                  ->getName(),
                              'gc'               => $gc,
                              'gc_numbers'       => array($gc->getCertNumber() => $gc->getCertNumber() . ($usePin ? sprintf($pinFormat, $gc->getPin()) : '')),
                         ));

        return $this;
    }

    public function sendManualIdEmail($certId)
    {
        /* @var $giftcerts Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
        $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection();
        if (!is_numeric($certId)) { // not number so it has to be certificate code
            $giftcerts->addFieldToFilter('cert_number', $certId);
        } else {
            $giftcerts->addIdFilter($certId);
            $giftcerts->getSelect()->orWhere('main_table.cert_number=?', $certId);
        }

        $giftcerts->addHistory();

        $giftcert = $giftcerts->getFirstItem();

        if ($giftcert) {
            $this->sendManualEmail($giftcert);

            return $this;
        }
        throw new Exception($this->__("Invalid certificate identifier."));
    }

    /**
     * Send GC confirmation email for order item
     *
     * @param Mage_Sales_Model_Order_Item                       $item
     * @param null|Unirgy_Giftcert_Model_Mysql4_Cert_Collection $giftcerts
     * @return Unirgy_Giftcert_Helper_Email
     */
    public function sendOrderItemEmail($item, $giftcerts = null)
    {
        if (!$item || !$item instanceof Mage_Sales_Model_Order_Item) {
            Mage::getSingleton('adminhtml/session')
                ->addError($this->__("Missing order item. Email not sent. Or item not instance of Mage_Sales_Model_Order_Item class."));
            Mage::log(gettype($item), Zend_Log::INFO, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            if (gettype($item) == 'object') {
                Mage::log(get_class($item), Zend_Log::INFO, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            }

            return $this;
        }

        if (!$giftcerts) {
            $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addItemFilter($item->getId());
        }

        if (!count($giftcerts)) {
            return $this;
        }

        $order   = $item->getOrder();
        $storeId = $order->getStoreId();
        $store   = Mage::app()->getStore($storeId);

        $gcs    = array();
        $self   = null;
        $amount = 0;

        $emailEnabled = Mage::getStoreConfig('ugiftcert/email/enabled', $store);
        $usePin       = Mage::getStoreConfig('ugiftcert/default/use_pin', $store);
        $pinFormat    = Mage::getStoreConfig('ugiftcert/email/pin_format', $store);

        foreach ($giftcerts as $gc) {
            if(!$this->_checkSchedule($gc)){
                continue; // if schedule to send is not today, skip sending
            }
            $email = $name = null;
            if (is_null($self)) {
                $self = !$gc->getRecipientName(); // set $self to false if recipient name is present.
                if (!$self && !$gc->getRecipientEmail()) {
                    return $this; // if someone else is recipient, and no email address, don't send
                }
                if (!$self && !$emailEnabled) {
                    return $this; // if someone else is recipient, and email sending is disabled, don't send
                }
                $email = $self ? $order->getCustomerEmail() : $gc->getRecipientEmail();
                $name  = $self ? $order->getBillingAddress()->getFirstname() : $gc->getRecipientName();
            }
            // save actual cert_number for later reference
            $gcs[$gc->getCertNumber()] = $gc->getCertNumber() . ($usePin ? sprintf($pinFormat, $gc->getPin()) : '');
            $amount = $gc->getAmount();
            $this->addCertHistory($gc, $email);
            $senderName = $gc->getData('sender_name');
            if (!$senderName) {
                $senderName = $order->getCustomerName() ? $order->getCustomerName() : $order
                                                                                      ->getBillingAddress()->getName();
            }
            $this->sendEmail(array(
                                  'store'            => $store,
                                  'order'            => $order,
                                  'item'             => $item,
                                  'email'            => $email,
                                  'name'             => $name,
                                  'sender_name'      => $senderName,
                                  'sender_firstname' => $senderName,
                                  'gc'               => $gc,
                                  'gc_numbers'       => $gcs,
                                  'amount'           => $amount,
                             ));
        }

        return $this;
    }

    /**
     * @param Unirgy_Giftcert_Model_Cert $gc
     * @param string                     $email
     * @return void
     */
    protected function addCertHistory($gc, $email)
    {
        $history = array(
            'action_code'    => 'email',
            'ts'             => now(),
            'amount'         => $gc->getAmount(),
            'currency_code'  => $gc->getCurrencyCode(),
            'status'         => $gc->getStatus(),
            'customer_email' => $email,
        );
        if (Mage::app()->getStore()->isAdmin() && Mage::getSingleton('admin/session')->isLoggedIn()) {
            $user                = Mage::getSingleton('admin/session')->getUser();
            $history['user_id']  = $user->getId();
            $history['username'] = $user->getUsername();
        }
        $gc->addHistory($history);
    }

    /**
     * Send GC confirmation emails for set of certificates
     *
     * Used in admin GC grid mass action
     * If $ignoreSchedule is 'ignore' emails should be sent anyway.
     *
     * @param array  $certIds
     * @param string $ignoreSchedule
     * @return array
     */
    public function sendGiftcertEmails(array $certIds, $ignoreSchedule = 'default')
    {
        $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addIdFilter($certIds);

        return $this->sendCollection($giftcerts, $ignoreSchedule);
    }

    /**
     * @param Unirgy_GiftCert_Model_Mysql4_Cert_Collection $collection
     *
     * @param string                                       $schedule
     * @return array
     */
    public function sendCollection($collection, $schedule = 'default')
    {
        $grouped     = array();
        $emailsSent  = 0;
        $certsSent   = 0;
        $oldCerts    = 0;
        $errorEmails = 0;
        foreach ($collection as $cert) {
            $oldSchedule = $cert->getData('send_on');
            if ($schedule == 'ignore' && $oldSchedule) {
                $cert->setData('send_on', now(true));
            }
            /* @var $cert Unirgy_Giftcert_Model_Cert */
            $certsSent++;
            $itemId = $cert->getOrderItemId();
            if (empty($itemId)) {
                $this->sendManualEmail($cert); // no order id, send using manual option
                if ($this->isLastSendSuccessful())
                    $emailsSent++;
                else
                    $errorEmails++;
                continue;
            }
            if (empty($grouped[$itemId])) {
                $item = Mage::getModel('sales/order_item')->load($itemId);
                //$item->setOrder(Mage::getModel('sales/order')->load($item->getOrderId()));
                $grouped[$itemId]['item'] = $item;
            }
            $grouped[$itemId]['certs'][$cert->getId()] = $cert;
        }

        foreach ($grouped as $g) {
            $this->sendOrderItemEmail($g['item'], $g['certs']);
            if ($this->isLastSendSuccessful())
                $emailsSent++;
            else
                $errorEmails++;
        }

        return array('emails' => $emailsSent, 'certs' => $certsSent, 'old' => $oldCerts, 'errors' => $errorEmails);
    }

    /**
     * @param Mage_Core_Model_Email_Template $email
     * @param Varien_Object                  $data
     * @return void
     */
    public function addPdfPrintouts(Mage_Core_Model_Email_Template $email, Varien_Object $data)
    {
        $gc_numbers = (array)$data->getGcNumbers();
        foreach ($gc_numbers as $cert_number => $value) {
            $cert = $this->getCertificateModel($cert_number);
            if ($cert->getId()) {
                $data->setData('gc', $cert);
                $this->attachPdfPrintout($email, $data);
            }
        }
    }

    /**
     * @param $cert_number
     * @return Unirgy_Giftcert_Model_Cert
     */
    public function getCertificateModel($cert_number)
    {
        return Mage::getModel('ugiftcert/cert')->load($cert_number, 'cert_number');
    }

    public function attachPdfPrintout(Mage_Core_Model_Email_Template $email, Varien_Object $data)
    {
        $pdf = $this->getDataHelper()->getPdfPrintout($data);
        if ($pdf) {
            $pdfData          = $pdf->render();
            $attach           = $email->getMail()->createAttachment($pdfData);
            $attach->type     = 'application/pdf';
            $attach->filename = $data->getData('gc')->getCertNumber() . '.pdf';
        }
    }

    public function isLastSendSuccessful()
    {
        return $this->_sent_success;
    }

    /**
     * @return Unirgy_Giftcert_Helper_Data
     */
    protected function getDataHelper()
    {
        if (!isset($this->_data_helper)) {
            $this->_data_helper = Mage::helper('ugiftcert');
        }

        return $this->_data_helper;
    }

    protected function setDataHelper(Mage_Core_Helper_Abstract $helper)
    {
        $this->_data_helper = $helper;
    }

    protected function setSentSuccess($success)
    {
        $this->_sent_success = (boolean)$success;
    }

    private function debugEmail($data)
    {
        $this->addError($data);
        if (!Mage::getIsDeveloperMode()) {
            return;
        }
        if ($this->isLastSendSuccessful() === false) {
            Mage::log("Email error, details follow:", Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::log($data, Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }
    }

    private function addError($data)
    {
        if (isset($_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_METHOD'])) { // invoked by browser
            Mage::getSingleton('admin/session')->addError((string)$data);
        } else {
            Mage::log((string)$data, Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }
    }
}
