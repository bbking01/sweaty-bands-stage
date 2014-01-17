<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_AuthorController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * Used to do things en-masse
	 * eg. include canonical URL
	 *
	 * @return false|Fishpig_Wordpress_Model_Post_Category
	 */
	public function getEntityObject()
	{
		return $this->_initAuthor();
	}
	
	/**
	  * Display the author page and list posts
	  *
	  */
	public function viewAction()
	{
		$author = Mage::registry('wordpress_author');
		
		$this->_addCustomLayoutHandles(array(
			'wordpress_author_view_' . $author->getId(),
			'wordpress_author_index', 
		));
			
		$this->_initLayout();
		
		$this->_rootTemplates[] = 'template_post_list';
	
		$this->_title($author->getDisplayName());
		$this->addCrumb('author_nolink', array('label' => $this->__('Author')));
		$this->addCrumb('author', array('link' => $author->getUrl(), 'label' => $author->getDisplayName()));

		$this->renderLayout();
	}

	/**
	 * Load user based on URI
	 *
	 * @return false|Fishpig_Wordpress_Model_User
	 */
	protected function _initAuthor()
	{
		if (($author = Mage::registry('wordpress_author')) !== null) {
			return $author;
		}

		$uri = Mage::helper('wordpress/router')->getBlogUri();
		$base = 'author';
		
		if (substr($uri, 0, strlen($base)) == $base) {
			$uri = trim(substr($uri, strlen($base)), '/');
		}

		$author = Mage::getModel('wordpress/user')->load($uri, 'user_nicename');

		if ($author->getId()) {
			Mage::register('wordpress_author', $author);
			return $author;
		}
		
		return false;
	}
}
