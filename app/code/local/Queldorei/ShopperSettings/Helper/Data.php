<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Helper_Data extends Mage_Core_Helper_Abstract
{

	protected function _loadProduct(Mage_Catalog_Model_Product $product)
	{
		$product->load($product->getId());
	}

	public function getLabel(Mage_Catalog_Model_Product $product)
	{
		if ('Mage_Catalog_Model_Product' != get_class($product))
			return;

		$html = '';
		if (!Mage::getStoreConfig("shoppersettings/labels/new_label") &&
			!Mage::getStoreConfig("shoppersettings/labels/sale_label")
		) {
			return $html;
		}
		if (Mage::getStoreConfig("shoppersettings/labels/new_label") && $this->_isNew($product)) {
			$html .= '<div class="new-label new-' . Mage::getStoreConfig('shoppersettings/labels/new_label_position') . '"></div>';
		}
		if (Mage::getStoreConfig("shoppersettings/labels/sale_label") && $this->_isOnSale($product)) {
			$html .= '<div class="sale-label sale-' . Mage::getStoreConfig('shoppersettings/labels/sale_label_position') . '"></div>';
		}

		return $html;
	}

	protected function _checkDate($from, $to)
	{
		$today = strtotime(
			Mage::app()->getLocale()->date()
				->setTime('00:00:00')
				->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
		);

		if ($from && $today < $from) {
			return false;
		}
		if ($to && $today > $to) {
			return false;
		}
		if (!$to && !$from) {
			return false;
		}
		return true;
	}

	protected function _isNew($product)
	{
		$from = strtotime($product->getData('news_from_date'));
		$to = strtotime($product->getData('news_to_date'));

		return $this->_checkDate($from, $to);
	}

	protected function _isOnSale($_product)
	{
		/* @var $_taxHelper Mage_Tax_Helper_Data */
		$_taxHelper  = Mage::helper('tax');
		if (!$_product->isGrouped()) {
			$_price = $_taxHelper->getPrice($_product, $_product->getPrice());
			$_finalPrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice());
			if ($_finalPrice < $_price){
				return true;
			} /* if ($_finalPrice < $_price): */
		} /* if (!$_product->isGrouped()): */
		return false;
	}

	public function priceFormat($string)
	{
		$currency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		return str_replace($currency, '<sup>' . $currency . '</sup>', $string);
	}

	/**
	 * Returns the identifier for the currently rendered CMS page.
	 * If current page is not from CMS, null is returned.
	 * @return String | Null
	 */
	public function getCurrentCmsPage()
	{
		return Mage::getSingleton('cms/page')->getIdentifier();
	}

}