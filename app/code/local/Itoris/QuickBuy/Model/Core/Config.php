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

class Itoris_QuickBuy_Model_Core_Config extends Mage_Core_Model_Config {

	/**
	 * Disable modules updates (reindex issue)
	 *
	 * @param null $path
	 * @param string $scope
	 * @param null $scopeCode
	 * @return bool|Mage_Core_Model_Config_Element
	 */
	public function getNode($path=null, $scope='', $scopeCode=null) {
		if ($path == Mage_Core_Model_App::XML_PATH_SKIP_PROCESS_MODULES_UPDATES) {
			return true;
		}
		return parent::getNode($path, $scope, $scopeCode);
	}
}
?>