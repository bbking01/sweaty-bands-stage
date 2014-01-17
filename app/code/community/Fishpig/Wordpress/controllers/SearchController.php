<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_SearchController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	  * Initialise the current category
	  */
	public function indexAction()
	{
		$this->_rootTemplates[] = 'template_post_list';
		
		$this->_addCustomLayoutHandles(array(
			'wordpress_search_index',
		));
		
		$this->_initLayout();
		
		$helper = Mage::helper('wordpress/search');
		$routerHelper = $this->getRouterHelper();
		
		if ($searchValue = $routerHelper->getTrimmedUri('search')) {
			$this->getRequest()->setParam($helper->getQueryVarName(), $searchValue);
		}

		$this->_title($this->__("Search results for: '%s'", $helper->getEscapedSearchString()));
		
		$this->addCrumb('search_label', array('link' => '', 'label' => $this->__('Search')));
		$this->addCrumb('search_value', array('link' => '', 'label' => $helper->getEscapedSearchString()));
		
		$this->renderLayout();
	}
}
