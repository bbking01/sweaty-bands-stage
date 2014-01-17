<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-6-28
 * Time: 9:12
 */

class Unirgy_Giftcert_Model_Api_V2
    extends Unirgy_Giftcert_Model_Api
{
    public function items($filters = null)
    {
        $filters = $this->_buildFilterArray($filters);
        $items = parent::items($filters);
        $result = $this->_prepareResultItems($items);
        return $result;
    }

    public function fetch($code)
    {
        $cert = parent::fetch($code);
        $result = $this->_prepareReturnObject($cert);
        return $result;
    }

    public function create($data)
    {
        $data = (array) $data;
        return parent::create($data);
    }

    public function massCreate($items)
    {
        return parent::massCreate($items);
    }

    public function update($code, $data)
    {
        $data = (array) $data;
        return (int) parent::update($code, $data);
    }

    public function massUpdate($items)
    {
        foreach ($items as $key => $item) {
            $items[$key] = (array)$item;
        }
        return parent::massUpdate($items);
    }

    public function delete($code)
    {
        return parent::delete($code);
    }

    public function massDelete($items)
    {
        return parent::massDelete($items);
    }

    protected function _buildFilterArray($filters)
    {
        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[$_filter->key] = $_filter->value;
            }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                $preparedFilters[$_filter->key] = array(
                    $_value->key => $_value->value
                );
            }
        }

        return $preparedFilters;
    }

    protected function _prepareResultItems($items)
    {
        $result = array();
        foreach ($items as $item) {
            $result[] = $this->_prepareReturnObject($item);
        }
        return $result;
    }

    /**
     * @param array $cert
     * @return object
     */
    protected function _prepareReturnObject($cert)
    {
        return (object) $cert;
    }
}
