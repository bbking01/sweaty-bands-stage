<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Filter extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Regular expression patterns for identifying
	 * shortcodes and parameters
	 *
	 */
	const EXPR_SHOTRCODE_OPEN_TAG = '(\[{{shortcode}}[^\]]{0,}\])';
	const EXPR_SHOTRCODE_CLOSE_TAG = '(\[\/{{shortcode}}[^\]]{0,}\])';
//	const EXPR_SHORTCODE_PARAM = '[ ]{1}([^ ]{1,})=["]{0,1}(.*)["]{0,1}[ \]]{1}';
	const EXPR_SHORTCODE_PARAM = '[ ]{1}([^ ]{1,})=["\']{1}(.*)["\']{1}[ \]]{1}';

	/**
	 * Extract shortcodes from a string
	 *
	 * @param string $content
	 * @param string $tag
	 * @return false|array
	 */
	protected function _getShortcodesByTag($content, $tag)
	{
		$shortcodes = array();

		if (strpos($content, '[' . $tag) !== false) {
			$hasCloser = strpos($content, '[/' . $tag . ']') !== false;
			$open = str_replace('{{shortcode}}', $tag, self::EXPR_SHOTRCODE_OPEN_TAG);

			if ($hasCloser) {
				$close = str_replace('{{shortcode}}', $tag, self::EXPR_SHOTRCODE_CLOSE_TAG);

				if (preg_match_all('/' . $open . '(.*)' . $close . '/iU', $content, $matches)) {
					foreach($matches[0] as $matchId => $match) {
						$shortcodes[] = new Varien_Object(array(
							'html' => $match,
							'opening_tag' => $matches[1][$matchId],
							'inner_content' => $matches[2][$matchId],
							'closing_tag' => $matches[3][$matchId],
							'params' => new Varien_Object($this->_parseShortcodeParameters($matches[1][$matchId])),
						));
					}
				}
			}
			else if (preg_match_all('/' . $open . '/iU', $content, $matches)) {
				foreach($matches[0] as $matchId => $match) {
					$shortcodes[] = new Varien_Object(array(
						'html' => $match,
						'opening_tag' => $matches[1][$matchId],
						'params' => new Varien_Object($this->_parseShortcodeParameters($matches[1][$matchId])),
					));
				}
			}
		}

		if (count($shortcodes) > 0) {
			return $shortcodes;
		}
		
		return false;
	}

	/**
	 * Extract parameters from a shortcode opening tag
	 *
	 * @param string $openingTag
	 * @return array
	 */
	protected function _parseShortcodeParameters($openingTag)
	{
		$openingTag = preg_replace('/(\]){1}$/', ' ]', trim($openingTag));
		$openingTag = str_replace(' ', '  ', $openingTag);
		
		$parameters = array();

		if (preg_match_all('/' . self::EXPR_SHORTCODE_PARAM . '/iU', $openingTag, $matches)) {
			foreach($matches[1] as $matchId => $key) {
				$parameters[trim($key)] = trim($matches[2][$matchId], '"\' ');
			}
		}
			
		return $parameters;
	}
	
	/**
	 * Applies a set of filters to the given string
	 *
	 * @param string $content
	 * @param array $params
	 * @return string
	 */
	public function applyFilters($content, array $params = array())
	{
		if (isset($params['object'])) {
			
			$content = trim(preg_replace('/(&nbsp;)$/', '', trim($content)));
			
			$contentObj = new Varien_Object(array('content' => $content));

			Mage::dispatchEvent('wordpress_string_filter_before', array('content' => $contentObj, 'object' => $params['object'], 'params' => $params, 'helper' => $this));
			
			$content = $contentObj->getContent();
			
			if (Mage::getStoreConfigFlag('wordpress_blog/posts/autop')) {
				$this->_addParagraphsToString($content);
			}

			$this->_applyShortcodes($content, $params);
			
			$this->_addMagentoFilters($content, $params);

			$contentObj = new Varien_Object(array('content' => $content));
					
			Mage::dispatchEvent('wordpress_string_filter_after', array('content' => $contentObj, 'object' => $params['object'], 'params' => $params, 'helper' => $this));
			
			$content = $contentObj->getContent();
		}
	
		return $content;
	}
	
	/**
	 * Add paragraph tags to the content
	 * Taken from the WordPress core
	 * Long live open source!
	 *
	 * @param string &$content
	 */
	protected function _addParagraphsToString(&$content)
	{
		$pee = $content;

		$br = true;
		$pre_tags = array();
	
		if ( trim($pee) === '' )
			return '';
	
		$pee = $pee . "\n"; // just to make things a little easier, pad the end
	
		if ( strpos($pee, '<pre') !== false ) {
			$pee_parts = explode( '</pre>', $pee );
			$last_pee = array_pop($pee_parts);
			$pee = '';
			$i = 0;
	
			foreach ( $pee_parts as $pee_part ) {
				$start = strpos($pee_part, '<pre');
	
				// Malformed html?
				if ( $start === false ) {
					$pee .= $pee_part;
					continue;
				}
	
				$name = "<pre wp-pre-tag-$i></pre>";
				$pre_tags[$name] = substr( $pee_part, $start ) . '</pre>';
	
				$pee .= substr( $pee_part, 0, $start ) . $name;
				$i++;
			}
	
			$pee .= $last_pee;
		}
	
		$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
		// Space things out a little
		$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
		$pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
		$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
		$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
		if ( strpos($pee, '<object') !== false ) {
			$pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
			$pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
		}
		$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
		// make paragraphs, including one at the end
		$pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
		$pee = '';
		foreach ( $pees as $tinkle )
			$pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
		$pee = preg_replace('|<p>\s*</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
		$pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
		$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
		$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
		$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
		$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
		$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
		$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
		if ( $br ) {
			$pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', array($this, '_preserveNewLines'), $pee);
			$pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
			$pee = str_replace('<WPPreserveNewline />', "\n", $pee);
		}
		$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
		$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
		$pee = preg_replace( "|\n</p>$|", '</p>', $pee );
	
		if ( !empty($pre_tags) )
			$pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
		
		foreach(array('script', 'style') as $tag) {
			$pee = str_replace(array('<p><' . $tag, '</' . $tag . '></p>'), array('<' . $tag, '</' . $tag . '>'), $pee);
		}
		
		$pee = str_replace(array('<p>[', ']</p>'), array('[', ']'), $pee);

		$content = $pee;
	}

	/**
	 * Preserve new lines
	 * Used as callback in _addParagraphsToString
	 *
	 * @param array $matches
	 * @return string
	 */
	public function _preserveNewLines($matches)
	{
		return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
	}

	/**
	 * Apply shortcodes to the content
	 *
	 * @param string &$content
	 * @param array $params = array
	 */
	protected function _applyShortcodes(&$content, $params = array())
	{
		$this->_applyCaptionShortcode($content, $params);
		$this->_applyYouTubeShortcode($content, $params);
		$this->_applyAssociatedProductsShortcode($content, $params);
		$this->_applyFeaturedProductShortcode($content, $params);
		$this->_applyGalleryShortcode($content, $params);
		
		if (Mage::getStoreConfigFlag('syntaxhighlighter/settings/is_enabled')) {
			$this->_applySyntaxHighlighterShortcode($content, $params);
		}
		
		$contentObj = new Varien_Object(array('content' => $content));
				
		Mage::dispatchEvent('wordpress_shortcode_apply', array('content' => $contentObj, 'object' => $params['object'], 'params' => $params, 'helper' => $this));
		
		$content = $contentObj->getContent();
	}

	/**
	  * Apply the Magento filters that are applied to static blocks
	  * This allows for {{store url=""}} & {{block type="..."}} strings
	  *
	  * @param string &$content
	  * @param array $params = array()
	  */
	protected function _addMagentoFilters(&$content, $params = array())
	{
		$content = Mage::helper('cms')->getBlockTemplateProcessor()->filter($content);
	}
	
	/**
	 * Apply the caption short code
	 *
	 * @param string &$content
	 * @param array $params = array
	 */
	protected function _applyCaptionShortcode(&$content, $params = array())
	{
		if (($shortcodes =	$this->_getShortcodesByTag($content, 'caption')) !== false) {
			foreach($shortcodes as $shortcode) {
				$params = $shortcode->getParams();
				$caption = $params->getCaption() ? sprintf('<p class="wp-caption-text">%s</p>', trim($params->getCaption())) : '';
				$style = $params->getAlign() != 'center' ? ' style="width:'.($params->getWidth()+10).'px;"' : '';

				$html = array(
					sprintf('<div id="%s" class="wp-caption %s"%s>', $params->getId(), $params->getAlign(), $style),
					$shortcode->getInnerContent(),
					$caption,
					'</div>'
				);

				$content = str_replace($shortcode->getHtml(), implode('', $html), $content);
			}
		}
	}
	
	/**
	 * Apply the YouTube short code
	 *
	 * @param string &$content
	 * @param array $params = array
	 */	
	protected function _applyYouTubeShortcode(&$content, $params = array())
	{
		$parts = $this->_explode('youtube', $content, true);
		$content = '';

		foreach($parts as $part) {
			if ($part['is_opening_tag']) {
				$videoUrl = trim($this->_match("/youtube=([^\]]+)\]/", $part['content'], 1), "\"' /");
				$data = array(
					'youtube_video_code' 	=> $this->_match("/[^a-zA-Z0-9]v=([a-zA-Z0-9_-]+)/", $videoUrl, 1),
					'video_width' 				=> $this->_match("/[^a-zA-Z0-9]w=([0-9]+)/", $videoUrl, 1),
					'video_height' 				=> $this->_match("/[^a-zA-Z0-9]h=([0-9]+)/", $videoUrl, 1),
					'hide_related' 				=> $this->_match("/[^a-zA-Z0-9](rel=0)/", $videoUrl, 1) ? true : false,
					'show_search' 				=> $this->_match("/(showsearch=0)/", $videoUrl, 1) ? false : true,
					'autoplay'		 				=> $this->_match("/(autoplay=1)/", $videoUrl, 1) ? true : false,
					'template'						=> 'wordpress/shortcode/youtube-video.phtml',
				);
				
				$part['content'] = $this->_generateBlockTag('core/template', $data);
			}

			$content .= $part['content'];
		}
	}

	/**
	 * Apply the associated products shortcode
	 *
	 * @param string &$content
	 * @param array $params = array
	 */
	protected function _applyAssociatedProductsShortcode(&$content, $params = array())
	{
		$parts = $this->_explode('associated-products', $content, true);
		$content = '';

		foreach($parts as $part) {
			if ($part['is_opening_tag']) {
				$blockParams = array(
					'template'		=> $this->_getMatchedString($part['content'], 'template', 'wordpress/shortcode/associated-products.phtml'),
					'title'				=> $this->_getMatchedString($part['content'], 'title', Mage::helper('wordpress')->__('Featured Products')),
				);
				
				$part['content'] = $this->_generateBlockTag('wordpress/post_associated_products', $blockParams);
			}

			$content .= $part['content'];
		}
	}
	
	/**
	 * Apply the featured products shortcode
	 *
	 * @param string &$content
	 * @param array $params = array
	 */
	protected function _applyFeaturedProductShortcode(&$content, $params = array())
	{
		$parts = $this->_explode('product', $content, true);
		$content = '';

		foreach($parts as $part) {
			if ($part['is_opening_tag']) {
				$blockParams = array(
					'product_id' 		=> $this->_match("/id=['\"]([^'\"]+)['\"]/", $part['content'], 1),
					'template' 			=> $this->_getMatchedString($part['content'], 'template', 'wordpress/shortcode/product.phtml'),
				);

				if ($blockParams['product_id']) {
					$part['content'] = $this->_generateBlockTag('core/template', $blockParams);
				}
				else {
					$part['content'] = '';
				}
			}

			$content .= $part['content'];
		}
	}
	
	/**
	 * Apply the gallery shortcode
	 *
	 * @param string &$content
	 * @param array $params = array()
	 */
	protected function _applyGalleryShortcode(&$content, $params = array())
	{
		if (($shortcodes =	$this->_getShortcodesByTag($content, 'gallery')) !== false) {
			foreach($shortcodes as $it => $shortcode) {
				$args = $shortcode->getParams();
				
				if (!$args->getColumns()) {
					$args->setColumns(3);
				}

				if (!$args->getSize()) {
					$args->setSize('thumbnail');
				}
				
				if (!$args->getLink()) {
					$args->setLink('attachment');
				}
				
				$post = $params['object'];

				if ($args->getPostId()) {
					if ($args->getPostId() !== $params['object']->getId()) {
						$post = Mage::getModel('catalog/post')->load($args->getPostId());
					}
				}
				
				$html = $this->_createBlock('core/template')
					->setImageCollection($params['object']->getImages())
					->setColumns($args->getColumns())
					->setPost($post)
					->setSize($args->getSize())
					->setLink($args->getLink())
					->setGalleryIt($it)
					->setTemplate('wordpress/shortcode/gallery.phtml')
					->toHtml();

				$content = str_replace($shortcode->getHtml(), $html, $content);
			}
		}
	}
	
	/**
	 * Apply the syntax highlighter shortcode
	 *
	 * @param string &$content
	 * @param array $params = array()
	 */
	protected function _applySyntaxHighlighterShortcode(&$content, $params = array())
	{
		foreach(array('code', 'sourcecode') as $tag) {
			$parts = $this->_explode($tag, $content, true);
			$content = '';
			$nextIsSource = false;
			$language = '';
			
			foreach($parts as $part) {
				$buffer = $part['content'];
	
				if ($part['is_opening_tag']) {
					$nextIsSource = true;
					$language = $this->_getMatchedString($buffer, 'language');
					$part['content'] = '';
				}
				elseif ($nextIsSource) {
					$nextIsSource = false;
					$part['content'] = '<pre class="brush: ' . ($language ? $language : 'php') . '">' . htmlentities($part['content']) . '</pre>';
				}
				elseif ($part['is_closing_tag']) {
					$part['content'] = '';
				}
	
				$content .= $part['content'];
			}
		}
	}

	/**
	 * Generate a block tag for Magento to process
	 *
	 * @param string $type
	 * @param $blockparams = array()
	 * @param string $name = null
	 * @return string
	 */
	protected function _generateBlockTag($type, array $blockParams = array(), $name = null)
	{
		if (isset($blockParams['type'])) {
			unset($blockParams['type']);
		}
		
		if (!$name) {
			$name = 'wp_block_' . rand(1, 9999);
		}
		
		$blockParams['name'] 	= $name;
		$blockParams 				= array_merge(array('type' => $type), $blockParams);
		
		foreach($blockParams as $key => $value) {
			if ($value) {
				$blockParams[$key] = sprintf('%s="%s"', $key, $value);
			}
			else {
				unset($blockParams[$key]);
			}
		}	
		
		return sprintf('{{block %s}}', implode(' ', $blockParams));
	}
	
	/**
	 * Explodes a string into parts based on the given short tag
	 *
	 * @param string $shortcode
	 * @param string $content
	 * @param bool $splitTags = false
	 * @return array
	 */
	protected function _explode($shortcode, $content, $splitTags = false)
	{
		$pattern 	= $splitTags ? "/(\[" . $shortcode . "[^\]]*\])|(\[\/".$shortcode . "\])/" : "/(\[" . $shortcode . "[^\]]*\].*?\[\/".$shortcode . "\])/";
		$parts 		= preg_split($pattern, $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		
		return $this->_sortExplodedString($parts, $shortcode);
	}

	/**
	 * Sorts and classifies a string exploded by self::_explode
	 *
	 * @param array $parts
	 * @param string $shortcode
	 * @return array
	 */
	protected function _sortExplodedString(array $parts, $shortcode)
	{
		foreach($parts as $key => $part) {
			if (strpos($part, "[$shortcode") !== false) {
				$parts[$key] = array('is_opening_tag' => true, 'is_closing_tag' => false,  'content' => $part);
			}
			else if (strpos($part, "[/$shortcode]")  !== false) {
				$parts[$key] = array('is_opening_tag' => false, 'is_closing_tag' => true,  'content' => $part);
			}
			else {
				$parts[$key] = array('is_opening_tag' => false, 'is_closing_tag' => false, 'content' => $part);
			}
		}

		return $parts;
	}

	/**
	 * Shortcut to create a block
	 *
	 * @param string $type
	 * @param string $name = null
	 * @return Mage_Core_Block_Abstract
	 */
	public function _createBlock($type, $name = null)
	{
		return Mage::getSingleton('core/layout')->createBlock($type, $name.microtime());
	}	

	/**
	 * Returns a matched string from $buffer
	 *
	 * @param string $buffer
	 * @param string $field
	 * @return string
	 */
	protected function _getMatchedString($buffer, $field, $defaultValue = null)
	{
		return ($matchedValue = $this->_match("/".$field."=['\"]([^'\"]+)['\"]/", $buffer, 1)) ? $matchedValue : $defaultValue;
	}

	/**
	 * Wrapper for preg_match that adds extra functionality
	 *
	 * @param string $pattern
	 * @param string $value
	 * @param int $keyToReturn
	 * @return mixed
	 */
	public function _match($pattern, $value, $keyToReturn = -1)
	{
		$result = array();
		preg_match($pattern, $value, $result);
		
		if ($keyToReturn == -1) {
			return $result;
		}

		return isset($result[$keyToReturn]) ? $result[$keyToReturn] : null;
	}
}
