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
class Unirgy_Giftcert_Model_Observer
{
    /**
     * GC used in order
     *
     * @var array
     */
    protected $_orderUpdates = array();

    /**
     * Whether the order contains new GCs
     *
     * @var boolean
     */
    protected $_newGcs = false;

    protected $_orderSaved = false;

    /**
     * Whether the GC items were invoiced and paid
     *
     * @var boolean
     */
    protected $_gcInvoiced = false;

    /**
     * Catch GC codes applied in cart
     *
     * @param mixed $observer
     */
    public function controller_action_predispatch_checkout_cart_couponPost($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp = Mage::helper('ugiftcert');

        $code    = trim($action->getRequest()->getParam('coupon_code'));
        $session = Mage::getSingleton('checkout/session');
        $quote   = $session->getQuote();
        try {
            if ($hlp->addCertificate($code, $quote)) {
                $session->addSuccess(Mage::helper('ugiftcert')
                                         ->__("Gift certificate '%s' was applied to your order.", $code));
                $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            }
        } catch (Unirgy_Giftcert_Exception_Coupon $gce) {
            $session->addError($gce->getMessage());
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
        } catch (Mage_Core_Exception $e) {
            $session->addError($hlp->__("Gift certificate '%s' could not be applied to your order.", $code));
            $session->addError($e->getMessage());
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
        } catch (Exception $e) {
            return;
        }
    }

    /**
     *  Add certificate amount to quote discount total
     *
     * Paypal checkout express collects base_subtotal and not grand total.
     * when certificate is used, it is not considered as discount and not subtracted
     * from base_subtotal, this returns error from paypal.
     *
     * Here we add certificate amount to discount amount. We need to do this once
     * with quote as $saleObject and once with order.
     *
     * @param $observer
     * @return void
     */
    public function paypal_prepare_line_items($observer)
    {
        if (!Mage::helper('ugiftcert')->checkDomain($_SERVER['HTTP_HOST'])) return;
        Mage::helper('ugiftcert/protected')->paypal_prepare_line_items($observer);
    }

    /**
     * Add certificate amount to discount amount
     *
     * @param $observer
     * @return void
     */
    public function google_checkout_discount_item_price($observer)
    {
        if (!Mage::helper('ugiftcert')->checkDomain($_SERVER['HTTP_HOST'])) return;
        Mage::helper('ugiftcert/protected')->google_checkout_discount_item_price($observer);
    }

    /**
     * In case customer's quote is loaded, remember current GC codes
     *
     * @param mixed $observer
     */
    public function controller_action_predispatch_customer_account_loginPost($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setGiftcertCode($session->getQuote()->getGiftcertCode());
    }

    /**
     * Restore and merge GC codes for customer's quote
     *
     * @param mixed $observer
     */
    public function customer_login($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        if ($session->getGiftcertCode()) {
            $gc1 = preg_split('#\s*,\s*#', $session->getGiftcertCode(true));
            $gc2 = preg_split('#\s*,\s*#', $session->getQuote()->getGiftcertCode());
            $gc  = join(',', array_unique(array_merge($gc1, $gc2)));
            $session->getQuote()->setGiftcertCode($gc)->save();
        }
    }

    /**
     * Trying not to overload sales/quote ...
     *
     * @param mixed $observer
     */
    public function apply_quote_item_to_products($observer)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $item->getProduct()->setQuoteItem($item);
        }
    }

    public function sales_order_payment_place_start($observer)
    {
        $order = $observer->getEvent()->getPayment()->getOrder();

        if (!$order) {
            return $this;
        }

        // new GCs were purchased
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'ugiftcert') {
                $this->_newGcs = true;
                break;
            }
        }

        if ($this->_orderSaved) {
            // Order has been saved before this method is called, so no new GCs are added
            $data = $this->_getDefaultGcData($order);
            $this->_addGcs($order, $data);
            $this->_orderSaved = false; // make sure that this branch won't be executed again
        }

        $certIds = explode(',', $order->getGiftcertCode());
        $certIds = array_unique($certIds);

        $totalAmount     = $order->getGiftcertAmount();
        $baseTotalAmount = $order->getBaseGiftcertAmount();

        $this->_orderUpdates = array();
        foreach ($certIds as $certId) {
            if (!$certId) {
                continue;
            }
            $cert = Mage::getModel('ugiftcert/cert')->load(trim($certId), 'cert_number');

            if (!$cert->getId() || $cert->getStatus() != 'A' || $cert->getBalance() == 0) {
                continue;
            }

            $baseAmount = min($baseTotalAmount, $cert->getBaseBalance());

            $amount = $baseAmount * $cert->getCurrencyRate();
            $cert->setAmount($amount)->setBalance($cert->getBalance() - $amount);

            if ($cert->getBalance() <= .001) {
                $cert->setStatus('I');
            }
            $this->_orderUpdates[] = $cert;

            $baseTotalAmount -= $baseAmount;
            if ($baseTotalAmount == 0) {
                break;
            }
        }

        if ($baseTotalAmount > 0 && $order->getBaseGrandTotal() == 0) {
            Mage::throwException(Mage::helper('ugiftcert')
                                     ->__('Gift certificates applied to this order have changed. Unable to proceed, please return to shopping cart to see the changes.'));
        }
    }

    public function sales_order_save_after($observer)
    {
        $order   = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $data    = $this->_getDefaultGcData($order, $storeId);

        $this->_updateAppliedGcs($data); // factored out so that we can call this from other methods as well

        if (!$this->_newGcs) {
            $this->_orderSaved = true; // there are no new GCs marked, might be case of "wrong" event order
        }

        $this->_addGcs($order, $data, $storeId); // factored out so that we can call this from other methods as well
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param                        mixed null|int $storeId
     * @return array
     */
    protected function _getDefaultGcData($order, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $order->getStoreId();
        }
        // default history row values
        $data = array(
            'ts'                 => now(),
            'action_code'        => 'order',
            'customer_id'        => $order->getCustomerId() ? $order->getCustomerId() : null,
            'customer_email'     => $order->getCustomerEmail(),
            'order_id'           => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'store_id'           => $storeId,
        );

        return $data;
    }

    protected function _updateAppliedGcs($data)
    { // process applied gift certificates
        foreach ($this->_orderUpdates as $cert) {
            $cert->save();
            $data['amount']        = $cert->getAmount();
            $data['status']        = $cert->getStatus();
            $data['currency_code'] = $cert->getCurrencyCode();
            $cert->addHistory($data);
        }
        $this->_orderUpdates = array();
    }

    const DATE_FORMAT = 'Y-m-d';

    protected function _addGcs($order, $data, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $order->getStoreId();
        }
        // process purchased gift certificates
        if ($this->_newGcs) {
            $config       = Mage::getStoreConfig('ugiftcert/default');
            $reqVars      = array_keys(Mage::helper('ugiftcert')->getGiftcertOptionVars());
            $autoSend     = Mage::getStoreConfig('ugiftcert/email/auto_send', $storeId);
            $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $storeId);

            $data['action_code']   = 'create';
            $data['currency_code'] = $order->getOrderCurrencyCode();
            $data['order_id']      = $order->getId();
            $data['status']        = ($this->_gcInvoiced && $changeStatus) ? 'A' : $config['status'];

            foreach ($order->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                if ($item->getProductType() != 'ugiftcert') {
                    continue;
                }
                /* @var $product Mage_Catalog_Model_Product */
                if ($product = $item->getProduct()) {
                    $product->load($item->getData('product_id'));
                } else {
                    $product = Mage::getModel('catalog/product')->load($item->getData('product_id'));
                }
                $options = $item->getProductOptions();
                $r       = $options['info_buyRequest'];
//                $pdfSettings = $product->getData('ugiftcert_pdf_settings');
                $defaultPdfSettings = $product->getData('ugiftcert_pdf_tpl_id');
                if (array_key_exists('pdf_template', $r)) {
                    $defaultPdfSettings = $r['pdf_template'];
                }
                $conditions           = $product->getData('ugiftcert_conditions');
                $defaultEmailTemplate = $product->getData('ugiftcert_email_template');
                if (array_key_exists('email_template', $r)) {
                    $defaultEmailTemplate = $r['email_template'];
                }
                $data['order_item_id'] = $item->getId();
                $data['amount']        = isset($r['amount']) ? $r['amount'] : $item->getPrice();

                for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                    /* @var $cert Unirgy_Giftcert_Model_Cert */
                    $cert = Mage::getModel('ugiftcert/cert')
                        ->setStatus($data['status'])
                        ->setBalance($data['amount'])
                        ->setCurrencyCode($data['currency_code'])
                        ->setStoreId($storeId);
                    if ($config['auto_cert_number']) {
                        $cert->setCertNumber($config['cert_number']);
                    }
                    if ($config['auto_pin']) {
                        $cert->setPin($config['pin']);
                    }
                    if (($days = intval($config['expire_timespan']))) {
                        $cert->setExpireAt(date('Y-m-d', time() + $days * 86400));
                    }
                    if ($defaultPdfSettings) {
                        $cert->setPdfSettings($defaultPdfSettings);
                    }
                    if ($defaultEmailTemplate) {
                        $cert->setData('template', $defaultEmailTemplate);
                    }

                    if ($conditions) {
                        $cert->getConditions()->setConditions(array())->loadArray(unserialize($conditions));
                    }

                    foreach ($reqVars as $f) {
                        if (!empty($r[$f])) {
                            $cert->setData($f, $r[$f]);
                        }
                    }
                    Mage::app()->dispatchEvent('ugiftcert_cert_create_from_order', array(
                                                                                        'cert'       => $cert,
                                                                                        'data'       => &$data,
                                                                                        'order_item' => $item,
                                                                                   ));
                    $cert->save();
                    $cert->addHistory($data);
                }
                if ((Unirgy_Giftcert_Model_Source_Autosend::ORDER == $autoSend)
                    || ((Unirgy_Giftcert_Model_Source_Autosend::PAYMENT == $autoSend) && $this->_gcInvoiced)
                ) {

                    Mage::helper('ugiftcert/email')->sendOrderItemEmail($item);
                    $item->setQtyShipped($item->getQtyOrdered());
                }
            }
            $this->_newGcs = false;
            if ($this->_gcInvoiced) {
                $order->save();
            }
        }
    }

    public function sales_order_load_after($observer)
    {
        if (!Mage::getStoreConfig('ugiftcert/default/giftcert_order_info')) {
            return;
        }
        try {
            /* @var $order Mage_Sales_Model_Order */
            $order   = $observer->getEvent()->getOrder();
            $cIds    = explode(',', $order->getGiftcertCode());
            $certIds = array();
            foreach ($cIds as $id) {
                $id        = trim($id);
                $certIds[] = $id;
            }
            $certIds = array_unique($certIds);

            /* @var $collection Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
            $collection = Mage::getModel('ugiftcert/cert')->getCollection();
//            $collection->addFieldToSelect(array('cert_number'))
//                ->addFieldToFilter('cert_number', array('in' => $certIds));
            $select = $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('cert_number')
                ->where('cert_number in (?)', $certIds);
            $select
                ->join(array('h' => $collection->getTable('ugiftcert/history')), 'h.cert_id=main_table.cert_id', array(
                                                                                                                      'ts',
                                                                                                                      'amount',
                                                                                                                      'customer_email',
                                                                                                                      'currency_code'
                                                                                                                 ))
                ->where("h.action_code='order'")
                ->join(array('hc' => $collection->getTable('ugiftcert/history')), 'hc.cert_id=main_table.cert_id', array('order_item_id'))
                ->where('hc.action_code="create"')
                ->join(array('s' => $collection->getTable('sales/order_item')), 'hc.order_item_id=s.item_id', array('sku'));
            if ($collection->count()) {
                $gcs = array();
                foreach ($collection as $item) {
                    $data                     = array();
                    $data['certificate_code'] = $item->getData('cert_number');
                    $data['certificate_sku']  = $item->getData('sku');
                    $data['redeemed_at']      = $item->getData('ts');
                    $data['amount_redeemed']  = $item->getData('amount');
                    $data['customer_email']   = $item->getData('customer_email');
                    $data['currency_code']    = $item->getData('currency_code');
                    $gcs[]                    = $data;
                }
                $order->setData('applied_gift_certificates', $gcs);
            }

            $select->reset(Zend_Db_Select::WHERE)->reset(Zend_Db_Select::FROM)->reset(Zend_Db_Select::COLUMNS)
                ->from(array('main_table' => $collection->getResource()->getMainTable()), array('cert_number'))
                ->join(array('hc' => $collection->getTable('ugiftcert/history')), 'hc.cert_id=main_table.cert_id',
                       array('order_item_id', 'ts', 'amount', 'currency_code', 'status', 'customer_email', 'comments'));
            foreach ($order->getAllItems() as $item) {
                if ($item->getData('product_type') != 'ugiftcert') {
                    continue;
                }
                $collection->clear();
                $select->where('hc.action_code="create"')
                    ->where('order_item_id=?', $item->getId());
                $data = array();
                foreach ($collection as $gcItem) {
                    /* @var $gcItem Unirgy_Giftcert_Model_Cert */
                    $data['certificate_code'] = $gcItem->getData('cert_number');
                    $data['amount']           = $gcItem->getData('amount');
                    $data['currency']         = $gcItem->getData('currency_code');
                    $data['status']           = $gcItem->getData('status');
                    if ($data['status'] == 'P') {
                        $data['status'] = 'Pending';
                    } elseif ($data['status'] == 'A') {
                        $data['status'] = 'Active';
                    } else {
                        $data['status'] = 'Inactive';
                    }

                    $data['created']        = $gcItem->getData('ts');
                    $data['customer_email'] = $gcItem->getData('customer_email');
                    $data['comment']        = $gcItem->getData('comment') ? $gcItem->getData('comment') : '';
                }

                $item->setData('certificate_data', $data);
            }
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Send new GC confirmation on invoice pay
     *
     * This event can be launched before sales_order_save_after or after
     *
     * @author vmaillot (Vincent)
     * @param mixed $observer
     */
    public function sales_order_invoice_pay($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order   = $invoice->getOrder();

        // order has been saved already
        if ($order->getId()) {
            $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $order->getStoreId());
            $autoSend     = Mage::getStoreConfig('ugiftcert/email/auto_send', $order->getStoreId());

            if ($changeStatus) {
                /* @var $certs Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
                $certs = Mage::getModel('ugiftcert/cert')->getCollection()
                    ->addOrderFilter($order->getId());
                $data  = $this->_getDefaultGcData($order);
                foreach ($certs->getItems() as $cert) {
                    /* @var $cert Unirgy_Giftcert_Model_Cert */
                    $cert->load($cert->getId()) // load full certificate data because otherwise some weird things happen
                        ->setStatus('A')->save();
                    $data['action']        = 'invoice';
                    $data['amount']        = $cert->getAmount();
                    $data['status']        = $cert->getStatus();
                    $data['currency_code'] = $cert->getCurrencyCode();
                    $cert->addHistory($data);
                }
            }

            if (Unirgy_Giftcert_Model_Source_Autosend::PAYMENT == $autoSend) {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getProductType() != 'ugiftcert') {
                        continue;
                    }
                    Mage::helper('ugiftcert/email')->sendOrderItemEmail($item);
                    $item->setQtyShipped($item->getQtyInvoiced());
                }
            }
        } // order has been invoiced before it was saved - remember and execute on save
        else {
            $this->_gcInvoiced = true;
        }
    }

    /**
     * On payment/order cancel refund GCs
     *
     * @param mixed $observer
     */
    public function sales_order_payment_cancel($observer)
    {
        $payment = $observer->getEvent()->getPayment();
        /* @var $order Mage_Sales_Model_Order */
        $order = $payment->getOrder();

        $this->_refundUsedCertificates($order->getId());
        $this->_disableOrderedCertificates($order->getId());
    }

    /**
     * Check for extension update news
     *
     * @param Varien_Event_Observer $observer
     */
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('ugiftcert/admin/notifications')) {
            try {
                //disable for time being
                Mage::getModel('ugiftcert/feed')->checkUpdate();
            } catch (Exception $e) {
                // silently ignore
            }
        }
    }

    /**
     * Hide E_STRICT for 1.2.x
     *
     * @deprecated
     * @see controller_front_init_before
     * @param mixed $observer
     */
    public function catalog_product_load_before($observer)
    {
        $errorReporting = error_reporting();
        if ($errorReporting & E_STRICT) {
            error_reporting($errorReporting & ~E_STRICT);
        }
    }

    public function catalog_product_load_after($observer)
    {
        $product = $observer->getProduct();
        $this->setProperPrices($product);
    }

    public function catalog_product_collection_load_after($observer)
    {
        $collection = $observer->getCollection();
        foreach ($collection as $product) {
            $this->setProperPrices($product);
        }
    }

    protected function setProperPrices($product)
    {
        if (!$product instanceof Mage_Catalog_Model_Product || $product->getTypeId() != 'ugiftcert') {
            return;
        }
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp   = Mage::helper('ugiftcert');
        $price = $hlp->getPrice($product);
        if ($price) {
            $product->setPrice($price)
                ->setMinPrice(min($price, $product->getMinPrice()))
                ->setMaxPrice($price)
                ->setFinalPrice($price);
            $product->setMinimalPrice(min($price, $product->getMinimalPrice()));
        }
    }

    public function dummy()
    {
    }

    /**
     * Use different version of product type class for 1.2.x
     *
     * @param mixed $observer
     */
    public function controller_front_init_before($observer)
    {
        if (version_compare(Mage::getVersion(), '1.3.0', '<')) {
            Mage::getConfig()->setNode('frontend/events/controller_action_predispatch_checkout/observers/ugiftcert/method',
                                       'apply_quote_item_to_products');
            Mage::getConfig()->setNode('global/catalog/product/type/ugiftcert/model', 'ugiftcert/product_type12');
        } elseif (Mage::helper('ugiftcert')->compareMageVer('1.5.0', '1.10.0')) {
            Mage::getConfig()->setNode('global/catalog/product/type/ugiftcert/model', 'ugiftcert/product_typeCE150');
        }
    }

    /**
     * Support for admin created orders
     *
     * @param mixed $observer
     * @return bool
     */
    public function controller_action_predispatch_adminhtml_sales_order_create_loadBlock($observer)
    {
        $data = Mage::app()->getRequest()->getPost('order');
        if (empty($data['coupon']['code'])) { // if there is no 'coupon' posted, return
            return true;
        }
        /* @var $session Mage_Adminhtml_Model_Session_Quote */
        $session = Mage::getSingleton('adminhtml/session_quote');
        $code    = trim($data['coupon']['code']);
        $quote   = $session->getQuote();

        try {
            $hlp = Mage::helper('ugiftcert');
            if ($hlp->addCertificate($code, $quote)) { // if adding code is successful, add message of it
                $session->addSuccess($hlp->__("Gift certificate '%s' was applied to your order.", $code));
            } else { // else return unchanged data
                return false;
            }
        } catch (Unirgy_Giftcert_Exception_Coupon $gce) {
            // any exception means something went wrong with adding
            // code to quote, 'Unirgy_Giftcert_Exception_Coupon' is thrown when coupon  is applied when it is not
            // allowed
            $session->addError($gce->getMessage());
        } catch (Exception $e) {
            $session->addError($hlp->__("Gift certificate '%s' could not be applied to your order.", $code));
            $session->addError($e->getMessage());
        }
        unset($data['coupon']['code']); // make sure no further processing of the code will be done
        Mage::app()->getRequest()->setPost('order', $data); // update order data

        return true;
    }

    public function sales_quote_collect_totals_before($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setBaseGiftcertBalances('');
        $quote->setGiftcertBalances('');
        foreach ($quote->getAddressesCollection() as $qa) {
            $qa->setGiftcertCode('');
            $qa->setBaseGiftcertBalances('');
            $qa->setGiftcertBalances('');
        }
    }

    public function catalog_product_prepare_save($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() !== 'ugiftcert') {
            return;
        }
        $price = Mage::helper('ugiftcert')->getPrice($product);
        $product->setPrice($price);
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp = Mage::helper('ugiftcert');
//        $product->setData('ugiftcert_pdf_settings', $hlp->loadPdfSettings());
        $conditions = $hlp->loadConditionData();
        if ($conditions) {
            $conditions = $conditions->getConditions();
            if ($conditions instanceof Mage_SalesRule_Model_Rule_Condition_Combine) {
                $product->setData('ugiftcert_conditions', serialize($conditions->asArray()));
            }
        }
    }

    public function core_block_abstract_to_html_before($observer)
    {
        $block = $observer->getBlock();
        if (!$block instanceof Mage_Checkout_Block_Cart_Sidebar) {
            return;
        }
        $block->getQuote()->collectTotals();
    }

    /**
     * During order refund, this method should:
     * 1. Find all certificates used in this order and restore amounts used to them, set them Active
     * 2. Deactivate any certificates purchased by the order.
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function sales_order_creditmemo_refund($observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getData('creditmemo');
        $this->_refundUsedCertificates($creditmemo->getOrder()->getId());
        $this->_disableOrderedCertificates($creditmemo->getOrder()->getId());
    }

    /**
     * Get all certificates that were used for paying for an order and restore paid amounts.
     * @param int $orderId
     */
    protected function _refundUsedCertificates($orderId)
    {
        /*
         * Get history collection of certificates that were used for order payment
         */
        $gcHistory = Mage::getModel('ugiftcert/history')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('action_code', 'order');

        if (!$gcHistory->count()) {
            return;
        }

        /*
         * Iterate history, try to load corresponding certificate,
         * if certificate is found, set action refund, set active (A),
         * get amount from order history object and set it as balance to certificate
         */
        foreach ($gcHistory as $gch) {
            /* @var $cert Unirgy_Giftcert_Model_Cert */
            $cert = Mage::getModel('ugiftcert/cert')->load($gch->getCertId());
            if (!$cert->getId()) continue;

            $data                = $gch->getData();
            $data['history_id']  = null;
            $data['action_code'] = 'refund';
            $data['ts']          = now();
            $data['status']      = 'A';
            $cert->addHistory($data);

            $amount  = Mage::helper('directory')->currencyConvert(
                $gch->getAmount(),
                $gch->getCurrencyCode(),
                $cert->getCurrencyCode()
            );
            $balance = $cert->getBalance() + $amount;
            $cert->setStatus('A')->setBalance($balance)->save();
        }
    }

    /**
     * Get history where certificates are created from this order and set their status inactive.
     * @param int $orderId
     */
    protected function _disableOrderedCertificates($orderId)
    {
        /*
         * Get history collection of certificates that were created from order
         */
        $gcHistory = Mage::getModel('ugiftcert/history')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('action_code', 'create');

        if (!$gcHistory->count()) {
            return;
        }

        /*
         * Iterate history, try to load corresponding certificate,
         * if certificate is found, set action 'cancel', set inactive (I)
         */
        foreach ($gcHistory as $gch) {
            /* @var $cert Unirgy_Giftcert_Model_Cert */
            $cert = Mage::getModel('ugiftcert/cert')->load($gch->getCertId());
            if (!$cert) continue;

            $data                = $gch->getData();
            $data['history_id']  = null;
            $data['action_code'] = 'cancel';
            $data['ts']          = now();
            $data['status']      = 'I';
            $cert->addHistory($data);
            $cert->setStatus('I')->save();
        }
    }

    public function cron()
    {
        Mage::log(__METHOD__, Zend_Log::INFO, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        $this->sendScheduledCertificates();

        return true;
    }

    public function sendScheduledCertificates()
    {
        $collection = $this->getScheduledCollection();
        if ($collection->count()) {
            Mage::helper('ugiftcert/email')->sendCollection($collection);
            Mage::log(sprintf("%d certificates sent", $collection->count()), Zend_Log::INFO,
                      Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        } else {
            Mage::log("No certificates to send", Zend_Log::INFO, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }
    }

    /**
     * @return Unirgy_GiftCert_Model_Mysql4_Cert_Collection
     */
    public function getScheduledCollection()
    {
        /* @var $collection Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
        $collection = Mage::getModel('ugiftcert/cert')
            ->getCollection()
            ->addFieldToFilter('send_on', array('eq' => new Zend_Db_Expr('DATE(NOW())')))
            ->addHistory();
        $select     = $collection->getSelect();
        $select->join(array('h2' => $collection->getTable('ugiftcert/history')),
                      'h2.cert_id=main_table.cert_id', array('ac' => new Zend_Db_Expr('group_concat(h2.action_code )')))
            ->group('main_table.cert_id');
        foreach ($collection->getItems() as $key => $item) {
            if (strpos($item->getData('ac'), 'email') !== false) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }
}
