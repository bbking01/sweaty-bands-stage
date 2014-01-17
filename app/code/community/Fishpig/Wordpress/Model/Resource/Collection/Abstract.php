<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

	/**
	 * I know this is an ugly hack but it's the only way I can get Magento 1.5.1.0 to work 
	 * now that I have converted the extension to the new Resource model system
	 *
	 */
	if (version_compare(Mage::getVersion(), '1.6.0.0', '<')) {
		abstract class Fishpig_Wordpress_Model_Resource_Collection_Abstract_Hack extends Mage_Core_Model_Mysql4_Collection_Abstract{}
	}
	else {
		abstract class Fishpig_Wordpress_Model_Resource_Collection_Abstract_Hack extends Mage_Core_Model_Resource_Db_Collection_Abstract{}
	}
	/**
	 * End of hack, thank fully
	 *
	 */

abstract class Fishpig_Wordpress_Model_Resource_Collection_Abstract extends Fishpig_Wordpress_Model_Resource_Collection_Abstract_Hack
{
	/**
	 * An array of all of the meta fields that have been joined to this collection
	 *
	 * @var array
	 */
	protected $_metaFieldsJoined = array();
	
	/**
	 * Add a meta field to the select statement columns section
	 *
	 * @param string $field
	 * @return $this
	 */
	public function addMetaFieldToSelect($metaKey)
	{
		if (($field = $this->_joinMetaField($metaKey)) !== false) {
			$this->getSelect()->columns(array($metaKey => $field));
		}
		
		return $this;
	}
	
	/**
	 * Add a meta field to the filter (where) part of the query
	 *
	 * @param string $field
	 * @param string|array $filter
	 * @return $this
	 */
	public function addMetaFieldToFilter($metaKey, $filter)
	{
		if (($field = $this->_joinMetaField($metaKey)) !== false) {
			$this->addFieldToFilter($field, $filter);
		}
		
		return $this;
	}
	
	/**
	 * Add a meta field to the SQL order section
	 *
	 * @param string $field
	 * @param string $dir = 'asc'
	 * @return $this
	 */
	public function addMetaFieldToSort($field, $dir = 'asc')
	{
		$this->getSelect()->order($field . ' ' . $dir);
		
		return $this;
	}
	
	/**
	 * Join a meta field to the query
	 *
	 * @param string $field
	 * @return $this
	 */
	protected function _joinMetaField($field)
	{
		$model = $this->getNewEmptyItem();
			
		if ($model->hasMeta()) {
			if (!isset($this->_metaFieldsJoined[$field])) {
				$alias = $this->_getMetaFieldAlias($field);

				$meta = new Varien_Object(array(
					'key' => $field,
					'alias' => $alias,
				));
				
				Mage::dispatchEvent($model->getEventPrefix() . '_join_meta_field', array('collection' => $this, 'meta' => $meta));
				
				if ($meta->getCanSkipJoin()) {
					$this->_metaFieldsJoined[$field] = $meta->getAlias();
				}
				else {
					$condition = "`{$alias}`.`{$model->getMetaObjectField()}`=`main_table`.`{$model->getResource()->getIdFieldName()}` AND "
						. $this->getConnection()->quoteInto("`{$alias}`.`meta_key`=?", $field);
						
					$this->getSelect()->joinLeft(array($alias => $model->getMetaTable()), $condition, '');

					$this->_metaFieldsJoined[$field] = $alias . '.meta_value';;
				}
			}
			
			return $this->_metaFieldsJoined[$field];
		}

		return false;
	}
	
	/**
	 * Convert a meta key to it's alias
	 * This is used in all SQL queries
	 *
	 * @param string $field
	 * @return string
	 */
	protected function _getMetaFieldAlias($field)
	{
		return 'meta_field_' . str_replace('-', '_', $field);
	}
}
