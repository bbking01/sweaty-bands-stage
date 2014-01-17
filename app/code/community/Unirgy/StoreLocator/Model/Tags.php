<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-3
 * Time: 22:17
 */
 
class Unirgy_StoreLocator_Model_Tags extends Varien_Object
{

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $conn;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var Unirgy_StoreLocator_Model_Mysql4_Location_Collection
     */
    protected $locations;

    public function getAllTags()
    {
        $conn = $this->getConn();
        $table = $this->getTable();
        $select = $conn->select();
        $select->from(array('main_table' => $table), 'product_type');
        $rows = $conn->fetchAll($select);
        $tags = array();
        foreach($rows as $row) {
            $tags = $this->parseTags($row, $tags);
        }
    }

    public function getTagLocations($tag)
    {
        $collection = $this->getLocationCollection();
        $select = $collection->select();
        $select->where('FIND_IN_SET(?, `product_types`)', $tag);
        return $collection;
    }
    protected function parseTags($row, $tags)
    {
        $values = explode(',', $row);
        foreach($values as $val) {
            if(!isset($tags[$val])) {
                $tags[$val] = 1;
            } else {
                $tags[$val] += 1;
            }
        }
        return $tags;
    }

    /**
     * @param Zend_Db_Adapter_Abstract $conn
     * @return void
     */
    public function setConn($conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getConn()
    {
        $conn = $this->conn;
        if(!$conn) {
            $conn = $this->getLocationCollection()->getConnection();
            $this->setConn($conn);
        }
        return $conn;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        $table = $this->table;
        if(!$table) {
            $table = Mage::getSingleton('core/resource')->getTableName('ustorelocator_location');
            $this->setConn($table);
        }
        return $table;
    }

    /**
     * @return Unirgy_StoreLocator_Model_Mysql4_Location_Collection
     */
    protected function getLocationCollection()
    {
        $collection = $this->locations;
        if(!$collection) {
            $collection = Mage::getModel('ustorelocator/location')->getCollection();
            $this->locations = $collection;
        }
        return $collection;
    }

}
