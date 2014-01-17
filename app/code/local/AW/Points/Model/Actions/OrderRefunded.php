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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Model_Actions_OrderRefunded extends AW_Points_Model_Actions_OrderInvoiced
{    
    protected $_action = 'order_refunded';     
   
    public function getComment()
    {
        if (isset($this->_commentParams['comment']))
            return $this->_commentParams['comment'];
        return $this->_comment;
    }
    
    public function getCommentHtml($area = self::ADMIN)
    {       
        preg_match_all("#\#(\S{4,})#isu", $this->_transaction->getComment(), $matches);
        
        if (!$this->_transaction)
            return;
         
        $patterns = $replacements = array();
        if(isset($matches[1]) && !empty($matches[1])) {
            for($i=0; $i < count($matches[1]); $i++) {                
                $order = Mage::getModel('sales/order')->loadByIncrementId($matches[1][$i]);
                $patterns[] = "#\#{$matches[1][$i]}#isu";                
                if(!$order->getId()) {
                    $replacements[] = "#{$matches[1][$i]}";  
                    continue;
                }                                   
                $replacements[] = "{$this->_getLinkHtml($order, $area)}";                                  
            }          
        }
       
        return preg_replace($patterns, $replacements, $this->_transaction->getComment());       
    }
    
    protected function _getLinkHtml($order, $area)
    {
        if ($area == self::ADMIN) {
            $orderUrl = Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view/', 
                    array('order_id' => $order->getId()));
        } else {
            $orderUrl = Mage::getUrl('sales/order/view/', 
                    array('order_id' => $order->getId()));
        }

        return "<a href='{$orderUrl}'>#{$order->getIncrementId()}</a>";
    }

}
