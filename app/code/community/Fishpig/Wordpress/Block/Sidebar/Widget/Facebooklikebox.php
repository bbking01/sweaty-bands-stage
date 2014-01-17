<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Facebooklikebox extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	protected $_fixOptionKeys = true;
	
	/**
	 * Retrieve the default feed title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return '';
	}
	
	/**
	 * Generate the HTML and return
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		if (!$this->helper('wordpress')->isPluginEnabled('facebook-like-box')) {
			return '';
		}

		try {
			if ($html = $this->_generateFacebookLikeBoxHtml()) {
				return $html;
			}
		}
		catch (Exception $e) {
			$this->helper('wordpress')->log($e->getMessage());
		}
	}
	
	/**
	 * Generate the HTML for the Facebook Like Box
	 *
	 * @return string
	 */
	protected function _generateFacebookLikeBoxHtml()
	{
		$this->_setDataDefaults(array(
			'plugin_display_type' => 'like_box',
			'layout_mode' => 'xfbml',
			'fblike_button_style' => 'standard',
			'fblike_button_show_faces' => 'no',
			'fblike_button_verb_to_display' => 'recommend',
			'fblike_button_font' => 'lucida grande',
			'fblike_button_width' => '292',
			'fblike_button_color_scheme' => 'light',
			'connection' => '10',
			'width' => '292',
			'height' => '255',
			'streams' => 'yes',
			'colour_scheme' => 'light',
			'border_color' => 'AAA',
			'show_faces' => 'yes',
			'header' => 'yes',
			'credit_on' => 'no',
		));
		
		$this->_convertDataValues(array(
			'yes' => 'true',
			'no' => 'false',
		));
		
		if ($this->getStreams() === 'true') {
			$this->setHeight($this->getHeight() + 300);
		}
		
		if ($this->getHeader() === 'yes') {
			$this->setHeight($this->getHeight() + 32);
		}
		
		if (strlen($this->getPageUrl()) > 23) {	
			$likeBoxIframe = $this->_buildElement(array(
				'src' => sprintf('http://www.facebook.com/plugins/likebox.php?href=%s&amp;width=%s&amp;colorscheme=%s&amp;border_color=$borderColor&amp;show_faces=%s&amp;connections=$connection&amp;stream=%s&amp;header=%s&amp;height=%s', $this->getPageUrl(), $this->getWidth(), $this->getColourScheme(), $this->getShowFaces(), $this->getStreams(), $this->getHeader(), $this->getHeight()),
				'scrolling' => 0,
				'frameborder' => 0,
				'style' => sprintf('border:none; overflow:hidden; width:%spx; height:%spx;', $this->getWidth(), $this->getHeight()),
				'allowTranspareny' => 'true',
			), 'iframe');
			
			$likeBoxXfbml = '<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>'
				. $this->_buildElement(array(
					'href' => $this->getPageUrl(), 
					'width' => $this->getWidth(),
					'show_faces' => $this->getShowFaces(),
					'border_color' => $this->getBorderColor(),
					'stream' => $this->getStreams(),
					'header' => $this->getHeader(),
				), 'fb:like-box');
		}
		else {
			$likeBoxIframe = $this->_buildElement(array(
				'src' => sprintf('http://www.facebook.com/plugins/likebox.php?id=%s&amp;width=%s&amp;colorscheme=%s&amp;border_color=$borderColor&amp;show_faces=%s&amp;connections=$connection&amp;stream=%s&amp;header=%s&amp;height=%s', $this->getPageId(), $this->getWidth(), $this->getColourScheme(), $this->getShowFaces(), $this->getStreams(), $this->getHeader(), $this->getHeight()),
				'scrolling' => 0,
				'frameborder' => 0,
				'style' => sprintf('border:none; overflow:hidden; width:%spx; height:%spx;', $this->getWidth(), $this->getHeight()),
				'allowTranspareny' => 'true',
			), 'iframe');
			
			$likeBoxXfbml = '<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>'
				. $this->_buildElement(array(
					'id' => $this->_getPageId(), 
					'width' => $this->getWidth(),
					'show_faces' => $this->getShowFaces(),
					'border_color' => $this->getBorderColor(),
					'stream' => $this->getStreams(),
					'header' => $this->getHeader(),
				), 'fb:like-box');
		}
		
		$likeButtonXfbml = '<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>'
			. $this->_buildElement(array(
				'layout' => $this->getFblikeButtonStyle(),
				'show_faces' => $this->getFblikeButtonShowFaces(),
				'width' => $this->getFblikeButtonWidth(),
				'action' => $this->getFblikeButtonVerbToDisplay(),
				'font' => $this->getFblikeButtonFont(),
				'colorscheme' => $this->getFblikeButtonColorScheme(),
			), 'fb:like');

		if ($this->getPluginDisplayType() === 'like_box') {
			if ($this->getLayoutMode() === 'iframe') {
				$html = $likeBoxIframe;
			}
			else {
				$html = $likeBoxXfbml;
			}
		}
		else if ($this->getPluginDisplayType() === 'like_button') {
			$html - $likeButtonXfbml;
		}
		else if ($this->getPluginDisplayType() === 'both') {
			if ($this->getLayoutMode() === 'iframe') {
				$html = $likeBoxIframe;
			}
			else {
				$html = $likeBoxXfbml;
			}
			
			$html .= "\r\n" . $likeButtonXfbml;
		}
	
		return trim($html);
	}
	
	/**
	 * Build a HTML element 
	 *
	 * @param array $params
	 * @param string $element
	 * @return string
	 */
	protected function _buildElement(array $params, $element)
	{
		foreach($params as $key => $param) {
			$params[$key] = sprintf('%s="%s"', $key, $param);
		}
		
		return sprintf('<%s %s></%s>', $element, implode(' ', $params), $element);
	}
}
