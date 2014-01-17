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

class Itoris_QuickBuy_Block_Grid extends Mage_Core_Block_Template {

	protected $categories = null;

	protected function _prepareLayout() {
	  /********
DCS: TURN OFF REGISTRATION CHECKING!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		if (!$this->getDataHelper()->isRegisteredAutonomous(Mage::app()->getWebsite())) {
			$this->setTemplate('itoris/quickbuy/no-registration.phtml');
			return;
		}
		if ($this->getDataHelper()->getSettings()->getEnable() == Itoris_QuickBuy_Model_Settings::DISABLED) {
			return;
		}
	  ******/
		$this->setTemplate('itoris/quickbuy/grid.phtml');
	}

	public function getSearchUrl() {
		switch ($this->getDataHelper()->getSettings()->getSearchEngine()) {
			case Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_CACHE:
				return $this->getQbuyUrl();
			default:
				return $this->getUrl('quickbuy/grid/products');
		}
	}

	public function getQbuyUrl() {
		return Mage::app()->getDefaultStoreView()->getBaseUrl('web') . 'qbuy/index.php';
	}

	public function getSearchEngineType() {
		switch ($this->getDataHelper()->getSettings()->getSearchEngine()) {
			case Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_CACHE:
				return 'cache';
			default:
				return 'sql';
		}
	}

	public function getAllTaxRatesByProductClass() {
		$result = array();
		/** @var $calc Mage_Tax_Model_Calculation */
		$calc = Mage::getSingleton('tax/calculation');
		$rates = $calc->getRatesForAllProductTaxClasses($calc->getRateRequest());

		foreach ($rates as $class=>$rate) {
			$result["value_{$class}"] = $rate;
		}
		return $result;
	}

	public function getTranslatesJson() {
		$translates = array(
			'items'                      => $this->__('Items'),
			'to'                         => $this->__('to'),
			'of'                         => $this->__('of'),
			'total'                      => $this->__('total'),
			'page'                       => $this->__('Page'),
			'addToList'                  => $this->__('Add to the List'),
			'priceFrom'                  => $this->__('From'),
			'priceTo'                    => $this->__('To'),
			'asLowAs'                    => $this->__('As low as'),
			'noProducts'                 => $this->__('No products found'),
			'containsOptions'            => $this->__('contains options'),
			'qty'                        => $this->__('Qty'),
			'expandAll'                  => $this->__('Expand all'),
			'collapseAll'                => $this->__('Collapse all'),
			'containsAssociatedProducts' => $this->__('contains associated products'),
			'associatedProducts'         => $this->__('Associated Products'),
			'options'                    => $this->__('Options'),
			'customOptions'              => $this->__('Custom Options'),
			'chooseOption'               => $this->__('Choose an Option...'),
			'productAddedToCart'         => $this->__('Products have been added to the cart'),
			'requiredField'              => $this->__('required fields'),
			'none'                       => $this->__('none'),
			'removeProduct'              => $this->__('Do you really want to remove this product from the list?'),
			'loading'                    => $this->__('Loading... Please wait'),
			'viewDetails'                => $this->__('view the product details'),
			'validateFail'               => $this->__('Fill all required fields'),
			'outOfStock'                 => $this->__('out of stock'),
			'inclTax'                    => $this->__('incl. tax'),
			'exclTax'                    => $this->__('excl. tax'),
			'products_not_added_to_cart' => $this->__('Some products have not been added to the cart'),
			'allowedFileExtensions'      => $this->__('Allowed file extensions to upload'),
			'maxImageWidth'              => $this->__('Maximum image width'),
			'maxImageHeight'             => $this->__('Maximum image height'),
			'dateFieldIsNotComplete'     => $this->__('Some date field is not complete'),
			'dateFieldIsNotValid'        => $this->__('Some date is not valid'),
			'minimum'                    => $this->__('minimum %d'),
			'increments'                 => $this->__('in increments of %d'),
			'minQtyInCart'               => $this->__('Minimum QTY allowed in Shopping Cart is %d'),
			'availableIncrements'        => $this->__('Available for purchase in increments of %d'),
			'sku'                        => $this->__('sku'),
		);
		return Zend_Json::encode($translates);
	}

	public function getConfigJson() {
		/** @var $taxHelper Mage_Tax_Helper_Data */
		$taxHelper = Mage::helper('tax');
		$currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$currencyRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::app()->getStore()->getCurrentCurrency());
		$defaultProducts = $this->getDefaultProducts();
		$config = array(
			'display_price_incl_tax' => $taxHelper->displayPriceIncludingTax(),
			'display_price_excl_tax' => $taxHelper->displayPriceExcludingTax(),
			'display_both_price'     => $taxHelper->displayBothPrices(),
			'addToCartUrl'           => Mage::getUrl('quickbuy/cart/add'),
			'currency_rate'          => $currencyRate ? $currencyRate : 1,
			'currency'               => Mage::app()->getLocale()->currency($currencyCode)->getSymbol(),
			'search_engine'          => $this->getSearchEngineType(),
			'checkoutLinkUrl'        => $this->getUrl('checkout/cart'),
			'store_id'               => Mage::app()->getStore()->getId(),
			'sid'					 => session_id(),
			'base_path'              => Mage::app()->getRequest()->getBasePath(),
			'taxes'                  => $this->getAllTaxRatesByProductClass(),
			'price_includes_tax'     => $taxHelper->priceIncludesTax(),
			'global_increment'       => (bool)Mage::getStoreConfig('cataloginventory/item_options/enable_qty_increments'),
			'global_increment_qty'   => (float)Mage::getStoreConfig('cataloginventory/item_options/qty_increments'),
			'default_products'       => $defaultProducts['products'],
			'default_products_total' => $defaultProducts['total'],
		);

		return Zend_Json::encode($config);
	}

	public function getDefaultProducts() {
		return Mage::helper('itoris_quickbuy/cacheSearch')->getDefaultProducts();
	}

	public function getUrlsJson() {
		$urls = array(
			'loadProducts'        => $this->getSearchUrl(),
			'loadProductConfig'   => $this->getUrl('quickbuy/grid/loadProductConfig'),
			'loadDefaultProducts' => $this->getUrl('quickbuy/grid/loadDefaultProducts'),
			'saveInSession'       => $this->getUrl('quickbuy/index/saveInSession'),
			'cart'                => $this->getUrl('checkout/cart'),
			'cartSummaryUrl'      => $this->getQbuyUrl(),
		);
		return Zend_Json::encode($urls);
	}

	/**
	 * @return Itoris_QuickBuy_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_quickbuy');
	}
}
?>