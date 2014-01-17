<?php

class Openstream_TagNotifications_Model_Observer
{
    /**
     * Send notification to admin once new tag is added
     *
     * @param   Varien_Event_Observer $observer
     */
	public function sendNewTagsNotification($observer)
	{
        /* @var $tag Mage_Tag_Model_Tag */
        $tag = $observer->getEvent()->getData('object');
        $templateId = 'tagnotifications_admin_notification_email_template';
		$name_to = '';
		$email_to = Mage::getStoreConfig('contacts/email/recipient_email');
		$sender = array(
            'name'  => Mage::getStoreConfig('trans_email/ident_general/name'),
            'email' => Mage::getStoreConfig('trans_email/ident_general/email')
        );
        $vars = array(
            'new_tag' => $tag->getName()
        );
		$storeId = Mage::app()->getStore()->getId();

        /* @var $translate Mage_Core_Model_Translate */
		$translate  = Mage::getSingleton('core/translate');
		Mage::getModel('core/email_template')
			->setTemplateSubject(Mage::helper('tagnotifications')->__('New Tags'))
			->sendTransactional($templateId, $sender, $email_to, $name_to, $vars, $storeId);
		$translate->setTranslateInline(true);
	}
}