<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_SlickSocialButtons extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * This isn't used for this widget/block
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return '';
	}
	
	/**
	 * Loads the Slick Social Buttons options into the block
	 *
	 */
	protected function _construct()
	{
		parent::_construct();
		
		if (($pluginOptions = $this->_getWpOption('dcssb_options', false)) !== false) {
			$options = unserialize($pluginOptions);
			
			foreach($options as $key => $value) {
				$value = trim($value);
				
				if ($value === '') {
					continue;
				}
				else if ($value === 'true') {
					$value = true;
				}
				else if ($value === 'false') {
					$value = false;
				}
				
				$key = preg_replace('/([A-Z]{1,})/e', '"_" . strtolower("$1");', $key);
			
				$this->setData($key, $value);
			}
		}
	}

	/**
	 * Forces the button content to be written out
	 *
	 */
	protected function _toHtml()
	{
		$this->setText('');
		
		if (($buttons = $this->getEnabledButtons()) !== false) {
			foreach($buttons as $button) {
				$text = '';

				if ($button === 'twitter') {
					$text = $this->_getTwitterCode();
				}
				else if ($button === 'facebook') {
					$text = $this->_getFacebookCode();
				}
				else if ($button === 'plusone') {
					$text = $this->_getPlusOneCode();
				}
				else if ($button === 'linkedin') {
					$text = $this->_getLinkedInCode();
				}
				else if ($button === 'stumble') {
					$text = $this->_getStumbleUponCode();
				}
				else if ($button === 'digg') {
					$text = $this->_getDiggCode();
				}
				else if ($button === 'delicious') {
					$text = $this->_getDeliciousCode();
				}
				else if ($button === 'pinit') {
					$text = $this->_getPintrestCode();
				}
				else if ($button === 'reddit') {
					$text = $this->_getRedditCode();
				}
				else if ($button === 'buffer') {
					$text = $this->_getBufferCode();
				}
				
				if (trim($text) !== '') {
					$this->addText($text);
				}
			}
		}
		
		if ($this->getText()) {
			$text = $this->getText();
			
			$this->setText('');
			
			$scripts = array(
				'wp-content/plugins/slick-social-share-buttons/js/ga.social_tracking.js?ver=3.4',
				'wp-content/plugins/slick-social-share-buttons/js/jquery.easing.js?ver=3.4',
				'wp-content/plugins/slick-social-share-buttons/js/jquery.social.float.1.3.js?ver=3.4',
			);

			$hasJquery = false;
			
			if ($headBlock = $this->getLayout()->getBlock('head')) {
				foreach($headBlock->getItems() as $src => $details) {
					if (strpos($details['type'], 'js') !== false && strpos($src, 'jquery') !== false) {
						$hasJquery = true;
						break;
					}
				}
			}
		
			if (!$hasJquery) {
				array_unshift($scripts, 'wp-includes/js/jquery/jquery.js?ver=1.7.2');
			}
						
			$installUrl = rtrim($this->_getWpOption('home'), '/');

			foreach($scripts as $script) {
				$this->addText(sprintf('<script type="text/javascript" src="%s/%s"></script>', $installUrl, ltrim($script, '/')));
			}

			$this->addText(sprintf('<link rel="Stylesheet" type="text/css" href="%s/css/dcssb.css" />', $this->_getPluginDirectoryUrl()));
			$this->addText('<div id="dc-dcssb">');
			$this->addText(sprintf('<ul id="nav-dcssb" class="%s">', $this->getDcssbDirection()));
			$this->addText($text);
			$this->addText('</ul>');
			$this->addText('<div class="clear"></div>');
			$this->addText('<div class="dc-corner"><span></span></div>');
			$this->addText('</div>');
			$this->addText($this->_getInitText());
		}
		
		return $this->getText();
	}

	protected function _getInitText()
	{
		$socialId = 'dc-dcssb';
		
		if (!$this->getMethod()) {
			$this->setMethod('stick');
		}

		if ($this->getPosition() === 'top-left') {
			$this->setLocation('top');
			$this->setAlign('left');
		}
		else if ($this->getPosition() === 'top-right') {
			$this->setLocation('top');
			$this->setAlign('right');
		}
		else if ($this->getPosition() === 'bottom-left') {
			$this->setLocation('bottom');
			$this->setAlign('left');
		}
		else if ($this->getPosition() === 'bottom-right') {
			$this->setLocation('bottom');
			$this->setAlign('right');
		}
		else if ($this->getPosition() === 'left') {
			if ($this->getMethod() === 'float') {
				$this->setLocation('top');
				$this->setAlign('left');
			}
			else {
				$this->setLocation('left');
				$this->setAlign('top');
			}
		}
		else if ($this->getPosition() === 'right') {
			if ($this->getMethod() === 'float') {
				$this->setLocation('top');
				$this->setAlign('right');
			}
			else {
				$this->setLocation('right');
				$this->setAlign('top');
			}
		}
		
		$this->_setDataDefaults(array(
			'width' => 200,
			'speed_menu' => 600,
			'speed_float' => 1500,
			'disable_float' => 'disableFloat: true,',
			'center' => 'false',
			'centerpx' => '0',
			'offset_l' => '0',
			'offset_a' => '0',
			'auto_close' => 'false',
			'load_open' => 'false',
			'direction' => 'vertical',
			'class_open' => 'dcssb-open',
			'clss_close' => 'dcssb-close',
			'class_toggle' => 'dcssb-link',
			'id_wrapper' => 'dcssb-float',
		));

		$this->setClassWrapper('dc-social-slick ' . $this->getDirection());
		
		$width = $this->getSizeTwitter() === 'horizontal' ? '130' : '98';

		if (!$this->getTabImage()) {
			if ($this->getMethod() === 'stick') {
				$this->setTabImage(sprintf('<img src="%s/css/images/tab_%s_%s.png" alt="Share" />', $this->_getPluginDirectoryUrl(), $this->getLocation(), $this->getLocation()));
			}
			else {
				if ($width == '130') {
					$this->setTabImage(sprintf('<img src="%s/css/images/tab_130.png" alt="Share" />', $this->_getPluginDirectoryUrl()));
				}
				else {
					$this->setTabImage(sprintf('<img src="%s/css/images/tab_%s_floating.png" alt="Share" />', $this->_getPluginDirectoryUrl(), $this->getLocation()));
				}
			}
		}
		else {				
			$this->setTabImage(sprintf('<img src="%s" alt="" />', $this->getTabImage()));
		}
			
		if ($this->getMethod() == 'stick') {
			$this->setIdWrapper('dcssb-slick');
		}
			
		if ($this->getMethod() === 'stick') {
			$text = array(
				'<script type="text/javascript">_gaq.trackFacebook();</script>',
				'<script type="text/javascript">',
				'jQuery(window).load(function() {',
				"jQuery('#dc-dcssb').dcSocialSlick({",
				sprintf("idWrapper : '%s',", $this->getIdWrapper()),
				sprintf("location: '%s',", $this->getLocation()),
				sprintf("align: '%s',", $this->getAlign()),
				sprintf("offset: '%spx',", $this->getOffsetL()),
				sprintf("speed: %s,", $this->getSpeedMenu()),
				sprintf("tabText: '%s',", $this->getTabImage()),
				sprintf("autoClose: %s,", $this->getAutoClose()),
				sprintf("loadOpen: %s,", $this->getLoadOpen()),
				sprintf("classWrapper: '%s',", $this->getClassWrapper()),
				sprintf("classOpen: '%s',", $this->getClassOpen()),
				sprintf("classClose: '%s',", $this->getClassOpen()),
				sprintf("classToggle: '%s'", $this->getClassToggle()),
				'});});',
				'</script>',
			);
		}
		else {
			$text = array(
				'<script type="text/javascript">_ga.trackFacebook();</script>',
				'<script type="text/javascript">',
				'jQuery(window).load(function() {',
				"jQuery('#dc-dcssb').dcSocialFloater({",
				sprintf("idWrapper : '%s',", $this->getIdWrapper()),
				sprintf("width: '%s',", $this->getWidth()),
				sprintf("location: '%s',", $this->getLocation()),
				sprintf("align: '%s',", $this->getAlign()),
				sprintf("offsetLocation: %s,", $this->getOffsetL()),
				sprintf("offsetAlign: %s,", $this->getOffsetA()),
				sprintf("center: %s,", $this->getCenter()),
				sprintf("centerPx: %s,", $this->getCenterpx()),
				sprintf("speedContent: %s,", $this->getSpeedMenu()),
				sprintf("speedFloat: %s,", $this->getSpeedFloat()),
				$this->getDisableFloat(),
				sprintf("tabText: '%s',", $this->getTabImage()),
				sprintf("autoClose: %s,", $this->getAutoClose()),
				sprintf("loadOpen: %s,", $this->getLoadOpen()),
				"tabClose: true,",
				sprintf("classOpen: '%s',", $this->getClassOpen()),
				sprintf("classClose: '%s',", $this->getClassOpen()),
				sprintf("classToggle: '%s'", $this->getClassToggle()),
				'});});',
				'</script>',
			);
		}
		
		return implode("\r\n", $text);
	}
	
	/**
	 * Retrieve the code for the Twitter button
	 *
	 * @return string
	 */
	protected function _getTwitterCode()
	{
		$title = $this->_getPageTitle();
		$size = $this->getSizeTwitter();
		$classSize = $size === 'horizontal' || $size === 'none' ? 'size-small' : 'size-box';
		$link = $this->_getPageUrl();
		$shortLink = $link;

		$text = array(
			sprintf('<li class="dcssb-twitter %s">', $classSize),
			sprintf('<a href="http://twitter.com/share" data-url="%s" data-counturl="%s" data-text="%s" class="twitter-share-button" data-count="%s" data-via="%s"></a>', $shortLink, $link, $title, $size, $this->getTwitterId()),
			'<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>',
			'</li>',
		);
		
		return implode('', $text);
	}
	
	/**
	 * Retrieve the code to display the Facebook social icon
	 *
	 * @return string
	 */
	protected function _getFacebookCode()
	{
		$link = $this->_getPageUrl();
		$eLink = urlencode($link);
		$size = $this->getSizeFacebook();
		$classSize = $size == 'standard' || $size == 'button_count' ? 'size-small' : 'size-box';
		$appId = $this->getAppFacebook();
		
		$method = $this->getMethodFacebook();
		
		if ($method === 'xfbml') {
			$text = array(
				'<div id="fb-root"></div><script>window.fbAsyncInit = function() {FB.init({appId: "'.$appId.'", status: true, cookie: true, xfbml: true});};',
				'(function(){var e = document.createElement("script");e.type = "text/javascript";e.src = document.location.protocol + "//connect.facebook.net/en_US/all.js";e.async = true;document.getElementById("fb-root").appendChild(e);}());</script>',
				sprintf('<li id="dcssb-facebook" class="%s">', $classSize),
				sprintf('<fb:like href="%s" send="false" layout="%s" show_faces="false" font=""></fb:like>', $eLink, $size),
				'</li>',
			);
		}
		else if($classSize == 'size-small'){
			$text = array(
				sprintf('<li id="dcssb-facebook" class="%s">', $classSize),
				sprintf('<iframe src="http://www.facebook.com/plugins/like.php?app_id=%s&amp;href=%s&amp;send=false&amp;layout=%s&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=30" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:30px;" allowTransparency="true"></iframe>', $appId, $eLink, $size),
				'</li>'
			);
		} else {
			$text = array(
				sprintf('<li id="dcssb-facebook" class="%s">', $classSize),
				sprintf('<iframe src="http://www.facebook.com/plugins/like.php?app_id=%s&amp;href=%s&amp;send=false&amp;layout=%s&amp;width=50&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=62" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:62px;" allowTransparency="true"></iframe>', $appId, $eLink, $size),
				'</li>',
			);
		}
		
		return implode('', $text);
	}
	
	/**
	 * Retrieve the Plus One code
	 *
	 * @return string
	 */
	protected function _getPlusOneCode()
	{
		$link = $this->_getPageUrl();
		$size = $this->getSizePlusone();
		$parts = explode('_', $size);
		$size = $parts[0];
		$count = $parts[1] ? ' count="true"': ' count="false"';
		$classSize = $size == 'standard' || $size == 'small' || $size == 'medium' ? 'size-small' : 'size-box';
		
		$text = array(
			sprintf('<li id="dcssb-plusone" class="%s"><g:plusone size="%s" href="%s"%s></g:plusone></li>', $classSize, $size, $link, $count),
			'<script type="text/javascript">(function(){var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;po.src = "https://apis.google.com/js/plusone.js";var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);})();</script>',
		);

		return implode('', $text);
	}
	
	/**
	 * Retrieve the Linked In Social button code
	 *
	 * @return string
	 */
	protected function _getLinkedInCode()
	{
		$size = $this->getSizeLinkedin();
		$classSize = $size == 'right' || $size == 'none' ? 'size-small' : 'size-box';
		$link = $this->_getPageUrl();

		$text = array(
			'<script type="text/javascript" src="http://platform.linkedin.com/in.js"></script>',
			sprintf('<li id="dcssb-linkedin" class="%s">', $classSize),
			sprintf('<script type="in/share" data-url="%s" data-counter="%s"></script>', $link, $size),
			'</li>',
		);
		
		return implode('', $text);
	}
	
	/**
	 * Retrieve the Stumble Upon social button code
	 *
	 * @return string
	 */
	protected function _getStumbleUponCode()
	{
		$link = $this->_getPageUrl();
		$eLink = urlencode($link);
		$size = $this->getSizeStumble();
		$classSize = $size == '1' || $size == '2' || $size == '3' || $size == '4' ? 'size-small' : 'size-box';
		$dim = 'width:50px; height: 60px;';
		
		if ($size == '1') {
			$dim = 'width:80px; height: 30px;';
		}
		else if ($size == '2') {
			$dim = 'width:70px; height: 30px;';		
		}
		else if ($size == '3') {
			$dim = 'width:50px; height: 30px;';
		}
		else if ($size == '4') {
			$dim = 'width:50px; height: 30px;';
		}
		
		$text = array(
			sprintf('<li id="dcssb-stumble" class="%s">', $classSize),
			sprintf('<iframe src="http://www.stumbleupon.com/badge/embed/%s/?url=%s" scrolling="no" frameborder="0" style="border:none; overflow:hidden; %s" allowTransparency="true">', $size, $eLink, $dim),
			'</iframe>',
			'</li>'
		);
		
		return implode('', $text);
	}
	
	/**
	 * Retrieve the code for the Digg social button
	 *
	 * @return string
	 */
	protected function _getDiggCode()
	{
		$link = $this->_getPageUrl();
		$eLink = urlencode($link);
		$title = $this->_getPageTitle();
		$description = $this->_getPageDescription();
		$size = $this->getSizeDigg();
		$classSize = $size == 'DiggCompact' || $size == 'DiggIcon' ? 'size-small' : 'size-box' ;

		$text = array(
			'<script type="text/javascript">',
			'(function(){var s = document.createElement("SCRIPT"), s1 = document.getElementsByTagName("SCRIPT")[0];s.type = "text/javascript";s.async = true;s.src = "http://widgets.digg.com/buttons.js";s1.parentNode.insertBefore(s, s1);})();',
			'</script>',
			sprintf('<li id="dcssb-digg" class="%s">', $classSize),
			sprintf('<a href="http://digg.com/submit?url=%s&amp;title=%s" class="DiggThisButton %s"></a>', $eLink, $title, $size),
			sprintf('<span style="display: none;">%s</span>', $description),
			'</li>',
		);

		return implode('', $text);
	}
	
	/**
	 * Retrieve the social code for Delicious
	 *
	 * @return string
	 */
	protected function _getDeliciousCode()
	{
		$size = $this->getSizeDelicious();
		$classSize = $size == 'wide' ? 'size-small' : 'size-box' ;

		$text = array(
			'<script type="text/javascript" src="http://delicious-button.googlecode.com/files/jquery.delicious-button-1.1.min.js"></script>',
			sprintf('<li id="dcssb-delicious" class="%s"><a class="delicious-button" href="http://delicious.com/save">', $classSize),
			"\r\n<!-- {\r\n",
			sprintf("url:\"%s\",\r\ntitle:\"%s\",\r\nbutton:\"%s\"", $this->_getPageUrl(), $this->_getPageTitle(), $size),
			"\r\n} -->\r\n",
			'Delicious',
			'</a></li>',
		);

		return implode('', $text);
	}

	/**
	 * Retrieve the code for the Pintrest social button
	 *
	 * @return string
	 */
	protected function _getPintrestCode()
	{
		$link = $this->_getPageUrl();
		$eLink = urlencode($link);
		$title = $this->_getPageTitle();
		$description = $this->_getPageDescription();
		$size = $this->getSizePinit();
		$classSize = $size == 'none' || $size == 'horizontal' ? 'size-small '.$size : 'size-box '.$size;
		$method = $this->getMethodPinit();

		$text = array(
			sprintf('<li id="dcssb-pinit" class="%s">', $classSize),
		);
		
		if ($method == 'featured') {
			$imageUrl = urlencode($this->getImagePinit());
			
			if ($post = Mage::registry('wordpress_post')) {
				if ($post->getFeaturedImage()) {
					$imageUrl = urlencode($post->getFeaturedImage()->getAvailableImage());
				}
			}

			$text[] = sprintf('<a href="http://pinterest.com/pin/create/button/?url=%s&amp;media=%s&amp;description=%s" class="pin-it-button" count-layout="%s">Pin It</a>', $eLink, $imageUrl, $description, $size);
			$text[] = '<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script></li>';
		
		}
		else {
			$text[] = sprintf('<div class="pinit-counter-count">%d</div>', 0);
			$text[] = '<a href="#" class="pinItButton" title="Pin It on Pinterest">Pin it</a></li>';
			$text[] = '<script type="text/javascript">function exec_pinmarklet(){var e=document.createElement("script");e.setAttribute("type","text/javascript");e.setAttribute("charset","UTF-8");e.setAttribute("src","http://assets.pinterest.com/js/pinmarklet.js?r=" + Math.random()*99999999);document.body.appendChild(e);}</script>';
		}
	
		return implode('', $text);
	}
	
	/**
	 * Retrieve Reddit social button code
	 *
	 * @return string
	 */
	protected function _getRedditCode()
	{
		$title = $this->_getPageTitle();
		$size = $this->getSizeReddit();
		$classSize = $size == 'horizontal' || $size == 'none' ? 'size-small' : 'size-box';
		$src = "http://www.reddit.com/static/button/button2.js";

		if ($size === 'horizontal') {
			$src = "http://www.reddit.com/static/button/button1.js";
		} 
		else if ($size === 'none') {
			$src = "http://www.reddit.com/buttonlite.js?i=2";
		}
		
		$text = array(
			sprintf('<li id="dcssb-reddit" class="%s">', $classSize),
			'<script type="text/javascript">',
			sprintf('reddit_url = "%s";reddit_title = "%s";reddit_newwindow="1";', $link, $title),
			'</script>',
			sprintf('<script type="text/javascript" src="%s"></script>', $src),
			'</li>',
		);
		
		return implode('', $text);
	}

	/**
	 * Retrieve the code for the Buffer social button
	 *
	 * @return string
	 */
	protected function _getBufferCode()
	{
		$title = $this->_getPageTitle();
		$twitterId = $this->getUserTwitter();
		$size = $this->getSizeBuffer();
		$classSize = $size == 'horizontal' || $size == 'none' ? 'size-small' : 'size-box';
		
		$text = array(
			sprintf('<li id="dcssb-buffer" class="%s">', $classSize),
			sprintf('<a href="http://bufferapp.com/add" data-url="%s" data-text="%s" class="buffer-add-button" data-count="%s" data-via="%s">Buffer</a>', $link, $title, $size, $twitterId),
			'</li>',
			'<script type="text/javascript" src="http://static.bufferapp.com/js/button.js"></script>',
		);

		return implode('', $text);
	}
	
	/**
	 * Retrieve an array of enabled buttons
	 *
	 * @return false|array
	 */
	public function getEnabledButtons()
	{
		if ($this->_canRun()) {
			$buttons = explode(',', $this->getDcssbOrder());
			
			foreach($buttons as $it => $button) {
				if (!$this->_getData('inc_' . $button)) {
					unset($buttons[$it]);
				}
			}
			
			return count($buttons) > 0 ? $buttons : false;
		}
		
		return false;
	}
	
	/**
	 * Retrieve the URL to the plugin directory in WordPress
	 *
	 * @return string
	 */
	protected function _getPluginDirectoryUrl()
	{
		return rtrim($this->_getWpOption('home'), '/') . '/wp-content/plugins/slick-social-share-buttons';
	}
	
	/**
	 * Determine if the current page is the homepage
	 *
	 * @return bool
	 */
	protected function _isHomepage()
	{
		if ($this->_getWpOption('show_on_front') == 'page') {
			if ($page = Mage::registry('wordpress_page')) {
				return $page->getId() == $this->_getWpOption('page_on_front');
			}
		}
		else {
			$request = Mage::app()->getRequest();
			
			return $request->getModuleName() === 'wordpress' && $request->getControllerName() === 'homepage';
		}
		
		return false;
	}

	/**
	 * Determine if the current page is a WordPress page
	 *
	 * @return bool
	 */	
	protected function _isPagePage()
	{
		return !is_null(Mage::registry('wordpress_page')) && !$this->_isHomepage();
	}

	/**
	 * Determine if the current page is the blog listing page
	 * Returns false is posts on homepage
	 *
	 * @return bool
	 */
	protected function _isBlogPage()
	{
		if (($page = Mage::registry('wordpress_page')) && !$this->_isHomepage()) {
			return $page->getId() != $this->_getWpOption('page_for_posts');
		}
		
		return false;
	}
	
	/**
	 * Determine if the current page is the homepage with posts
	 *
	 * @return bool
	 */
	protected function _isPostPage()
	{
		return !is_null(Mage::registry('wordpress_post'));
	}
	
	/**
	 * Determine if the current page is a category page
	 *
	 * @return bool
	 */
	protected function _isCategoryPage()
	{
		return !is_null(Mage::registry('wordpress_category'));
	}
	
	/**
	 * Determine if the current page is an archive page
	 *
	 * @return bool
	 */
	protected function _isArchivePage()
	{
		return !is_null(Mage::registry('wordpress_archive'));	
	}
	
	/**
	 * Determine if we can display the Slick Social Buttons on the current page
	 *
	 * @return bool
	 */
	protected function _canRun()
	{
		if (!$this->helper('wordpress')->isPluginEnabled('slick-social-share-buttons')) {
			return false;
		}

		if ($this->_isHomepage()) {
			return $this->getShowHome();
		}
		else if ($this->_isPagePage()) {
			return $this->getShowPage();
		}
		else if ($this->_isBlogPage()) {
			return $this->getShowBlog();
		}
		else if ($this->_isCategoryPage()) {
			return $this->getShowCategory();
		}
		else if ($this->_isPostPage()) {
			return $this->getShowPost();
		}
		else if ($this->_isArchivePage()) {
			return $this->getShowArchive();
		}
		
		return false;
	}

	/**
	 * Retrieve an option from the WordPress config
	 *
	 * @param string $key
	 * @param mixed $default = null
	 * @return mixed
	 */
	protected function _getWpOption($key, $default = null)
	{
		$dataKey = 'wp_option_' . $key;
		
		if (!$this->_getData($dataKey)) {
			$this->setData($dataKey, $this->helper('wordpress')->getWpOption($key, $default));
		}
		
		return $this->_getData($dataKey);
	}
	
	/**
	 * Add text to the output buffer
	 *
	 * @param string $text
	 * @return $this
	 */
	public function addText($text)
	{
		$this->setText( $this->getText() . $text);
		
		return $this;
	}
}
