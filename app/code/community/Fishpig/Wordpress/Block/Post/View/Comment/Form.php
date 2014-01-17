<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Post_View_Comment_Form extends Fishpig_Wordpress_Block_Post_View_Abstract
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('wordpress/post/view/comment/form.phtml');
	}
	
	protected function _beforeToHtml()
	{
		if ($this->helper('wordpress')->isPluginEnabled('comment-reply-notification')) {
			if ($options = Mage::helper('wordpress')->getWpOption('commentreplynotification')) {
				$options = unserialize($options);
				
				if (isset($options['mail_notify'])) {
					if (in_array($options['mail_notify'], array('parent_check', 'parent_uncheck'))) {
						$this->setCommentReplyNotificationEnabled(true);
						
						if ($options['mail_notify'] === 'parent_check') {
							$this->setCommentReplyNotificationOptInChecked(true);
						}
					}
				}
			}
		}
		
		return parent::_beforeToHtml();		
	}
	
	/**
	 * Retrieve the comment form action
	 *
	 * @return string
	 */
	public function getCommentFormAction()
	{
		return Mage::helper('wordpress')->getBaseUrl('wp-comments-post.php');
	}

	/**
	 * Determine whether the customer needs to login before commenting
	 *
	 * @return bool
	 */
	public function customerMustLogin()
	{
		if ($this->helper('wordpress')->getWpOption('comment_registration')) {
			return !Mage::getSingleton('customer/session')->isLoggedIn();
		}
		
		return false;
	}

	/**
	 * Retrieve the link used to log the user in
	 * If redirect to dashboard after login is disabled, the user will be redirected back to the blog post
	 *
	 * @return string
	 */
	public function getLoginLink()
	{
		return Mage::getUrl('customer/account/login', array(
			'referer' => $this->helper('core')->urlEncode($this->getPost()->getPermalink() . '#respond'),
		));
	}

	/**
	 * Returns true if the user is logged in
	 *
	 * @return bool
	 */
	public function isCustomerLoggedIn()
	{
		return Mage::getSingleton('customer/session')->isLoggedIn();
	}
}
