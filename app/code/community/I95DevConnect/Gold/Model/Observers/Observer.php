<?php

/**
 * i95Dev.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.i95dev.com/LICENSE-M1.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@i95dev.com so we can send you a copy immediately.
 *
 * @category       I95DevConnect
 * @package        I95DevConnect_Gold
 * @Description    observer will be called after invoice creation of order
 * @author         I95Dev
 * @copyright      Copyright (c) 2013 i95Dev
 * @license        http://store.i95dev.com/LICENSE-M1.txt
 */
class I95DevConnect_Gold_Model_Observers_Observer
{

    /**
     * Constructor checking I95DevConnect is enabled or Not
     */
    public function __construct()
    {
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return;
        }
    }

    /**
     * Observer calling when created invoice for order.
     *  @param Varien_Event_Observer $observer
     *  @return void
     */
    public function salesOrderInvoicePay(Varien_Event_Observer $observer)
    {
        try
        {
            $helper = Mage::helper('I95DevConnect_Base');
            $serviceObject = Mage::getModel('I95DevConnect_Base/service_service');
            /* getting the order id from the invoice */
            $invoice = $observer->getEvent()->getInvoice()->getOrderId();
            $order = mage::getModel('sales/order')->load($invoice);
            $i95DevCanInvoice = $this->i95DevCanInvoice($order);
            if (!$i95DevCanInvoice && $order->getGpOrderprocessFlag() == 0 && $order->getTargetOrderStatus() != 'Shipped')
            {
                Mage::throwException(Mage::helper('core')->__("Can't create Shipment wihout Ready To Ship in GP."));
                return;
            }
                /* support online capture */
                $args = array('entity' => 'Invoice', 'param' => 'orderData', 'invoicedata' => $invoice);
                $serviceResponse = $this->initiateService("InvoiceOrder", $args);
           
            
        }
        catch (ErrorException $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * Observer calling when created shipment for order.
     *  @param Varien_Event_Observer $observer
     *  @return void
     */
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {


        try
        {

            $helper = Mage::helper('I95DevConnect_Base');
            $shipmentData = $observer->getEvent()->getData('data_object')->getData('order_id');
            $order = Mage::getModel('sales/order')->load($shipmentData);
            $args = array('entity' => 'Shipment', 'param' => 'orderData', 'invoicedata' => $shipmentData);
            $method = "ShipmentOrder";

            if ($order->getGpOrderprocessFlag() == 0 && $order->getTargetOrderStatus() != 'Ready To Ship')
            {
                Mage::throwException(Mage::helper('core')->__("Can't create Shipment wihout Ready To Ship in GP."));
                return;
            }


            $serviceResponse = $this->initiateService($method, $args);
        }
        catch (ErrorException $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * initiates SOAP service call
     *
     * @param type $method
     * @param type $args
     * @return type std object
     */
    public function initiateService($method, $args)
    {
        try
        {
            $helper = Mage::helper('I95DevConnect_Base');
            $serviceObject = Mage::getModel('I95DevConnect_Base/service_service');
            return $serviceResponse = $serviceObject->_makeServiceCall($method, $args);
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * Listening to i95dev_order_status_update_after Event
     * @param Varien_Event_Observer $observer
     */
    public function orderStatusUpdateAfter(Varien_Event_Observer $observer)
    {
        try
        {
            $helper = Mage::helper('I95DevConnect_Base');
            $data = $observer->getEvent()->getData();
            $helper->debugLog("======orderStatusUpdateAfter====", $data['orderId'], 'orderStatusUpdateAfter');
            $orderId = $data['orderId'];
            $order = Mage::getModel('sales/order')->load($orderId);
            $targetOrderStatus = $order->getTargetOrderStatus();
            $helper->debugLog("======orderStatusUpdateAfter====", $targetOrderStatus, 'orderStatusUpdateAfter');
            $i95DevCanInvoice = $this->i95DevCanInvoice($order);

            if ($i95DevCanInvoice && $targetOrderStatus == 'Shipped')
            {
                $helper->debugLog("======orderStatusUpdateAfter====", 'if condition', 'orderStatusUpdateAfter');
                $invoiceShipmentData = array();
                $invoiceShipmentData['orderid'] = $orderId;
                $args = array('entity' => 'Invoice', 'param' => 'orderData', 'invoicedata' => $invoiceShipmentData);
                $method = "InvoiceOrder";
                $serviceResponse = $this->initiateService($method, $args);
            }
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * check can invoice
     * @input order object
     */
    public function i95DevCanInvoice($order)
    {
        $paymentData = $order->getPayment()->getData();
        $paymentMethod = $paymentData['method'];
        $authAction = Mage::getStoreConfig('payment/' . $paymentMethod . '/payment_action');
        $paymentActionArray = array('authorize_capture', 'Sale');
        if(in_array($authAction, $paymentActionArray) || $paymentMethod=='creditlimits')
        return true;
        else
        return false;
    }

}

?>
