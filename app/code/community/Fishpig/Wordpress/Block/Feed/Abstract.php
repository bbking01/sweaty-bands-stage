<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Feed_Abstract extends Mage_Core_Block_Template
{
	public function __construct()
	{
		$this->setTemplate('wordpress/feed/default.phtml');
	}

	/**
	 * Retrieve the DocType for the XML feed
	 *
	 * @return string
	 */
	public function getDocType()
	{
		return '<?xml version="1.0" encoding="'.$this->getBlogCharset().'"?>'."\n";
	}
	
	/**
	 * Retrieve the blog charset
	 * This is set in the WordPress Admin
	 *
	 * @return string
	 */
	public function getBlogCharset()
	{
		return Mage::helper('wordpress')->getWpOption('blog_charset');
	}
	
	/**
	 * Retrieve the RSS title
	 * This is the blog name
	 *
	 * @return string
	 */
	public function getRssTitle()
	{
		return $this->getBlogName();
	}

	/*
	 * Retrieve the blog name
	 *
	 * @return string
	 */
	public function getBlogName()
	{
		return $this->decode(Mage::helper('wordpress')->getWpOption('blogname'));
	}
	
	public function getDescription()
	{
		$description = $this->decode(Mage::helper('wordpress')->getWpOption('blogdescription'));
		
		if (!$description) {
			return $this->getBlogName();
		}
		
		return $description;
	}
	
	/**
	 * Retrieve the language of the RSS file
	 *
	 * @return string
	 */
	public function getRssLanguage()
	{
		return Mage::helper('wordpress')->getWpOption('rss_language');
	}

	/**
	 * Decode a values html entities
	 *
	 * @param string $value
	 * @return string
	 */
	public function decode($value)
	{
		return html_entity_decode($value, ENT_NOQUOTES, $this->getBlogCharset());
	}
	
	/**
	 * Shorten a string 
	 *
	 * @param string $str
	 * @param int $wordCount = 100
	 * @param string $end = '...'
	 * @return string
	 */
	public function shorten($str, $wordCount = 100, $end = '...')
	{
		$str = strip_tags(preg_replace("/(<br[ ]{0,}>)/", "--TEST--", $str));
		$words = explode(' ', $str);
		$length = count($words);
		
		if ($length > $wordCount) {
			$words = array_splice($words, 0, $wordCount);
			return rtrim(implode(' ', $words), " .!?,;-:'\"\n") . $end;
		}
		
		return $str;
	}
	
	/**
	 * Retrieve a posts featured image
	 *
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return false|Fishpig_Wordpress_Model_Image
	 */
	public function getFeaturedImageUrl(Fishpig_Wordpress_Model_Post $post)
	{
		if ($image = $post->getFeaturedImage()) {
			return $image->getThumbnailImage();
		}
		
		return false;
	}
}
