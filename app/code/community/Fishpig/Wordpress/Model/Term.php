<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
class Fishpig_Wordpress_Model_Term extends Fishpig_Wordpress_Model_Abstract
{
	/**
	 * Event data
	 *
	 * @var string
	 */
	protected $_eventPrefix      = 'wordpress_term';
	protected $_eventObject      = 'term';
	
	public function _construct()
	{
		$this->_init('wordpress/term');
	}
	
	/**
	 * Retrieve an array of the default WP taxonomies
	 *
	 * @return array
	 */
	public function getDefaultTermTaxonomyTypes()
	{
		return array('category', 'link_category', 'post_tag');
	}
	
	/**
	 * Determine whether this term is a custom term or a default term
	 *
	 * @return bool
	 */
	public function isDefaultTerm()
	{
		return in_array($this->_getData('taxonomy'), $this->getDefaultTermTaxonomyTypes());
	}
	
	/**
	 * Retrieve the taxonomy label
	 *
	 * @return string
	 */
	public function getTaxonomyLabel()
	{
		if ($this->getTaxonomy()) {
			return ucwords(str_replace('_', ' ', $this->getTaxonomy()));
		}
		
		return false;
	}
	
	/**
	 * Retrieve the parent term
	 *
	 * @reurn false|Fishpig_Wordpress_Model_Term
	 */
	public function getParentTerm()
	{
		if (!$this->hasParentTerm()) {
			$this->setParentTerm(false);
			
			if ($this->getParentId()) {
				$parentTerm = Mage::getModel($this->getResourceName())->load($this->getParentId());
				
				if ($parentTerm->getId()) {
					$this->setParentTerm($parentTerm);
				}
			}
		}
		
		return $this->_getData('parent_term');
	}
	
	/**
	 * Retrieve the path for the term
	 *
	 * @return string
	 */
	public function getPath()
	{
		if (!$this->hasPath()) {
			if ($this->getParentTerm()) {
				$this->setPath($this->getParentTerm()->getPath() . '/' . $this->getId());
			}
			else {
				$this->setPath($this->getId());
			}
		}
		
		return $this->_getData('path');
	}
	
	/**
	 * Retrieve a collection of children terms
	 *
	 * @return Fishpig_Wordpress_Model_Mysql_Term_Collection
	 */
	public function getChildrenTerms()
	{
		if (!$this->hasChildrenTerms()) {
			$this->setChildrenTerms($this->getCollection()->addParentFilter($this));
		}
		
		return $this->_getData('children_terms');
	}
	
	/**
	 * Loads the posts belonging to this category
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */    
    public function getPostCollection()
    {
		if (!$this->hasPostCollection()) {
			if ($this->getTaxonomy()) {
				$posts = Mage::getResourceModel('wordpress/post_collection')
    				->addIsPublishedFilter()
    				->addTermIdFilter($this->getId(), $this->getTaxonomy());
    			
	    		$this->setPosts($posts);
	    	}
    	}
    	
    	return $this->_getData('posts');
    }
    
	/**
	 * Retrieve the numbers of items that belong to this term
	 *
	 * @return int
	 */
	public function getItemCount()
	{
		return $this->getCount();
	}

	/**
	 * Load a term based on it's slug
	 *
	 * @param string $slug
	 * @return $this
	 */	
	public function loadBySlug($slug)
	{
		return $this->load($slug, 'slug');
	}
	
	/**
	 * Load a term by an array of slugs
	 * If the slugs match a category URI
	 * The most child term will be returned
	 *
	 * @param array $slugs
	 * @return Fishpig_Wordpress_Model_Term
	 */
	public function loadBySlugs(array $slugs)
	{
		$this->getResource()->loadBySlugs($slugs, $this);
		
		return $this;
	}
	
	/**
	 * Retrieve the parent ID
	 *
	 * @return int|false
	 */	
	public function getParentId()
	{
		if ($this->_getData('parent')) {
			return $this->_getData('parent');
		}
		
		return false;
	}
	
	/**
	 * Retrieve the taxonomy type for this term
	 *
	 * @return string
	 */
	public function getTaxonomyType()
	{
		return $this->getTaxonomy();
	}
	
	/**
	 * Retrieve the URL for this term
	 *
	 * @return string
	 */
	public function getUrl()
	{
		if (!$this->hasUrl()) {
			$this->setUrl(Mage::helper('wordpress')->getUrl($this->getUri() . '/'));
		}
		
		return $this->_getData('url');
	}
	
	/**
	 * Retrieve the URI for this term
	 * This takes into account parent relationships
	 * This does not include the base URL
	 *
	 * @return string
	 */
	public function getUri()
	{
		if (($tree = $this->getTermTree()) !== false) {
			$uri = array();
			
			foreach($tree as $branch) {
				$uri[] = $branch->getSlug();
			}

			return $this->getTaxonomy() . '/' . implode('/', $uri);		
		}
		
		return false;
	}
	
	/**
	 * Retrieve an array of parent terms
	 * The first element of the array is the most parent term
	 * The last element of the array is $this
	 *
	 * @return false|array
	 */	
	public function getTermTree()
	{
		if (!$this->hasTermTree()) {
			if ($this->getParentTerm()) {
				$term = $this;
				$terms = array();
	
				do {
					$terms[] = $term;
					$term = $term->getParentTerm();
				} while ($term);
				
				$this->setTermTree(array_reverse($terms));
			}
			else {
				$this->setTermTree(array($this));
			}
		}
		
		return $this->_getData('term_tree');
	}
}
