<?php
class Magestore_Fontmanagement_Block_Adminhtml_Fontcategory_Edit extends Magestore_Fontmanagement_Block_Adminhtml_Widget_Form_Container
//class Magestore_Fontmanagement_Block_Adminhtml_Fontcategory_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'fontmanagement';
        $this->_controller = 'adminhtml_fontcategory';
		$this->_removeButton('reset');
       	$this->_updateButton('save', 'label', Mage::helper('fontmanagement')->__('Save Font Category'));
        
		$this->_updateButton('delete', 'label', Mage::helper('fontmanagement')->__('Delete Font Category') );
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
		
	    $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
		
		
    }

    public function getHeaderText()
    {
        if( Mage::registry('fontmanagement_data') && Mage::registry('fontmanagement_data')->getId() ) {
            return Mage::helper('fontmanagement')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('fontmanagement_data')->getCategory_name()));
        } else {
            return Mage::helper('fontmanagement')->__('Add Item');
        }
    }
}