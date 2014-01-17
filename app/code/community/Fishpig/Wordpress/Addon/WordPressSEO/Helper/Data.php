<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_WordPressSEO_Helper_Data extends Fishpig_Wordpress_Helper_Plugin_Seo_Abstract
{
	/**
	 * A list of option fields used by the extension
	 * All fields are prefixed with wpseo_
	 *
	 * @var array
	 */
	protected $_optionFields = array('', 'titles', 'xml', 'social', 'rss', 'internallinks');
	
	/**
	 * The value used to separate token's in the title
	 *
	 * @var string
	 */
	protected $_rewriteTitleToken = '%%';

	/**
	 * Automatically load the plugin options
	 *
	 */
	protected function _construct()
	{
		parent::_construct();

		$data = array();
		
		foreach($this->_optionFields as $key) {
			if ($key !== '') {
				$key = '_' . $key;
			}

			$options = Mage::helper('wordpress')->getWpOption('wpseo' . $key);
			
			if ($options) {
				$options = unserialize($options);

				foreach($options as $key => $value) {
					if (strpos($key, '-') !== false) {
						unset($options[$key]);
						$options[str_replace('-', '_', $key)] = $value;
					}
				}
				
				$data = array_merge($data, $options);
			}
		}

		$this->setData($data);
	}

	/**
	 * Determine whether All In One SEO is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::helper('wordpress')->isPluginEnabled('Wordpress SEO');
	}
	
	/**
	 * Perform global actions after the user_func has been called
	 *
	 * @return $this
	 */	
	protected function _afterObserver()
	{
		$headBlock = $this->_getHeadBlock();
		
		$robots = array();
			
		if ($this->getNoodp()) {
			$robots[] = 'noodp';
		}
			
		if ($this->getNoydir()) {
			$robots[] = 'noydir';
		}
		
		if (count($robots) > 0) {
			if ($headBlock->getRobots() === '*') {
				$headBlock->setRobots('index,follow,' . implode(',', $robots));
			}
			else {
				$robots = array_unique(array_merge(explode(',', $headBlock->getRobots()), $robots));

				$headBlock->setRobots(implode(',', $robots));
			}
		}

		$this->_updateBreadcrumb('blog', $this->getBreadcrumbsHome());

		return $this;
	}

	/**
	 * Process the SEO values for the homepage
	 *
	 * @param $action
	 * @param Varien_Object $object
	 */	
	public function processRouteWordPressIndexIndex($object = null)
	{
		if (($headBlock = $this->_getHeadBlock()) !== false) {
			$this->_applyMeta(array(
				'title' => $this->_getTitleFormat('home'),
				'description' => trim($this->getMetadescHome()),
				'keywords' => trim($this->getMetakeyHome()),
			));
			
			if ($this->getPlusAuthor()) {
				$this->_addGooglePlusLinkRel($this->getPlusAuthor());
			}
		}
			
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
		$this->_applyPostPageLogic($page, 'page');

		return $this;
	}
	
	protected function _applyPostPageLogic($object, $type = 'post')
	{
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat($type),
			'description' => trim($this->getData('metadesc_' . $type)),
			'keywords' => trim($this->getData('metakey_' . $type)),
		));

		if (($value = trim($object->getMetaValue('_yoast_wpseo_title'))) !== '') {
			$data = $this->getRewriteData();
			$data['title'] = $value;
			$this->setRewriteData($data);
		}
		
		if (($value = trim($object->getMetaValue('_yoast_wpseo_metadesc'))) !== '') {
			$meta->setDescription($value);
		}
		
		$robots = array();

		$noIndex = (int)$object->getMetaValue('_yoast_wpseo_meta-robots-noindex');

		if ($noIndex === 0) {
			$robots['index'] = '';
		}
		else if ($noIndex === 1) {
			$robots['noindex'] = '';
		}
		else if ($noIndex === 2) {
			$robots['index'] = '';
		}
		else if ($this->getNoindexPost()) {
			$robots['noindex'] = '';
		}
		
		if ($object->getMetaValue('_yoast_wpseo_meta-robots-nofollow')) {
			$robots['nofollow'] = '';
		}
		else {
			$robots['follow'] = '';
		}

		if (($advancedRobots = trim($object->getMetaValue('_yoast_wpseo_meta-robots-adv'))) !== '') {
			if ($advancedRobots !== 'none') {
				$robots = explode(',', $advancedRobots);
			}
		}
		
		$robots = array_keys($robots);

		if (count($robots) > 0) {
			$meta->setRobots(implode(',', $robots));
		}

		$this->_applyMeta($meta->getData());

		if (($headBlock = $this->_getHeadBlock()) !== false) {
			if ($canon = $object->getMetaValue('_yoast_wpseo_canonical')) {
				$headBlock->removeItem('link_rel', $object->getUrl());
				$headBlock->addItem('link_rel', $canon, 'rel="canonical"');
			}
			
			$this->_addGooglePlusLinkRel($object->getAuthor());
		}
		
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
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat('category'),
			'description' => $this->getMetadescCategory(),
			'keywords' => $this->getMetakeyCategory(),
			'robots' => $this->getNoindexCategory() ? 'noindex,follow' : '',
		));

		$this->_applyMeta($meta->getData());

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
		if ($this->getDisableDate()) {
			$this->_redirect(Mage::helper('wordpress')->getBlogRoute());
		}
		
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat('archive'),
			'description' => $this->getMetadescArchive(),
			'keywords' => $this->getMetakeyArchive(),
			'robots' => $this->getNoindexArchive() ? 'noindex,follow' : '',
		));

		$this->_applyMeta($meta->getData());
		
		$this->_updateBreadcrumb('archive_label', $this->getBreadcrumbsArchiveprefix());
		
		return $this;
	}
	
	/**
	 * Author page
	 *
	 * @param $action
	 * @param Varien_Object $author
	 */
	public function processRouteWordpressAuthorView($author)
	{
		if ($this->getDisableAuthor()) {
			$this->_redirect(Mage::helper('wordpress')->getBlogRoute());
		}
		
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat('author'),
			'description' => $this->getMetadescAuthor(),
			'keywords' => $this->getMetakeyAuthor(),
			'robots' => $this->getNoindexAuthor() ? 'noindex,follow' : '',
		));

		$this->_applyMeta($meta->getData());
		
		$this->_addGooglePlusLinkRel($author);
			
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
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat('post_tag'),
			'description' => $this->getMetadescPostTag(),
			'keywords' => $this->getMetakeyPostTag(),
			'robots' => $this->getNoindexPostTag() ? 'noindex,follow' : '',
		));

		$this->_applyMeta($meta->getData());

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
		$meta = new Varien_Object(array(
			'title' => $this->_getTitleFormat('search'),
		));

		$this->_applyMeta($meta->getData());
		
		$this->_updateBreadcrumb('search_label', $this->getBreadcrumbsSearchprefix());
		
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
				'sitename' => Mage::helper('wordpress')->getWpOption('blogname'),
				'sitedesc' => Mage::helper('wordpress')->getWpOption('blogdescription'),
			);
			
			if (($object = Mage::registry('wordpress_post')) !== null || ($object = Mage::registry('wordpress_page')) !== null) {
				$data['date'] = $object->getPostDate();
				$data['title'] = $object->getPostTitle();
				$data['excerpt'] = trim(strip_tags($object->getPostExcerpt()));
				$data['excerpt_only'] = $data['excerpt'];
				
				$categories = array();
				
				if ($object instanceof Fishpig_Wordpress_Model_Post) {
					foreach($object->getParentCategories()->load() as $category) {
						$categories[] = $category->getName();	
					}
				}
				
				$data['category'] = implode(', ', $categories);
				$data['modified'] = $object->getPostModified();
				$data['id'] = $object->getId();
				$data['name'] = $object->getAuthor()->getUserNicename();
				$data['userid'] = $object->getAuthor()->getId();
			}
			
			if (($category = Mage::registry('wordpress_category')) !== null) {
				$data['category_description'] = trim(strip_tags($category->getDescription()));
				$data['term_description'] = $data['category_description'];
				$data['term_title'] = $category->getName();
			}
			
			if (($tag = Mage::registry('wordpress_post_tag')) !== null) {
				$data['tag_description'] = trim(strip_tags($tag->getDescription()));
				$data['term_description'] = $data['tag_description'];
				$data['term_title'] = $tag->getName();
			}
			
			if (($term = Mage::registry('wordpress_term')) !== null) {
				$data['term_description'] = trim(strip_tags($term->getDescription()));
				$data['term_title'] = $term->getName();
			}
			
			if (($archive = Mage::registry('wordpress_archive')) !== null) {
				$data['date'] = $archive->getName();
			}
			
			$data['currenttime'] = Mage::helper('wordpress')->formatTime(date('Y-m-d H:i:s'));
			$data['currentdate'] = Mage::helper('wordpress')->formatDate(date('Y-m-d H:i:s'));
			$data['currentmonth'] = date('F');
			$data['currentyear'] = date('Y');
			$data['sep'] = '|';

			if (($value = trim(Mage::helper('wordpress/search')->getEscapedSearchString())) !== '') {
				$data['searchphrase'] = $value;
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
		return trim($this->getData('title_' . $key));
	}
	
	/**
	 * Add the Google Plus rel="author" tag
	 *
	 * @param int|Fishpig_Wordpress_Model_User
	 * @return $this
	 */
	protected function _addGooglePlusLinkRel($user)
	{
		if (!is_object($user)) {
			$user = Mage::getModel('wordpress/user')->load($user);
			
			if (!$user->getId()) {
				return $this;
			}
		}
		
		if ($user->getId() && $user->getMetaValue('googleplus')) {
			$this->_getHeadBlock()->addItem('link_rel', $user->getMetaValue('googleplus'), 'rel="author"');
		}
		
		return $this;	
	}
}
