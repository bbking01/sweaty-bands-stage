<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_AllInOneSEO_Helper_Data extends Fishpig_Wordpress_Helper_Plugin_Seo_Abstract
{
	/**
	 * Prefix for options field in options table
	 *
	 * @var string|null
	 */
	protected $_optionsFieldPrefix = 'aioseop';

	/**
	 * Prefix for options value keys
	 *
	 * @var string
	 */	
	protected $_optionsValuePrefix = 'aiosp';
	
	/**
	 * Determine whether All In One SEO is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::helper('wordpress')->isPluginEnabled('All In One SEO') && $this->getEnabled();
	}
	
	/**
	 * Perform global actions after the user_func has been called
	 *
	 * @return $this
	 */	
	protected function _beforeObserver()
	{
		if (($headBlock = $this->_getHeadBlock()) !== false) {
			if ($this->getGooglePublisher()) {
				$this->_getHeadBlock()->addItem('link_rel', $this->getGooglePublisher(), 'rel="author"');	
			}
		}
		
		return parent::_beforeObserver();
	}
	
	/**
	 * Process the SEO values for the homepage
	 *
	 * @param $action
	 * @param Varien_Object $object
	 */	
	public function processRouteWordPressIndexIndex($object = null)
	{
		$this->_applyMeta(array(
			'title' => $this->getData('home_title'),
			'description' => $this->getData('home_description'),
			'keywords' => $this->getData('home_keywords'),
		));

		return $this;
	}

	/**
	 * Process the SEO values for the blog view page
	 *
	 * @param $action
	 * @param Varien_Object $post
	 */	
	public function processRouteWordPressPostView($post)	
	{
		$this->_applyPostPageLogic($post);

		return $this;
	}
	
	/**
	 * Process the SEO values for the blog view page
	 *
	 * @param $action
	 * @param Varien_Object $page
	 */	
	public function processRouteWordPressPageView($page)	
	{
		$this->_applyPostPageLogic($post, 'page');

		return $this;
	}

	protected function _applyPostPageLogic($object, $type = 'post')
	{
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat($type),
		));
		
		if (($value = trim($object->getMetaValue('_aioseop_title'))) !== '') {
		
			$data = $this->getRewriteData();
			$data[$type . '_title'] = $value;

			$this->setRewriteData($data);
		}
		
		if (($value = trim($object->getMetaValue('_aioseop_description'))) !== '') {
			$meta->setDescription($value);
		}
		
		if (($value = trim($object->getMetaValue('_aioseop_keywords'))) !== '') {
			$meta->setKeywords($value);
		}

		if ($type === 'post') {		
			$keywords = rtrim($meta->getKeywords(), ',') . ',';
			
			if ($this->getUseCategories()) {
				foreach($object->getParentCategories() as $category) {
					$keywords .= $category->getName() . ',';
				}
			}
			
			if ($this->getUseTagsAsKeywords()) {
				foreach($object->getTags() as $tag) {
					$keywords .= $tag->getName() . ',';
				}
			}

			$meta->setKeywords(trim($keywords, ','));
		}

		$this->_applyMeta($meta->getData());
		
		return $this;
	}
	
	/**
	 * Category page
	 *
	 * @param $action
	 * @param Varien_Object $category
	 */
	public function processRouteWordpressPostCategoryView($category)
	{
		if (($headBlock = $this->_getHeadBlock()) !== false) {
			if ($this->getCategoryNoindex()) {
				$headBlock->setRobots('noindex,follow');
			}
		}


		
		return $this;
	}


	/**
	 * Archive page
	 *
	 * @param $action
	 * @param Varien_Object $archive
	 */
	public function processRouteWordpressArchiveView($archive)
	{
		if (($headBlock = $this->_getHeadBlock()) !== false) {
			if ($this->getArchiveNoindex()) {
				$headBlock->setRobots('noindex,follow');
			}
		}



		return $this;
	}
	
	/**
	 * Tag page
	 *
	 * @param $action
	 * @param Varien_Object $tag
	 */
	public function processRouteWordpressPostTagView($tag)
	{
		if (($headBlock = $this->_getHeadBlock()) !== false) {
			if ($this->getTagsNoindex()) {
				$headBlock->setRobots('noindex,follow');
			}
		}

		
		return $this;
	}
	
	/**
	 * Process the search results page
	 *
	 * @param $action
	 * @param $object
	 */
	public function processRouteWordpressSearchIndex($object = null)
	{

		
		return $this;		
	}

	
	/**
	 * Retrieve the rewrite data
	 *
	 * @return array
	 */
	public function getRewriteData()
	{
		if (!$this->hasRewriteData()) {
			$data = array(
				'blog_title' => Mage::helper('wordpress')->getWpOption('blogname'),
				'blog_description' => Mage::helper('wordpress')->getWpOption('blogdescription'),
			);
			
			if (($post = Mage::registry('wordpress_post')) !== null) {
				$data['post_title'] = $post->getPostTitle();
				$data['category_title'] = $post->getParentCategory()->getName();
				$data['category'] = $post->getParentCategory()->getName();
				$data['post_author_login'] = $post->getAuthor()->getUserLogin();
				$data['post_author_nicename'] = $post->getAuthor()->getUserNicename();
				$data['post_author_firstname'] = $post->getAuthor()->getMetaValue('first_name');
				$data['post_author_lastname'] = $post->getAuthor()->getMetaValue('last_name');
			}
			
			if (($page = Mage::registry('wordpress_page')) !== null) {
				$data['page_title'] = $page->getPostTitle();
				$data['page_author_login'] = $page->getAuthor()->getUserLogin();
				$data['page_author_nicename'] = $page->getAuthor()->getUserNicename();
				$data['page_author_firstname'] = $page->getAuthor()->getMetaValue('first_name');
				$data['page_author_lastname'] = $page->getAuthor()->getMetaValue('last_name');
			}
			
			if (($category = Mage::registry('wordpress_category')) !== null) {
				$data['category_title'] = $category->getName();
				$data['category_description'] = trim(strip_tags($category->getDescription()));
			}
			
			if (($tag = Mage::registry('wordpress_post_tag')) !== null) {
				$data['tag'] = $tag->getName();
			}

			if (($archive = Mage::registry('wordpress_archive')) !== null) {
				$data['date'] = $archive->getName();
			}

			$data['sep'] = '|';

			if (($value = trim(Mage::helper('wordpress/search')->getEscapedSearchString())) !== '') {
				$data['search'] = $value;
			}

			$this->setRewriteData($data);
		}
		
		return $this->_getData('rewrite_data');		
	}
	
	/**
	 * Retrieve the title format for the given key
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _getTitleFormat($key)
	{
		return trim($this->getData($key . '_title_format'));
	}
}
