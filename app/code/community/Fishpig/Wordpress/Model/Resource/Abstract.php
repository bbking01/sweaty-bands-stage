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
		abstract class Fishpig_Wordpress_Model_Resource_Abstract_Hack extends Mage_Core_Model_Mysql4_Abstract{}
	}
	else {
		abstract class Fishpig_Wordpress_Model_Resource_Abstract_Hack extends Mage_Core_Model_Resource_Db_Abstract{}
	}
	/**
	 * End of hack, thank fully
	 *
	 */

abstract class Fishpig_Wordpress_Model_Resource_Abstract extends Fishpig_Wordpress_Model_Resource_Abstract_Hack
{
	/**
	 * Retrieve the appropriate read adapter
	 *
	 * @return
	 */
	protected function _getReadAdapter()
	{
		return Mage::helper('wordpress/database')->getReadAdapter();
	}

	/**
	 * Retrieve the appropriate write adapter
	 *
	 * @return
	 */	
	protected function _getWriteAdapter()
	{
		return Mage::helper('wordpress/database')->getWriteAdapter();
	}
	
	/**
	 * Retrieve a meta value from the database
	 * This only works if the model is setup to work a meta table
	 * If not, null will be returned
	 *
	 * @param Fishpig_Wordpress_Model_Meta_Abstract $object
	 * @param string $metaKey
	 * @param string $selectField
	 * @return null|mixed
	 */
	public function getMetaValue(Fishpig_Wordpress_Model_Abstract $object, $metaKey, $selectField = 'meta_value')
	{
		if ($object->hasMeta()) {
			$select = $this->_getReadAdapter()
				->select()
				->from($object->getMetaTable(), $selectField)
				->where($object->getMetaObjectField() . '=?', $object->getId())
				->where('meta_key=?', $metaKey)
				->limit(1);

			if(($value = $this->_getReadAdapter()->fetchOne($select)) !== false) {
				return trim($value);
			}
			
			return false;
		}
		
		return null;
	}

	/**
	 * Save a meta value to the database
	 * This only works if the model is setup to work a meta table
	 *
	 * @param Fishpig_Wordpress_Model_Meta_Abstract $object
	 * @param string $metaKey
	 * @param string $metaValue
	 */
	public function setMetaValue(Fishpig_Wordpress_Model_Abstract $object, $metaKey, $metaValue)
	{
		if ($object->hasMeta()) {
			$metaValue = trim($metaValue);
			$metaData = array(
				$object->getMetaObjectField() => $object->getId(),
				'meta_key' => $metaKey,
				'meta_value' => $metaValue,
			);
							
			if (($metaId = $this->getMetaValue($object, $metaKey, $object->getMetaPrimaryKeyField())) !== false) {
				$this->_getWriteAdapter()->update($object->getMetaTable(), $metaData, $object->getMetaPrimaryKeyField() . '=' . $metaId);
			}
			else {
				$this->_getWriteAdapter()->insert($object->getMetaTable(), $metaData);
			}
		}
	}
}
