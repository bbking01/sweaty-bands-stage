<?php

class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('customerpicturesGrid');
      $this->setDefaultSort('customerpictures_image_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customerpictures/images')->getCollection()->setOrder('customerpictures_image_id', 'DESC');
	  if($this->getRequest()->getControllerName()=='adminhtml_winner')
		$collection->addFieldToFilter('winner_time', array('neq' => ''));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('image_name', array(
          'header'    => Mage::helper('customerpictures')->__('Image'),
          'align'     =>'center',
          'index'     => 'image_name',
		  'filter'    => false,
		  'width'	  => '90px',
		  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Image(),
      ));
	  
	  $this->addColumn('user_id', array(
          'header'    => Mage::helper('customerpictures')->__('Customer Name'),
          'align'     =>'left',
          'index'     => 'user_id',
		  'filter'    => false,
		  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_User(),
      ));
	  
	  $this->addColumn('image_title', array(
          'header'    => Mage::helper('customerpictures')->__('Picture Title'),
          'align'     =>'left',
          'index'     => 'image_title',
      ));
	  
	  $this->addColumn('viewed', array(
          'header'    => Mage::helper('customerpictures')->__('Viewed'),
          'align'     =>'center',
          'index'     => 'viewed',
		  'width'	  => '70px',
      ));
	  
	  if($this->getRequest()->getControllerName()!='adminhtml_winner'){
		$this->addColumn('liked', array(
          'header'    => Mage::helper('customerpictures')->__('Facebook Likes'),
          'align'     =>'center',
          'index'     => 'liked',
		  'width'	  => '100px',
		  'filter'    => false,
		  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Like(),
      ));
	  
		  $this->addColumn('status', array(
			  'header'    => Mage::helper('customerpictures')->__('Status'),
			  'align'     => 'left',
			  'width'     => '80px',
			  'index'     => 'status',
			  'type'      => 'options',
			  'options'   => array(
				  0 => 'Pending',
				  1 => 'Denied',
				  2 => 'Approved',
			  ),
		  ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customerpictures')->__('Status Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customerpictures')->__('Approve'),
                        'url'       => array('base'=> '*/*/approve'),
                        'field'     => 'id',
                    ),
					array(
						'caption'   => Mage::helper('customerpictures')->__('Deny'),
						'url'       => array('base'=> '*/*/deny'),
						'field'     => 'id',
					),
					array(
						'caption'   => Mage::helper('customerpictures')->__('Remove'),
						'url'       => array('base'=> '*/*/remove'),
						'field'     => 'id',
						'javascript'=> 'alert(1)',
					)
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
			$this->addColumn('winner_time', array(
			  'header'    => Mage::helper('customerpictures')->__('Winner'),
			  'align'     =>'center',
			  'index'     => 'winner_time',
			  'width'	  => '50px',
			  'filter'    => false,
			  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Winner(),
		  ));
		  
		  $this->addColumn('coupon', array(
			  'header'    => Mage::helper('customerpictures')->__('Coupon code'),
			  'align'     =>'center',
			  'filter'    => false,
			  'width'	  => '180px',
			  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Coupon(),
		  ));
	  }
	  else
	  {
		$this->addColumn('liked', array(
          'header'    => Mage::helper('customerpictures')->__('<span style="line-height:14px">Facebook Likes<br>on winning day</span>'),
          'align'     =>'center',
          'index'     => 'liked',
		  'width'	  => '100px',
		  'filter'    => false,
		  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Like(),
      ));
	  
		$this->addColumn('winner_time', array(
			  'header'    => Mage::helper('customerpictures')->__('Date of winning'),
			  'align'     =>'center',
			  'index'     => 'winner_time',
			  'filter'    => false,
			  'renderer'	=> new AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Date(),
		  ));
	  }
	  
      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}