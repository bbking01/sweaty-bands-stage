<?php 
  require_once 'Mage/Checkout/controllers/CartController.php';
   class Magestore_Checkoutcart_Checkout_CartController extends Mage_Checkout_CartController
     {
		  public function addAction()
		  {
			$cart   = $this->_getCart();
			$params = $this->getRequest()->getParams();
			/*Code added for edit cart fucntionality remove old cart id starts*/
			$cid = (int) $this->getRequest()->getParam('cart_id');
			if ($cid) {
					$this->_getCart()->removeItem($cid);
					  //->save();
			}
			/*Code added for edit cart fucntionality remove old cart id ends*/
			try {
				if (isset($params['qty'])) {
						$filter = new Zend_Filter_LocalizedToNormalized(
							array('locale' => Mage::app()->getLocale()->getLocaleCode())
						);
					   $params['qty'] = $filter->filter($params['qty']);
					}
		
					$product = $this->_initProduct();
					$related = $this->getRequest()->getParam('related_product');
		
					// By Naincy
					// Start - By Naincy							
					if(isset($params['color_id']) && isset($params['color_value']))
					{
						$attr[$params['color_id']] = $params['color_value'];
					}
					if(isset($params['size_id']) && isset($params['size_value']))
					{
						$attr[$params['size_id']] = $params['size_value'];
					}
					if(!empty($attr))
					{
						$params['super_attribute'] = $attr;
					}
	
					if (!$product) {
						$this->_goBack();
						return;
					}
					
					
					 $cart->addProduct($product, $params);
					if (!empty($related)) {
						$cart->addProductsByIds(explode(',', $related));
					}
					$cart->save();
					$this->_getSession()->setCartWasUpdated(true);
					/**
					 * @todo remove wishlist observer processAddToCart
					 */
					Mage::dispatchEvent('checkout_cart_add_product_complete',
						array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
					);
		
		
					if (!$this->_getSession()->getNoCartRedirect(true)) {
						if (!$cart->getQuote()->getHasError()){
					   $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
					   $this->_getSession()->addSuccess($message);
						}
						$this->_goBack();
					}
				}
				catch (Mage_Core_Exception $e) {
					if ($this->_getSession()->getUseNotice(true)) {
						$this->_getSession()->addNotice($e->getMessage());
					} else {
						$messages = array_unique(explode("\n", $e->getMessage()));
						foreach ($messages as $message) {
							$this->_getSession()->addError($message);
						}
					}
		
					$url = $this->_getSession()->getRedirectUrl(true);
					if ($url) {
						$this->getResponse()->setRedirect($url);
					} else {
						$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
					}
				}
				catch (Exception $e) {
					$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
					$this->_goBack();
				}
			}
			
			/**
			 * Delete shoping cart item action
			 */
			public function deleteAction()
			{
				$url = Mage::getUrl('checkout/cart');
				$this->getResponse()->setRedirect($url);
				$id = (int) $this->getRequest()->getParam('id');
				if ($id) {
					try {
						$this->_getCart()->removeItem($id)
						  ->save();
					} catch (Exception $e) {
						$this->_getSession()->addError($this->__('Cannot remove the item.'));
						Mage::logException($e);
					}
				}
				$this->getResponse()->setRedirect($url);
				//$this->_redirectReferer(Mage::getUrl('checkout/cart'));
			}
      }
	 ?>