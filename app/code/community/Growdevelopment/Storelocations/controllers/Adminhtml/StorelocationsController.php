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
class Growdevelopment_Storelocations_Adminhtml_StorelocationsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Index Action
     * Display grid of all locations
     *
     */
	public function indexAction() {

		$this->_title($this->__('System'))->_title($this->__('Store Locations'));	
		
		$this->loadLayout();
			
        $this->_setActiveMenu('cms/storelocations');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store Locations'), Mage::helper('adminhtml')->__('Store Locations'));

		$this->_addContent($this->getLayout()->createBlock('growdev_adminhtml/locations'));
		
		$this->renderLayout();
	}	
	
	
	
    /**
     * Edit Action
     * Edit the location with ID matching parameter, or show an empty edit form
     *
     * @return string
     */
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('storelocation/storelocation')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('storelocation_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('cms/storelocations');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->_addContent($this->getLayout()->createBlock('growdev_adminhtml/locations_edit'))
				->_addLeft($this->getLayout()->createBlock('growdev_adminhtml/locations_edit_tabs'));


			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('growdevstorelocations')->__('Location does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
    /**
     * New Action
     * Create a new location with an empty edit form
     *
     * @return void
     */
	public function newAction() {
		$this->editAction();
	}
	
    /**
     * Save Action
     * Save the new location or update the existing location
     *
     * @return void
     */
	public function saveAction()
	{
	    $data = $this->getRequest()->getPost();
	    
        if ( $this->getRequest()->getPost() ) {
            try {

                /* Process photo */
				if(isset($data['photo']['delete']) && $data['photo']['delete'] == 1){
					// delete existing photo
					$delete_path = Mage::getBaseDir('media') . DS . $data['photo']['value'];
					unset($delete_path);
					
					$photo_location = ''; 
				} else {
	                if ( isset($_FILES['photo']['name']) and (file_exists($_FILES['photo']['tmp_name']))) {				
	                    // new photo
	                	$uploader = new Varien_File_Uploader('photo');
						$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						
						$path = Mage::getBaseDir('media') . DS . 'storelocations';
						
						$uploader->save($path, $_FILES['photo']['name']);
						$photo_location = 'storelocations' . DS . $_FILES['photo']['name'];
						
					} else {
						// leave existing photo if there is one
						if (isset($data['photo']['value'])){
							$photo_location = $data['photo']['value'];
						} else {
							$photo_location = '';
						}
					}
				}

                $model = Mage::getModel('storelocation/storelocation')
                    ->setId($this->getRequest()->getParam('id'))
                    ->setStoreName($this->getRequest()->getParam('store_name'))
                    ->setStatus($this->getRequest()->getParam('status'))
                    ->setOwnerName($this->getRequest()->getParam('owner_name'))
                    ->setStreet($this->getRequest()->getParam('street'))
                    ->setStreet2($this->getRequest()->getParam('street2'))
                    ->setCity($this->getRequest()->getParam('city'))
                    ->setLocationRegionId($this->getRequest()->getParam('location_region_id'))
                    ->setPostalCode($this->getRequest()->getParam('postal_code'))
                    ->setLocationCountryId($this->getRequest()->getParam('location_country_id'))
                    ->setPhone($this->getRequest()->getParam('phone'))
                    ->setFax($this->getRequest()->getParam('fax'))
                    ->setEmail($this->getRequest()->getParam('email'))
                    ->setUrl($this->getRequest()->getParam('url'))
                    ->setStoreType($this->getRequest()->getParam('store_type'))
                    ->setDescription($this->getRequest()->getParam('description'))
                    ->setOpeningHours($this->getRequest()->getParam('opening_hours'))
                    ->setGoogleLatitude($this->getRequest()->getParam('google_latitude'))
                    ->setGoogleLongitude($this->getRequest()->getParam('google_longitude'))
                    ->setGoogleZoomLevel($this->getRequest()->getParam('google_zoom_level'))
                    ->setPhoto($photo_location);
                    
                $model->save();
                
                
                /* Update the products associated with this store location */
				$store_id = $model->getid();

		        $ids = Mage::getModel('storeproduct/storeproduct')->getCollection()
        						->addFieldToFilter('store_id', array('eq'=> $store_id ));
                
                /* remove all rows */
                foreach ($ids as $product) {
                	$product->delete();
                }
                
                /* add new rows */
               	parse_str($data['location_products'], $products);
                foreach( $products as $key => $val) {
                	$tmp = Mage::getModel('storeproduct/storeproduct')
                					->setStoreId($store_id)
                					->setProductId($key)
                					->save();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store location was successfully saved'));
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
				} else {
                	$this->_redirect('*/*/');
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
		
        $this->_redirect('*/*/');
	}
	
    /**
     * Delete Action
     * Display list of products related to current store location
     *
     * @return void
     */
	public function deleteAction() {
	
		if( $this->getRequest()->getParam('id') > 0 ){
			try{

				$store_id = $this->getRequest()->getParam('id');
				$locationModel = Mage::getModel('storelocation/storelocation');
                $locationModel->setId($store_id)->delete();

		        $ids = Mage::getModel('storeproduct/storeproduct')->getCollection()
        						->addFieldToFilter('store_id', array('eq'=> $store_id ));
                
                /* remove all rows */
                foreach ($ids as $product) {
                	$product->delete();
                }

                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Location was successfully deleted'));
                $this->_redirect('*/*/');
				
			} catch(Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
			
		}
		$this->_redirect('*/*/');
	}
	
    /**
     * Mass Delete Action
     *
     */
    public function massDeleteAction()
    {
        $locationIds = $this->getRequest()->getParam('store_id');
        if (!is_array($locationIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
        } else {
            if (!empty($locationIds)) {
                try {
                    foreach ($locationIds as $store_id) {
                        $location = Mage::getModel('storelocation/storelocation')->load($store_id);
                        $location->delete();
				        $ids = Mage::getModel('storeproduct/storeproduct')->getCollection()
		        						->addFieldToFilter('store_id', array('eq'=> $store_id ));
		                
		                /* remove all rows */
		                foreach ($ids as $product) {
		                	$product->delete();
		                }
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($locationIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Update location(s) status action
     *
     */
    public function massStatusAction()
    {
        $locationIds = (array)$this->getRequest()->getParam('store_id');
        $status     = (int)$this->getRequest()->getParam('status');

        try {
            foreach ($locationIds as $store_id) {
                $location = Mage::getModel('storelocation/storelocation')->load($store_id);
				$location->setStatus($status)->save();                
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($locationIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()
                ->addException($e, $this->__('An error occurred while updating the location(s) status.'));
        }

        $this->_redirect('*/*/');
    }
	
    /**
     * Grid Action
     * Display list of products related to current store location
     *
     * @return void
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('growdev_adminhtml/locations_edit_tab_product', 'location.products.grid')
                ->toHtml()
        );
    }
	
}