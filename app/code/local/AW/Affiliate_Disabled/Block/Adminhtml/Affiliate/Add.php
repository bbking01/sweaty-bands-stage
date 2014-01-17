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


class AW_Affiliate_Block_Adminhtml_Affiliate_Add extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_affiliate';
        $this->_objectId = 'id';
        $this->_blockGroup = 'awaffiliate';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Affiliate'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('back', 'id', 'back_button');
        $this->_updateButton('reset', 'id', 'reset_button');

        $this->_formScripts[] = '
            $("page:left").select("h3:first")[0].innerHTML = "' . $this->__('Manage Affiliate') . '";
            toggleVis("awaffiliate_affiliate_tabs_balance_section");
            toggleParentVis("edit_form");
            toggleVis("save_button");
            toggleVis("reset_button");
        ';

        $this->_formInitScripts[] = '
            //<![CDATA[
            var awaffiliate = function() {
                return {
                    customerInfoUrl : null,
                    formHidden : true,

                    gridRowClick : function(data, click) {
                        if(Event.findElement(click,\'TR\').title){
                            awaffiliate.customerInfoUrl = Event.findElement(click,\'TR\').title;
                            awaffiliate.loadCustomerData();
                            awaffiliate.showForm();
                            awaffiliate.formHidden = false;
                        }
                    },

                    loadCustomerData : function() {
                        var con = new Ext.lib.Ajax.request(\'POST\', awaffiliate.customerInfoUrl, {success:awaffiliate.reqSuccess,failure:awaffiliate.reqFailure}, {form_key:FORM_KEY});
                    },

                    showForm : function() {
                        toggleParentVis("edit_form");
                        toggleVis("customerGrid");
                        toggleVis("save_button");
                        toggleVis("reset_button");
                        toggleVis("aw-affiliate-select-customer-container");
                        $("back_button").onclick = awaffiliate.hideForm;
                    },

                    hideForm : function(){
                        toggleParentVis("edit_form");
                        toggleVis("customerGrid");
                        toggleVis("save_button");
                        toggleVis("reset_button");
                        toggleVis("aw-affiliate-select-customer-container");
                        $("back_button").onclick = function(){ eval($("back_button").getAttribute("onclick")); }
                    },

                    reqSuccess :function(o) {
                        var response = Ext.util.JSON.decode(o.responseText);
                        if( response.error ) {
                            alert(response.message);
                        } else if( response.id ){
                            $("general_customer_id").value = response.id;

                            $("general_customer_name").innerHTML = \'<a href="' . $this->getUrl('adminhtml/customer/edit') . 'id/\' + response.id + \'" target="_blank">\' + response.firstname + \' \' + response.lastname + \' &lt\' + response.email + \'&gt</a>\';
                            $("general_customer_group").innerHTML = \'<a href="' . $this->getUrl('adminhtml/customer_group/edit') . 'id/\' + response.group_id + \'" target="_blank">\' + response.group_code + \'</a>\';
                        } else if( response.message ) {
                            alert(response.message);
                        }
                    }
                }
            }();
           //]]>
        ';
    }

    public function getHeaderText()
    {
        return $this->__('New Affiliate');
    }
}
