<?php

class Openstream_ReviewNotifications_Model_Observer {
	/**
	 * Send notification to admin once new review is added
	 *
	 * @param   Varien_Event_Observer $observer
	 */
	public function sendNewReviewNotification(Varien_Event_Observer $observer) {
		/* @var $review Mage_Review_Model_Review */
		$review = $observer->getEvent()->getData('object');
		$templateId = 'reviewnotifications_admin_notification_email_template';
		$name_to = '';
		$email_to = Mage::getStoreConfig('contacts/email/recipient_email');
		$sender = array(
			'name' => Mage::getStoreConfig('trans_email/ident_general/name'),
			'email' => Mage::getStoreConfig('trans_email/ident_general/email')
		);
		/** @var $product Mage_Catalog_Model_Product */
		$product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
		$vars = array(
			'nickname' => $review->getNickname(),
			'title' => $review->getTitle(),
			'detail' => $review->getDetail(),
			'product_id' => $product->getId(),
			'product_name' => $product->getName()
		);
		$storeId = Mage::app()->getStore()->getId();

		/* @var $translate Mage_Core_Model_Translate */
		$translate = Mage::getSingleton('core/translate');
		Mage::getModel('core/email_template')
			->setTemplateSubject(Mage::helper('reviewnotifications')->__('New Review'))
			->sendTransactional($templateId, $sender, $email_to, $name_to, $vars, $storeId);
		$translate->setTranslateInline(true);
	}
}