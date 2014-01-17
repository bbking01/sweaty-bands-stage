<?php

class Mage_Qcprice_Block_Adminhtml_Qcprice_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'qcprice';
        $this->_controller = 'adminhtml_qcprice';
        
        $this->_updateButton('save', 'label', Mage::helper('qcprice')->__('Save Price Combination'));
        $this->_updateButton('delete', 'label', Mage::helper('qcprice')->__('Delete Price Combination'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('qcprice_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'qcprice_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'qcprice_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('qcprice_data') && Mage::registry('qcprice_data')->getId() ) {
            return Mage::helper('qcprice')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('qcprice_data')->getTitle()));
        } else {
            return Mage::helper('qcprice')->__('Add Item');
        }
    }
}