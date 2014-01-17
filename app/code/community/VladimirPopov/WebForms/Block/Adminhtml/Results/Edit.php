<?php
class VladimirPopov_WebForms_Block_Adminhtml_Results_Edit
	extends Mage_Adminhtml_Block_Widget_Form_Container
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		if ((float)substr(Mage::getVersion(), 0, 3) > 1.3 && substr(Mage::getVersion(), 0, 5) != '1.4.0' || Mage::helper('webforms')->getMageEdition() == 'EE')
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled())
			{
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
				$this->_formScripts[] = "
					function toggleEditor() {
						if (tinyMCE.getInstanceById('page_content') == null) {
							tinyMCE.execCommand('mceAddControl', false, 'content');
						} else {
							tinyMCE.execCommand('mceRemoveControl', false, 'content');
						}
					}";
			}

        // add scripts
        $js = $this->getLayout()->createBlock('core/template','webforms_js',array(
            'template' => 'webforms/logic.phtml',
            'result' => Mage::registry('result'),
            'webform' => Mage::registry('webform'),
        ));

        $this->getLayout()->getBlock('content')->append(
            $js
        );
	}

	public function __construct()
	{
		$result = Mage::getModel('webforms/results');
		$id = $this->getRequest()->getParam('id');
		if($id){
			$result->load($id);
		}
		Mage::register('result',$result);
        $webform = Mage::getModel('webforms/webforms')->setStoreId($result->getStoreId())->load($result->getWebformId());
        Mage::register('webform',$webform);

		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'webforms';
		$this->_controller = 'adminhtml_results';

		$this->_addButton('reply', array
		(
			'label' => Mage::helper('webforms')->__('Reply'),
			'onclick' => 'setLocation(\'' . $this->getUrl('*/*/reply',array('_current'=>true,'webform_id' => $result->getWebformId())) . '\')',
		));
		
		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => "$('saveandcontinue').value = true; editForm.submit()",
			'class'     => 'save',
		), -100);
	}
	
	public function getSaveUrl()
	{
		return $this->getUrl('*/adminhtml_results/save');
	}
	
	public function getBackUrl(){
		return $this->getUrl('*/adminhtml_results/index',array('webform_id'=>Mage::registry('result')->getWebformId()));
	}

	public function getHeaderText()
	{
		if(!is_null(Mage::registry('result')->getId())) {
			return Mage::helper('webforms')->__("Result # %s | %s",Mage::registry('result')->getId(),Mage::helper('core')->formatDate(Mage::registry('result')->getCreatedTime(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,true));
		} 
	}	
}
?>
