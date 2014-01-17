<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2012 Vladimir Popov
 */

class VladimirPopov_WebForms_Block_Adminhtml_Reply_Results
	extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		$this->setFilterVisibility(false);
		parent::__construct();
		$this->setId('webforms_reply_grid_'.$this->getRequest()->getParam('webform_id'));
	}
	
	protected function _prepareCollection(){
		$Ids = $this->getRequest()->getParam('id');
		
		if(!is_array($Ids)){
			$Ids = array($Ids);
		} 
		
		$collection = Mage::getModel('webforms/results')->getCollection()
			->addFieldToFilter('id',$Ids);
		
		$collection->getSelect()->order('id desc');
		
		$this->setCollection($collection);
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('id',array(
			'header' => Mage::helper('webforms')->__('ID'),
			'align'	=> 'right',
			'width'	=> '50px',
			'index'	=> 'id',
			'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Id'
		));
		
		$fields = Mage::getModel('webforms/fields')
			->setStoreId($this->getRequest()->getParam('store'))
			->getCollection()
			->addFilter('webform_id',$this->getRequest()->getParam('webform_id'));
		$fields->getSelect()->order('position asc');
		
		$maxlength = Mage::getStoreConfig('webforms/results/fieldname_display_limit');
		foreach($fields as $field){
			if($field->getType() != 'html'){
				$field_name = $field->getName();
				if($field->getResultLabel()){
					$field_name = $field->getResultLabel();
				}
				if(strlen($field_name)>$maxlength && $maxlength>0){
					if(function_exists('mb_substr')){
						$field_name = mb_substr($field_name,0,$maxlength).'...';
					} else {
						$field_name = substr($field_name,0,$maxlength).'...';
					}
				}
				$config = array(
					'header' => $field_name,
					'index' => 'field_'.$field->getId(),
					'sortable' => false,
					'filter_condition_callback' => array($this, '_filterFieldCondition'),
					'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Value'
				);
				if($this->_isExport){
					$config['renderer'] = false;
				} else {
					if($field->getType() == 'image'){
						$config['filter'] = false;
						$config['width'] =  Mage::getStoreConfig('webforms/images/grid_thumbnail_width').'px';
					}
					if(strstr($field->getType(),'select')){
						$config['type'] = 'options';
						$config['options'] = $field->getSelectOptions();
					}
					if($field->getType() == 'number' || $field->getType() == 'stars'){
						$config['type'] = 'number';
					}
					if($field->getType() == 'date'){
						$config['type'] = 'date';
					}
					if($field->getType() == 'datetime'){
						$config['type'] = 'datetime';
					}
                    if($field->getType() == 'subscribe'){
                        $config['type'] = 'options';
                        $config['renderer'] = false;
                        $config['options'] = Mage::getModel('adminhtml/system_config_source_yesno')->toArray();
                    }
                }
				$config = new Varien_Object($config);
				Mage::dispatchEvent('webforms_block_adminhtml_results_grid_prepare_columns_config',array('field'=>$field,'config'=>$config));
				
				$this->addColumn('field_'.$field->getId(), $config->getData());
			}
		}
		$config = array(
			'header' => Mage::helper('webforms')->__('Customer'),
			'align' => 'left',
			'index' => 'customer_id',
			'renderer' => 'VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Customer',
			'filter_condition_callback' => array($this, '_filterCustomerCondition'),
			'sortable' => false
		);
		if($this->_isExport){
			$config['renderer'] = false;
		}
		$this->addColumn('customer_id',$config);

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'        => Mage::helper('webforms')->__('Store View'),
				'index'         => 'store_id',
				'type'          => 'store',
				'store_all'     => true,
				'store_view'    => true,
				'sortable'      => false,
				'filter'		=> false,
				'filter_condition_callback'	=> array($this, '_filterStoreCondition'),
			));
		}
		
		$this->addColumn('ip',array(
			'header' => Mage::helper('webforms')->__('IP'),
			'index' => 'ip',
			'sortable' => false,
			'filter' => false,
		));
		
		
		$this->addColumn('created_time', array(
			'header'    => Mage::helper('webforms')->__('Date Created'),
			'index'     => 'created_time',
			'type'      => 'datetime',
		));
			
		return parent::_prepareColumns();
	}
}
?>
