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
 * to sub@i95dev.com so we can send you a copy immediately.
 *
 * @category       I95DevConnect
 * @package        I95DevConnect_Base
 * @Description    Model for getting information from GP for all supported operations
 * @author         I95Dev
 * @copyright      Copyright (c) 2013 i95Dev
 * @license        http://store.i95dev.com/LICENSE-M1.txt
 */

/**
 * Model for perfoming all webservices supported operations
 */
class I95DevConnect_Base_Model_BaseConnect_Api extends Mage_Api_Model_Resource_Abstract
{
    //Defining constans for different payment methods
//@TODO need make this comptiable methods for all services

    const ACTION_CASH_DEPOSIT = 'CashDeposit';
    const ACTION_CHEQUE_DEPOSIT = 'checkmo';
    const ACTION_CREDIT_CARD_DEPOSIT = 'CreditCardDeposit';
    const ACTION_CREDIT_CARD_PAYMENT = 'ccsave';
    const ACTION_CREDIT_LIMITS = 'creditlimits';
    const ACTION_PURCHASE_ORDER = 'purchaseorder';
    const ACTION_CASH_ON_DELIVERY = 'cashondelivery';
    const ACTION_CUSTOMER_CREDITS = 'customercredits';
    const PROCESS_ID = 'orderProcess';

    private $_indexProcess; //private to public

    /**
     * Subscrption system
     *
     */
    public function apiInfo()
    {
        $result = array();
        $subDetails = Mage::helper('I95DevConnect_Base/Subscription')->getSubscriptionDetails();
        $uuid = $subDetails['customerId'];
        $result['customeruuid'] = base64_encode($uuid);
        $key = $subDetails['subscriptionKey'];
        $result['subscriptionkey'] = base64_encode($key);
        return $result;
    }

    /**
     * Gets current store id
     * @return int 
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    /**
     * Gets current website id
     * @return int 
     */
    public function getWebsiteId()
    {
        return Mage::app()->getStore()->getWebsiteId();
    }

    /**
     * This function should be called when ever we are calling save method on the object
     * which is the event  we are listing to avoid the looping issue
     * @param string $value
     */
    public function normilizeRequest($value = '')
    {
        Mage::app()->getRequest()->setParam('I95DEV_TEST', $value);
    }

    /**
     * Creates or updates customer
     * @param entity $customerData
     * @return boolean
     */
    public function createCustomer($customerData)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }
        
        try
        {
            $className = $helper->getMapperClassByEntity('CustomerApi');
            $mapperModel = mage::getModel($className);
            $this->normilizeRequest(true);
            //To decrypt target response
            $decryptResponse = Mage::helper('I95DevConnect_Base')->decryptString($customerData);
            
            $helper->i95devLog(__METHOD__, $decryptResponse, "i95devApi");
            
            $addressData = $decryptResponse->addresses;

            $componentHelper = Mage::helper('I95DevConnect_Base/Config');
            $component = $componentHelper->getConfigValues()->getcomponent();

            $customer = $mapperModel->setCustomer($decryptResponse);
            if (!$customer->getOrigin())
            {
                $customer->setOrigin(strtoupper($component));
            }
            $this->normilizeRequest(true);
            //If customer email already exists in magento
            try
            {
                $customer->save();
            }
            catch (Exception $ex)
            {
                $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
                //Inserting failed request data nto i95dev error reports table
                Mage::getModel('I95DevConnect_Base/Utils_Generic')->saveTargetExceptionData($decryptResponse, 'customerData', $ex->getMessage());
            }
            $this->normilizeRequest();
            $customerId = $customer->getId();
            //Deleting entries from i95dev error reports table which are synced to magento
            if (isset($customerId) && $customerId != '')
            {
                Mage::getModel('I95DevConnect_Base/Utils_Generic')->deleteSyncedData($decryptResponse->targetCustomerId, 'customerData');
            }
            if ($customerId)
                $this->createAddress($addressData, $customerId);
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
            return $ex->getMessage();
            
        }
        return $customerId;
    }

    /**
     * Creates or updates customer addresses
     * @param array $addressData
     * @param string $customerId
     * @return boolean
     */
    public function createAddress($addressData, $customerId)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        try
        {
            $customerAddress = null;
            $existingAddressIds = array();
            $addressesCollection = mage::getModel('customer/customer')->load($customerId)->getAddressesCollection()
                    ->addAttributeToSelect('target_address_id')
                    ->getItems();

            //Getting existing  target address ids with combination of magento customer address ids
            foreach ($addressesCollection as $address)
            {
                $existingAddressIds [$address->getEntityId()] = $address->getTargetAddressId();
            }


            for ($i = 0; $i < sizeof($addressData); $i++)
            {

                if (!$addressData[$i]->firstName)
                    return true;

                $regionCode = $addressData[$i]->regionId;
                $countryCode = $addressData[$i]->countryId;
                if (strlen($regionCode) > 3)
                {
                    //getting region name & id based on the name
                    $regionModel = Mage::getModel('directory/region')->loadByName($regionCode, $countryCode);
                }
                else
                {
                    //getting region name & id based on the code
                    $regionModel = Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode);
                }
                $regionName = $regionModel->getName();
                //In case of creating address other than United States
                if (!isset($regionName))
                {
                    $regionName = $addressData[$i]->region;
                }
                $regionId = $regionModel->getId();
                if (in_array($addressData[$i]->targetAddressId, $existingAddressIds))
                {
                    $helper->i95devLog(__METHOD__, "updating address" . $addressData[$i]->targetAddressId, "i95devApi");
                    //updating exisntg addresses
                    $addressId = array_search($addressData[$i]->targetAddressId, $existingAddressIds);
                    $customerAddress = Mage::getModel('customer/address')->load($addressId);
                }
                else
                {
                    $helper->i95devLog(__METHOD__, "creating address" . $addressData[$i]->targetAddressId, "i95devApi");
                    //create new address    
                    $customerAddress = Mage::getModel('customer/address');
                }
                $customerAddress->firstname = $addressData[$i]->firstName;
                $customerAddress->lastname = $addressData[$i]->lastName;
                $customerAddress->country_id = $addressData[$i]->countryId;
                $customerAddress->region_id = $regionId;
                $customerAddress->region = $regionName;
                $customerAddress->city = $addressData[$i]->city;
                $customerAddress->street = array($addressData[$i]->street, $addressData[$i]->street2);
                $customerAddress->postcode = $addressData[$i]->postcode;
                $customerAddress->telephone = $addressData[$i]->telephone;
                $customerAddress->target_address_id = $addressData[$i]->targetAddressId;
                $customerAddress->is_default_billing = $addressData[$i]->isDefaultBilling;
                $customerAddress->is_default_shipping = $addressData[$i]->isDefaultShipping;
                $customerAddress->prefix = $addressData[$i]->prefix;
                $customerAddress->company = $addressData[$i]->company;
                //To update fax number in billing address 
                if ($addressData[$i]->fax)
                {
                    $customerAddress->fax = $addressData[$i]->fax;
                }
                $customerAddress->setCustomerId($customerId);
                $this->normilizeRequest(true);
                $customerAddress->save();
                $this->normilizeRequest();
            }
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }

        return true;
    }

    /**
     * Retrieve product
     * @param int $productId
     * @param string|int $store
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct($productId)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        try
        {
            $product = Mage::getModel('catalog/product')->setStoreId(1);
            $idBySku = $product->getIdBySku($productId);
            if ($idBySku)
            {
                $productId = $idBySku;
            }
            $product->load($productId);
            /* @var $product Mage_Catalog_Model_Product */
            if (!$product->getId())
            {
                Mage::throwException('product_not_exists');
            }
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $product;
    }

    /** 	
     * Update product data	 
     * @param array $productData
     * @param string|int $store
     * @return boolean
     */
    public function updateProduct($prodData)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        $stockLevelQty = 0;
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return false;
        }

        //To decrypt target response
        $productData = Mage::helper('I95DevConnect_Base')->decryptString($prodData);
        $sku = $productData->sku;
        $product = $this->_initProduct($sku);
        $stockLevelQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        if (!$product->getId())
        {
            return false;
        }
        $isBackorder = $product->getStockItem()->getBackorders();
        if (!$stockData = $product->getStockData())
        {
            $stockData = array();
        }
        if (isset($productData->qty))
        {
            $stockData['qty'] = $productData->qty;
        }
        if ($productData->isInStock || $isBackorder || $stockLevelQty)
        {
            $stockData['is_in_stock'] = 1;
        }
        else
        {
            $stockData['is_in_stock'] = 0;
        }
        if (isset($productData->manageStock))
        {
            $stockData['manage_stock'] = $productData->manageStock;
        }
        if (isset($productData->useConfigManageStock))
        {
            $stockData['use_config_manage_stock'] = $productData->useConfigManageStock;
        }
        if (isset($productData->backorders))
        {
            $stockData['backorders'] = $productData->backorders;
        }
        $product->setStockData($stockData);
        $product->setCost($productData->cost);
        $product->setPrice($productData->price);
        $product->setWeight($productData->weight);
        try
        {
            Mage::app()->getRequest()->setParam('i95product', "saveResponse");
            $product->save();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return true;
    }

    /**
     * Creates an order
     * @param type $customerData
     * @return string 
     */
    public function createOrder($orderData)
    {    
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }
        
        //To Display failed RMS request data in i95dev RMS TO Magento error reports
        Mage::register('targetorder', "target");
        try
        {
			$processOrderId = "";
            //If the response is encrypted /json encrypted
            $orderResponse = Mage::helper('I95DevConnect_Base')->decryptString($orderData);
            $processOrderId = $orderResponse->targetOrderId;
            $this->_indexProcess = new Mage_Index_Model_Process();
            $this->_indexProcess->setId(self::PROCESS_ID . $processOrderId);
            $this->_indexProcess->lockAndBlock();            
            $helper->i95devLog(__METHOD__, $orderResponse, "i95devApi");
            $OrderObj = "";
            $OrderObj = Mage::getModel('sales/order')->loadByAttribute('target_order_id', $orderResponse->targetOrderId);
            if ($OrderObj->getEntityId())
            {
                //Checking whether the order totals are equal or not on both side(Magento & GP/RMS),If matched deleteing error report entry from table
                if (isset($orderResponse->orderDocumentAmount) && $orderResponse->orderDocumentAmount != '')
                {
                    Mage::helper('I95DevConnect_Base')->compareTargetValue($OrderObj->getBaseGrandTotal(), $orderResponse->orderDocumentAmount, $OrderObj->getIncrementId(), $OrderObj->getCreatedAt(), $flag = true);
                }
                return 'Order Exists';
            }
            $targetOrderStatus = (isset($orderResponse->targetOrderStatus) ? $orderResponse->targetOrderStatus : 'New');
            $helper = Mage::helper('I95DevConnect_Base');

            $componentHelper = Mage::helper('I95DevConnect_Base/Config');
            $component = $componentHelper->getConfigValues()->getcomponent();

            $className = $helper->getMapperClassByEntity('OrderApi');
            $mapperModel = mage::getModel($className);
            $orderObject = $mapperModel->setOrderData($orderResponse);
            $orderId = mage::getModel('I95DevConnect_Base/BaseConnect_Api_OrderApi')->createOrder($orderObject);
           
            if($orderId != "")
            {
            	$orderObj = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            	$orderObj->setTargetOrderId($orderResponse->targetOrderId);
            	$orderObj->setTargetOrderStatus($targetOrderStatus);
            	$orderObj->setOrigin(strtoupper($component));
            	if (isset($orderResponse->chequeNumber))
            		$orderObj->getPayment()->setTargetChequeNumber($orderResponse->chequeNumber);
            	$this->normilizeRequest(true);
            	$orderObj->save();
            	//Checking whether the order totals are equal or not on both side(Magento & GP/RMS),If matched deleteing error report entry from table
            	if (isset($orderResponse->orderDocumentAmount) && $orderResponse->orderDocumentAmount != '')
            	{
            		Mage::helper('I95DevConnect_Base')->compareTargetValue($orderObj->getBaseGrandTotal(), $orderResponse->orderDocumentAmount, $orderObj->getIncrementId(), $orderObj->getCreatedAt(), $flag = false);
            	}
            	$this->normilizeRequest();
            }
            else
            {
            	//Inserting failed request data from GP to Magento into i95dev error reports table
               Mage::getModel('I95DevConnect_Base/Utils_Generic')->saveTargetExceptionData($orderResponse, 'orderData', 'Invalid Shipping/Payment Method');
            	return 'Invalid Shipping/Payment Method';
            }
            $this->_indexProcess->unlock(); 
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $orderId;
    }

    /**
     * Updates the target order status
     * @param array $orderStaus
     * @return boolean
     */
    public function orderStatusUpdate($orderStatus)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        try
        {
            //To decrypt target response
            $statusResponse = Mage::helper('I95DevConnect_Base')->decryptString($orderStatus);
            $className = $helper->getMapperClassByEntity('OrderStatusApi');
            $mapperModel = mage::getModel($className);
            $this->normilizeRequest(true);
            $orderStatus = $mapperModel->orderStatusUpdate($statusResponse);
            $this->normilizeRequest();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $orderStatus;
    }

    /**
     * Creates Invoice
     * @param entity $orderInvoice
     * @return string
     */
    public function createInvoice($orderInvoice)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        try
        {
            //To decrypt target response
            $invoiceResponse = Mage::helper('I95DevConnect_Base')->decryptString($orderInvoice);
            $helper->i95devLog(__METHOD__, $invoiceResponse, "i95devApi");
            $className = $helper->getMapperClassByEntity('CreateInvoiceApi');
            $mapperModel = mage::getModel($className);
            $this->normilizeRequest(true);
            $invoiceId = $mapperModel->createInvoice($invoiceResponse);
            $this->normilizeRequest();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $invoiceId;
    }

    /**
     * Creates Shipment
     * @param entity $orderShipment
     * @return string
     */
    public function createShipment($orderShipment)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        try
        {
            //To decrypt target response
            $shipmentResponse = Mage::helper('I95DevConnect_Base')->decryptString($orderShipment);
            $helper->i95devLog(__METHOD__, $shipmentResponse, "i95devApi");
            $className = $helper->getMapperClassByEntity('CreateShipmentApi');
            $mapperModel = Mage::getModel($className);
            $this->normilizeRequest(true);
            $shipmentId = $mapperModel->createShipment($shipmentResponse);
            $this->normilizeRequest();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $shipmentId;
    }

    /**
     * Creates product in magento
     * @param array $productData
     * @return string $productId
     */
    public function createProduct($productData)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        try
        {
            //To decrypt target response
            $productResponse = Mage::helper('I95DevConnect_Base')->decryptString($productData);
            
            $helper->i95devLog(__METHOD__, $productResponse, "i95devApi");
            $className = $helper->getMapperClassByEntity('CreateProductApi');
            $mapperModel = mage::getModel($className);
            $this->normilizeRequest(true);
            $productId = $mapperModel->createProduct($productResponse);
            $this->normilizeRequest();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $productId;
    }

    /**
     * Creates Customer Group in magento
     * @param array $customerGroupData
     * @return string $result
     */
    public function createCustomerGroup($customerGroupData)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }
        
        try
        {
            //To decrypt target response
            $groupResponse = Mage::helper('I95DevConnect_Base')->decryptString($customerGroupData);
            $helper->i95devLog(__METHOD__, $groupResponse, "i95devApi");
            $className = $helper->getMapperClassByEntity('CreateCustomerGroupApi');
            $mapperModel = mage::getModel($className);
            $this->normilizeRequest(true);
            $result = $mapperModel->createCustomerGroup($groupResponse);
            $this->normilizeRequest();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $result;
    }

    /**
     * get tier prices from Magento
     * @param array $productData
     * @return string $productId
     */
    public function updateTierPrice($productId, $tierPrices)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        try
        {
            //To decrypt target response
            $tierpriceResponse = Mage::helper('I95DevConnect_Base')->decryptString($tierPrices);
            $helper->i95devLog(__METHOD__, $tierpriceResponse, "i95devApi");
            $decryptproductId = Mage::helper('I95DevConnect_Base')->decryptString($productId);
            $helper->i95devLog(__METHOD__, $decryptproductId, "i95devApi");
            $className = $helper->getMapperClassByEntity('CreateProductApi');
            $mapperModel = mage::getModel($className);            
            $product = $this->_initProduct($decryptproductId);
             if (!$product->getId())
             {
                $helper->i95devLog(__METHOD__, $decryptproductId . ' product does not exist in magento', "i95devApiException");
                return 'product does not exist in magento';
             }
               $this->normilizeRequest(true);
                $productId = $mapperModel->updateTierPrice($decryptproductId, $tierpriceResponse);           
            $this->normilizeRequest();
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $productId;
    }

    /**
     * Creates or updates customer in Gp from out of magento with magento id
     * @param entity $customerData
     * @return boolean
     */
    public function createCustomerGp($customerId)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }
        $helper->i95devLog(__METHOD__, $customerId, "i95devApi");
        $isValidCustomer = $this->_validateCustomer($customerId);
        if (!$isValidCustomer)
        {
            $helper->i95devLog(__METHOD__, 'No customer Exist with given entity id', "i95devApi");
            return;
        }
        try
        {
            
            $customerData['entity_id'] = $customerId;

            $args = array('entity' => 'Customer', 'param' => 'customerEntity', 'customerdata' => $customerData);

            $initServiceModel = Mage::getModel('I95DevConnect_Silver/Observers_Observer');
            $result = $initServiceModel->initiateService("createCustomer", $args);
            if ($result->Result == 1)
                $helper->i95devLog(__METHOD__, ' Status : Synced ; TargetCustomerId : ' . $result->CreateCustomerResult->TargetCustomerId, "i95devApi");
            else
                $helper->i95devLog(__METHOD__, ' Status : unSynced ', "i95devApi");
        }
        catch (Exception $ex)
        {
			$helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
            return $ex->getMessage();
            
        }
        return $customerId;
    }

    /**
     * Validate customer id wich is taken from out of magento 
     * @param int $customerId
     * @param string|int 
     * @return boolean
     */
    public function _validateCustomer($customerId)
    {
        $customerExist = Mage::getModel('customer/customer')->load($customerId)->getId();
        if ($customerExist)
            return true;
        else
            return false;
    }

    /**
     * Creates oreder in Gp from out of magento with magento id
     * @param $orderId
     * @return boolean
     */
    public function createOrderGp($orderId)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }
        $helper->i95devLog(__METHOD__, $orderId, "i95devApi");
        $isValidOrder = $this->_validateOrder($orderId);
        if (!$isValidOrder)
        {
            $helper->i95devLog(__METHOD__, 'No Order Exist with given entity id', "i95devApi");
            return;
        }
        try
        {
            
            $orderData = array('orderId' => $orderId, 'order' => '');
            $args = array('entity' => 'Order', 'param' => 'orderEntity', 'orderdata' => $orderData);
            $initServiceModel = Mage::getModel('I95DevConnect_Silver/Observers_Observer');
            $result = $initServiceModel->initiateService("CreateOrder", $args);
            if ($result->Result == 1)
                $helper->i95devLog(__METHOD__, ' Status : Synced ; TargetOrderId : ' . $result->CreateOrderResult->TargetOrderId, "i95devApi");
            else
                $helper->i95devLog(__METHOD__, ' Status : unSynced ', "i95devApi");
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $orderId;
    }

    /**
     * Validate order
     * @param int $orderId
     * @param string|int 
     * @return boolean
     */
    public function _validateOrder($orderId)
    {
        $orderExist = Mage::getModel('sales/order')->load($orderId)->getId();
        if ($orderExist)
            return true;
        else
            return false;
    }

    /**
     * Creates product in Gp  witch is taken from out of magento with magento id
     * @param string $productId
     * @return string $productId
     */
    public function createProductGp($productId)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        if (!Mage::helper('I95DevConnect_Base')->isEnabled())
        {
            return 'Extension Disabled';
        }

        $isValidProduct = $this->_validateProduct($productId);
        if (!$isValidProduct)
        {
            $helper->i95devLog(__METHOD__, "No Product Exist with given entity id", "i95devApi");
            return;
        }
        try
        {
            $productData['entity_id'] = $productId;
            $args = array('entity' => 'Product', 'param' => 'itemEntity', 'productdata' => $productData);
            $initServiceModel = Mage::getModel('I95DevConnect_Platinum/Observers_Observer');
            $result = $initServiceModel->initiateService("CreateProduct", $args);
            if ($result->Result == 1)
                $helper->i95devLog(__METHOD__, "Status : Synced ; TargetItemId " . $result->CreateProductResult->TargetItemId, "i95devApi");
            else
                $helper->i95devLog(__METHOD__, "Status : unSynced ", "i95devApi");
        }
        catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devApiException");
        }
        return $productId;
    }

    /**
     * Validate product which is taken from out source of magento 
     * @param int $productId
     * @param string|int 
     * @return boolean
     */
    public function _validateProduct($productId)
    {
        $productExist = Mage::getModel('catalog/product')->load($productId)->getId();
        if ($productExist)
            return true;
        else
            return false;
    }

    /**
     * Method to retrieve the token from the database of this domain for current date.
     *
     * 
     */
    public function getToken()
    {
        $productSku = Mage::helper('I95DevConnect_Base/Subscription')->getProductSku();
        if (Mage::helper('I95DevConnect_Base/Subscription')->validateToken($productSku))
            return Mage::helper('I95DevConnect_Base/Subscription')->getToken();
    }

}

?>
