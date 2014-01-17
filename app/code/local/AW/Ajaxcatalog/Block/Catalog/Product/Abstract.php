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
 * @package    AW_Ajaxcatalog
 * @version    1.0.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Ajaxcatalog_Block_Catalog_Product_Abstract extends Mage_Catalog_Block_Product_List
{
        
    /**
     * Ask Helper
     * @return AW_Ajaxcatalog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('awajaxcatalog');
    }       
    
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }    
    
    public function needAjaxLoad()
    {      
        $collection = $this->getLoadedProductCollection();
        if ($collection && $collection->getPageSize()){                     
            return ($this->getNextPageNum() <= ( ceil($collection->getSize() / $collection->getPageSize() ) ));
        }
        
    }    
    
    /**
     * Next Page Url
     * @return string
     */
    public function getNextJumpUrl()
    {
        $collection = $this->getLoadedProductCollection();
        $nextPageNum = $this->getNextPageNum();
        if ($collection && $collection->getPageSize()){
            if ( ($nextPageNum <= ( ceil($collection->getSize() / $collection->getPageSize() ) )) ){                
                return $this->getPagerUrl(array('p'=>$nextPageNum));
            }
        }
    }    
    
    /**
     * Next Page Number
     * @return integer
     */
    public function getNextPageNum()
    {
        return $nextPageNum = $this->getToolbarBlock()->getCurrentPage() + 1;
    }
    
    /**
     * Session Key
     * @return string
     */
    public function getViewKey()
    {
        if ($skey = $this->getRequest()->getParam('skey')){
            return $skey;
        } elseif ($skey = Mage::registry('aw_ajaxcatalog_session_key')) {
            return $skey;
        }        
        Mage::register('aw_ajaxcatalog_session_key', hash('ripemd160', time()));
        return Mage::registry('aw_ajaxcatalog_session_key');
    }
    
    /**
     * Customer Session
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    public function getSession($key)
    {
        return $this->_getCustomerSession()->getData( $this->getViewKey()."_".$key);
    }
            
    public function setSession($key, $value)
    {
        $this->_getCustomerSession()->setData($this->getViewKey()."_".$key, $value);
        return $this;
    }
    
    public function getFilterParams() {
        $params = $this->getRequest()->getParams();
        $params['skey'] = $this->getViewKey();
        $params['route'] = $this->getRequest()->getRouteName();
        if (!isset($params['dir'])) {
            $params['dir'] = $this->getToolbarBlock()->getCurrentDirection();
        }
        if (!isset($params['order'])) {
            $params['order'] = $this->getToolbarBlock()->getCurrentOrder();
        }
        return Zend_Json_Encoder::encode($params);
    }

}
