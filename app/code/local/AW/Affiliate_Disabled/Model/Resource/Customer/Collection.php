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


if (@class_exists('Mage_Customer_Model_Entity_Customer_Collection')) {
    class AW_Affiliate_Model_Resource_Customer_CollectionCommon extends Mage_Customer_Model_Entity_Customer_Collection
    {
    }
} else {
    class AW_Affiliate_Model_Resource_Customer_CollectionCommon extends Mage_Customer_Model_Resource_Customer_Collection
    {
    }
}

class AW_Affiliate_Model_Resource_Customer_Collection extends AW_Affiliate_Model_Resource_Customer_CollectionCommon
{
    const WTHD_REQUEST_NOT_EXIST = 0;
    const WTHD_REQUEST_EXIST = 1;

    protected $_affiliateInfoAdded = false;
    protected $_withdrawalRequestInfoAdded = false;

    public function addAffiliateInfo()
    {
        if (!$this->_affiliateInfoAdded) {
            $affiliateTableName = Mage::getResourceModel('awaffiliate/affiliate_collection')->getMainTable();
            $this->getSelect()->join(
                array('affiliate_table' => $affiliateTableName),
                'affiliate_table.customer_id = e.entity_id',
                array(
                    'affiliate_id' => 'id',
                    'affiliate_status' => 'status',
                    'current_balance' => 'current_balance'
                )
            );
            $this->_affiliateInfoAdded = true;
        }
        return $this;
    }

    public function addWithdrawalRequestInfo()
    {
        if (!$this->_withdrawalRequestInfoAdded) {
            $this->addAffiliateInfo();

            $transactionProfitTableName = Mage::getResourceModel('awaffiliate/transaction_profit_collection')->getMainTable();
            $this->getSelect()->joinLeft(
                array('affiliate_profit' => $transactionProfitTableName),
                'affiliate_profit.affiliate_id = affiliate_table.id',
                null
            );

            $withdrawalRequestTableName = Mage::getResourceModel('awaffiliate/withdrawal_request_collection')->getMainTable();
            $this->getSelect()->joinLeft(
                array('affiliate_withdrawal_request' => $withdrawalRequestTableName),
                'affiliate_withdrawal_request.affiliate_id = affiliate_table.id',
                null
            );

            $transactionWithdrawalTableName = Mage::getResourceModel('awaffiliate/transaction_withdrawal_collection')->getMainTable();
            $this->getSelect()->joinLeft(
                array('affiliate_withdrawal' => $transactionWithdrawalTableName),
                'affiliate_withdrawal.id = affiliate_withdrawal_request.transaction_id',
                array(
                    'withdrawal_requested' => 'IF(
                      SUM(
                          IF(affiliate_withdrawal_request.status = \'pending\', affiliate_withdrawal_request.amount, NULL)
                      ) IS NOT NULL,
                      ' . self::WTHD_REQUEST_EXIST . ',
                      ' . self::WTHD_REQUEST_NOT_EXIST . '
                   )'
                )
            );
            $this->_withdrawalRequestInfoAdded = true;
        }
        return $this;
    }

    /* REWRITE PARENT METHODS FROM EAV TO NATIVE SQL*/
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        if (!count($countSelect->getPart(Zend_Db_Select::HAVING))) {
            $countSelect->resetJoinLeft();
        } else {
            $countSelect->columns(array(
                'withdrawal_requested' => 'IF(
                      SUM(
                          IF(affiliate_withdrawal_request.status = \'pending\', affiliate_withdrawal_request.amount, NULL)
                      ) IS NOT NULL,
                      1,
                      0
                   )'
            ));
        }
        return $countSelect;
    }

    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->limit($limit, $offset);
        $realIdsSelect = new Zend_Db_Select($idsSelect->getAdapter());
        $realIdsSelect->from(new Zend_Db_Expr('(' . $idsSelect . ')'));
        $realIdsSelect->reset(Zend_Db_Select::COLUMNS);
        $realIdsSelect->columns($this->getEntity()->getIdFieldName());
        return $realIdsSelect;
    }

    public function setOrder($attribute, $dir = self::SORT_ORDER_ASC)
    {
        $res = parent::setOrder($attribute, $dir);
        $_orderExpr = new Zend_Db_Expr($attribute . ' ' . strtoupper($dir));
        $this->getSelect()->order($_orderExpr);
        return $res;
    }

    public function addFieldToFilter($attribute, $condition = null)
    {
        $__condAsStr = $this->_getConditionSql($attribute, $condition);
        $this->getSelect()->where($__condAsStr, null, null);
        return $this;
    }

    public function addFieldFilterToHaving($attribute, $condition = null)
    {
        $_condStr = $this->_getConditionSql($attribute, $condition);
        $this->getSelect()->having($_condStr, null, null);
        return $this;
    }

    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->_getAllIdsSelect();
            $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }

    public function addStoreFilter($stores = array())
    {
        if (!empty($stores)) {
            $websites = array();
            foreach ($stores as $storeId) {
                $store = Mage::app()->getSafeStore($storeId);
                if ($store) {
                    $websites[] = $store->getWebsiteId();
                }
            }
            $websites = array_unique($websites);
            $_sqlString = '(';
            $i = 0;
            foreach ($websites as $_store) {
                $_sqlString .= sprintf('find_in_set(%s, website_id)', $this->getConnection()->quote($_store));
                if (++$i < count($websites))
                    $_sqlString .= ' OR ';
            }
            $_sqlString .= ')';
            $this->getSelect()->where($_sqlString);
        }
    }

    /**
     * Get alias for attribute value table
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeTableAlias($attributeCode)
    {
        return $this->_getAttributeTableAlias($attributeCode) ;

    }


}
