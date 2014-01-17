<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

	/**
	 * I know this is an ugly hack but it's the only way I can get Magento 1.5.1.0 to work 
	 * now that I have converted the extension to the new Resource model system
	 *
	 */
	if (version_compare(Mage::getVersion(), '1.6.0.0', '<')) {
		abstract class Fishpig_Wordpress_Model_Resource_Admin_User_Hack extends Mage_Core_Model_Mysql4_Abstract{}
	}
	else {
		abstract class Fishpig_Wordpress_Model_Resource_Admin_User_Hack extends Mage_Core_Model_Resource_Db_Abstract{}
	}
	/**
	 * End of hack, thank fully
	 *
	 */

class Fishpig_Wordpress_Model_Resource_Admin_User extends Fishpig_Wordpress_Model_Resource_Admin_User_Hack
{
	public function _construct()
	{
		$this->_init('wordpress/admin_user', 'autologin_id');
	}

	/**
	 * Custom load SQL
	 *
	 * @param string $field - field to match $value to
	 * @param string|int $value - $value to load record based on
	 * @param Mage_Core_Model_Abstract $object - object we're trying to load to
	 */
	protected function _getLoadSelect($field, $value, $object)
	{
		$select = $this->_getReadAdapter()->select()
			->from(array('e' => $this->getMainTable()))
			->where("e.{$field}=?", $value)
			->where('user_id=?', Mage::getSingleton('admin/session')->getUser()->getId())
			->limit(1);

		return $select;
	}
	
	protected function _getWriteAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_write');
	}
	
	public function  _getReadAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_read');
	}
}