<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-10-21
 * Time: 0:09
 */

class Unirgy_StoreLocator_Model_Import
    extends Mage_Core_Model_Config_Data
{
    const TITLE   = 'title';
    const ADDRESS = 'address';
    protected $_tablesCleared;
    /**
     * @var array
     */
    protected $_importStoreIds;
    protected $_importHeaders;
    protected $_importErrors;
    protected $_importedRows;
    protected $_tableColumns;

    /**
     * @var Unirgy_StoreLocator_Model_Mysql4_Location
     */
    protected $_locationsResource;
    public $importHeadersIdx;
    public $validationLength;

    protected function _afterSave()
    {
        if (empty($_FILES['groups']['tmp_name']['general']['fields']['upload_locations']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['general']['fields']['upload_locations']['value'];

        $app = Mage::app();

        switch ($this->getData('scope')) {
            case 'websites':
                $website  = $app->getWebsite($this->getData('website_code'));
                $storeIds = $website->getStoreIds();
                break;
            case 'stores' :
                $storeIds = (array)$app->getStore($this->getData('store_code'))->getId();
                break;
            default :
                $storeIds = (array)$app->getStore()->getId();
                break;
        }

        $this->_importStoreIds = $storeIds;
        $this->_importErrors   = array();
        $this->_importedRows   = 0;

        $io   = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
        $rawHeaders = $io->streamReadCsv();
        $this->validateImportedCoolumns($rawHeaders, $io);

        $this->importDb($io, $rawHeaders);

        return $this;
    }

    /**
     * Import stores as objects, a bit slow for many stores
     *
     * @param Varien_Io_File $io
     * @param array          $rawHeaders
     * @return $this
     */
    protected function importObjects($io, $rawHeaders)
    {
        try {
            $model = Mage::getModel("ustorelocator/location");
            $rowNumber = 1;

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }
                $importData = array_combine($rawHeaders, $csvLine);
                $row = $this->_getImportRowAssoc($importData, $rowNumber);
                if ($row !== false) {
                    $tmp = clone $model;
                    $tmp->setData($row);
                    $tmp->save();
                    unset($tmp);
                }
            }
        } catch(Exception $e) {
            Mage::log($e->getTraceAsString(), Zend_Log::CRIT, 'sl.log', true);
        }
        return $this;
    }

    /**
     * @param Varien_Io_File $io
     * @param                $rawHeaders
     * @return $this
     */
    protected function importDb($io, $rawHeaders)
    {
        $resource = $this->_getLocationResource();

        /* @var $hlp Unirgy_StoreLocator_Helper_Data */
        $hlp = Mage::helper('ustorelocator');
        /* @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $adapter = $resource->getReadConnection();
        $adapter->beginTransaction();
        $this->clearOldLocations($adapter, $resource);
        try {
            $rowNumber  = 1;
            $importData = array();

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRowAssoc(array_combine($rawHeaders, $csvLine), $rowNumber);
                if ($row !== false) {
                    $this->saveRow($row);
                }
            }

            $io->streamClose();

            $adapter->commit();
            $hlp->populateEmptyGeoLocations();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException($hlp->__('An error occurred while importing locations.'));
        }

        if ($this->_importErrors) {
            $error = $hlp->__('%d locations have been imported.', $this->_importedRows);
            $error .= $hlp->__(
                'See the following list of errors for each locations that has not been imported: %s',
                implode(" \n", $this->_importErrors)
            );
            Mage::throwException($error);
        }
        return $this;
    }

    /**
     * @return Unirgy_StoreLocator_Model_Mysql4_Location
     */
    protected function _getLocationResource()
    {
        $resource = $this->_locationsResource;
        if (!$resource) {
            $resource                 = Mage::getResourceSingleton('ustorelocator/location');
            $this->_locationsResource = $resource;
        }

        return $resource;
    }

    /**
     * Prepare import data
     * There are 2 required fields - title, address
     *
     * There are many optional fields, bellow are defaults used in case of an empty value:
     *
     * latitude => 0, // coordinate of lactation, if omitted, it will be searched from google.
     * longitude => 0, // coordinate of location
     * address_display => 'address', // formatted address to be displayed to users. If omitted,
     * 'address' field will be used
     * notes => '', // notes that will be displayed to users when clicking on store icon
     * website_url => '', // store website or contact email. To be used as url, start it with http
     * phone => '', // store phone number
     * product_types => '', // store tags, what type of products are sold here. semicolon list of tags
     * country => null, // country of the location, not verified
     * stores => null, // string of semicolons separated store codes
     * icon => null, // custom icon file
     * use_label => 1, // should numeric label be displayed over the Icon used
     * is_featured => 0, // is product in featured list
     * zoom => 10 // initial location zoom
     * udropship_vendor => null, // if dropship  is installed, you can relate location to a vendor
     *
     * @param array $row
     * @param int   $rowNumber
     * @return array
     */
    protected function _getImportRowAssoc($row, $rowNumber)
    {
        $required = array(self::TITLE, self::ADDRESS);
        $hlp      = Mage::helper('ustorelocator');

        $importRow = array();
        foreach ($row as $key => $field) {
            if (in_array($key, $required) && empty($field)) {
                $this->_importErrors[] = $hlp->__("Empty required field: '%s' in row: %s", $key, $rowNumber);
                return false;
            }
            $value           = !empty($field) ? $field : null;
            $importRow[$key] = $this->cleanUpFieldData($key, $value);
        }
        $this->validateImportRow($importRow);
        $obj = new Varien_Object($importRow);
        Mage::dispatchEvent("store_location_import_row_validate", array("row" => $obj));
        return $obj->getData();
    }

    protected function cleanUpFieldData($field, $value)
    {
        switch ($field) {
            case 'latitude':
            case 'longitude':
                if (empty($value)) {
                    $value = 0;
                } else {
                    $value = round(Mage::app()->getLocale()->getNumber($value), 9);
                }
                break;
            case 'notes':
                if ($value === null) {
                    $value = '';
                }
                break;
            case 'address_display':
            case 'address':
            case 'phone':
            case 'product_types':
            case 'website_url':
            case 'country':
            case 'stores':
            case 'icon':
                $length = isset($this->validationLength[$field]) ? $this->validationLength[$field] : null;
                if ($value === null) {
                    $value = '';
                } else if ($length && strlen($value) > $length) {
                    $this->_importErrors[] = $hlp->__(
                        "Value for '%s' is too long. Max allowed length is %s",
                        $field, $length
                    );

                    return false;
                }
                break;
            case 'use_label':
                if ($value === null) {
                    $value = 1;
                } else {
                    $value = ($value == 1) ? 1 : 0;
                }
                break;
            case 'is_featured':
                if ($value === null) {
                    $value = 0;
                } else {
                    $value = ($value == 1) ? 1 : 0;
                }
                break;
            case 'zoom':
                if ($value === null) {
                    $value = 10;
                } else {
                    $value = (int)$value;
                    if ($value < 1) {
                        $value = 1;
                    } else if ($value > 25) {
                        $value = 25;
                    }
                }
                break;
            case 'udropship_vendor':
                $ud = Mage::getConfig()->getNode('modules/Unirgy_Dropship');
                if (!$ud || (string)$ud->active == 'false') {
                    $value = null;
                    break;
                }
                if ($value !== null) {
                    $vendor = Mage::getModel('udropship/vendor');
                    $value  = $vendor->getId();
                }
                break;
        }
        return $value;
    }

    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns  = $this->_importHeaders;
            $resource = $this->_getLocationResource();
            $resource->getReadConnection()->insertArray($resource->getMainTable(), $columns, $data);
        }
    }

    protected function saveRow($row)
    {
        if(!empty($row)){
            $data = array();
            foreach ($this->_importHeaders as $f) {
                if(isset($row[$f])){
                    $data[$f] = $row[$f];
                }
            }

            $resource = $this->_getLocationResource();
            $resource->getReadConnection()->insert($resource->getMainTable(), $data);
            $id = $resource->getReadConnection()->lastInsertId($resource->getMainTable());
            $row['location_id'] = $id;
            $obj = new Varien_Object($row);
            Mage::dispatchEvent("store_location_import_row_insert_after", array('row' => $obj));
        }
    }

    /**
     * @param $headers
     * @return array|bool
     */
    protected function _setHeaders($headers)
    {
        $resource = $this->_getLocationResource();
        $conn     = $resource->getReadConnection();
        $table    = $conn->describeTable($resource->getMainTable());
        foreach ($table as $column) {
            $colName = $column['COLUMN_NAME'];
            $key     = array_search($colName, $headers);
            if ($key !== false) {
                $this->_importHeaders[$key]       = $colName;
                $this->importHeadersIdx[$colName] = $key;
                if (!empty($column['LENGTH'])) {
                    $this->validationLength[$colName] = $column['LENGTH'];
                }
            }
        }
        if (!empty($this->importHeadersIdx) &&
            isset($this->importHeadersIdx[self::TITLE], $this->importHeadersIdx[self::ADDRESS])
        ) {
            return $this->_importHeaders;
        }

        return false;
    }

    protected function _shouldOverwrite()
    {
        $ov = Mage::getStoreConfig('ustorelocator/general/upload_overwrite');
        if (isset($_POST['groups']['general']['fields']['upload_overwrite']['value'])) {
            $ov = $_POST['groups']['general']['fields']['upload_overwrite']['value'];
        }

        return $ov;
    }

    /**
     * @param Varien_Db_Adapter_Pdo_Mysql $adapter
     * @param Unirgy_StoreLocator_Model_Mysql4_Location $resource
     */
    protected function clearOldLocations($adapter, $resource)
    {
        $overwrite = $this->_shouldOverwrite();
        if ($overwrite && !$this->_tablesCleared) {
            $adapter->delete($resource->getMainTable());
            $this->_tablesCleared = true;
        }
    }

    /**
     * @param array $rawHeaders
     * @param Varien_Io_File $io
     */
    protected function validateImportedCoolumns($rawHeaders, $io)
    {
        // check and skip headers
        $headers = $this->_setHeaders($rawHeaders);
        if ($headers === false || count($headers) < 2) {
            $io->streamClose();
            Mage::throwException(
                Mage::helper('ustorelocator')
                ->__('Invalid Store Locations File Format. Provide at least "title" and "address".')
            );
        }
    }

    protected function validateImportRow(&$importRow)
    {
        if(!$importRow['address_display'] && isset($importRow['address']) && $importRow['address']){
            $importRow['address_display'] = $importRow['address'];
        }
    }
}
