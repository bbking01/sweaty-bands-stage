<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Data extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Retrieve the top link URL
	 *
	 * @return false|string
	 */
	public function getTopLinkUrl()
	{
		try {
			if ($this->isEnabled()) {
				if ($this->isFullyIntegrated()) {
					return $this->getUrl();
				}
			
				return $this->getWpOption('home');
			}
		}
		catch (Exception $e) {
			$this->log('Magento & WordPress are not correctly integrated (see entry below).');
			$this->log($e->getMessage());
		}
		
		return false;
	}
	
	/**
	 * Retrieve the position for the top link
	 *
	 * @return false|int
	 */
	public function getTopLinkPosition()
	{
		if ($this->isEnabled()) {
			return (int)Mage::getStoreConfig('wordpress_blog/layout/toplink_position');
		}
		
		return false;
	}
	
	/**
	 * Returns the label for the top link
	 * This is also used for the breadcrumb
	 *
	 * @return false|string
	 */
	public function getTopLinkLabel()
	{
		if ($this->isEnabled()) {
			return Mage::getStoreConfig('wordpress_blog/layout/toplink_label');
		}
		
		return false;
	}
	
	/**
	 * Returns the pretty version of the blog route
	 * This is deprecated. Instead, use self::getTopLinkLabel
	 *
	 * @return false|string
	 */
	public function getPrettyBlogRoute()
	{
		return $this->getTopLinkLabel();
	}
	
	/**
	  * Returns the URL Wordpress is installed on
	  *
	  * @param string $extra
	  * @return string
	  */
	public function getBaseUrl($extra = '')
	{
		return rtrim($this->getWpOption('siteurl'), '/') . '/' . $extra;
	}
	
	/**
	  * Get Wordpress Admin URL
	  *
	  */
	public function getAdminUrl($extra = null)
	{
		return $this->getBaseUrl('wp-admin/' . $extra);
	}
	
	/**
	 * Returns the given string prefixed with the Wordpress table prefix
	 *
	 * @return string
	 */
	public function getTableName($table)
	{
		return Mage::helper('wordpress/database')->getTableName($table);
	}
	
	/**
	 * Determine whether the module is enabled
	 * This can be changed by going to System > Configuration > Advanced
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::getStoreConfigFlag('wordpress/module/enabled')
			&& !Mage::getStoreConfig('advanced/modules_disable_output/Fishpig_Wordpress');
	}
	
	/**
	  * Formats a Wordpress date string
	  *
	  */
	public function formatDate($date, $format = null, $f = false)
	{
		if ($format == null) {
			$format = $this->getDefaultDateFormat();
		}
		
		/**
		 * This allows you to translate month names rather than whole date strings
		 * eg. "March","Mars"
		 *
		 */
		$len = strlen($format);
		$out = '';
		
		for( $i = 0; $i < $len; $i++) {	
			$out .= $this->__(Mage::getModel('core/date')->date($format[$i], strtotime($date)));
		}
		
		return $out;
	}
	
	/**
	  * Formats a Wordpress date string
	  *
	  */
	public function formatTime($time, $format = null)
	{
		if ($format == null) {
			$format = $this->getDefaultTimeFormat();
		}
		
		return $this->formatDate($time, $format);
	}
	
	/**
	  * Return the default date formatting
	  *
	  */
	public function getDefaultDateFormat()
	{
		return $this->getWpOption('date_format', 'F jS, Y');
	}
	
	/**
	  * Return the default time formatting
	  *
	  */
	public function getDefaultTimeFormat()
	{
		return $this->getWpOption('time_format', 'g:ia');
	}
	
	/**
	 * Determine whether a WordPress plugin is enabled in the WP admin
	 *
	 * @param string $name
	 * @param bool $format
	 * @return bool
	 */
	public function isPluginEnabled($name, $format = true)
	{
		$name = $format ? Mage::getSingleton('catalog/product_url')->formatUrlKey($name) : $name;
		
		$plugins = false;

		if ($this->isWordPressMU() && Mage::helper('wpmultisite')->canRun()) {
			$plugins = Mage::helper('wpmultisite')->getWpSiteOption('active_sitewide_plugins');
			$plugins = unserialize($plugins);
		}
		else if ($plugins = $this->getWpOption('active_plugins')) {
			$plugins = unserialize($plugins);
		}
		
		if ($plugins) {
			foreach($plugins as $a => $b) {
				if (strpos($a, $name) !== false || strpos($b, $name) !== false) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Determine whether Cryllic locale support is enabled
	 *
	 * @return bool
	 */
	public function isCryllicLocaleEnabled()
	{
		return Mage::getStoreConfigFlag('wordpress_blog/locale/cyrillic_enabled');
	}

	/**
	 * Determine whether to force single store
	 *
	 * @return bool
	 */
	public function forceSingleStore()
	{
		return Mage::getStoreConfigFlag('wordpress_blog/associations/force_single_store');
	}
	
	/**
	 * Determine whether Fishpig_WordpressMu can run
	 *
	 * @return bool
	 */
	public function isWordPressMU()
	{
		if (!$this->_isCached('is_wpmu')) {
			$this->_cache('is_wpmu', false);
			
			if ($this->isWordPressMUInstalled()) {
				$this->_cache('is_wpmu', Mage::helper('wpmultisite')->canRun());
			}
		}
		
		return $this->_cached('is_wpmu');
	}

	/**
	 * Determine whether Fishpig_WordpressMu is installed
	 *
	 * @return bool
	 */
	public function isWordPressMUInstalled()
	{
		if (!$this->_isCached('is_wpmu_installed')) {
			$modules = (array)Mage::getConfig()->getNode('modules')->children();

			if (isset($modules['Fishpig_WordpressMu'])) {
				$module = (array)$modules['Fishpig_WordpressMu'];

				$this->_cache('is_wpmu_installed', ($module['active'] == 'true' || $module['active'] === true));
			}
			else if (isset($modules['Fishpig_Wordpress_Addon_Multisite'])) {
				$module = (array)$modules['Fishpig_Wordpress_Addon_Multisite'];

				$this->_cache('is_wpmu_installed', ($module['active'] == 'true' || $module['active'] === true));
			}
			else {
				$this->_cache('is_wpmu_installed', false);
			}
		}
		
		return $this->_cached('is_wpmu_installed');
	}
	
	/**
	 * Retrieve the upload URL
	 *
	 * @return string
	 */
	public function getFileUploadUrl()
	{
		$url = $this->getWpOption('fileupload_url');
		
		if (!$url) {
			foreach(array('upload_url_path', 'upload_path') as $config) {
				if ($value = $this->getWpOption($config)) {
					if (strpos($value, 'http') === false) {
						$url = $this->getBaseUrl($value);
					}
					else {
						$url = $value;
					}

					break;
				}
			}
			
			if (!$url) {
				$url = $this->getBaseUrl('wp-content/uploads/');
			}
		}
		
		return rtrim($url, '/') . '/';
	}
	
	/**
	 * Retrieve the local path to file cache path
	 *
	 * @return string
	 */
	public function getFileCachePath()
	{
		return Mage::getBaseDir('var') . DS . 'wordpress' . DS;
	}
	
	/**
	 * Retrieve the path for the WordPress installation
	 * The main use of this is to include the phpass class file for Customer Synchronisation
	 *
	 * @return false|string
	 */
	public function getWordPressPath()
	{
		$path = rtrim($this->getConfigValue('wordpress/misc/path'), DS);
		
		if ($path === '') {
			return false;
		}

		if (substr($path, 0, 1) !== DS) {
			$path = Mage::getBaseDir() . DS . $path . DS;
		}
		else {
			$path = rtrim($path, DS) . DS;
		}

		if (is_dir($path) && is_file($path . 'wp-config.php')) {
			return $path;
		}

		return false;
	}
}
