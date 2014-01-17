<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerGroupsPrice_Model_Mysql4_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price
{
    public function getMaxPrice($filter)
    {   
        $i = Mage::getVersionInfo();
        if($i['minor'] < 7 ){
          $t = 'price_index';
        } else {
          $t = 'e';
        }
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);
        $websiteId  = Mage::app()->getStore()->getWebsiteId();

        $table = $this->_getIndexTableAlias();

        $additional   = join('', $response->getAdditionalCalculations());
        $maxPriceExpr = new Zend_Db_Expr("MAX({$table}.min_price {$additional})");
        if(($customer = Mage::getModel('customer/session')->getCustomer()) && $customer->getGroupId()){
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $from = $select->getPart('from');
            if(!in_array('cgp_prices', array_keys($from))){
                $select->joinLeft(array('cgp_prices' => $tablePrefix.'customergroupsprice_prices'),
                                'cgp_prices.product_id=e.entity_id AND cgp_prices.group_id='.$customer->getGroupId().' and cgp_prices.website_id in (0, '.$websiteId.')',
                                array())
                       ->joinLeft(array('cgp_sprices' => $tablePrefix.'customergroupsprice_special_prices'),
                                'cgp_sprices.product_id=e.entity_id AND cgp_sprices.group_id='.$customer->getGroupId().' and cgp_sprices.website_id in (0, '.$websiteId.')',
                                array())
                       ->joinLeft(array('cgp_global' => $tablePrefix.'customergroupsprice_prices_global'),
                                 'cgp_global.group_id='.$customer->getGroupId(),
                                array());
            }
            // Roman's crazy condition
            
            $col = "IFNULL(cgp_sprices.price, IFNULL(
                    cgp_prices.price,
                    IF(
                        cgp_global.price IS NULL,
                        IF({$t}.final_price IS NULL,{$t}.min_price,{$t}.final_price),
                        IF(
                            cgp_global.price_type=2,
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF(price_index.final_price IS NULL,price_index.min_price * (100 + cgp_global.price) / 100 ,price_index.final_price * (100 + cgp_global.price) / 100),
                                IF(price_index.final_price IS NULL,price_index.min_price * cgp_global.price / 100,price_index.final_price * cgp_global.price / 100)
                            ),
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF(price_index.final_price IS NULL,price_index.min_price + cgp_global.price,price_index.final_price + cgp_global.price),
                                cgp_global.price
                            )
                        )
                    )))";

            $maxPriceExpr = new Zend_Db_Expr($col);
            $select->columns(array('price' => $maxPriceExpr));
            $res = max($connection->fetchAll($select));
            return $res['price'];
        }

        $select->columns(array($maxPriceExpr));
        return $connection->fetchOne($select) * $filter->getCurrencyRate();
    }

    public function getCount($filter, $range)
    {
        $i = Mage::getVersionInfo();
        if($i['minor'] < 7 ){
          $t = 'price_index';
        } else {
          $t = 'e';
        }
        
        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $select     = $this->_getSelect($filter);
        $connection = $this->_getReadAdapter();
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);
        $table      = $this->_getIndexTableAlias();

        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();
        $countExpr  = new Zend_Db_Expr('COUNT(*)');
        $rangeExpr  = new Zend_Db_Expr("FLOOR((({$table}.min_price {$additional}) * {$rate}) / {$range}) + 1");

        if(($customer = Mage::getModel('customer/session')->getCustomer()) && $customer->getGroupId()){
            $from = $select->getPart('from');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            if(!in_array('cgp_prices', array_keys($from))){
                $select->joinLeft(array('cgp_prices' => $tablePrefix.'customergroupsprice_prices'),
                                'cgp_prices.product_id=e.entity_id AND cgp_prices.group_id='.$customer->getGroupId().' and cgp_prices.website_id in (0,'.$websiteId.')',
                                array())
                       ->joinLeft(array('cgp_sprices' => $tablePrefix.'customergroupsprice_special_prices'),
                                'cgp_sprices.product_id=e.entity_id AND cgp_sprices.group_id='.$customer->getGroupId().' and cgp_sprices.website_id in (0,'.$websiteId.')',
                                array())
                       ->joinLeft(array('cgp_global' => $tablePrefix.'customergroupsprice_prices_global'),
                                 'cgp_global.group_id='.$customer->getGroupId(),
                                array());
            }
            // My crazy condition
            $col = "IFNULL(cgp_sprices.price, IFNULL(
                    cgp_prices.price,
                    IF(
                        cgp_global.price IS NULL,
                        IF({$t}.final_price IS NULL,{$t}.min_price,{$t}.final_price),
                        IF(
                            cgp_global.price_type=2,
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF({$t}.final_price IS NULL,{$t}.min_price * (100 + cgp_global.price) / 100 ,{$t}.final_price * (100 + cgp_global.price) / 100),
                                IF({$t}.final_price IS NULL,{$t}.min_price * cgp_global.price / 100,{$t}.final_price * cgp_global.price / 100)
                            ),
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF({$t}.final_price IS NULL,{$t}.min_price + cgp_global.price,{$t}.final_price + cgp_global.price),
                                cgp_global.price
                            )
                        )
                    )))";

            $rangeExpr  = new Zend_Db_Expr("FLOOR((({$col} {$additional}) * {$rate}) / {$range}) + 1");
        }

        $select->columns(array(
            'range' => $rangeExpr,
            'count' => $countExpr
        ));
        $select->group('range');
        return $connection->fetchPairs($select);
    }
}