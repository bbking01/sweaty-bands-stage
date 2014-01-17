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
 * @Description    To perform generic database operations
 * @author         I95Dev
 * @copyright      Copyright (c) 2013 i95Dev
 * @license        http://store.i95dev.com/LICENSE-M1.txt
 */

/**
 * Model for performing different operations on database
 */
class I95DevConnect_Base_Model_Utils_Generic extends Mage_Core_Model_Abstract
{
    const XML_PATH_DEFAULT_TIMEZONE = 'general/locale/timezone';

    /**
     * Retrieve the read connection
     */
    protected function getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Retrieve the write connection
     */
    protected function getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Get the resource model
     * @return string
     */
    protected function getAdapter()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource;
    }

    /**
     * Gets setting.xml data as array by node
     * @param String node
     * @return array 
     */
    public function getSettings($node = null)
    {
        $helper = Mage::helper('I95DevConnect_Base');
        try
        {
            $path = Mage::getModuleDir('etc', 'I95DevConnect_Base');
            $xmlPath = $path . DS . 'settings.xml';
            $xmlObj = new Varien_Simplexml_Config($xmlPath);
            $xmlData = $xmlObj->getNode($node);
        } catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $xmlData->asArray();
    }

    /**
     * function to get dynamic payment class loading 
     * 
     * @param type $paymentMethod
     * @return type 
     */
    public function getPaymentClass($paymentMethod)
    {
        $path = Mage::getModuleDir('lib', 'I95DevConnect_Base') . DS . 'lib' . DS . 'classes' . DS . 'paymentmethods';
        $paymentClassName = "";
        $paymentMethods = $this->getSettings('paymentmethods');
        if (isset($paymentMethods[$paymentMethod]))
        {
            $paymentClassName = $paymentMethods[$paymentMethod]['classname'];
            $fileName = $path . DS . $paymentClassName . '.php';
            if (file_exists($fileName))
            {

                require_once $fileName;
            }
            else
            {
                //TODO throws exception
            }
        }
        return $paymentClassName;
    }

    /**
     * Removes log files reading from setting xml
     * 
     */
    public function cleanlog()
    {
        $helper = Mage::helper('I95DevConnect_Base');
        try
        {
            $xmlData = $this->getSettings('logs');
            $fileNames = explode(",", $xmlData['files']);
            $path = Mage::getBaseDir('log');
            foreach ($fileNames as $name)
            {
                $fileName = $path . DS . $name . '.log';
                if (file_exists($fileName))
                {
                    unlink($fileName);
                }
            }
        } catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     *
     * @param type $store
     * @return type
     */
    protected function _getStoreId($store = null)
    {
        try
        {
            $storeId = Mage::app()->getStore($store)->getId();
        } catch (Mage_Core_Model_Store_Exception $e)
        {
            mage::log("getstoreid", 1, "gpexceptionlog.log");
            mage::log($e->getMessage(), 1, "gpexceptionlog.log");
        }

        return $storeId;
    }

    protected function _initProduct($productId)
    {

        try
        {

            $product = Mage::getModel('catalog/product')
                            ->setStoreId($this->_getStoreId());
            $idBySku = $product->getIdBySku($productId);
            if ($idBySku)
            {
                $productId = $idBySku;
            }
        } catch (Exception $excep)
        {
            
        }

        return $productId;
    }

    /**
     * Saving target system exceptions for error reports
     * @param unknown_type $response
     * @param unknown_type $targetSyncMsg
     */
    public function saveTargetExceptionData($response, $entityClass, $execMsg)
    {
        try
        {

            if ($entityClass == 'checkInventoryData')
            {
                return;
            }
            $helper = Mage::helper('I95DevConnect_Base');
            //To Display failed RMS request data in i95dev RMS TO Magento error reports
            $registryData = Mage::registry('targetorder');
            //In case of inserting the order which orders total are mismatched
            if (is_array($response))
                $response = (object) $response;
            if (isset($response->entity_id) || isset($response->SourceOrderId))
                $SourceOrderId = (isset($response->entity_id) ? $response->entity_id : $response->SourceOrderId);
            $errorMessage = "";
            $schedulerName = $entityClass;
            $errorMessage = $execMsg;
            $entityId = "Subscription";
            /* it is configured time zone date , this date we can use for send email purpose
             * do not use for store in tables , when it comes to disply it will be changed.             *   
             */
            $createdDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
            $name = "";
            $status = 0;
            $tableName = 'i95dev_failed_schedular_details';
            $helper->i95devLog(__METHOD__, 'in saveTargetExceptionData' . $execMsg, "Errorreport");
            $flag = $this->getWriteAdapter()->isTableExists(trim($tableName, '`'));
            if ($flag == '1')
            {
                if ('customerData' == $schedulerName)
                {
                    if (is_object($response))
                    {
                        $entityId = (isset($response->targetCustomerId) ? $response->targetCustomerId : $response->SourceCustomerId);
                        $customer = Mage::getModel('customer/customer')->load($entityId);
                        $createdAt = $customer->getCreatedAt();
                        if(strtotime($createdAt)==''){
                         $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
                          $createdAt = date('Y-m-d', $currentTimestamp);
                        }
                        $firstName = (isset($response->FirstName) ? $response->FirstName : $response->firstName);
                        $lastName = (isset($response->LastName) ? $response->LastName : $response->lastName);
                        $name = $firstName . " " . $lastName;
                        $status = (isset($response->targetCustomerId) ? 1 : 0);
                        $template = "sales_customer_fail_update_template";

                        $emailTemplateVariables['user_name'] = 'Admin';
                        $emailTemplateVariables['customer_id'] = $entityId;
                        $emailTemplateVariables['customer_name'] = $name;
                        $emailTemplateVariables['gp_error_message'] = $errorMessage;
                        Mage::helper('I95DevConnect_Base')->sendMail($template, $emailTemplateVariables);
                    }
                }
                else if ('orderData' == $schedulerName)
                {
                    if (is_object($response))
                    {
                        $customerEntity = "";
                        if (property_exists($response, 'customerEntity'))
                        {
                            $customerEntity = $response->customerEntity;
                        }
                        $store = null;
                        $customerData = (isset($response->Customer) ? $response->Customer : $customerEntity);
                        $entityId = (isset($response->targetOrderId) ? $response->targetOrderId : $SourceOrderId);


                        if (is_object($customerData))
                            $customerFirstName = (isset($customerData->FirstName) ? $customerData->FirstName : "");

                        if (is_object($customerData))
                            $customerLastName = (isset($customerData->LastName) ? $customerData->LastName : "");
                        $firstName = (isset($response->customer_firstname) ? $response->customer_firstname : $customerFirstName);
                        $lastName = (isset($response->customer_lastname) ? $response->customer_lastname : $customerLastName);
                        $name = $firstName . " " . $lastName;
                        if ($registryData == 'target')
                        {
                            $status = 1;
                        }
                        else
                        {
                            $status = 0;
                        }
                        $template = "sales_order_fail_update_template";

                        $emailTemplateVariables['user_name'] = 'Admin';
                        $order = mage::getModel('sales/order');
                        if (isset($SourceOrderId))
                        {
                            $order->load($SourceOrderId);
                        }
                        else if (isset($response->targetOrderId))
                        {
                            $order->loadByAttribute('target_order_id', $response->targetOrderId);
                        }
                        $createdAt = $order->getCreatedAt();
                        $incrementId = $order->getIncrementId();
                        $granTotal = $order->getData('grand_total');
                        $emailTemplateVariables['order_number'] = $incrementId;
                        $emailTemplateVariables['order_total'] = $order->getData('grand_total');
                        $emailTemplateVariables['order_created_at'] = $createdDate;
                        $emailTemplateVariables['gp_error_message'] = $errorMessage;
                        Mage::helper('I95DevConnect_Base')->sendMail($template, $emailTemplateVariables);
                        //TO DO need to fix case sensitve issue properly //Starts here
                        if (property_exists($customerData, 'targetCustomerId'))
                            $targetCustId = "targetCustomerId";
                        else
                        {
                            $targetCustId = "TargetCustomerId";
                        }
                        $targetCustomerId = (isset($customerData->$targetCustId) ? $customerData->$targetCustId : '');
                        $isGuest = (isset($customerData->IsGuestCustomer) ? $customerData->IsGuestCustomer : '');

                        //Ends here
                        //To save admin offline customer in error report table
                        if ('orderData' == $schedulerName && $targetCustomerId == "" && $isGuest != 1)
                        {
                            $this->customerData($customerData, $execMsg, $tableName);
                        }
                    }
                }
                else if ('itemData' == $schedulerName)
                {
                    if (is_object($response))
                    {
                        $SKU = (isset($response->SKU) ? $response->SKU : "");
                        $entityId = $this->_initProduct($SKU);
                        $name = $SKU;
                        $product = mage::getModel('catalog/product')->load($entityId);
                        $createdAt = $product->getCreatedAt();
                    }
                }
                else if ('trakingData' == $schedulerName)
                {
                    if (is_object($response))
                    {
                        $incrementId = (isset($response->sourceOrderId) ? $response->sourceOrderId : $response->SourceOrderId);
                        $targetOrderId = (isset($response->targetOrderId) ? $response->targetOrderId : $response->TargetOrderId);
                        $trackingNumber = (isset($response->TrakingNumer) ? $response->TrakingNumer : $response->trakingNumer);
                        $carrierCode = (isset($response->ShippingCarrier) ? $response->ShippingCarrier : $response->shippingCarrier);
                        $shippingTitle = (isset($response->ShippingMethod) ? $response->ShippingMethod : $response->shippingMethod);
                        $template = "sales_tracking_number_template";

                        $emailTemplateVariables['user_name'] = 'Admin';
                        $emailTemplateVariables['increment_id'] = $incrementId;
                        $emailTemplateVariables['gp_order_id'] = $targetOrderId;
                        $emailTemplateVariables['tracking_number'] = $trackingNumber;
                        $emailTemplateVariables['carrier_code'] = $carrierCode;
                        $emailTemplateVariables['title'] = $shippingTitle;
                        $emailTemplateVariables['gp_error_message'] = $errorMessage;
                        Mage::helper('I95DevConnect_Base')->sendMail($template, $emailTemplateVariables);
                    }
                }

                //Retrieving the existed information from table
                $fields = array('item_id' => '');
                $criteria = array('item_id' => $entityId, 'schedulername' => $schedulerName);

                $result = Mage::helper('errorreport')->select($fields, $tableName, $criteria);

                //Inserting failed request data into table and checking whether the failed data is already existed or not
                if (isset($result['item_id']) && $result['item_id'] != $entityId)
                {

                    // fetch write database connection that is used in Mage_Core module
                    $this->getWriteAdapter()->query("INSERT INTO " . $tableName . "(schedulername, item_id, error_message,created_time,name,status)	VALUES ('" . $schedulerName . "','" . $entityId . "','" . mysql_escape_string($errorMessage) . "','" . $createdAt . "','" . $name . "','" . $status . "')");
                }
                //Fixed bug order total mismatched showing in error reports for offline orders
                else
                {
                    //Checking for orderentity for same entity id to update mismatched order
                    if ($schedulerName == "orderData")
                    {
                        $entityId = Mage::app()->getRequest()->getParam('order_id');
                        if ($entityId == "order_ids")
                        {
                            $entityId = Mage::app()->getRequest()->getParam('order_ids');
                        }
                        if (is_array($entityId))
                        {
                            foreach ($entityId as $id)
                            {
                                $this->getWriteAdapter()->query("UPDATE  " . $tableName . " SET error_message='" . mysql_escape_string($errorMessage) . "' Where schedulername='$schedulerName' AND item_id = '$id'");
                            }
                        }
                        else
                        {
                            $this->getWriteAdapter()->query("UPDATE  " . $tableName . " SET error_message='" . mysql_escape_string($errorMessage) . "' Where schedulername='$schedulerName' AND item_id = '$entityId'");
                        }
                    }
                }
            }
        } catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }

        return true;
    }

    /**
     * Deleteing entries from i95dev failed reports table when cutsomers/orders are synced properly to magento
     * @param $targetId-target customer id/order id
     */
    public function deleteSyncedData($targetId, $schedulerName, $errorStatus = null)
    {
        try
        {
            $helper = Mage::helper('I95DevConnect_Base');
            $tableName = 'i95dev_failed_schedular_details';
            $flag = $this->getWriteAdapter()->isTableExists(trim($tableName));

            if ($flag == '1')
            {
                //Retrieving the existed information from table
                $fields = array('item_id' => '');
                $criteria = array('item_id' => $targetId, 'schedulername' => $schedulerName);
                $result = Mage::helper('errorreport')->select($fields, $tableName, $criteria);

                //Inserting failed request data into table and checking whether the failed data is already existed or not
                $entityId = Mage::app()->getRequest()->getParam('order_id');
                // Deleting the previous occurance of error report entity for mismatched orders
                if (isset($result['item_id']) && $result['item_id'] != "" && $errorStatus == null)
                {
                    $sql = "update " . $tableName . " set is_delete ='1' where item_id='" . $targetId . "'";
                    $this->getWriteAdapter()->query($sql);
                }
                else
                {
                    $sql = "update " . $tableName . " set error_message='Order Total Mismatch', is_delete ='0' where item_id='" . $targetId . "'";
                    $this->getWriteAdapter()->query($sql);
                }
            }
        } catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * Saving customer in i95dev failed reports table, while order placing with new cusotmer from admin in offline
     * @param type $customerData
     * @param type $execMsg
     * @param type $tableName 
     */
    public function customerData($customerData, $execMsg, $tableName)
    {
        try
        {
            $helper = Mage::helper('I95DevConnect_Base');

            $entityId = (isset($targetCustomerId) ? $targetCustomerId : $customerData->SourceCustomerId);
            $customer = Mage::getModel('customer/customer')->load($entityId);
            $createdAt = $customer->getCreatedAt();
            $firstName = (isset($customerData->FirstName) ? $customerData->FirstName : $customerData->firstName);
            $lastName = (isset($customerData->LastName) ? $customerData->LastName : $customerData->lastName);
            $name = $firstName . " " . $lastName;
            $status = (isset($customerData->targetCustomerId) ? 1 : 0);
            $template = "sales_customer_fail_update_template";
            $errorMessage = "Customer failed to update";

            $emailTemplateVariables['user_name'] = 'Admin';
            $emailTemplateVariables['customer_id'] = $entityId;
            $emailTemplateVariables['customer_name'] = $name;
            $emailTemplateVariables['gp_error_message'] = $errorMessage;
            Mage::helper('I95DevConnect_Base')->sendMail($template, $emailTemplateVariables);

            //Retrieving the existed information from table
            $fields = array('item_id' => '');
            $criteria = array('item_id' => $entityId, 'schedulername' => 'customerData');
            $result = Mage::helper('errorreport')->select($fields, $tableName, $criteria);

            //Inserting failed request data into table and checking whether the failed data is already existed or not
            if (isset($result['item_id']) && $result['item_id'] != $entityId)
            {

                // fetch write database connection that is used in Mage_Core module
                $this->getWriteAdapter()->query("INSERT INTO " . $tableName . "(schedulername, item_id, error_message,created_time,name,status)	VALUES ('customerData','" . $entityId . "','" . mysql_escape_string($execMsg) . "','" . $createdAt . "','" . $name . "','" . $status . "')");
            }
        } catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * for updating product status
     * @param type $entityId
     * @param type $attributeId 
     */
    public function setProductStatus($tableName, $entityId, $attributeId, $status)
    {
        try
        {
            $helper = Mage::helper('I95DevConnect_Base');

            $flag = $this->getWriteAdapter()->isTableExists(trim($tableName));
            if ($flag == '1')
            {
                $sql = "update " . $tableName . " set value ='" . $status . "' where entity_id='" . $entityId . "' and attribute_id='" . $attributeId . "'";
                $this->getWriteAdapter()->query($sql);
            }
        } catch (Exception $ex)
        {
            $helper->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

}

?>
