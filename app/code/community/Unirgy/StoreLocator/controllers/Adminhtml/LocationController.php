<?php
/**
 * Unirgy_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_StoreLocator_Adminhtml_LocationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('cms/ustorelocator');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store Locations'), Mage::helper('adminhtml')->__('Store Locations'));
        $this->_addContent($this->getLayout()->createBlock('ustorelocator/adminhtml_location'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('cms/ustorelocator');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store Locations'), Mage::helper('adminhtml')->__('Store Locations'));

        $this->_addContent($this->getLayout()->createBlock('ustorelocator/adminhtml_location_edit'))
                ->_addLeft($this->getLayout()->createBlock('ustorelocator/adminhtml_location_edit_tabs'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            $req = $this->getRequest();
            $redirectBack  = $req->getParam('back', false);
            $id = $req->getParam('id');
            try {
                $stores = $req->getParam('stores');
                if(is_array($stores)) {
                    $stores = join(',',$stores);
                }
                $icons = $req->getParam('icon');
                $icon = isset($icons['value']) ? $icons['value'] : null;
                if (isset($icons['delete']) && $icons['delete'] == 1) {
                    $file = Mage::getBaseDir('media') . $icon;
                    if (file_exists($file) && is_writable($file)) {
                        unlink($file);
                    } else {
                        $this->_getSession()->addWarning($this->__("Icon file does not exist or cannot be deleted."));
                    }
                    $icon = null;
                } else {
                    if(isset($_FILES['icon']['tmp_name']) && !empty($_FILES['icon']['tmp_name'])) {
                        try {$uploader = new Varien_File_Uploader('icon');
                            $target = $this->getIconsDir();
                            $result = $uploader->setAllowCreateFolders(true)
                                         ->setAllowedExtensions(array('png'))
                                         ->addValidateCallback('size', Mage::helper('ustorelocator/protected'), 'validateIconSize')
                                         ->save($target);
                            $icon = Mage::helper('ustorelocator')->getIconDirPrefix() . DS . $result['file'];

                        } catch (Exception $e) {
                            $this->_getSession()->addWarning($e->getMessage());
                        }
                    }
                }
                $udVendor = $req->getParam('udropship_vendor');

                if(empty($udVendor) && $udVendor !== 0) $udVendor = null;

                $model = Mage::getModel('ustorelocator/location')
                //->addData($req->getParams())
                        ->setId($req->getParam('id'))
                        ->setTitle($req->getParam('title'))
                        ->setAddress($req->getParam('address'))
                        ->setNotes($req->getParam('notes'))
                        ->setLongitude($req->getParam('longitude'))
                        ->setLatitude($req->getParam('latitude'))
                        ->setAddressDisplay($req->getParam('address_display'))
                        ->setNotes($req->getParam('notes'))
                        ->setWebsiteUrl($req->getParam('website_url'))
                        ->setPhone($req->getParam('phone'))
                        ->setUdropshipVendor($udVendor)
                        ->setCountry($req->getParam('country'))
                        ->setProductTypes($req->getParam('product_types'))
                        ->setIsFeatured($req->getParam('is_featured'))
                        ->setUseLabel($req->getParam('use_label'))
                        ->setZoom($req->getParam('zoom') ? $req->getParam('zoom'): 15) // set default location zoom to 15.
                        ->setStores($stores)
                        ->setIcon($icon);
                
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store location was successfully saved'));
                if ($redirectBack) {
                    if ($model->getId()) {
                        $id = $model->getId();
                    }
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/');
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function getIconsDir()
    {
        return Mage::helper('ustorelocator')->getIconsDir();
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('ustorelocator/location');
                /* @var $model Mage_Rating_Model_Rating */
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store location was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/ustorelocator');
    }

    protected function _validateSecretKey()
    {
        if ($this->getRequest()->getActionName() == 'updateEmptyGeoLocations') {
            return true;
        }
        return parent::_validateSecretKey();
    }

    public function updateEmptyGeoLocationsAction()
    {
        Mage::helper('ustorelocator')->populateEmptyGeoLocations();
        exit;
    }

    public function exportAction()
    {
        try {
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            $stores = array();
            if($store) {
                $stores[] = $store;
            } else if ($website) {
                $stores = Mage::app()->getWebsite($website)->getStoreCodes();
            }
            /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Location_Collection */
            $collection = Mage::getModel('ustorelocator/location')->getCollection();
            if(!empty($stores)) {
                $select = $collection->getSelect();
                $select->where('`stores`=""')->orWhere('ISNULL(`stores`)'); // if store filter, select non filtered stores,
                foreach($stores as $s) {
                    $select->orWhere('FIND_IN_SET(?, `stores`)', $s); // and then filtered
                }
            }
            $data = $collection->getData();
            if (!empty($data)) {
                $target = Mage::getConfig()->getVarDir('storelocator/export');
                Mage::getConfig()->createDirIfNotExists($target);
                $filename = 'export.' . time() . '.csv';
                $path = $target . DS .$filename;
                $fh = @fopen($path, 'w');
                if (!$fh) {
                    Mage::throwException(Mage::helper('ustorelocator')->__("Could not open %s for writing.", $path));
                }
                $headers = false;
                foreach($data as $line) {
                    if(isset($line['location_id'])) {
                        unset($line['location_id']);
                    }
                    if($headers === false) {
                        $headers = array_keys($line);
                        fputcsv($fh, $headers);
                    }
                    fputcsv($fh, $line);
                }
                fclose($fh);
                return $this->_prepareDownloadResponse($filename, file_get_contents($path), 'text/csv');
            } else {
                $this->_getSession()->addWarning(Mage::helper('ustorelocator')->__("No locations found."));
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/'); // redirect to previous page
        }
    }

    public function massDeleteAction()
    {
        // implement mass delete action
        $ids = $this->getRequest()->getParam('location');
        try {
            if (!empty($ids)) {
                $collection = $this->getLocationCollection($ids);
                $result = $collection->walk('delete');
                $this->_getSession()->addSuccess($this->__("%d locations deleted.", count($result)));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    public function massCoordinatesAction()
    {
        // implement mass renew coordinates action.
        $ids = $this->getRequest()->getParam('location');
        try {
            if (!empty($ids)) {
                $collection = $this->getLocationCollection($ids);
                /* @var $helper Unirgy_StoreLocator_Helper_Data */
                $helper = Mage::helper('ustorelocator');
                $result = $helper->populateEmptyGeoLocations($collection);
                if (false === $result) {
                    $this->_getSession()->addError($this->__("Coordinates not updated, check 'usl.log' for details."));
                } else {
                    $this->_getSession()->addSuccess($this->__("Coordinates of %d locations updated.", $result));
                }
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param $ids
     * @return Unirgy_StoreLocator_Model_Mysql4_Location_Collection
     */
    protected function getLocationCollection($ids)
    {
        /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Location_Collection */
        $collection = Mage::getModel('ustorelocator/location')->getCollection();
        $collection->addFieldToFilter('location_id', array('in' => $ids));
        return $collection;
    }

    public function reinstallAction()
    {
        try {
            /* @var $installer Unirgy_StoreLocator_Model_Resource_Setup */
            $installer = new Unirgy_StoreLocator_Model_Resource_Setup('ustorelocator_setup');
            $installer->reinstall();
            $this->_getSession()->addSuccess($this->__("Module DB files reinstalled"));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
