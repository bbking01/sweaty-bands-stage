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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    /*delete all transactions*/
    protected function resetTransactionsAction()
    {
        $withdrawalsTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/transaction_withdrawal');
        $profitsTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/transaction_profit');
        $withdrawalRequestsTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/withdrawal_request');
        $affiliateTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/affiliate');
        $trafficSourceTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/affiliate');
        $clientTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/client');
        $clientHistoryTable = Mage::getSingleton('core/resource')->getTableName('awaffiliate/client_history');
        /** @var $write Varien_Db_Adapter_Pdo_Mysql*/
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $write->delete($withdrawalsTable);
            $write->delete($profitsTable);
            $write->delete($withdrawalRequestsTable);
            $write->delete($affiliateTable);
            $write->delete($clientHistoryTable);
            $write->delete($clientTable);
            $write->delete($trafficSourceTable);
            $this->_getSession()->addSuccess(Mage::helper('awaffiliate')->__('All transactions has been deleted'));
        }
        catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectReferer();
        return;
    }
}
