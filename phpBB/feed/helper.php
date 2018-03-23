<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\feed;

use phpbb\config\config;
use phpbb\path_helper;
use phpbb\textformatter\s9e\renderer;
use phpbb\user;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class with some helpful functions used in feeds
 */
class helper
{
	/** @var config */
	protected $config;

	/** @var ContainerInterface */
	protected $container;

	/** @var path_helper */
	protected $path_helper;

	/** @var renderer */
	protected $renderer;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param	config				$config			Config object
	 * @param	ContainerInterface	$container		Service container object
	 * @param	path_helper			$path_helper 	Path helper object
	 * @param	renderer			$renderer		TextFormatter renderer object
	 * @param	user				$user			User object
	 */
	public function __construct(config $config, ContainerInterface $container, path_helper $path_helper, renderer $renderer, user $user)
	{
		$this->config = $config;
		$this->container = $container;
		$this->path_helper = $path_helper;
		$this->renderer = $renderer;
		$this->user = $user;
	}

	/**
	 * Returns the board url (and caches it in the function)
	 */
	public function get_board_url()
	{
		static $board_url;

		if (empty($board_url))
		{
			$board_url = generate_board_url();
		}

		return $board_url;
	}

	/**
	 * Run links through append_sid(), prepend generate_board_url() and remove session id
	 */
	public function append_sid($url, $params)
	{
		return append_sid($this->get_board_url() . '/' . $url, $params, true, '');
	}

	/**
	 * Generate ISO 8601 date string (RFC 3339)
	 */
	public function format_date($time)
	{
		static $zone_offset;
		static $offset_string;

		if (empty($offset_string))
		{
			$zone_offset = $this->user->create_datetime()->getOffset();
			$offset_string = phpbb_format_timezone_offset($zone_offset);
		}

		return gmdate("Y-m-d\TH:i:s", $time + $zone_offset) . $offset_string;
	}

	/**
	 * Generate text content
	 *
	 * @param string $content is feed text content
	 * @param string $uid is bbcode_uid
	 * @param string $bitfield is bbcode bitfield
	 * @param int $options bbcode flag options
	 * @param int $forum_id is the forum id
	 * @param array $post_attachments is an array containing the attachments and their respective info
	 * @return string the html content to be printed for the feed
	 */
	public function generate_content($content, $uid, $bitfield, $options, $forum_id, $post_attachments)
	{
		if (empty($content))
		{
			return '';
		}

		// Setup our own quote_helper to remove all attributes from quotes
		$this->renderer->configure_quote_helper($this->container->get('feed.quote_helper'));

		$this->renderer->set_smilies_path($this->get_board_url() . '/' . $this->config['smilies_path']);

		$content = generate_text_for_display($content, $uid, $bitfield, $options);

		// Remove "Select all" link and mouse events
		$content = str_replace('<a href="#" onclick="selectCode(this); return false;">' . $this->user->lang['SELECT_ALL_CODE'] . '</a>', '', $content);
		$content = preg_replace('#(onkeypress|onclick)="(.*?)"#si', '', $content);

		// Firefox does not support CSS for feeds, though

		// Remove font sizes
		//	$content = preg_replace('#<span style="font-size: [0-9]+%; line-height: [0-9]+%;">([^>]+)</span>#iU', '\1', $content);

		// Make text strong :P
		//	$content = preg_replace('#<span style="font-weight: bold?">(.*?)</span>#iU', '<strong>\1</strong>', $content);

		// Italic
		//	$content = preg_replace('#<span style="font-style: italic?">([^<]+)</span>#iU', '<em>\1</em>', $content);

		// Underline
		//	$content = preg_replace('#<span style="text-decoration: underline?">([^<]+)</span>#iU', '<u>\1</u>', $content);

		// Remove embed Windows Media Streams
		$content	= preg_replace( '#<\!--\[if \!IE\]>-->([^[]+)<\!--<!\[endif\]-->#si', '', $content);

		// Do not use &lt; and &gt;, because we want to retain code contained in [code][/code]

		// Remove embed and objects
		$content	= preg_replace( '#<(object|embed)(.*?) (value|src)=(.*?) ([^[]+)(object|embed)>#si',' <a href=$4 target="_blank"><strong>$1</strong></a> ',$content);

		// Remove some specials html tag, because somewhere there are a mod to allow html tags ;)
		$content	= preg_replace( '#<(script|iframe)([^[]+)\1>#siU', ' <strong>$1</strong> ', $content);

		// Parse inline images to display with the feed
		if (!empty($post_attachments))
		{
			$update_count = array();
			parse_attachments($forum_id, $content, $post_attachments, $update_count);
			$content .= implode('<br />', $post_attachments);

			// Convert attachments' relative path to absolute path
			$content = str_replace($this->path_helper->get_web_root_path() . 'download/file.' . $this->path_helper->get_php_ext(), $this->get_board_url() . '/download/file.' . $this->path_helper->get_php_ext(), $content);
		}

		// Remove Comments from inline attachments [ia]
		$content = preg_replace('#<dd>(.*?)</dd>#','',$content);

		// Replace some entities with their unicode counterpart
		$entities = array(
			'&nbsp;'	=> "\xC2\xA0",
			'&bull;'	=> "\xE2\x80\xA2",
			'&middot;'	=> "\xC2\xB7",
			'&copy;'	=> "\xC2\xA9",
		);

		$content = str_replace(array_keys($entities), array_values($entities), $content);

		// Remove CDATA blocks. ;)
		$content = preg_replace('#\<\!\[CDATA\[(.*?)\]\]\>#s', '', $content);

		// Other control characters
		$content = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $content);

		return $content;
	}
}
