/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var customerCredit = new Class.create();

customerCredit.prototype = new AdminOrder();

customerCredit.prototype.initialize = function()
{ 
    

Event.observe(window, 'load',  (function(){
   
  var orderitemid=document.getElementById('order-items').getElementsByClassName('form-buttons'); 
var parent=orderitemid[0];
buttonid=orderitemid[0].getElementsByTagName('button');  
  if(buttonid.length>1)
      {
          parent.removeChild(buttonid[1]);         
          
      }
}
))


}

customerCredit.prototype.switchPaymentMethod = function(method)
{   
    order.setPaymentMethod(method);  
    var data = {};
    data['order[payment_method]'] = method;
    order.loadArea(['card_validation','shipping_method', 'totals','billing_method' ], true, data);
}
customerCredit.prototype.setCreditMethod  = function(method)
{   
   
    data = (method == true ? "1" : "0");
    var text = {};   
    order.loadArea(['shipping_method', 'totals', 'billing_method',data], true,data);
    order.setShippingMethod(0);
}


  
