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
class Unirgy_StoreLocator_Helper_Data extends Mage_Core_Helper_Data
{
    protected $_locations = array();

    public function getLocation($id)
    {
        if (!isset($this->_locations[$id])) {
            $location              = Mage::getModel('ustorelocator/location')->load($id);
            $this->_locations[$id] = $location->getId() ? $location : false;
        }

        return $this->_locations[$id];
    }

    /**
     * @param Unirgy_StoreLocator_Model_Mysql4_Location_Collection $collection
     * @return bool | int
     */
    public function populateEmptyGeoLocations($collection = null)
    {
        set_time_limit(0);
        ob_implicit_flush();
        if(null == $collection){
            $collection = Mage::getModel('ustorelocator/location')->getCollection();
            $collection->getSelect()->where('latitude=0');
        } elseif(!$collection instanceof Unirgy_StoreLocator_Model_Mysql4_Location_Collection) {
            Mage::log($this->__("Expected 'Unirgy_StoreLocator_Model_Mysql4_Location_Collection', got %s", get_class($collection))
                , Zend_Log::ERR, 'usl.log', true);
            return false;
        }
        foreach ($collection as $loc) {
//            usleep(10000); // make a sort delay to not overflow google
//            echo $loc->getTitle()."<br/>";
            $loc->save();
        }
        return $collection->count();
    }

    /**
     * @deprecated
     * @return array
     */
    public function getDefaultLocations()
    {
        $collection = $this->getDefaultCollection();
        $data = $this->prepareLocationData($collection->getData());

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function prepareLocationData($data)
    {
        foreach ($data as $i => &$item) {
            $item['units']    = null;
            $item['distance'] = null;
            if (!empty($item['use_label'])) {
                $item['marker_label'] = ++$i;
            }
            if (!empty($item['icon'])) {
                $v = ltrim($item['icon'], '/');
                if ($icon_info = @getimagesize(Mage::getBaseDir('media') . DS . $v)) {
                    $item['icon_width']  = (int) $icon_info[0];
                    $item['icon_height'] = (int) $icon_info[1];
                }
                $item['icon'] = Mage::getBaseUrl('media') . $v;
            }

            if (!empty($item['is_featured'])) {
                $item['is_featured'] = (boolean)$item['is_featured'];
            }
        }

        return $data;
    }

    /**
     * @return Unirgy_StoreLocator_Model_Mysql4_Location_Collection
     */
    public function getDefaultCollection()
    {
        $country = Mage::getStoreConfig('ustorelocator/general/default_country');
        /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Location_Collection */
        $collection = Mage::getModel('ustorelocator/location')->getCollection();
//        $num        = (int)Mage::getStoreConfig('ustorelocator/general/num_results');
        $store      = $this->getStoreId();
        // filter by store, show only current store locations, or those without any location
        $collection->addStoreFilter($store)
            ->addFieldToFilter('latitude', array('neq' => 0))
            ->addOrder('is_featured', Zend_Db_Select::SQL_DESC)
            ->addOrder('title', Zend_Db_Select::SQL_ASC);
//            ->setPageSize($num); // honor config limit

        if ($country) {
            $collection->addFieldToFilter('country', $country);
        }

        return $collection;
    }

    /**
     * Get configured sort
     * @return string
     */
    public function getLocationSort()
    {

        if (!isset($this->_sort)) {
            if (Mage::getStoreConfig('ustorelocator/general/default_sort')
                == Unirgy_StoreLocator_Model_Settings_Sort::ALPHA
            ) {
                $this->_sort = 'title';
            } else {
                $this->_sort = 'distance';
            }
        }

        return $this->_sort;
    }

    /**
     * @return string
     */
    public function getIconsDir()
    {
        return Mage::getBaseDir('media') . $this->getIconDirPrefix();
    }

    /**
     * @return string
     */
    public function getIconDirPrefix()
    {
        return DS . 'storelocator' . DS . 'locations' . DS . 'icons';
    }

    /**
     * Coordinates of point to center map to when no results are found.
     * @return array
     */
    public function getNoResultCoordinates()
    {
        $_no_result = explode(',', Mage::getStoreConfig('ustorelocator/general/no_result'), 2);
        if (!isset($_no_result[0])) {
            $_no_result[0] = 40;
            $_no_result[1] = -100;
        } elseif (!isset($_no_result[1])) {
            $_no_result[1] = -100;
        }

        $_no_result[0] = floatval($_no_result[0]);
        $_no_result[1] = floatval($_no_result[1]);

        return $_no_result;
    }
}
