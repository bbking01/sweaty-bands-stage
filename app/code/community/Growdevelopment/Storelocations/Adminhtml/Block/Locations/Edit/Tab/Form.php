<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */
class Growdevelopment_Storelocations_Adminhtml_Block_Locations_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $this->setTemplate('storelocations/edit/tab/form.phtml');
                
		$route = Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName') . '/cms_wysiwyg_images/index';
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
        						array('add_widgets' => true,
        							  'add_variables' => true,
									  'add_images' => true, 
        							  'files_browser_window_url'=> $this->getUrl($route)
        						));

        $fieldset = $form->addFieldset('location_form', array(
            'legend'=>Mage::helper('growdevstorelocations')->__('Store Location Info')
        ));

        $fieldset->addField('store_name', 'text', array(
            'name'      => 'store_name',
            'label'     => Mage::helper('growdevstorelocations')->__('Store Name'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

		$fieldset->addField('status', 'select', array(
		  'label'     => Mage::helper('growdevstorelocations')->__('Status'),
		  'name'      => 'status',
		  'class'      => '',
          'required'  => false,
		  'values'    => array(
		      array(
		          'value'     => 1,
		          'label'     => Mage::helper('growdevstorelocations')->__('Enabled'),
		      ),
		
		      array(
		          'value'     => 2,
		          'label'     => Mage::helper('growdevstorelocations')->__('Disabled'),
		      ),
		  ),
		));

		$fieldset->addField('store_type', 'select', array(
		  'name'      => 'store_type',
		  'label'     => Mage::helper('growdevstorelocations')->__('Type'),
          'class'     => 'required-entry',
          'required'  => true,
		  'values'    => array(
		      array(
		          'value'     => 1,
		          'label'     => Mage::helper('growdevstorelocations')->__('Online'),
		      ),
		
		      array(
		          'value'     => 2,
		          'label'     => Mage::helper('growdevstorelocations')->__('Physical'),
		      ),

		      array(
		          'value'     => 3,
		          'label'     => Mage::helper('growdevstorelocations')->__('Online & Physical'),
		      ),
		  ),
		));

        $fieldset->addField('owner_name', 'text', array(
            'name'      => 'owner_name',
            'label'     => Mage::helper('growdevstorelocations')->__('Owner Name'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('street', 'text', array(
            'name'      => 'street',
            'label'     => Mage::helper('growdevstorelocations')->__('Street Address'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('street2', 'text', array(
            'name'      => 'street2',
            'label'     => Mage::helper('growdevstorelocations')->__('Street Address (cont.)'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('city', 'text', array(
            'name'      => 'city',
            'label'     => Mage::helper('growdevstorelocations')->__('City'),
            'class'     => '',
            'required'  => false,
        ));

        $countries = Mage::getModel('adminhtml/system_config_source_country')
            ->toOptionArray();

        $fieldset->addField('location_country_id', 'select', array(
            'name'      => 'location_country_id',
            'label'     => Mage::helper('growdevstorelocations')->__('Country'),
            'class'     => 'countries',
            'required'  => true,
            'values'    => $countries
        ));


		$location_data = Mage::registry('storelocation_data')->getData();

		$regions = 0;
		$temp_region = 0; 
		
		if (isset($location_data['location_country_id'])){
	        $regionCollection = Mage::getModel('directory/region')
	            ->getCollection()
	            ->addCountryFilter($location_data['location_country_id']);

	        $regions = $regionCollection->toOptionArray();
        }

		
		if (isset($location_data['location_region_id'])){
			$temp_region = $location_data['location_region_id'];
		}
		
        if ($regions) {
            $regions[0]['label'] = '*';
        } else {
            $regions = array(array('value'=>'', 'label'=>'*'));
        }

		if (isset($regions[$temp_region])){
		
	        $fieldset->addField('location_region_id', 'select', array(
	            'name'      => 'location_region_id',
	            'label'     => Mage::helper('growdevstorelocations')->__('Region/State'),
	            'class'     => 'location_region_id',
	            'values'    => $regions
	        ));
				
		} else {	

	        $fieldset->addField('location_region_id', 'text', array(
	            'name'      => 'location_region_id',
	            'label'     => Mage::helper('growdevstorelocations')->__('Region/State'),
	            'class'     => 'location_region_id',
	            'required'  => false,
	        ));

        }

        $fieldset->addField('postal_code', 'text', array(
            'name'      => 'postal_code',
            'label'     => Mage::helper('growdevstorelocations')->__('Postal/Zip Code'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('phone', 'text', array(
            'name'      => 'phone',
            'label'     => Mage::helper('growdevstorelocations')->__('Phone'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('fax', 'text', array(
            'name'      => 'fax',
            'label'     => Mage::helper('growdevstorelocations')->__('Fax'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('email', 'text', array(
                'name'      => 'email',
                'label'     => Mage::helper('growdevstorelocations')->__('Email'),
                'class'     => '',
                'required'  => false,
        ));

        $fieldset->addField('url', 'text', array(
            'name'      => 'url',
            'label'     => Mage::helper('growdevstorelocations')->__('Website Address'),
            'class'     => '',
            'required'  => false,
        ));

		$fieldset->addField('description', 'editor', array(
		  'name'      => 'description',
		  'label'     => Mage::helper('growdevstorelocations')->__('Description'),
		  'title'     => Mage::helper('growdevstorelocations')->__('Description'),
          'style'     => 'height: 24em; width: 500px;',
          'config' 	  => $wysiwygConfig,
		  'wysiwyg'   => true,
		  'required'  => false,
			));

		$fieldset->addField('opening_hours', 'editor', array(
	        'name'      => 'opening_hours',
	        'label'     => Mage::helper('growdevstorelocations')->__('Opening Hours'),
	        'title'     => Mage::helper('growdevstorelocations')->__('Opening Hours'),
	        'style'     => 'height: 24em; width: 500px;',
	        'config' 	=> $wysiwygConfig,
	        'wysiwyg'   => true,
	        'required'  => false,
		));

							 
        $fieldset->addField('photo', 'image', array(
            'label'     => Mage::helper('growdevstorelocations')->__('Image'),
            'required'  => false,
            'name'      => 'photo',
        ));

        $fieldset->addField('google_latitude', 'text', array(
            'name'      => 'google_latitude',
            'label'     => Mage::helper('growdevstorelocations')->__('Latitude'),
            'class'     => '',
            'required'  => false,
        ));

        $fieldset->addField('google_longitude', 'text', array(
            'name'      => 'google_longitude',
            'label'     => Mage::helper('growdevstorelocations')->__('Longitude'),
            'class'     => '',
            'required'  => false,
        ));
        
        $fieldset->addField('google_zoom_level', 'select', array(
            'name'      => 'google_zoom_level',
            'label'     => Mage::helper('growdevstorelocations')->__('Zoom Level'),
            'class'     => '',
            'required'  => false,
            'values'	=> array(
            	array(
            		'value'	=> 1,
            		'label' => '1',
            	),
            	array(
            		'value'	=> 2,
            		'label' => '2',
            	),
            	array(
            		'value'	=> 3,
            		'label' => '3',
            	),
            	array(
            		'value'	=> 4,
            		'label' => '4',
            	),
            	array(
            		'value'	=> 5,
            		'label' => '5',
            	),
            	array(
            		'value'	=> 6,
            		'label' => '6',
            	),
            	array(
            		'value'	=> 7,
            		'label' => '7',
            	),
            	array(
            		'value'	=> 8,
            		'label' => '8',
            	),
            	array(
            		'value'	=> 9,
            		'label' => '9',
            	),
            	array(
            		'value'	=> 10,
            		'label' => '10',
            	),
            	array(
            		'value'	=> 11,
            		'label' => '11',
            	),
            	array(
            		'value'	=> 12,
            		'label' => '12',
            	),
            	array(
            		'value'	=> 13,
            		'label' => '13',
            	),
            	array(
            		'value'	=> 14,
            		'label' => '14',
            	),
            	array(
            		'value'	=> 15,
            		'label' => '15',
            	),
            	array(
            		'value'	=> 16,
            		'label' => '16',
            	),
            	array(
            		'value'	=> 17,
            		'label' => '17',
            	),
            	array(
            		'value'	=> 18,
            		'label' => '18',
            	),
            	array(
            		'value'	=> 19,
            		'label' => '19',
            	),
            ),
        ));


		$fieldset->addField('in_location_products', 'hidden', array(
			'name'	=> 'location_products',
		));			

        if (Mage::registry('storelocation_data')) {
            $form->setValues(Mage::registry('storelocation_data')->getData());
        }

        return parent::_prepareForm();
    }
}