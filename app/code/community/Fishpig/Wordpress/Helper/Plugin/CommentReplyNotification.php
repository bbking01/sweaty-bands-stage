<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Plugin_CommentReplyNotification extends Fishpig_Wordpress_Helper_Plugin_Abstract
{
	/**
	 * Determine whether to display the opt in
	 *
	 * @return bool
	 */
	public function canDisplayOptIn()
	{
		Mage::helper('wordpress')->log(get_class($this) . ' has been deprecated and should no longer be used.');

		return $this->isEnabled() 
			&& in_array($this->getPluginOption('mail_notify'), array('parent_check', 'parent_uncheck'));
	}
	
	/**
	 * Determine whether the opt in is checked by default
	 *
	 * @return bool
	 */
	public function isOptInChecked()
	{
		return $this->getPluginOption('mail_notify') == 'parent_check';
	}
	
	/**
	 * Determine whether the plugin is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::helper('wordpress')->isPluginEnabled('comment-reply-notification')
			&& $this->getPluginOption('mail_notify') != 'none';
	}
	
	/**
	 * Retrieve the options for this plugin
	 *
	 * @param string $key = null
	 * @return null|array
	 */
	public function getPluginOptions()
	{
		if (is_null($this->_options)) {
			$this->_options = array();

			if ($options = Mage::helper('wordpress')->getWpOption('commentreplynotification')) {
				$this->_options = unserialize($options);
			}
		}

		return $this->_options;
	}
	
	/**
	 * Retrieve a specific plugin option
	 *
	 * @param string $key
	 * @return string
	 */
	public function getPluginOption($key, $default = null)
	{
		if ($options = $this->getPluginOptions()) {
			return isset($options[$key]) ? $options[$key] : $default;
		}
		
		return $default;
	}
}
