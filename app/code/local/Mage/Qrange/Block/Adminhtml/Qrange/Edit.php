<?php

class Mage_Qrange_Block_Adminhtml_Qrange_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'qrange';
        $this->_controller = 'adminhtml_qrange';
        
        $this->_updateButton('save', 'label', Mage::helper('qrange')->__('Save Quantity Range'));
        $this->_updateButton('delete', 'label', Mage::helper('qrange')->__('Delete Quantity Range'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('qrange_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'qrange_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'qrange_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('qrange_data') && Mage::registry('qrange_data')->getId() ) {
            return Mage::helper('qrange')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('qrange_data')->getTitle()));
        } else {
            return Mage::helper('qrange')->__('Add Quantity Range');
        }
    }
}