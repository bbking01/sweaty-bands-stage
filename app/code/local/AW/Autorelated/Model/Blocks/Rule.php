<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Model_Blocks_Rule extends Mage_CatalogRule_Model_Rule
{
    const EMPTY_ARRAY_ON_NO_CONDITIONS = 1;
    const ALL_IDS_ON_NO_CONDITIONS = 2;

    protected $_matchedCollection;
    protected $_joinedAttributes;
    protected $_rulesConditions;
    protected $_retMode = 1;

    public function __construct()
    {
        parent::__construct();
        $this->_joinedAttributes = array();
    }

    public function getMatchingProductIds()
    {
        //check if conditions isset and not null
        if (!$this->_getRuleConditions()) {
            if ($this->getReturnMode() === self::EMPTY_ARRAY_ON_NO_CONDITIONS) {
                return array();
            } else {
                return Mage::getModel('catalog/product')->getCollection()->getAllIds();
            }
        }

        $this->_matchedCollection = Mage::getModel('catalog/product')->getCollection();
        $this->_rulesConditions = array();

        $this->addRulesToMatch($this->_getRuleConditions(), $this->getConditions()->getAggregator(), $this->getConditions()->getValue());
        $sql = $this->prepareRules($this->_rulesConditions, $this->getConditions()->getAggregator(), $this->getConditions()->getValue());

        if ($sql) {
            $this->_matchedCollection->getSelect()->where($sql, null, Zend_Db::INT_TYPE);
            $this->_matchedCollection->getSelect()->group('e.entity_id');
            $this->_matchedCollection->getSelect()->order('e.entity_id ASC');
            return $this->_matchedCollection->getAllIds();
        } else
            return null;
    }

    protected function prepareRules($rules = null, $aggregator = null, $condition = null)
    {
        if (!$rules)
            return null;
        $sql = null;
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                $rule = unserialize($rule);

                if ($aggregator == null)
                    $aggregator = $rule['aggregator'];

                $condition = $rule['condition'];

                $where = ($aggregator == 'all') ? 'where' : 'orWhere';

                $suffix = ($rule['aggregator'] == 'any') ? ' OR ' : ' AND ';
                $sql = (isset($sql)) ? $sql . $suffix . $rule['sql'] : $rule['sql'];
            } else {
                $suffix = ($aggregator == 'any') ? ' OR ' : ' AND ';
                $sign = ($condition) ? '' : '!';
                $sql = (isset($sql)) ? $sql . $suffix . $sign . '(' . $this->prepareRules($rule, $aggregator, $condition) . ')' : $this->prepareRules($rule, $aggregator, $condition);
            }
        }
        return $sql;
    }

    protected function addRulesToMatch($rules, $aggregator, $condition)
    {
        foreach ($rules as $rule) {
            $ruleID = preg_replace('/^[^\d]+/i', '', $rule->getId());
            $ruleID = str_replace('--', '/', $ruleID);

            if (!$rule->getRelated()) {
                if ($rule->getAttribute()) {

                    $where = $this->prepareSqlForAtt($rule->getAttribute(), $this->_joinedAttributes, $this->_matchedCollection, $rule->getOperator(), $rule->getValue());

                    if (!$where)
                        continue;

                    $where = ($condition) ? $where : '!' . $where;

                    $data = array(
                        'aggregator' => $aggregator,
                        'condition' => $condition,
                        'sql' => $where
                    );
                    $data = serialize($data);

                    $array = $this->setKeyByPath($ruleID, $data);
                    if (!empty($this->_rulesConditions))
                        $this->_rulesConditions = $this->multimerge($this->_rulesConditions, $array);
                    else
                        $this->_rulesConditions = $array;
                }
            } else {
                $this->addRulesToMatch($rule->getRelated(), $rule->getAggregator(), $rule->getValue());
            }
        }
    }

    /**
     * prepare sql for attribute compare
     *
     * @param  object $attribute  - attribute class with data
     * @param  array  $joinedAttributes  - attribute which was joined into query
     * @param  Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection - product collection
     * @param  string $operator   - operator for compare
     * @param  string $value       - value for compare
     * @return string $where      - sql query
     */
    public function prepareSqlForAtt($attribute, $joinedAttributes, $collection, $operator, $value)
    {
        $nOperator = array('==', '!{}', '{}', '!()', '()');
        $mOperator = array('=', 'NOT LIKE', 'LIKE', 'NOT IN', 'IN');

        //category operator
        //$cnOperator = array('==', '!=', '=', '>=', '<=', '>', '<', '!{}', '{}', '!()', '()', 'NOT LIKE', 'LIKE');
        $cnOperator = array('==', '!=', '<=', '>=', '=', '>', '<', '!{}', '{}', '!()', '()', 'NOT LIKE', 'LIKE');
        //$cmOperator = array('=','!=','!=','!=','!=','!=','NOT IN','IN','NOT IN','IN');
        //$cmOperator = array('IN', 'NOT IN', 'IN', 'NOT IN', 'NOT IN', 'NOT IN', 'NO IN', 'NOT IN', 'IN', 'NOT IN', 'IN', 'NOT IN', 'IN');
        $cmOperator = array('IN', 'NOT IN', 'NOT IN', 'NOT IN', 'IN', 'NOT IN', 'NO IN', 'NOT IN', 'IN', 'NOT IN', 'IN', 'NOT IN', 'IN');

        $operator = ($attribute == 'category_ids') ? str_replace($cnOperator, $cmOperator, $operator) : str_replace($nOperator, $mOperator, $operator);
        /* Quote rule value depending operator type */
        switch ($operator) {
            case 'LIKE':
            case 'NOT LIKE':
                $value = $collection->getConnection()->quote("%{$value}%");
                break;
            case 'IN':
            case 'NOT IN':
                $value = '(' . $collection->getConnection()->quote(explode(',', $value)) . ')';
                break;
            default:
                $value = $collection->getConnection()->quote($value);
        }
        /* */

        if (!in_array($attribute, $this->_joinedAttributes)) {
            array_push($this->_joinedAttributes, $attribute);
            //$this->_joinedAttributes = $joinedAttributes;

            $att = Mage::getModel('catalog/product')->getResource()->getAttribute($attribute);

            if (!$att && $attribute != 'category_ids')
                return null;

            switch ($attribute) {
                case 'sku':
                    $collection->getSelect()
                        ->join(array('att_table_' . $attribute => $att->getBackend()->getTable()), 'att_table_' . $attribute . '.entity_id = e.entity_id', array('sku'));
                    break;
                case 'category_ids':
                    $collection->getSelect()
                        ->joinLeft(array('att_table_' . $attribute => $collection->getTable('catalog/category_product')), 'att_table_' . $attribute . '.product_id = e.entity_id', array('category_id'));
                    break;
                case 'attribute_set_id':
                    $collection->getSelect()
                        ->joinLeft(array('att_table_' . $attribute => $att->getBackend()->getTable()), 'att_table_' . $attribute . '.entity_id = e.entity_id', array('attribute_set_id'));
                    break;
                case 'price':
                    $collection->addPriceData();
                    break;
                default:
                    $collection->getSelect()
                        ->joinLeft(array('att_table_' . $attribute => $att->getBackend()->getTable()), 'att_table_' . $attribute . '.entity_id = e.entity_id AND att_table_' . $attribute . '.attribute_id = ' . $att->getId(), array('value'));
            }
        }

        switch ($attribute) {
            case 'sku':
                $where = '(att_table_' . $attribute . '.' . 'sku' . ' ' . $operator . ' ' . $value . ')';
                break;
            case 'category_ids':
                $where = '(IFNULL(att_table_' . $attribute . '.' . 'category_id,\'\')' . ' ' . $operator . ' ' . $value . ' AND e.entity_id ' . $operator . '(SELECT `product_id` FROM `' . $collection->getTable('catalog/category_product') . '` WHERE `category_id` IN' . $value . ')' . '    )';
                break;
            case 'price':
                $where = $collection->getConnection()->quoteInto('(price_index.final_price ' . $operator . '?)', $value);
                break;
            case 'attribute_set_id':
                $where = '(IFNULL(att_table_' . $attribute . '.' . 'attribute_set_id,\'\')' . ' ' . $operator . ' ' . $value . ')';
                break;
            default:
                $where = '(IFNULL(att_table_' . $attribute . '.' . 'value,\'\')' . ' ' . $operator . ' ' . $value . ')';
        }

        return $where;
    }

    public function setKeyByPath($path, $data)
    {
        $array = array();
        $path = explode('/', $path);
        foreach ($path as $key) {
            if (isset($last)) {
                $ghost[$last] = array($key => $data);
                unset($x);
                $x = &$ghost[$last][$key];
                $ghost = $ghost[$last];
                $last = $key;
            } else {
                $array[$key] = $data;
                $x = &$array[$key];
                $ghost = $array;
                $last = $key;
            }
        }
        unset($x);
        return $array;
    }

    public function multimerge($array1, $array2)
    {
        if (is_array($array2) && count($array2)) {
            foreach ($array2 as $k => $v) {
                if (is_array($v) && count($v) && isset($array1[$k])) {
                    $array1[$k] = $this->multimerge($array1[$k], $v);
                } else {
                    $array1[$k] = $v;
                }
            }
        } else {
            $array1 = $array2;
        }
        return $array1;
    }

    protected function _getRuleConditions()
    {
        $prefix = $this->getConditions()->getPrefix();
        return $this->getConditions()->getData($prefix);
    }

    public function setReturnMode($mode)
    {
        $this->_retMode = $mode;
        return $this;
    }

    public function getReturnMode()
    {
        return $this->_retMode;
    }
}
