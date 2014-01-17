<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_QUICKBUY
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */


class Itoris_Quickbuy_Helper_Cache extends Itoris_QuickBuy_Helper_Data {

	static public $CACHE_DIR = '/var/itoris_quickbuy/';

	/** @var int cache lifetime in minutes */
	protected $cacheLifetime = 15;

	public function __construct() {
		self::$CACHE_DIR = Mage::getBaseDir() . str_replace('/', DS, self::$CACHE_DIR);
		$this->cacheLifetime = $this->getDataHelper()->getSettings()->getCacheLifetime();
	}

	/**
	 * Remove all cache files
	 *
	 * @param null $storeId
	 * @return Itoris_Quickbuy_Helper_Cache
	 */
	public function clearAll($storeId = null) {
		if ($this->checkDir()) {
			if ($handle = opendir(self::$CACHE_DIR)) {
				while (false !== ($entry = readdir($handle))) {
					if (is_file(self::$CACHE_DIR . $entry)) {
						unlink(self::$CACHE_DIR . $entry);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Save data in cache file $type.$postfix.txt
	 * $withoutSerialization if true, data will be written as is
	 *
	 * @param $suggestions
	 * @param $type
	 * @param null $postfix
	 * @param bool $withoutSerialization
	 * @return Itoris_Quickbuy_Helper_Cache
	 */
	public function saveCacheInFile($suggestions, $type, $postfix = null, $withoutSerialization = false) {
		if ($this->checkDir()) {
			file_put_contents(self::$CACHE_DIR . $type . $postfix . '.txt', date('U') . '@' . ($withoutSerialization ? $suggestions : serialize($suggestions)));
		}

		return $this;
	}

	/**
	 * Load file content $type.$postfix.txt
	 *
	 * @param $type
	 * @param null $postfix
	 * @return null|string
	 */
	public function loadCacheFromFile($type, $postfix = null, $unserializedData = true) {
		if (is_null($postfix)) {
			$storeId = Mage::app()->getStore()->getId();
			$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
			$postfix = '_gr' . $customerGroupId . '_s' . $storeId;
		}
		if (is_file(self::$CACHE_DIR . $type . $postfix . '.txt')) {
			$content = file_get_contents(self::$CACHE_DIR . $type . $postfix . '.txt');
			$content = preg_replace('/^[0-9]*@/', '', $content);
			if ($unserializedData) {
				return unserialize($content);
			}
			return $content;
		}

		return null;
	}

	/**
	 * Try to create cahce directory
	 *
	 * @return bool
	 */
	public function checkDir() {
		if (!is_dir(self::$CACHE_DIR)) {
			return mkdir(self::$CACHE_DIR);
		}

		return true;
	}

	/**
	 * Check if file $type.$postfix.txt exists
	 *
	 * @param $type
	 * @param null $postfix
	 * @return bool
	 */
	public function isFileExists($type, $postfix = null) {
		if ($this->checkDir() && file_exists(self::$CACHE_DIR . $type . $postfix . '.txt')) {
			$content = file_get_contents(self::$CACHE_DIR . $type . $postfix . '.txt');
			$matches = array();
			preg_match('/^[0-9]*@/', $content, $matches);
			$modificationTimeInSeconds = null;
			if (isset($matches[0])) {
				$modificationTimeInSeconds = (int) rtrim($matches[0], '@');
			}

			if ($modificationTimeInSeconds && (date('U') - $modificationTimeInSeconds) / 60 < $this->cacheLifetime) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return Itoris_QuickBuy_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_quickbuy');
	}
}
?>