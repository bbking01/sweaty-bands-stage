<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Twitterpro extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * Retrieve the default feed title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return 'Twitter Feed';
	}
	
	/**
	 * Retrieve an array of Tweets from Twitter
	 *
	 * @return false|array
	 */
	public function getTweets()
	{
		try {
			if ($feedUrl = $this->_getFeedUrl()) {
				if ($items = $this->_requestFeed($feedUrl)) {
					return $this->_parseFeed($items);
				}
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
		}
		
		return false;
	}
	
	protected function _toHtml()
	{
		if ($this->helper('wordpress')->isPluginEnabled('twitter-widget-pro')) {
			return parent::_toHtml();
		}
		
		return '';
	}

	/**
	 * Parse an array of items from the Twitter feed
	 *
	 * @param array $items
	 * @return array
	 */
	protected function _parseFeed(array $items)
	{
		$tweets = array();
		
		foreach($items as $it => $item) {
		
			$tweet = array(
				'time_since_tweet' => $this->_timeSinceTweet(strtotime($item['created_at'])),
				'tweet' => $this->_parseTwitterText($item['text']),
			);
			
			if ($this->getHideFrom() !== 'true') {
				$tweet['from'] = $this->__('from %s', $item['source']);
			}
			
			if (!empty($item['in_reply_to_screen_name'])) {
				$params = array(
					'href' => sprintf('http://twitter.com/%s/statuses/%s', $item['in_reply_to_screen_name'], $item['in_reply_to_status_id_str']),
					'class' => 'reply-to',
				);
				
				$tweet['in_reply_to'] = $this->_buildLink($this->__('in reply to %s', $item['in_reply_to_screen_name']), $params);
			}

			if ($this->getShowintents() === 'true') {
				$intents = array(
					'reply' => $this->_buildLink($this->__('Reply'), array('href' => sprintf('http://twitter.com/intent/tweet?in_reply_to=%s', $item['id_str']), 'class' => 'in-reply-to')),
					'retweet' => $this->_buildLink($this->__('Retweet'), array('href' => sprintf('http://twitter.com/intent/retweet?tweet_id=%s', $item['id_str']), 'class' => 'retweet')),
					'favorite' => $this->_buildLink($this->__('Favorite'), array('href' => sprintf('http://twitter.com/intent/favorite?tweet_id=%s', $item['id_str']), 'class' => 'favorite')),
				);
			
				$tweet['intents'] = $intents;
			}

			$tweets[] = new Varien_Object($tweet);
		}

		return $tweets;
	}
	
	/**
	 * Generate the feed URL
	 *
	 * @return string
	 */
	protected function _getFeedUrl()
	{
		$params = array(
			'screen_name' => $this->getUsername(),
			'count' => $this->getItems(),
			'exclude_replies' => $this->getHidereplies() === 'true' ? 'true' : null,
			'include_rts' => $this->getShowretweets() === 'true' ? 'true' : null,
		);
		
		return sprintf('%s?%s', $this->_getApiUrl(), http_build_query($params));
	}
	
	/**
	 * Retrieve the API URL
	 *
	 * @return string
	 */
	protected function _getApiUrl()
	{
		return sprintf('%s://api.twitter.com/1/statuses/user_timeline.json', $this->getHttpVsHttps());
	}
	
	/**
	 * Request the Twitter feed using CURL
	 *
	 * @param string $url
	 * @param return false|array
	 */
	protected function _requestFeed($url)
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		
		if ($this->getHttpVsHttps() === 'https') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$data = curl_exec($ch);
		
		curl_close($ch);
		
		if ($data) {
			return json_decode($data, true);
		}

		return false;
	}

	/**
	 * Parse the twitter text field and add links where needed
	 *
	 * @param string $text
	 * @return string
	 */
	protected function _parseTwitterText($text)
	{
		$text = preg_replace_callback('/(^|\s)@(\w+)/i', array($this, '_linkTwitterUsersCallback'), $text);
		$text = preg_replace_callback("/(^|\s)(([a-zA-Z]+:\/\/)([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", array($this, '_linkUrlsCallback'), $text);
		$text = preg_replace_callback("/(^|\s)(www\.([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", array($this, '_linkUrlsCallback'), $text);
		$text = preg_replace_callback('/(^|\s)(#\w*)/i', array($this, '_linkHashtagsCallback'), $text);
		
		return $text;
	}

	/**
	 * Replace #hashtag with a link to search.twitter.com for that hashtag
	 *
	 * @param array $matches - Tweet text
	 * @return string - Tweet text with #hashtags linked
	 */
	private function _linkHashtagsCallback($matches)
	{
		$linkAttrs = array(
			'href'	=> 'http://search.twitter.com/search?q=' . urlencode( $matches[2] ),
			'class'	=> 'twitter-hashtag'
		);
		return $matches[1] . $this->_buildLink( $matches[2], $linkAttrs );
	}
	
	/**
	 * Add anchor's to URL's
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function _linkUrlsCallback($matches)
	{
		return $matches[1] . $this->_buildLink( $matches[2],  array('href' => $matches[2]));
	}
	
	/**
	 * Add anchor's to twitter users
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function _linkTwitterUsersCallback($matches)
	{
		$linkAttrs = array(
			'href'	=> 'http://twitter.com/' . urlencode($matches[2]),
			'class'	=> 'twitter-user'
		);

		return $matches[1] . $this->_buildLink( '@'.$matches[2], $linkAttrs );
	}
	
	/**
	 * Build a link HTML anchor element
	 *
	 * @param string $text
	 * @param array $attributes
	 * @return string
	 */
	protected function _buildLink($text, array $attributes)
	{
		if (!isset($attributes['href'])) {
			return '';
		}

		if ($this->_getData('targetBlank') !== 'true') {
			$attributes['target'] = '_blank';
		}
		
		foreach($attributes as $key => $value) {
			$attributes[$key] = sprintf('%s="%s"', $key, $value);
		}

		return sprintf('<a %s>%s</a>', implode(' ', $attributes), $text);
	}
	
	/**
	 * Calculate the time since the last tweet
	 *
	 * @param string $startTimestamp
	 * @return string
	 */
	protected function _timeSinceTweet($startTimestamp)
	{
		$max = $this->getShowts();
		$dateFormat = $this->_getData('dateFormat');

		$chunks = array(
			'year'   => 60 * 60 * 24 * 365,
			'month'  => 60 * 60 * 24 * 30,
			'week'   => 60 * 60 * 24 * 7,
			'day'    => 60 * 60 * 24,
			'hour'   => 60 * 60,
			'minute' => 60,
			'second' => 1
		);
	
		$since = time() - $startTimestamp;
	
		if ($max != '-1' && $since >= $max) {
			return Mage::getModel('core/date')->date($dateFormat, $startTimestamp);	
		}

		foreach ( $chunks as $key => $seconds ) {
			if (($count = floor( $since / $seconds )) != 0) {
				break;
			}
		}
	
		$messages = array(
			'year'   => $this->__('about %s year ago', $count),
			'month'  => $this->__('about %s month ago', $count),
			'week'   => $this->__('about %s week ago', $count),
			'day'    => $this->__('about %s day%s ago', $count, $count !== 1 ? 's' : ''),
			'hour'   => $this->__('about %s hour ago', $count),
			'minute' => $this->__('about %s minute ago', $count),
			'second' => $this->__('about %s second ago', $count),
		);
	
		return sprintf($messages[$key], $count);
	}
}
